<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/makemvcss
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2020-08-20
*/

namespace Lib;

use Lib\In;
use Lib\URI;
use Lib\Out;
use Lib\CSRF;
use Lib\DataManager;

class Route
{

    public static $in;
    public static $out;
    public static $route;

    public static function init()
    {
        self::$in = new In;
        self::$out = new Out;
        self::$route = [];
    }

    public static function in()
    {
        return self::$in;
    }

    public static function out()
    {
        return self::$out;
    }

    private static function stringCountReg($str, $reg = "{/}")
    {
        preg_match_all($reg, $str, $contador);
        return count($contador[0]);
    }

    private static function stringRemoveFinalStripe($str)
    {
        $len = strlen($str);
        if ($len > 0) {
            if (substr($str, $len - 1, $len) == "/") {
                $str = substr($str, 0, $len - 1);
            }
        }
        return $str;
    }

    private static function uri()
    {
        $uri = self::$in->paramServer("REQUEST_URI");        
        $uriSize = (int) strlen($uri);
        $base = URI::scriptName();
        if ($base == "//") {
            $base = "/";
        }
        $baseSize = strlen($base);       
        $baseUriIndex = strpos($uri, $base);
        if ($baseUriIndex !== false && $baseUriIndex >= 0 && $uriSize > 0 && $baseSize >= 0) {
            $uri = substr($uri, $baseSize);
        }
        $uri = self::uriParams($uri);
        //dumpd($uri, $base, $baseUriIndex);
        return $uri;
    }

    private static function uriParams($uri)
    {
        $index = strpos($uri, "?");        
        if ($index !== false && $index >= 0) {
            $params = "";
            $params = substr($uri, $index + 1);
            $uri = substr($uri, 0, $index);
            $uriSignalInterrogation = strripos($params, "?");
            $uriSignalEqual = strpos($params, "=");
            if (!!$uriSignalInterrogation && $uriSignalInterrogation >= 0 && $uriSignalEqual >= 1) {
                $params2 = explode("&", $params);
                $params2[0] = $params;
                $params3 = substr($params, $uriSignalInterrogation + 1);
                $params3 = explode("&", $params3);
                foreach ($params3 as $value) {
                    if (($x = array_search($value, $params2) ?? false)) {
                        $params2[$x] = $value;
                    } else {
                        $params2[] = $value;
                    }
                }
                $params = $params2;
                $_GET = [];
                foreach ($params as $value) {
                    $index = strpos($value, "=");
                    if ($index) {
                        $key = substr($value, 0, $index);
                        $val = substr($value, $index + 1);
                        $_GET[$key] = $val;
                        $_REQUEST[$key] = $val;
                    }
                }
                self::$in->setGet($_GET);
                self::$in->setReq($_REQUEST);
            }
        }
        return self::stringRemoveFinalStripe($uri);
    }

    private static function path($path)
    {        
        return self::stringRemoveFinalStripe(substr($path, 1, strlen($path)));
    }

    private static function uriArray($uri)
    {
        return explode("/", $uri);
    }

    private static function pathArray($path)
    {
        $pathParts = explode("/", $path);
        $array = [];
        for ($i = 0, $j = count($pathParts); $i < $j; $i++) {
            $name = $pathParts[$i];
            $var = false;
            $req = true;
            if (self::stringCountReg($name, "({.+?}/?)")) {
                $name = str_replace(['{', '}'], '', $name);
                $var = true;
                if (((int) strpos($name, "?")) > 0) {
                    $name = str_replace('?', '', $name);
                    $req = false;
                }
            }
            if ($name != "") {
                $array[] = [
                    "name" => $name,
                    "var" => $var,
                    "req" => $req,
                ];
            }
        }
        return $array;
    }

    private static function matchPath(string $path)
    {
        $search = false;
        foreach (self::$route as $key => $route) {
            if ($route["name"] == $path || $route["path"] == $path) {
                $search = $route["path"];
                break;
            }
        }
        return $search;
    }

    public static function link(string $path = "", array $params = [])
    {
        $route_name_exist = self::matchPath($path);
        if (!$route_name_exist) {
            self::createAllRoutesStartingFromControllerAndMethod();
            $route_name_exist = self::matchPath($path);
        }
        if ($route_name_exist) {
            $path = $route_name_exist;
        }
        $link = URI::base(true);
        $inc = self::stringCountReg($path, "({.+?}/?)");
        if ($inc <= 0) {
            if ($path[0] != "/") {
                $path = "/" . $path;
            }
            $link .= $path;
        } else if ($inc > 0) {
            $pathParts = self::pathArray($path);
            $i = 0;
            foreach ($pathParts as $key => $pathPart) {
                if ($pathPart["var"] === false) {
                    $link .= "/" . $pathPart["name"];
                } else if ($pathPart["var"] === true) {
                    $link .= "/" . ($params[$i++] ?? die("Param `" . $pathPart["name"] . "` not found"));
                }
            }
        }
        // dumpd($path, $link);
        return $link;
    }

    private static function route(string $path, $action, bool $csrf = false, bool $jwt = false)
    {
        $path = self::path($path);
        $uri = self::uri();
        $isRoute = false;
        $arg = [];
        $inc = self::stringCountReg($path, "({.+?}/?)");
        if (self::stringCountReg($path) == self::stringCountReg($uri) && strtolower($uri) == strtolower($path) && $inc <= 0) {
            $isRoute = true;
        } else if ($inc > 0) {
            $uriParts = self::uriArray($uri);
            $pathParts = self::pathArray($path);
            for ($i = 0, $j = count($pathParts); $i < $j; $i++) {
                $isRoute = false;
                $pathPart = $pathParts[$i];
                $uriPart = $uriParts[$i] ?? "";
                if (strtolower($pathPart["name"]) == strtolower($uriPart) && !$pathPart["var"]) {
                    $isRoute = true;
                } else if ($pathPart["name"] != $uriPart && $pathPart["var"]) {
                    if ($pathPart["var"]) {
                        if ($pathPart["req"] && $uriPart != "") {
                            $arg[$pathPart["name"]] = string_to_number($uriPart);
                            $isRoute = true;
                        } else if (!$pathPart["req"]) {
                            $isRoute = true;
                            if ($uriPart != "") {
                                $arg[$pathPart["name"]] = string_to_number($uriPart);
                            }
                        }
                    }
                } else {
                    break;
                }
            }
        }
        // dumpd($uri, $path, $isRoute, $arg, $_REQUEST, $_SERVER);
        return self::isRoute($isRoute, $arg, $action, $csrf, $jwt);
    }

    private static function isRoute(&$isRoute, &$arg, &$action, &$csrf, &$jwt)
    {
        try {
            if ($isRoute) {
                if ($csrf && !CSRF::valid()) {
                    self::$out->pageCSRF();
                }
                if ($jwt && !self::$in->paramJwt()->valid()) {
                    self::$out->pageJWT();
                }
                $result = "";
                self::$in->setArg($arg);
                if (is_callable($action)) {
                    $result = $action(...array_values(self::$in->paramArg()));
                } else {
                    $action = (string) $action;
                    $ControllerMethod = explode("@", $action);
                    if (count($ControllerMethod) == 2) {
                            $Controller = "\App\Controller\\" . $ControllerMethod[0];
                            $Method = $ControllerMethod[1];
                            $pathfile = realpath(DataManager::path(__DIR__ . "/../app/Controller/" . $ControllerMethod[0] . ".php"));
                            // dumpd($pathfile, $Controller, class_exists($Controller), method_exists($Controller, $Method));
                        if (DataManager::exist($pathfile) == "FILE" && class_exists($Controller) && method_exists($Controller, $Method)) {
                            self::$out->filename($ControllerMethod[0] . "_" . $Method);
                            $result = (function (&$Controller, &$Method) {
                                try {
                                    return (new $Controller)->$Method(...array_values(self::$in->paramArg()));
                                } catch (\Throwable $e) {
                                    dumpd($e->getMessage());
                                }
                            })($Controller, $Method);
                        } else {
                            self::$out->page404();
                        }
                    } else {
                        view($action, self::$in);
                    }
                }
                if ($result instanceof Route) {
                    $result->out->go();
                } else if ($result instanceof Out) {
                    $result->go();
                } else {
                    self::$out
                        ->content($result)
                        ->go();
                }
                return true;
            }
        } catch (\Throwable $e) {
            dumpd($e);
        }
        return false;
    }

    private static function type($action)
    {
        if (!is_string($action) && is_callable($action)) {
            return "closure";
        } else if (is_string($action)) {
            $x = strpos($action, "@");
            if ($x  && $x > 0) {
                return "controller";
            } else {
                return "view";
            }                    
        }
        return "undefined";
    }

    public static function method(string $method)
    {
        $index = count(self::$route) - 1;
        if ($index >= 0) {
            self::$route[$index]["method"] = strtoupper($method);
        }
        return __CLASS__;
    }

    public static function name(string $name)
    {
        $index = count(self::$route) - 1;
        if ($index >= 0) {
            self::$route[$index]["name"] = $name;
        }
        return __CLASS__;
    }

    public static function csrf(bool $csrf)
    {
        $index = count(self::$route) - 1;
        if ($index >= 0) {
            self::$route[$index]["csrf"] = (int) $csrf;
        }
        return __CLASS__;
    }

    public static function jwt(bool $jwt)
    {
        $index = count(self::$route) - 1;
        if ($index >= 0) {
            self::$route[$index]["jwt"] = (int) $jwt;
        }
        return __CLASS__;
    }

    public static function parseName(string $name){
        $name = strtolower($name);
        if (!empty($name) && strlen($name) > 2) {
            $name = substr($name, 1);
            $firstBar = strpos($name, "/{");
            if ($firstBar !== false) {
                $name = substr($name, 0, $firstBar);
            }
        }
        $name = str_replace("/", ".", $name);
        $name = str_replace(["{", "}", "?"], "", $name);
        return $name;
    }

    private static function defRoute(string $method, string $path, $action, string $name = null, bool $csrf = false, bool $jwt = false)
    {
        self::$route[] = [
            "method" => strtoupper($method),
            "path" => $path,
            "action" => $action,
            "type" => self::type($action),            
            "name" => $name ?? ((self::type($action) == "controller") ? str_replace("controller@", ".", strtolower($action)) : self::parseName($path)),
            "csrf" => (int) $csrf,
            "jwt" => (int) $jwt,
        ];
        return __CLASS__;
    }

    public static function any(string $path, $action, string $name = null, bool $csrf = false, bool $jwt = false)
    {
        return self::defRoute("ANY", $path, $action, $name, $csrf, $jwt);
    }

    public static function get(string $path, $action, string $name = null, bool $csrf = false, bool $jwt = false)
    {
        return self::defRoute("GET", $path, $action, $name, $csrf, $jwt);
    }

    public static function post(string $path, $action, string $name = null, bool $csrf = false, bool $jwt = false)
    {
        return self::defRoute("POST", $path, $action, $name, $csrf, $jwt);
    }

    public static function put(string $path, $action, string $name = null, bool $csrf = false, bool $jwt = false)
    {
        return self::defRoute("PUT", $path, $action, $name, $csrf, $jwt);
    }

    public static function patch(string $path, $action, string $name = null, bool $csrf = false, bool $jwt = false)
    {
        return self::defRoute("PATCH", $path, $action, $name, $csrf, $jwt);
    }

    public static function options(string $path, $action, string $name = null, bool $csrf = false, bool $jwt = false)
    {
        return self::defRoute("OPTIONS", $path, $action, $name, $csrf, $jwt);
    }

    public static function delete(string $path, $action, string $name = null, bool $csrf = false, bool $jwt = false)
    {
        return self::defRoute("DELETE", $path, $action, $name, $csrf, $jwt);
    }

    public static function match(array $verbs, string $path, $action, string $name = null, bool $csrf = false, bool $jwt = false)
    {
        foreach ($verbs as $verb) {
            self::defRoute($verb, $path, $action, $name, $csrf, $jwt);
        }
    }

    private static function runRoute()
    {
        $result = false;
        $METHOD = strtoupper(self::$in->paramServer("REQUEST_METHOD"));
        $METHOD = strtoupper(self::$in->paramReq("_method", $METHOD));
        foreach (self::$route as $key => $route) {
            if ($METHOD === $route["method"] || "ANY" === $route["method"]) {
                $result = self::route($route["path"], $route["action"], $route["csrf"], $route["jwt"]);
            }
            if ($result) {
                break;
            }
        }
        return $result;
    }

    private static function routesDump($method, int $index = -1)
    {
        // dumpd($method, $id);
        $method = strtoupper($method);
        $result = [];
        foreach (self::$route as $key => $route) {
            if ($method === "ANY" || $method === $route["method"] || "ANY" === $route["method"]) {
                if ($route["type"] == "closure") {
                    $route["action"] = "function";
                }
                $result[] = $route;
            }
        }
        if ($index != -1) {
            return $result[$index];
        }
        return $result;
    }

    public static function on()
    {
        if (!self::runRoute()) {
            $uri = self::uri();
            $path = DataManager::path(realpath(__DIR__ . "/../public") . "/$uri");
            // dumpd($uri, $path);
            if (DataManager::exist($path) && $uri != "" && $uri != "/") {
                $uri = URI::base() . "public/" . $uri;
                header("Location: $uri");
                die;
            }
            if (input_env("ENV") === "dev") {
                self::get("/routes/all/json/{method?}", function (string $method = "ANY") {
                    return self::routesDump($method);
                });
                self::get("/routes/all/{method?}", function (string $method = "ANY") {
                    dumpd(self::routesDump($method));
                });
                self::get("/routes/{index}/{method?}", function (int $index, string $method = "ANY") {
                    dumpd(self::routesDump($method, $index));
                });
            }            
            if (input_env("AUTOMATICALLY_GENERATE_ALL_CONTROLLER_ROUTES")) {
                self::createAllRoutesStartingFromControllerAndMethod();
            } else {
                self::controllerAndMethod();
            }
            if (input_env("AUTO_VIEW_ROUTE")) {            
                $avrs = self::getAllAutoViewRoutes();
                self::generateAutoViewRoutes($avrs);
                // dumpd($avrs, self::$route);
            }
            self::match(["GET", "POST"], "/{search}", function (string $search) {
                function isPublicFile($array, $search)
                {
                    foreach ($array as $key => $value) {
                        if (($value["name"] == $search || $value["md5"] == $search) && $value["type"] == "FILE") {
                            return $value["path"];
                        }
                    }
                    return false;
                }
                if (!strpos($search, "..")) {
                    $file = isPublicFile(DataManager::folderScan(realpath(__DIR__ . "/../public/"), false, true), $search);
                    if ($file) {
                        self::$out
                            ->fopen($file)
                            ->name(pathinfo($file)["basename"])
                            ->bitrate(256)
                            ->go();
                    }
                }
                self::$out->page404();
            });
            self::get("/", "HomeController@index");
            // dumpd(self::$route);
            if (!self::runRoute()) {
                // If no route is served, it returns an html page containing the 404 error.
                self::$out->page404();
            }
        }
    }

    private static function getTheClassMethodsAndTheirParameters(string $class)
    {        
        $result = [];
        if (class_exists($class)) {
            $methods = get_class_methods($class);
            foreach ($methods as $index => $method) {
                $ReflectionMethod = new \ReflectionMethod($class, $method);
                $reflectionParams = $ReflectionMethod->getParameters();
                $result[$method] = [];
                foreach ($reflectionParams as $param) {
                    try {
                        $result[$method][] = [
                            "name" => $param->getName(),                            
                            "type" => ($param->getType() !== null) ? $param->getType()->getName() : "string",//"type" => (string) $param->getType(),
                            "optional" => $param->isOptional(),                            
                            "value" => $param->isOptional() ? ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : "") : ""
                        ];
                    } catch (\Throwable $e) { /* dumpd($e); */ }
                }
            }
        }
        return $result;
    }

    private static function getAllAutoViewRoutes()
    {
        $folderView = DataManager::folderScan(realpath(__DIR__ . "/../app/View/"), false, true);
        $result = [];
        foreach ($folderView as $key => $path) {
            if ($path["type"] == "FILE" && strpos($path["name"], "avr-") !== false) {                
                $indexAppView = strpos($path["path"], "/app/View/");
                if ($indexAppView !== false) {
                    $path["route_path"] = substr($path["path"], $indexAppView + 9);
                } else {
                    $path["route_path"] = "/" . $path["name"];                
                }
                $name = $path["route_path"];
                $path["route_path"] = str_replace(["avr-", ".php"], "", $name);
                $path["route_name"] = str_replace(".php", "", $name);
                $result[] = $path;
            }
        }
        return $result;
    }

    private static function generateAutoViewRoutes(array &$avrs)
    {
        foreach ($avrs as $key => $avr) {
            self::any($avr["route_path"], $avr["route_name"]);
        }
    }

    private static function getAllControllersAndMethods($only_valid = true)
    {
        $folderController = DataManager::folderScan(realpath(__DIR__ . "/../app/Controller/"));
        $result = [];
        foreach ($folderController as $key => $path) {
            if ($path["type"] == "FILE" && strpos($path["name"], "Controller.php") !== false) {
                $name = $path["name"];
                $name = str_replace(".php", "", $name);
                $class = "\app\Controller\\" . $name;
                $class2 = "\App\Controller\\" . $name;
                if (!class_exists($class) && class_exists($class2)) {
                    $class = $class2;
                }
                if (class_exists($class) && (property_exists($class, "generateRoutes") || !$only_valid)) {
                    $path["class"] = $class;
                    $path["methods"] = self::getTheClassMethodsAndTheirParameters($class);
                    $keys = array_keys($path["methods"]);
                    $construct = array_search('__construct', $keys);
                    if ($construct !== false && $construct >= 0) {
                        unset($path["methods"]["__construct"]);
                    }
                    $destruct = array_search('__destruct', $keys);
                    if ($destruct !== false && $destruct >= 0) {
                        unset($path["methods"]["__destruct"]);
                    }
                    self::generatePathRoutes($path);
                    $result[] = $path;
                }
            }
            unset($folderController[$key]);
        }
        return $result;
    }

    private static function generatePathRoutes(array &$path)
    {
        $path["routes"] = [];
        foreach ($path["methods"] as $method => $params) {
            $route_config = [];
            $arguments = [""];
            foreach ($params as $param) {
                $name = $param["name"];
                if ($name == "CONFIG") {
                    $route_config = $param;
                    continue;
                }                            
                if ($param["optional"]) {
                    $op = "?";
                    $arguments[] = str_replace("?", "", $arguments[count($arguments) - 1]) . "/{" . "${name}${op}}";
                    continue;
                }
                $arguments[count($arguments) - 1] = $arguments[count($arguments) - 1] . "/{" . "${name}}";
            }
            $route_base = strtolower("/" . str_replace("Controller.php", "", $path["name"]) . "/$method");
            $action = str_replace(".php", "", $path["name"]) . "@$method";
            $arguments = array_reverse($arguments);
            foreach ($arguments as $key => $arg) {
                $_path_route = $route_base . $arg;
                $_method = (string) ($route_config["value"]["method"] ?? "ANY");
                $_csrf = (bool) ($route_config["value"]["csrf"] ?? false);
                $_jwt = (bool) ($route_config["value"]["jwt"] ?? false);
                $path["routes"][] = [
                    "path" => $_path_route,
                    "action" => $action,
                    "method" => $_method,
                    "csrf" => $_csrf,
                    "jwt" => $_jwt
                ];
                $index_route = explode("/", $_path_route);
                if (count($index_route) == 3) {
                    if (!empty($index_route[1]) && $index_route[2] == "index") {
                        $_path_route = "/" . $index_route[1];
                        $path["routes"][] = [
                            "path" => $_path_route,
                            "action" => $action,
                            "method" => $_method,
                            "csrf" => $_csrf,
                            "jwt" => $_jwt
                        ];
                    }
                }                
            }
        }
    }

    private static function createRoute(array &$path)
    {
        foreach ($path["routes"] as $key => $route) {
            self::any($route["path"], $route["action"])::method($route["method"])::csrf($route["csrf"])::jwt($route["jwt"]);
        }
    }

    private static function createAllRoutesStartingFromControllerAndMethod()
    {
        $controllers = self::getAllControllersAndMethods(input_env("GENERATE_SIGNED_CONTROLLER_ROUTES_ONLY", true));
        $route_backup = self::$route;
        self::$route = [];
        for ($i = 0, $j = count($controllers); $i < $j; $i++) { 
            self::createRoute($controllers[$i]);
        }
        // dumpd(self::$route);
        self::$route = array_merge(self::$route, $route_backup);
    }

    private static function controllerAndMethod()
    {
        self::any("/{controller}/{method?}", function (string $controller = "home", string $method = "index") {
            $controller = strtolower($controller);
            $controllers = self::getAllControllersAndMethods(input_env("GENERATE_SIGNED_CONTROLLER_ROUTES_ONLY", true));
            // dumpd(self::$route[count(self::$route) - 4]);
            unset(self::$route[count(self::$route) - 4]);            
            $route_backup = self::$route;
            self::$route = [];
            for ($i = 0, $j = count($controllers); $i < $j; $i++) {
                $path = $controllers[$i];
                unset($controllers[$i]);
                $name = str_replace("controller.php", "", strtolower($path["name"]));
                if ($name == $controller) {
                    if (in_array($method, array_keys($path["methods"]))) {
                        self::createRoute($path);
                    }
                    unset($controllers);
                    break;
                }
            }
            self::$route = array_merge(self::$route, $route_backup);
            // dumpd($controller, $method, self::$route);
            self::runRoute();
            self::$out->page404();
        });
    }

}
