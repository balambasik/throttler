Throttler - simple php library.

Designed to limit any action. Minimum configuration, maximum simplicity.

Works with MySql drivers and Memory.
Add your driver, implement DriverInterface.

**Installation**
```text
composer require balambasik/throttler
```

**One example of using**

```php
$driver = new MySQLDriver("localhost", "root", "", "throttler", "throttler");
$factory = new ThrottlerFactory($driver);

$registerThrottler = $factory
                ->id($_SERVER["REMOTE_ADDR"])
                ->tag("/api/register")
                ->waitSeconds(5)
                ->create();

// 1 request per 5 seconds per IP, per route - "/api/register"
if($registerThrottler->isLimit()){
    exit("Limit!")
} else {
  // handle request
}

// or -------------------------------------

$registerThrottler->isLimit(function(){
    exit("Limit!")
});

// handle request
```


**Multiple instances**

```php

// 1 request per 5 seconds per IP, per route - "/api/register"
$registerThrottler = $factory
                ->id($_SERVER["REMOTE_ADDR"])
                ->tag("/api/register")
                ->waitSeconds(5)
                ->create();

// 1 request per 1 minute per IP, per route - "/api/forgot_password"              
$forgotThrottler = $factory
                ->id($_SERVER["REMOTE_ADDR"])
                ->tag("/api/forgot_password")
                ->waitMinutes(1)
                ->create();


$registerThrottler->isLimit(function(){
    exit("Limit!")
});


$forgotThrottler->isLimit(function(){
    exit("Limit!")
});
```

**Manual mode**

By default, method **isLimit()** logs a hit and checks if the limit has been reached. You can separate these operations.

```php
// 1 request per 5 seconds per IP, per route - "/api/register"
$registerThrottler = $factory
                ->id($_SERVER["REMOTE_ADDR"])
                ->tag("/api/register")
                ->waitSeconds(5)
                ->createManualMode();

// set hit
$registerThrottler->hit();

// check limit
$registerThrottler->isLimit(function(){
    exit("Limit!")
});

```

The MySql driver needs a table.

```sql
CREATE TABLE IF NOT EXISTS `table_name` (
    `id` varchar(10),
    `tag` varchar(10),
    `wait` INT(11) UNSIGNED NOT NULL
    );

ALTER TABLE `table_name` ADD INDEX (`id`, `tag`);
```

Or call the **createTable()** method of the **MySqlDriver** object once

```php
$MySQLDriver = new MySqlDriver("localhost", "login", "password", "dbName", "tableName");
$MySQLDriver->createTable();

```
**InMemory driver**

The **InMemoryDriver** does not require any configuration. For obvious reasons, it can persist state across requests. Therefore, protecting routes with it is a bad idea. It is great for limiting operations within a single request.
```php
$driver = new InMemoryDriver();
$factory = new ThrottlerFactory($driver);
// ...
```


Licence - MIT.
