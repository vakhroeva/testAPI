<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>


### Тестовое API на фреймворке Laravel

#### Реализована выдача сущностей 
- Продажи 
- Заказы
- Склады
- Доходы

### Основное

- Авторизация происходит посредством передачи секретного токена в строке запроса с параметром **key**
- Формат даты **Y-m-d**
- Формат дата + время **Y-m-d H:i:s**
- Все эндпоинты выдают ответ в **json** с пагинацией
- Лимит на количество возвращаемых записей за запрос - **500** (по умолчанию выдает по **500** строк)
- Если нужно меньше, то передавать в параметре **limit** в строке запроса
- Перебор данных происходит по параметру **page** в строке запроса

_**Пример запроса:** /api/orders?dateFrom={Дата выгрузки ОТ}&dateTo={Дата выгрузки ДО}}&page={номер страницы}&limit={количество записей}key={ваш токен}_

#### Продажи

Параметры:

- dateFrom
- dateTo

`Путь: GET /api/sales`

#### Заказы

Параметры:

- dateFrom
- dateTo

`Путь: GET /api/orders`

#### Склады 
_Выгрузка только за текущий день_

Параметры:

- dateFrom

`Путь: GET /api/stocks`

#### Доходы

Параметры:

- dateFrom
- dateTo

`Путь: GET /api/incomes`

`Стек: docker/docker-compose, php 8.1, Laravel 8, Laravel Octane`

[Ссылка на коллекцию Postman](https://www.postman.com/cy322666/workspace/app-api-test/overview)


### Реализация

Необходимые преднастройки: 

.env файл должен содержать следующие значения: 

```
API_KEY={key}
WB_API_BASE_URL={host}

DB_CONNECTION=mysql
DB_HOST=mysql-vakhroeva.j.aivencloud.com
DB_PORT=11002
DB_DATABASE=defaultdb
DB_USERNAME=avnadmin
DB_PASSWORD="AVNS_hvbivhCRYsp_4iHrBi-"
```


Данные загружаются посредством использования консольных команд Artisan со следующими сигнатурами:

#### Продажи (таблица sales)

```bash
php artisan fetch:sales
	{--accountID= : Идентификатор аккаунта, обязателен для ручного запуска}
	{--dateFrom= : Начальная дата Y-m-d (по умолчанию последняя на основе БД)}
	{--dateTo= : Конечная дата Y-m-d (по умолчанию: текущая дата)}
	{--limit=500 : Количество записей (максимум: 500, минимум: 1, по умолчанию: 500)}
	{--cron : Запуск из расписания}
```

#### Заказы (таблица orders)

```bash
php artisan fetch:orders
	{--accountID= : Идентификатор аккаунта, обязателен для ручного запуска}
	{--dateFrom= : Начальная дата Y-m-d (по умолчанию последняя на основе БД)}
	{--dateTo= : Конечная дата Y-m-d (по умолчанию: текущая дата)}
	{--limit=500 : Количество записей (максимум: 500, минимум: 1, по умолчанию: 500)}
	{--cron : Запуск из расписания}
```

#### Склады (таблица stocks)
	
```bash
php artisan fetch:stocks
	{--accountID= : Идентификатор аккаунта, обязателен для ручного запуска}
	{--dateFrom= : Дата выгрузки Y-m-d (необязательно, игнорируется)}
	{--limit=500 : Количество записей (максимум: 500, минимум: 1, по умолчанию: 500)}
	{--cron : Запуск из расписания}
```

#### Доходы (таблица incomes)

```bash
php artisan fetch:incomes
	{--accountID= : Идентификатор аккаунта, обязателен для ручного запуска}
	{--dateFrom= : Начальная дата Y-m-d (по умолчанию последняя на основе БД)}
	{--dateTo= : Конечная дата Y-m-d (по умолчанию: текущая дата)}
	{--limit=500 : Количество записей (максимум: 500, минимум: 1, по умолчанию: 500)}
	{--cron : Запуск из расписания}
```

### Задание на тестовую неделю

#### Этап 1

Нужно доработать приложение, которое Вы написали:
- приложение должно быть развернуто с помощью docker-compose с 2 сервисами: php и mysql,
	- *Скопируйте файл .env.example и переименуйте его в .env:*
		```bash
		cp .env.example .env
		```
		
	- *Обратите внимание! В файл .env необходимо добавить следующие переменные*
		```bash
		API_KEY={key}
		WB_API_BASE_URL={host}
		```

	- *Данные для создания бд в докере будут использоваться из файла .env*

	- *Сборка и запуск контейнеров:*
		```bash
		docker-compose up --build -d
		```

	- *Просмотр логов приложения:*
		```bash
		docker-compose logs -f app
		```

	- *Подключение к контейнеру(теперь можно вводить консольные команды):*
		```bash
		docker-compose exec app sh
		```

	- *Остановка и удаление контейнеров:*
		```bash
		docker-compose down -v
		```
	
- используйте нестандартный порт MySQL (не 3306),
- нужно организовать ежедневное обновление данных дважды в день,
	- *Реализовано посредством cron*
	- *Флаг ' --cron' обязательно должен быть передан при запуске по расписанию, другие флаги отсутствуют*
- предусмотреть в методах ошибки типа 'Too many requests' и их преодоление,
	- *Если отловили ошибку 429 ставим задержку и повторяем попытку, задержку умножаем на 2 при следующей провальной попытке*
	- *Всего допускается 5 провальных попыток подряд*
- сделать вывод отладочной информации в консоль,
- в бд организовать структуру для хранения данных: компании, у неё может быть несколько  аккаунтов, у каждого аккаунта может быть один токен одного типа для одного апи сервиса,
	- *Организованная структура для удобства визуализирована в файле \storage\app\docs\DB_structure.png*
- токены могут быть разного типа(bearer,api-key,login and password и тд), так-же должны быть апи сервисы, для которых предназначены токены, у каждого апи сервиса свой набор типов токенов,
- написать команды для добавления через консоль новых: компании, аккаунтов, апи токена, апи сервиса, типа токена,

	- *Создание новой компании по имени*
		```bash
		php artisan create:company {name}
		```
		
	- *Создание нового аккаунта для указанной компании(ее название или айди)*
		```bash
		php artisan create:account {name} {company}
		```
		
	- *Создание нового типа токена по имени*
		```bash
		php artisan create:token-type {name}
		```
		
	- *Создание нового апи сервиса по имени + разрешенные токены, пример: php artisan create:api-service WB --tokens="api-key,login and password"*
		```bash
		php artisan create:api-service {name} {--tokens=}
		```

	- *Создание токена определенного типа(имя или айди) для аккаунта(имя или айди), сервиса(имя или айди)*
		```bash
		php artisan create:api-token {account} {service} {type} {token}
		```

	- *Примечание: если значение состоит из нескольких слов, необходимо заключить его в кавычки, например, php artisan create:api-service "Weather API"*

- в методах получения данных предусмотреть использование разных аккаунтов,
	- *При ручном запуске необходимо указывать accountID*
	- *Подтвердить аккаунт вводом пароля*
	- *Полученные данные сохраняются в таблице, также в поле account_id записывается соответствующий accountID*
- в таблицы данных добавить поле account_id, так же предотвратить затирание данных из разных аккаунтов,
	- *Проведены миграции на добавление поля account_id к таблицам*
	- *nullable чтобы не терять записанные данные, а также потому что запуск по расписанию не идентифицирует пользователя*
- предусмотреть получение только свежих данных по полю date
	- *При ручном запуске получаем значение поля date последней записи с соответствующим account_id*
	- *Запрос к апи будет делаться от последнего date в таблице(если таблица пуста - 1970-01-01) до текущей даты*
	- *При запуске команды по расписанию аналогичные действия производятся для каждого аккаунта*
	- *Поскольку нет информации об уникальности полей, чтобы не дублировать записи, этот промежуток будет удален из бд(с учетом пользователя). Возможную потерю данных при возникновении ошибок предотвратит использование транзакции.*

#### Этап 2

После проверки залить приложение на сервер(доступы предоставим позже) и запустить.