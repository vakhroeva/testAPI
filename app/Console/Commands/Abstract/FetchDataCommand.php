<?php

namespace App\Console\Commands\Abstract;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

abstract class FetchDataCommand extends Command
{
    protected string $endpoint;
    protected string $modelClass;

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
            'dateFrom' => $this->option('dateFrom') ?? Carbon::createFromTimestamp(0)->toDateString(),
            'dateTo' => $this->option('dateTo') ?? Carbon::createFromFormat('Y-m-d', '9999-12-31')->toDateString(),
            'limit' => $this->option('limit') ?? 500,
            'key' => config('services.api.key'),
        ]);
    }

    protected function areCorrectDates(): bool
    {
        $dateFrom = $this->option('dateFrom');
        $dateTo = $this->option('dateTo');

        $minDate = Carbon::createFromTimestamp(0);

        if (($dateFrom && !$this->isValidDate($dateFrom)) ||
            ($dateTo && !$this->isValidDate($dateTo)) ||
            ($dateFrom && $dateFrom < $minDate->toDateString()) ||
            ($dateTo && $dateFrom && $dateTo < $dateFrom)) {

            $this->error('Даты должны быть указаны в формате Y-m-d, dateTo не может быть раньше dateFrom');
            return false;
        }

        return true;
    }

    public function handle()
    {
        $limit = (int) $this->option('limit');

        if (!$this->areCorrectDates()) return Command::FAILURE;

        if ($limit < 1 || $limit > 500) {
            $this->error('Лимит должен быть от 1 до 500.');
            return Command::FAILURE;
        }

        $this->info("Загружаем данные из {$this->endpoint}...");
        $page = 1;
        $written_lines = 0;
        $total_lines = 0;

        try {
            do {
                $queryParams = $this->buildQueryParams(['page' => $page]);
                $this->info('Параметры запроса: ' . json_encode($queryParams));
                $response = Http::get(config('services.api.base_url') . $this->endpoint, $queryParams);
                $data = $response->json('data');
                $count = count($data);

                $total_lines += $count;

                foreach ($data as $item) {
                    try {
                        $this->modelClass::create($item);
                        $written_lines ++;
                    } catch (\Exception $e) {
                        $this->error("Ошибка при создании записи: " . $e->getMessage());
                        $this->error("Данные: " . json_encode($item));
                    }
                }

                $page++;

            } while ($count === $limit);
        } catch (\Exception $e) {
            $this->error('Произошла ошибка: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info("Загрузка окончена. Получено записей: $total_lines. Добавлено записей: $written_lines.");
        return Command::SUCCESS;
    }
}
