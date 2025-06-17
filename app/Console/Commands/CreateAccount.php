<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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

        $password = $this->secret('Придумайте пароль');

        $validator = Validator::make(['password' => $password], [
            'password' => [
                'required',
                'string',
                'min:4',
                'max:6'
            ],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }
            return Command::FAILURE;
        }

        // Создание аккаунта
        $account = Account::create([
            'company_id' => $company->id,
            'name' => $name,
            'password' => Hash::make($password),
        ]);

        $this->info("Аккаунт '{$account->name}' успешно создан для компании '{$company->name}'.");
        return Command::SUCCESS;
    }
}
