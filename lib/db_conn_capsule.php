<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/makemvcss
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2020-09-05
*/

// https://github.com/illuminate/database
// https://medium.com/@kshitij206/use-eloquent-without-laravel-7e1c73d79977
// https://laravel.com/docs/5.0/schema
// https://www.amitmerchant.com/how-to-utilize-capsule-use-eloquent-orm-outside-laravel/
// composer require "illuminate/database"
// composer require "illuminate/events"
// composer require "illuminate/support"

use Lib\DataManager;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\DB;

$capsule = new Capsule;

if (input_env("DB_CONNECTION") == "sqlite") {
    $database = input_env("DB_DATABASE", __DIR__ . '/../database') . ".sqlite";
    if (DataManager::exist($database) === false) {
        DataManager::fileWrite($database);
    }
    $capsule->addConnection([
        'driver'   => input_env("DB_CONNECTION"),
        'database' => $database,
        'prefix'   => input_env("DB_PREFIX", "")
    ]); 
} else {
    $capsule->addConnection([
        'driver'    => input_env("DB_CONNECTION"),
        'host'      => input_env("DB_HOST"),
        'port'      => input_env("DB_PORT"),
        'database'  => input_env("DB_DATABASE"),
        'username'  => input_env("DB_USERNAME"),
        'password'  => input_env("DB_PASSWORD"),
        'charset'   => input_env("DB_CHARSET"),
        'collation' => input_env("DB_CHARSET_COLLATE"),
        'prefix'    => input_env("DB_PREFIX", "")
    ]);
}

// Set the event dispatcher used by Eloquent models... (optional)
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
$capsule->setEventDispatcher(new Dispatcher(new Container));

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();

DB::setFacadeApplication(new Container());
DB::swap($capsule->getDatabaseManager());

/*function DB() {
    global $capsule;
    return $capsule;
}*/

function path_schema_apply(string $path) {
    if (DataManager::exist($path) == "FILE") {
        $path = realpath($path);
        require_once $path;
        echo "\r\nSchema Apply Success: app/Schema/" . pathinfo($path)['basename'] . "\r\n";
    } else {
        echo "\r\nSchema Apply Error: app/Schema/" . pathinfo($path)['basename'] . "\r\n";
    }
}

function db_schemas_apply(string $nameFile)
{     
    $nameFile = strtolower($nameFile);
    $base = __DIR__ . "/../app/Schema/";
    if ($nameFile == "-a" || $nameFile == "--all") {
        $schemas = DataManager::folderScan(realpath($base));
        foreach ($schemas as $key => $schema) {
            $index = strpos($schema["name"], "s_capsule_schema.php");
            if ($schema["type"] == "FILE" && $index !== false && $index > 0) {
                path_schema_apply($schema["path"]);
            }                
        }
    } else {
        $path = "${base}${nameFile}s_capsule_schema.php";
        path_schema_apply($path);
    }
}
