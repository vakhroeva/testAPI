<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\ApiService;
use App\Models\ApiToken;
use App\Models\TokenType;
use Illuminate\Console\Command;

class CreateApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:api-token {account} {service} {type} {token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создает апи токен определенного типа для аккаунта, сервиса';

    public function handle()
    {
        $accountInput = $this->argument('account');
        $serviceInput = $this->argument('service');
        $typeInput = $this->argument('type');
        $tokenValue = $this->argument('token');

        // Найти аккаунт по имени или айди
        $account = is_numeric($accountInput)
            ? Account::find($accountInput)
            : Account::where('name', $accountInput)->first();

        if (!$account) {
            $this->error("Аккаунт '{$accountInput}' не найден.");
            return Command::FAILURE;
        }

        // Найти API-сервис по имени или айди
        $service = is_numeric($serviceInput)
            ? ApiService::find($serviceInput)
            : ApiService::where('name', $serviceInput)->first();

        if (!$service) {
            $this->error("API-сервис '{$serviceInput}' не найден.");
            return Command::FAILURE;
        }

        // Найти тип токена по имени или айди
        $tokenType = is_numeric($typeInput)
            ? TokenType::find($typeInput)
            : TokenType::where('name', $typeInput)->first();

        if (!$tokenType) {
            $this->error("Тип токена '{$typeInput}' не найден.");
            return Command::FAILURE;
        }

        // уже есть токен этого типа для этого аккаунта и сервиса?
        $existing = ApiToken::where('account_id', $account->id)
            ->where('api_service_id', $service->id)
            ->where('token_type_id', $tokenType->id)
            ->first();

        if ($existing) {
            $this->error("Такой токен уже существует для данного аккаунта, сервиса и типа токена.");
            return Command::FAILURE;
        }

        // Создание токена
        $token = ApiToken::create([
            'account_id'     => $account->id,
            'api_service_id' => $service->id,
            'token_type_id'  => $tokenType->id,
            'token'          => $tokenValue,
        ]);

        $this->info("Токен успешно создан (ID: {$token->id}) для аккаунта '{$account->name}', сервиса '{$service->name}', типа '{$tokenType->name}'.");
        return Command::SUCCESS;
    }
}
