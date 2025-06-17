<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Console\Commands\Abstract\FetchDataCommand;

class FetchOrders extends FetchDataCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:orders
        {--accountID= : Идентификатор аккаунта, обязателен для ручного запуска}
        {--dateFrom= : Начальная дата Y-m-d (по умолчанию последняя на основе БД)}
        {--dateTo= : Конечная дата Y-m-d (по умолчанию: текущая дата)}
        {--limit=500 : Количество записей (максимум: 500, минимум: 1, по умолчанию: 500)}
        {--cron : Запуск из расписания}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загружает данные заказов (orders) из API';

    protected string $endpoint = '/api/orders';
    protected string $modelClass = Order::class;
}
