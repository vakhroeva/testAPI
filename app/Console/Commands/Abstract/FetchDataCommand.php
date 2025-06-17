<?php

namespace App\Console\Commands\Abstract;

use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

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

    protected function getLatestDate($accountID = null)
    {
        $model = app($this->modelClass);

        if ($accountID){
            $lastRecord = $model::latest('date')->where('account_id', $accountID)->first();
        } else {
            $lastRecord = $model::whereNull('account_id')->latest('date')->first();
        }

        if (!$lastRecord) {
            $this->warn("Последняя дата не найдена, будет использована дата по умолчанию.");
            return Carbon::createFromTimestamp(0)->format('Y-m-d');
        }

        return Carbon::parse($lastRecord->date)->format('Y-m-d');
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

    protected function purgeExistingData($accountID): void
    {
        $model = app($this->modelClass);

        if ($accountID) {
            $this->info("Удаляем старые записи с {$this->resolvedDateFrom} по {$this->resolvedDateTo} пользователя с ID {$accountID}...");
            $deleted = $model::whereBetween('date', [$this->resolvedDateFrom, $this->resolvedDateTo])
                ->where('account_id', $accountID)
                ->delete();
        } else {
            $this->info("Удаляем старые записи с {$this->resolvedDateFrom} по {$this->resolvedDateTo}...");
            $deleted = $model::whereBetween('date', [$this->resolvedDateFrom, $this->resolvedDateTo])
                ->whereNull('account_id')
                ->delete();
        }

        $this->info("Удалено записей: {$deleted}");
    }

    protected function prepareDates(): bool
    {
        $isCron = $this->option('cron');
        $dateFrom = $this->option('dateFrom');
        $dateTo = $this->option('dateTo');
        $accountID = $this->option('accountID') ?? null;

        if (!$isCron) {
            $this->resolvedDateFrom = $dateFrom ?: $this->getLatestDate($accountID);
            $this->resolvedDateTo = $dateTo ?: now()->format('Y-m-d');

            // Если даты указаны из консоли, должны проверить, что они корректны
            return $this->areCorrectDates();
        } else {
            $this->resolvedDateFrom = $dateFrom ?: $this->getLatestDate();
            $this->resolvedDateTo = $dateTo ?: now()->format('Y-m-d');
            return true;
        }
    }

    protected function isReallyUser($accountID): bool{
        if (!$accountID) {
            $this->error("Флаг --accountID при ручном запуске обязателен.");
            return false;
        }

        $account = Account::find($accountID);

        if (!$account) {
            $this->error("Аккаунт с ID {$accountID} не найден.");
            return false;
        }

        $password = $this->secret('Введите пароль');

        if (!Hash::check($password, $account->password)) {
            $this->error('Пароль не верный. Выполнение команды запрещено');
            return false;
        }

        return true;
    }

    public function handle()
    {
        // проверка доступа
        $accountID = $this->option('accountID') ?? null;

        if ($this->option('cron')) {
            if ($accountID) {
                $this->error('С опцией --cron недопустимо использовать --accountID');
                return Command::FAILURE;
            }
        } else {
            if (! $this->isReallyUser($accountID)){
                return Command::FAILURE;
            }
        }

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
            DB::transaction(function () use (&$written_lines, &$total_lines, &$page, $limit, &$retryDelaySeconds, $maxRetries, $accountID) {
                $this->purgeExistingData($accountID);

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
                            if ($accountID) {
                                $item['account_id'] = (int) $accountID;
                            }
                            $created = $this->modelClass::create($item);
                            $written_lines++;

                            //$this->info('Создана запись: ' . $created->toJson(JSON_PRETTY_PRINT));

                            //throw new \Exception("Тестовая ошибка для проверки отката транзакции");
                        } catch (\Exception $e) {
                            $this->error("Ошибка при создании записи: " . $e->getMessage());
                            $this->error("Данные: " . json_encode($item));
                            throw $e; // проброс наружу - вызовет откат, иначе - не даст откатиться транзакции
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
