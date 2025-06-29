<?php

namespace App\Console\Commands;

use App\Console\Commands\Abstract\FetchDataCommand;
use App\Models\Stock;
use Carbon\Carbon;
use Symfony\Component\Console\Command\Command;

class FetchStocks extends FetchDataCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:stocks
        {--accountID= : Идентификатор аккаунта, обязателен для ручного запуска}
        {--dateFrom= : Дата выгрузки Y-m-d (необязательно, игнорируется)}
        {--limit=500 : Количество записей (максимум: 500, минимум: 1, по умолчанию: 500)}
        {--cron : Запуск из расписания}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загружает данные остатков (stocks) из API';

    protected string $endpoint = '/api/stocks';
    protected string $modelClass = Stock::class;


    protected function buildQueryParams(array $base = []): array
    {
        return array_merge($base, [
            'dateFrom' => $this->resolvedDateFrom,
            'limit' => $this->option('limit') ?? 500,
            'key' => config('services.api.key'),
        ]);
    }

    protected function purgeExistingData($accountID): void
    {
        $model = app($this->modelClass);
        $today = now()->toDateString();

        $this->info("Удаляем записи за {$today} пользователя с ID {$accountID}...");
        $deleted = $model::whereDate('date', $today)
            ->where('account_id', $accountID)
            ->delete();

        $this->info("Удалено записей: {$deleted}");
    }

    protected function prepareDates($accountID): bool
    {
        $this->resolvedDateFrom = now()->toDateString();
        if (filled($this->option('dateFrom'))) {
            $this->warn('Обратите внимание! Параметр dateFrom будет проигнорирован');
        }
        return true;
    }
}
