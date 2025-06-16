<?php

namespace App\Console\Commands;

use App\Console\Commands\Abstract\CreateDataCommand;
use App\Models\TokenType;
use Illuminate\Console\Command;

class CreateTokenType extends CreateDataCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:token-type {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создает новый тип токена по имени';

    protected string $modelClass = TokenType::class;
    protected string $entity = 'Тип токена';
}
