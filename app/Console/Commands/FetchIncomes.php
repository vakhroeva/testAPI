<?php

namespace App\Console\Commands;

use App\Models\Income;
use App\Console\Commands\Abstract\FetchDataCommand;

class FetchIncomes extends FetchDataCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:incomes
        {--dateFrom= : Начальная дата Y-m-d (по умолчанию: 1970-01-01)}
        {--dateTo= : Конечная дата Y-m-d (по умолчанию: текущая дата)}
        {--limit=500 : Количество записей (максимум: 500, минимум: 1, по умолчанию: 500)}
        {--cron : Запуск из расписания}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загружает данные доходов (incomes) из API';

    protected string $endpoint = '/api/incomes';
    protected string $modelClass = Income::class;
}
