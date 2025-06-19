<?php

namespace App\Console\Commands;

use App\Models\ApiService;
use App\Models\TokenType;
use Illuminate\Console\Command;

class CreateApiService extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'create:api-service {name} {--tokens=}';

    /**
     * The console command description.
     */
    protected $description = 'Создает новый апи сервис по имени, требуется указать разрешенные типы токенов';

    public function handle()
    {
        $name = $this->argument('name');
        $tokens = $this->option('tokens');

        if (ApiService::where('name', $name)->exists()) {
            $this->error("Апи сервис с именем '{$name}' уже существует.");
            return Command::FAILURE;
        }

        if (!$tokens) {
            $this->error("При создании апи сервиса необходимо указать разрешенные типы токенов.");
            return Command::FAILURE;
        }

        $tokenItems = explode(',', $tokens);
        $tokenItems = array_map('trim', $tokenItems);

        $isAllNumeric = ctype_digit(implode('', $tokenItems));

        if ($isAllNumeric) {
            // Обработка по ID
            $tokenTypes = TokenType::whereIn('id', $tokenItems)->get();
            $tokenTypeIds = $tokenTypes->pluck('id')->toArray();
            $notFound = array_diff($tokenItems, $tokenTypeIds);

            if (!empty($notFound)) {
                $this->error('Ошибка! Следующие ID токенов не найдены: ' . implode(', ', $notFound));
                return Command::FAILURE;
            }

            $displayNames = $tokenTypes->pluck('name')->toArray();

        } else {
            // Обработка по именам
            $tokenTypes = TokenType::whereIn('name', $tokenItems)->get();
            $foundNames = $tokenTypes->pluck('name')->toArray();
            $notFound = array_diff($tokenItems, $foundNames);

            if (!empty($notFound)) {
                $this->error('Ошибка! Следующие типы токенов не найдены: ' . implode(', ', $notFound));
                return Command::FAILURE;
            }

            $tokenTypeIds = $tokenTypes->pluck('id')->toArray();
            $displayNames = $foundNames;
        }

        $apiService = ApiService::create(['name' => $name]);
        $apiService->allowedApiTokenTypes()->attach($tokenTypeIds);

        $this->info("Создание Апи сервиса '{$name}' успешно завершено.");
        $this->info("Разрешенные типы токенов: " . implode(', ', $displayNames));

        return Command::SUCCESS;
    }
}
