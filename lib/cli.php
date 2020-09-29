<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/makemvcss
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2020-09-23
*/

define("CLI", true);

require_once __DIR__ . "/../vendor/autoload.php";

use Lib\DataManager;

$BASE_DIR = realpath(__DIR__ . "/../");
define("__BASE_DIR__", $BASE_DIR);

function fun_routes(string $method = ""){
    $routes = file_get_contents("http://localhost/"  . pathinfo(__BASE_DIR__)["basename"] . "/routes/all/json/$method");
    $routes = (array) json_decode($routes, true);
    foreach ($routes as $key => $route) {
        echo " ---------------------------------------------------------------------------------------------------------------------------------------------------\r\n";
        echo "\r\n method = ", $route["method"], " | path = ", $route["path"], " | action = ", $route["action"], " | name = ", $route["name"], " | csrf = ", $route["csrf"], " | jwt = ", $route["jwt"], "\r\n\r\n";
    }
    echo " ---------------------------------------------------------------------------------------------------------------------------------------------------
    ";
}

function fun_test_route(string $name, string $test_md5, string $uri){
    $page = @file_get_contents($uri);
    $md5 = md5($page);
    echo " ", ($test_md5 == $md5) ? "OK" : "FAIL", " | $md5 | $name | $uri\r\n";
}

function fun_test_routes(){
    $baseDomain = "http://localhost/" . pathinfo(__BASE_DIR__)["basename"];
    echo "\r\n";
    fun_test_route("/.env", "d41d8cd98f00b204e9800998ecf8427e", $baseDomain . "/.env");
    echo "\r\n";
    fun_test_route("/storage/text.txt", "d41d8cd98f00b204e9800998ecf8427e", $baseDomain . "/storage/text.txt");
    echo "\r\n";
    fun_test_route("/js/index.js", "63862b643614e5c5ac53595957b99cff", $baseDomain . "/js/index.js");
    echo "\r\n";
    fun_test_route("/public/js/index.js", "63862b643614e5c5ac53595957b99cff", $baseDomain . "/public/js/index.js");
    echo "\r\n";
    fun_test_route("Home", "037f6c5ed3861233d54ee21a64fcf12a", $baseDomain . "/");
    echo "\r\n";
    fun_test_route("Route 1", "099fa973fbc67aa1efd6b7e81263cdbe", $baseDomain . "/contact");
    echo "\r\n";
    fun_test_route("Route 2", "d8d7ea26d7e43a05b42cb6347500f356", $baseDomain . "/template");
    echo "\r\n";
    fun_test_route("Route 3", "85814b01e71a1d8818eecdab00796817", $baseDomain . "/json");
    echo "\r\n";
    fun_test_route("Route 4", "a875cf3d1b7cc054e87efd97be888710", $baseDomain . "/auth");
    echo "\r\n";
    fun_test_route("Route 5", "41a8208d39dc572a02726dfeb5a6674e", $baseDomain . "/jwt");
    echo "\r\n";
    fun_test_route("Route 6", "d1aa650420b74450590eb3b59ff25a0a", $baseDomain . "/text");
    echo "\r\n";
    fun_test_route("Route 7", "aa08e10eb9b3c8424429cf15fe8e2fe6", $baseDomain . "/video/stream");
    echo "\r\n";
    fun_test_route("Route 8", "aa08e10eb9b3c8424429cf15fe8e2fe6", $baseDomain . "/video");
    
}

function fun_create_app_file(string $class, string $content, string $pathFile){
    if($class != "" && $content != "" && $pathFile != ""){
        $pathFile = __BASE_DIR__ . "/app/" . $pathFile;
        DataManager::fileWrite($pathFile, $content);
        if(file_exists($pathFile) && is_file($pathFile)){
            echo "File created in \"$pathFile\"\r\n";
            echo "Content:\r\n\r\n$content";
        }
    }
}

function fun_create_controller(string $nameFile, bool $require = true){
    $class = "${nameFile}Controller";
    $pathFile = "/Controller/${class}.php";
    $methods = "";
    if ($require) {
        $nameFileLower = strtolower($nameFile);
        if (DataManager::exist(__BASE_DIR__ . "/app/Service/${nameFile}Service.php") == "FILE") {
            $require = "\r\nuse App\Service\\${nameFile}Service;\r\n";
            $methods = "private \$${nameFileLower}Service;
            
    public function __construct(){
        \$this->${nameFileLower}Service = new ${nameFile}Service;
    }\r\n
    ";
        } else {
            $require = "";
        }
        $methods .= "/*

        Creating routes from the methods of a controller dynamically
        ------------------------------------------------------------------------------------------------
        This array below configures how the route works
        ------------------------------------------------------------------------------------------------
        array \$CONFIG = [
            'method' => 'POST',
            'csrf' => false,
            'jwt' => false,
            'name' => 'test.create'
        ]
        ------------------------------------------------------------------------------------------------
        To use the route, it is necessary to inform the name of the Controller, the name of the Method 
        and the value of its parameters, the `array parameter \$CONFIG` being only for configuration
        ------------------------------------------------------------------------------------------------
        Examples of use the routes:

            Controller = ${nameFile}Controller
            Method = action
            Call = ${nameFile}Controller@action(...params)
        ------------------------------------------------------------------------------------------------
            | HTTP Verb | ${nameFile}Controller@method   | PATH ROUTE
        ------------------------------------------------------------------------------------------------
            | GET       | ${nameFile}Controller@index    | /${nameFileLower}/index
            | POST      | ${nameFile}Controller@create   | /${nameFileLower}/create
            | GET       | ${nameFile}Controller@new      | /${nameFileLower}/new
            | GET       | ${nameFile}Controller@edit     | /${nameFileLower}/edit/1
            | GET       | ${nameFile}Controller@show     | /${nameFileLower}/show/1
            | PUT       | ${nameFile}Controller@update   | /${nameFileLower}/update/1
            | DELETE    | ${nameFile}Controller@destroy  | /${nameFileLower}/destroy/1
        ------------------------------------------------------------------------------------------------
            
    */

    // This variable informs that the public methods of this controller must be automatically mapped in routes
    private \$generateRoutes;   

    // List all ${nameFileLower}
    public function index(array \$CONFIG = [\"method\" => \"GET\"])
    {
        return \"${nameFile}Controller@index()\";
    }

    // Create a single ${nameFileLower}
    public function create(array \$CONFIG = [\"method\" => \"POST\", \"csrf\" => true])
    {
        return \"${nameFile}Controller@create()\";
    }

    // Redirect page - Create a single ${nameFileLower}
    public function new(array \$CONFIG = [\"method\" => \"GET\"])
    {
        return \"${nameFile}Controller@new()\";
    }

    // Redirect page - Update a single ${nameFileLower}
    public function edit(int \$id, array \$CONFIG = [\"method\" => \"GET\"])
    {
        return \"${nameFile}Controller@edit(\$id)\";
    }   

    // Get single ${nameFileLower}
    public function show(int \$id, array \$CONFIG = [\"method\" => \"GET\"])
    {
        return \"${nameFile}Controller@show(\$id)\";
    }   

    // Update a single ${nameFileLower}
    public function update(int \$id, array \$CONFIG = [\"method\" => \"PUT\", \"csrf\" => true])
    {
        return \"${nameFile}Controller@update(\$id)\";
    }

    // Destroy a single ${nameFileLower}
    public function destroy(int \$id, array \$CONFIG = [\"method\" => \"DELETE\", \"csrf\" => true])
    {
        return \"${nameFile}Controller@destroy(\$id)\";
    }";
    } else {
        $require = "";
    }
    $content = "<?php

namespace App\Controller;
$require
class $class
{

    $methods

}
";
    fun_create_app_file($class, $content, $pathFile);
}

function fun_create_service(string $nameFile, bool $require = false){
    $class = "${nameFile}Service";
    $pathFile = "/Service/${class}.php";
    $constructor = "";
    if ($require) {
        $require = "\r\nuse App\Model\\${nameFile};\r\n";
        $instance = strtolower($nameFile);
        $constructor = "\r\n    private \$$instance;\r\n
    public function __construct()
    {
        \$this->$instance = new $nameFile();
        \$this->${instance}->create();
    }\r\n";
    }
    $content = "<?php

namespace App\Service;
$require        
class $class
{
    $constructor
}
";
    fun_create_app_file($class, $content, $pathFile);
}

function fun_create_model(string $nameFile, $require = false){
    if (!$require) {
        $columns = "        'name',
        'email'";
        $typesAndColumns = "    \$table->string('name');

    \$table->string('email')->unique();";
    } else {
        $columns = "";
        $typesAndColumns = "";
        $require = explode(",", $require);    
        foreach ($require as $key => $value) {
            $value = explode(":", $value);
            if (count($value) == 2) {
                $type = trim($value[0]);
                $column = trim($value[1]);
                $columns .= "       '$column',\r\n";
                $typesAndColumns .= "    \$table->$type('$column');\r\n\r\n";
            } else if (count($value) == 1) {
                $type = "string";
                $column = trim($value[0]);
                $columns .= "       '$column',\r\n";
                $typesAndColumns .= "    \$table->$type('$column');\r\n\r\n";
            }
        }
        $columns = rtrim($columns);
        $typesAndColumns = rtrim($typesAndColumns);
    }
    // dumpd($require, $columns, $typesAndColumns);

    $class = "${nameFile}";
    $pathFile = "/Model/${class}.php";
    $table = strtolower($class) . "s";
    $content = "<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class ${class} extends Eloquent
{

    protected \$table = '$table';

	protected \$fillable = [
$columns
    ];

}
";
    fun_create_app_file($class, $content, $pathFile);
    echo "\r\n";
    $class = $table;
    $pathFile = "/Schema/${class}_capsule_schema.php";
    $content = "<?php

use Illuminate\Database\Capsule\Manager as Capsule;

Capsule::schema()->dropIfExists('${class}');

Capsule::schema()->create('${class}', function (\$table) {

    \$table->increments('id');

$typesAndColumns

    \$table->timestamps();

});
";
    fun_create_app_file($class, $content, $pathFile);
}

function fun_create_view(string $nameFile){
    $class = "${nameFile}";
    $pathFile = "/View/${class}.php";
    $content = "<!DOCTYPE html>
<html lang=\"pt-BR\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>${nameFile}</title>
</head>
<body>
    <h1>Welcome to page ${nameFile}</h1>
    <?php dumpd(\$args); ?>
</body>
</html>
";
    fun_create_app_file($class, $content, $pathFile);
}

function fun_init_server(int $port = 80){
    $basename = pathinfo(__BASE_DIR__)["basename"];
    $URL = "http://127.0.0.1:$port/$basename/";
    echo "\r\n";
    echo "URI address: $URL";
    echo "\r\n";
    echo "Server port: $port";
    echo "\r\n";
    /*var_dump(PHP_OS);
    shell_exec("start $URL");
    shell_exec("xdg-open $URL || sensible-browser $URL || x-www-browser $URL || gnome-open $URL");*/
    shell_exec("cd .. && php -S 0.0.0.0:$port/$basename/");
}

function fun_folder_denied(string $basedir){
    DataManager::fileWrite($basedir . ".htaccess", "
<IfModule authz_core_module>
    Require all denied
</IfModule>
<IfModule !authz_core_module>
    Deny from all
</IfModule>
");
    DataManager::fileWrite($basedir . "index.html", "
<!DOCTYPE html>
<html>
<head>
    <title>403 Forbidden</title>
</head>
<body>
    <h1>Directory access is forbidden.</h1>
</body>
</html>
");
}

function fun_clean_simple_mvcs(){
    echo "Cleaning up...";
    $basedir = __BASE_DIR__ . "/app/";
    DataManager::delete($basedir);
    DataManager::folderCreate($basedir . "Schema");
    fun_folder_denied($basedir . "Schema/");
    DataManager::folderCreate($basedir . "Controller");
    fun_folder_denied($basedir . "Controller/");
    DataManager::folderCreate($basedir . "Model");
    fun_folder_denied($basedir . "Model/");
    DataManager::folderCreate($basedir . "Service");
    fun_folder_denied($basedir . "Service/");
    DataManager::folderCreate($basedir . "View");
    fun_folder_denied($basedir . "View/");
    DataManager::fileWrite($basedir . "View/page_message.php", "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title><?= \$title; ?></title>
</head>
<body>
    <style>
        body{
            background: #ccc;
            color: #b00;
            text-shadow: 1px 1px 1px #888;
        }
        div{
            margin-top: 350px;
            margin-bottom: 350px;
            text-align: center;
        }
    </style>
    <div>
        <?= \$body; ?>
    </div>            
</body>
</html>
");
    fun_folder_denied($basedir);
    DataManager::fileWrite($basedir . "web.php", "<?php

use Lib\Route;

Route::get(\"/\", function(){	
	dumpd(\"Welcome\", input());
})::name(\"home.index\");

Route::on();
");
    $basedir = __BASE_DIR__ . "/storage/";
    DataManager::delete($basedir);
    DataManager::folderCreate($basedir);
    fun_folder_denied($basedir);

    $basedir = __BASE_DIR__ . "/vendor/";
    DataManager::folderCreate($basedir);
    fun_folder_denied($basedir);

    $basedir = __BASE_DIR__ . "/public/";
    DataManager::delete($basedir);
    DataManager::folderCreate($basedir . "css");
    DataManager::folderCreate($basedir . "js");
    DataManager::folderCreate($basedir . "img");
    DataManager::fileWrite($basedir . ".htaccess", "
# Enable directory browsing 
Options +Indexes
            
# Show the contents of directories
IndexIgnoreReset ON

<Files *.*>
    Order Deny,Allow
    Allow from all
</Files>

<Files *>
    Order Deny,Allow
    Allow from all
</Files>
");
    DataManager::fileWrite($basedir . "robots.txt", "
User-agent: *
Disallow:
");
    echo "\r\nClean!";
}

function fun_update_project()
{
    $folderActual = DataManager::path(realpath(__DIR__ . "/../"));
    $folderUpdate = DataManager::path($folderActual . "makemvcss-master/");

    echo "Dir actual: " . $folderActual;
    echo "\r\n";
    echo "Dir updated: " . $folderUpdate;
    echo "\r\n";
    echo "Download . . .";
    echo "\r\n";
    $link = "https://github.com/matheusjohannaraujo/makemvcss/archive/master.zip";
    $zip = file_get_contents($link);
    $zipName = "makemvcss-master.zip";
    file_put_contents($folderActual . $zipName, $zip);

    DataManager::zipExtract($folderActual . $zipName, $folderActual);
    DataManager::delete($zipName);

    DataManager::delete($folderUpdate . "app/");
    DataManager::delete($folderUpdate . "public/");
    DataManager::delete($folderUpdate . "storage/");
    DataManager::delete($folderUpdate . "db_makemvcss.sqlite");
    DataManager::delete($folderUpdate . "composer.lock");
    DataManager::copy($folderActual . "app/", $folderUpdate . "app/");
    DataManager::copy($folderActual . "public/", $folderUpdate . "public/");
    DataManager::copy($folderActual . "storage/", $folderUpdate . "storage/");
    DataManager::copy($folderActual . ".env", $folderUpdate . ".env_old");
    DataManager::copy($folderActual . ".gitignore", $folderUpdate . ".gitignore_old");
    DataManager::copy($folderActual . "composer.json", $folderUpdate . "composer_old.json");

    $folderUpdateFinal = $folderActual . "../" . pathinfo($folderActual)["basename"] . "_" . date("Y.m.d_H.i.s") . "/";

    DataManager::move($folderUpdate, $folderUpdateFinal);

    echo "Dir updated: " . $folderUpdateFinal;
}

function fun_list_commands()
{
    echo "
 COMMAND COMPLETE        | COMMAND MINIFIED   | DESCRIPTION
 -------------------------------------------------------------------------------------------------------------------
 php adm help            | php adm h          | List all commands
 -------------------------------------------------------------------------------------------------------------------
 php adm clean           | php adm c          | Clears the project, leaving only the default settings
 -------------------------------------------------------------------------------------------------------------------
 php adm server          | php adm s:80       | Start a web server on port 80
 -------------------------------------------------------------------------------------------------------------------
 php adm controller Test | php adm c Test     | Creates a file inside the folder \"app/Controller/TestController.php\"
 -------------------------------------------------------------------------------------------------------------------
 php adm model Test      | php adm m Test     | Creates a file inside the folder \"app/Model/Test.php\"
                                              | and another one in \"app/Schema/tests_capsule_schema.php\"
 -------------------------------------------------------------------------------------------------------------------
 php adm database Test   | php adm d Test     | Run the Schema file (Table) \"app/Schema/tests_capsule_schema.php\"
 -------------------------------------------------------------------------------------------------------------------
 php adm database --all  | php adm d -a       | Run all schema files (tables) in the \"app/Schema\" folder
 -------------------------------------------------------------------------------------------------------------------
 php adm service Test    | php adm s Test     | Creates a file inside the folder \"app/Service/TestModel.php\"
 -------------------------------------------------------------------------------------------------------------------
 php adm view Test       | php adm v Test     | Creates a file inside the folder \"app/View/Test.php\"
 -------------------------------------------------------------------------------------------------------------------
 php adm update          | php adm u          | Updates the core framework 
 -------------------------------------------------------------------------------------------------------------------
 php adm test            | php adm t          | Testing the default routes
 -------------------------------------------------------------------------------------------------------------------
 php adm zip             | php adm z          | Zipping files and folders from the `vendor` folder
 -------------------------------------------------------------------------------------------------------------------
 php adm unzip           | php adm uz         | Unzipping the zip files from the `vendor` folder
 -------------------------------------------------------------------------------------------------------------------
 php adm route           | php adm r          | Listing existing routes and listing existing routes by http verb
 -------------------------------------------------------------------------------------------------------------------
 php adm route:get       | php adm r:get      | Lists existing routes by the http GET verb
 -------------------------------------------------------------------------------------------------------------------
 php adm route:post      | php adm r:post     | Lists existing routes by the http POST verb
 -------------------------------------------------------------------------------------------------------------------
 php adm route:put       | php adm r:put      | Lists existing routes by the http PUT verb
 -------------------------------------------------------------------------------------------------------------------
 php adm route:patch     | php adm r:patch    | Lists existing routes by the http PATCH verb
 -------------------------------------------------------------------------------------------------------------------
 php adm route:options   | php adm r:options  | Lists existing routes by the http OPTIONS verb
 -------------------------------------------------------------------------------------------------------------------
 php adm route:delete    | php adm r:delete   | Lists existing routes by the http DELETE verb  
";
}

function fun_apply_database(string $nameFile){
    \Lib\Route::init();
    require_once __DIR__ . "/db_conn_capsule.php";
    db_schemas_apply($nameFile);
}

function fun_switch_app_options(string $cmd, string $nameFile, $require = false){
    switch ($cmd) {
        case "controller":
        case "c":
            fun_create_controller($nameFile, !$require);
            break;
        case "service":
        case "s":
            fun_create_service($nameFile, $require);
            break;
        case "model":
        case "m":
            fun_create_model($nameFile, $require);
            break;
        case "view":
        case "v":
            fun_create_view($nameFile);
            break;
        case "database":
        case "d":
            fun_apply_database($nameFile);
            break;
    }
}

function fun_switch_other_options(string $cmd){
    $attr = 80;
    if (preg_match("/:/", $cmd)) {
        $arr = explode(':', $cmd);
        if (count($arr) == 2) {
            $cmd = (string) $arr[0];
            $attr = (string) $arr[1];
        }
    }
    switch ($cmd) {
        case "server":
        case "s":
            fun_init_server($attr);
            break;
        case "test":
        case "t":
            fun_test_routes();
            break;
        case "route";
        case "r":
            if ($attr == 80) {
                $attr = "";
            }
            fun_routes($attr);
            break;
        case "clean":
        case "c":
            fun_clean_simple_mvcs();
            break;
        case "help":
        case "h":
            fun_list_commands();
            break;
        case "update":
        case "u":
            fun_update_project();
            break;
    }
}
