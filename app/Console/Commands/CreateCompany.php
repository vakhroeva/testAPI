<?php

namespace App\Console\Commands;

use App\Console\Commands\Abstract\CreateDataCommand;
use App\Models\Company;
use Illuminate\Console\Command;

class CreateCompany extends CreateDataCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:company {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создает новую компанию по имени';

    protected string $modelClass = Company::class;
    protected string $entity = 'Компания';
}
