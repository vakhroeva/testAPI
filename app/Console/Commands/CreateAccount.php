<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Company;
use Illuminate\Console\Command;

class CreateAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:account {name} {company}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создает новый аккаунт для указанной компании(ее название или айди)';

    public function handle()
    {
        $companyInput = $this->argument('company');
        $name = $this->argument('name');

        // Проверка: существует ли компания
        $company = is_numeric($companyInput)
            ? Company::find($companyInput)
            : Company::where('name', $companyInput)->first();

        if (!$company) {
            $this->error("Компания '{$companyInput}' не найдена.");
            return Command::FAILURE;
        }

        // Проверка: есть ли уже аккаунт с таким именем у этой компании
        $exists = Account::where('company_id', $company->id)
            ->where('name', $name)
            ->exists();

        if ($exists) {
            $this->error("Аккаунт '{$name}' уже существует в компании '{$company->name}'.");
            return Command::FAILURE;
        }

        // Создание аккаунта
        $account = Account::create([
            'company_id' => $company->id,
            'name' => $name,
        ]);

        $this->info("Аккаунт '{$account->name}' успешно создан для компании '{$company->name}'.");
        return Command::SUCCESS;
    }
}
