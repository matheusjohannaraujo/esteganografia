<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/makemvcss
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2020-09-05
*/

$vendor = __DIR__ . "/vendor/";
$autoload = $vendor . "autoload.php";

function config_init()
{
	ini_set("set_time_limit", 3600);
	ini_set("max_execution_time", 3600);
	ini_set("default_socket_timeout", 3600);
	ini_set("memory_limit", "6144M");
}

if ((($_GET["adm"] ?? null) == "unzip") && file_exists($vendor) && is_dir($vendor)) {
	config_init();
	require_once __DIR__ . "/lib/DataManager.php";
	Lib\DataManager::zipUnzipFolder("./vendor/", "unzip");
	die("End unzip");
}

if (!file_exists($autoload)) {
	config_init();
	shell_exec("composer dump-autoload");
	shell_exec("composer install");
}

if (file_exists($autoload)) {
	require_once $autoload;
	require_once __DIR__ . "/lib/config.php";
	require_once __DIR__ . "/app/web.php";
} else {
	die("<br><center># The `$autoload` not found. If you are reading this message, open a command prompt inside the project folder and run the command below:<hr><h1><b>composer install</b></h1></center>");
}
