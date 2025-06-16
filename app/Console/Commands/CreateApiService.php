<?php

namespace App\Console\Commands;

use App\Console\Commands\Abstract\CreateDataCommand;
use App\Models\ApiService;
use Illuminate\Console\Command;

class CreateApiService extends CreateDataCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:api-service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создает новый апи сервис по имени';

    protected string $modelClass = ApiService::class;
    protected string $entity = 'Апи сервис';
}
