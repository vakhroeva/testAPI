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
    {--dateFrom= : Начальная дата Y-m-d (по умолчанию: 1970-01-01)}
    {--dateTo=   : Конечная дата Y-m-d (по умолчанию: 9999-12-31)}
    {--limit=500 : Количество записей (максимум: 500, минимум: 1, по умолчанию: 500)}
```

#### Заказы (таблица orders)

```bash
php artisan fetch:orders
    {--dateFrom= : Начальная дата Y-m-d (по умолчанию: 1970-01-01)}
    {--dateTo=   : Конечная дата Y-m-d (по умолчанию: 9999-12-31)}
    {--limit=500 : Количество записей (максимум: 500, минимум: 1, по умолчанию: 500)}
```

#### Склады (таблица stocks)
	
```bash
php artisan fetch:stocks
    {--dateFrom= : Дата выгрузки Y-m-d (необязательно, игнорируется)}
    {--limit=500 : Количество записей (максимум: 500, минимум: 1, по умолчанию: 500)}
```

#### Доходы (таблица incomes)

```bash
php artisan fetch:incomes
    {--dateFrom= : Начальная дата Y-m-d (по умолчанию: 1970-01-01)}
    {--dateTo=   : Конечная дата Y-m-d (по умолчанию: 9999-12-31)}
    {--limit=500 : Количество записей (максимум: 500, минимум: 1, по умолчанию: 500)}
```