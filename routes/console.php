<?php

use App\Console\Commands\FetchIncomes;
use App\Console\Commands\FetchOrders;
use App\Console\Commands\FetchSales;
use App\Console\Commands\FetchStocks;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Schedule::command(FetchIncomes::class . ' --cron')->twiceDaily(8, 20);
Schedule::command(FetchOrders::class . ' --cron')->twiceDaily(8, 20);
Schedule::command(FetchSales::class . ' --cron')->twiceDaily(8, 20);
Schedule::command(FetchStocks::class . ' --cron')->twiceDaily(8, 20);
