<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Console\Commands\Abstract\FetchDataCommand;

class FetchSales extends FetchDataCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:sales
        {--dateFrom= : Начальная дата Y-m-d (по умолчанию: 1970-01-01)}
        {--dateTo= : Конечная дата Y-m-d (по умолчанию: текущая дата)}
        {--limit=500 : Количество записей (максимум: 500, минимум: 1, по умолчанию: 500)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загружает данные продаж (sales) из API';

    protected string $endpoint = '/api/sales';
    protected string $modelClass = Sale::class;
}
