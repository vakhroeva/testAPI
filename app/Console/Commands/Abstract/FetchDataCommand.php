<?php

namespace App\Console\Commands\Abstract;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Input\ArgvInput;

abstract class FetchDataCommand extends Command
{
    protected string $endpoint;
    protected string $modelClass;
    protected string $resolvedDateFrom;
    protected string $resolvedDateTo;
    protected string $resolvedLimit;

    protected function isValidDate(string $date): bool
    {
        try {
            $d = Carbon::createFromFormat('Y-m-d', $date);
            return $d && $d->format('Y-m-d') === $date;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function buildQueryParams(array $base = []): array
    {
        return array_merge($base, [
            'dateFrom' => $this->resolvedDateFrom,
            'dateTo' => $this->resolvedDateTo,
            'limit' => $this->resolvedLimit,
            'key' => config('services.api.key'),
        ]);
    }

    protected function getLatestDate()
    {
        $model = app($this->modelClass);
        $lastRecord = $model::latest('date')->first();

        if (!$lastRecord) {
            $this->warn("Последняя дата не найдена, будет использована дата по умолчанию.");
            return null;
        }

        return $lastRecord ? Carbon::parse($lastRecord->date) : null;
    }

    protected function areCorrectDates(): bool
    {
        $dateFrom = $this->resolvedDateFrom;
        $dateTo = $this->resolvedDateTo;

        $minDate = Carbon::createFromTimestamp(0);

        if (($dateFrom && !$this->isValidDate($dateFrom)) ||
            ($dateTo && !$this->isValidDate($dateTo)) ||
            ($dateFrom && $dateFrom < $minDate->format('Y-m-d')) ||
            ($dateTo && $dateFrom && $dateTo < $dateFrom)) {

            $this->error('Даты должны быть указаны в формате Y-m-d, dateTo не может быть раньше dateFrom');
            return false;
        }

        return true;
    }

    protected function purgeExistingData(): void
    {
        $this->info("Удаляем старые записи с {$this->resolvedDateFrom} по {$this->resolvedDateTo}...");

        $model = app($this->modelClass);
        $deleted = $model::whereBetween('date', [$this->resolvedDateFrom, $this->resolvedDateTo])->delete();

        $this->info("Удалено записей: {$deleted}");
    }

    protected function prepareDates() : bool
    {
        $isCron = $this->option('cron');
        $dateFrom = $this->option('dateFrom');
        $dateTo = $this->option('dateTo');

        if (!$isCron) {
            $this->resolvedDateFrom = $dateFrom ?: Carbon::createFromTimestamp(0)->format('Y-m-d');
            $this->resolvedDateTo = $dateTo ?: now()->format('Y-m-d');

            // Если даты указаны из консоли, должны проверить, что они корректны
            return $this->areCorrectDates();
        } else {
            $latest = $this->getLatestDate();
            $this->resolvedDateFrom = $latest ? $latest->format('Y-m-d') : Carbon::createFromTimestamp(0)->format('Y-m-d');
            $this->resolvedDateTo = now()->format('Y-m-d');
            return true;
        }
    }

    public function handle()
    {
        if (!$this->prepareDates()) {
            return Command::FAILURE;
        }

        $limit = (int) $this->option('limit');

        if ($limit < 1 || $limit > 500) {
            $this->error('Лимит должен быть от 1 до 500.');
            return Command::FAILURE;
        }

        $this->resolvedLimit = $limit;

        $this->info("Загружаем данные из {$this->endpoint}...");
        $page = 1;
        $written_lines = 0;
        $total_lines = 0;

        $maxRetries = 5;          // Максимум попыток при ошибке 429
        $retryDelaySeconds = 1;   // Начальная задержка для повторов (в секундах)

        try {
            DB::transaction(function () use (&$written_lines, &$total_lines, &$page, $limit, &$retryDelaySeconds, $maxRetries) {
                $this->purgeExistingData();

                //throw new \Exception("Тестовая ошибка для проверки отката транзакции");

                do {
                    $queryParams = $this->buildQueryParams(['page' => $page]);
                    $this->info('Параметры запроса: ' . json_encode($queryParams));

                    $attempt = 0;
                    do {
                        $response = Http::get(config('services.api.base_url') . $this->endpoint, $queryParams);

                        if ($response->status() == 429) {
                            $attempt++;
                            if ($attempt > $maxRetries) {
                                throw new \Exception('Превышено количество попыток из-за Too Many Requests');
                            }
                            $this->warn("Ошибка 429 Too Many Requests. Попытка $attempt из $maxRetries. Ждем секунд: {$retryDelaySeconds}...");
                            sleep($retryDelaySeconds);
                            $retryDelaySeconds *= 2;
                        } else {
                            break; // Успешный ответ или другая ошибка, выходим из цикла повторов
                        }
                    } while (true);

                    if (!$response->successful()) {
                        throw new \Exception("Ошибка HTTP: " . $response->status());
                    }

                    $data = $response->json('data');
                    $count = count($data);
                    $this->info('Ответ успешно получен');

                    $total_lines += $count;

                    foreach ($data as $item) {
                        try {
                            $this->modelClass::create($item);
                            $written_lines++;

                            //throw new \Exception("Тестовая ошибка для проверки отката транзакции");
                        } catch (\Exception $e) {

                            if ($this->isCritical($e)) {
                                throw $e; // проброс наружу - вызовет откат, иначе - не даст откатиться транзакции
                            }

                            $this->error("Ошибка при создании записи: " . $e->getMessage());
                            $this->error("Данные: " . json_encode($item));
                        }
                    }

                    $page++;

                    $retryDelaySeconds = 1;

                } while ($count === $limit);
            });

        } catch (\Exception $e) {
            $this->error('Произошла ошибка: ' . $e->getMessage());
            $this->warn('Транзакция откатилась. Изменения не были сохранены.');
            return Command::FAILURE;
        }

        $this->info("Загрузка окончена. Получено записей: $total_lines. Добавлено записей: $written_lines.");
        return Command::SUCCESS;

    }
}
