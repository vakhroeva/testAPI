<?php

namespace App\Console\Commands\Abstract;

use Illuminate\Console\Command;

abstract class CreateDataCommand extends Command
{
    protected string $modelClass;
    protected string $entity;

    public function handle()
    {
        $name = $this->argument('name');

        if ($this->modelClass::where('name', $name)->exists()) {
            $this->error("$this->entity с именем '{$name}' уже существует.");
            return Command::FAILURE;
        }

        $this->modelClass::create(['name' => $name]);

        $this->info("Создание $this->entity '{$name}' успешно завершено.");
        return Command::SUCCESS;
    }
}
