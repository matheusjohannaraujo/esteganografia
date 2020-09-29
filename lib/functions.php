<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/makemvcss
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2020-09-16
*/

use Lib\Out;
use Lib\URI;
use Lib\CSRF;
use Lib\Route;
use Lib\DataManager;

// Retorna uma instância da `Classe In (INPUT)` que está armazenada na váriavel estática `$in` da classe Route, e que contém a entrada de todos os dados no sistema
function input()
{
    return Route::$in;
}

// Retorna uma instância da `Classe Out (OUTPUT)` que está armazenada na váriavel estática `$out` da classe Route, e que contém a saída de dados do sistema
function output()
{
    return Route::$out;
}

// Retorna a chave e valor das variáveis de uma rota
function input_arg($key = null, $valueDefault = null)
{
    return Route::$in->paramArg($key, $valueDefault);
}

// Retorna as chaves e valores do `$_ENV`, incluíndo a configuração que existe dentro do arquivo `.env`
function input_env($key = null, $valueDefault = null)
{
    return Route::$in->paramEnv($key, $valueDefault);
}

// Retorna as chaves e valores do $_REQUEST
function input_req($key = null, $valueDefault = null)
{
    return Route::$in->paramReq($key, $valueDefault);
}

// Retorna as chaves e valores do `$_GET`
function input_get($key = null, $valueDefault = null)
{
    return Route::$in->paramGet($key, $valueDefault);
}

// Retorna as chaves e valores do `$_POST`
function input_post($key = null, $valueDefault = null)
{
    return Route::$in->paramPost($key, $valueDefault);
}

// Retorna as chaves e valores do `$_FILES`
function input_file($key = null, $valueDefault = null)
{
    return Route::$in->paramFile($key, $valueDefault);
}

// Retorna as chaves e valores do `$_SERVER`
function input_server($key = null, $valueDefault = null)
{
    return Route::$in->paramServer($key, $valueDefault);
}

// Retorna as chaves e valores do `JSON` enviado ao servidor
function input_json($key = null, $valueDefault = null)
{
    return Route::$in->paramJson($key, $valueDefault);
}

// Retorna a `Autorização (código JWT)` que foi enviado ao servidor
function input_auth()
{    
    return Route::$in->paramAuth();
}

// Retorna uma instância da `classe JWT` já com a `Autorização (código JWT)` que foi enviado ao servidor
function input_jwt()
{
    return Route::$in->paramJwt();
}

// Retorna o `código CSRF` que foi gerado no servidor
function csrf()
{
    return CSRF::get();
}

// Retorna o valor da mensagem dentro do `$_SESSION["info"]`
function message($key, $value = "")
{
    if (($_SESSION["info"] ?? false)) {        
        if (is_array($_SESSION["info"])) {
            $value = $_SESSION["info"][$key] ?? null;
        }        
        if ($value !== null) {
            unset($_SESSION["info"][$key]);
        }
        if (count($_SESSION["info"]) == 0) {
            unset($_SESSION["info"]);
        }
    }
    return $value;
}

// Define uma mensagem dentro do $_SESSION["info"]
function info($key, $value)
{
    if (isset($_SESSION["info"]) && is_array($_SESSION["info"])) {
        $_SESSION["info"][$key] = $value;
    } else {
        $_SESSION["info"] = [$key => $value];
    }
}

// Retorna o valor de um parâmetro que foi encaminhado ao servidor
function param($key, $value = "")
{
    if (($_SESSION["back"] ?? false)) {
        if (($x = $_SESSION["back"][$key] ?? false)) {
            $value = $x;
            unset($_SESSION["back"][$key]);
        }
        if (count($_SESSION["back"]) == 0) {
            unset($_SESSION["back"]);
        }
    }
    return $value;
}

// Retorna o link de uma rota
function action(string $path, ...$params)
{
    return Route::link($path, $params);
}

// Redireciona a página usando uma rota existente
function redirect(string $path, ...$params)
{
    $path = Route::link($path, $params);
    header("Location: $path", true, 301);
    die;
}

// Volta para a página anterior
function back($array = null)
{
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            info($key, $value);
        }
    }
    $no_referer = $_SERVER["HTTP_NO_REFERER"] ?? false;
    $back = $_SERVER["HTTP_REFERER"] ?? false;
    if ($back && !$no_referer) {        
        $_SESSION["back"] = $_REQUEST;
        header("Location: $back");
        // header("Location:javascript://history.go(-1)");
        // header("Refresh: 5; URL=\"$back\"");
        die;        
    } else {
        $array = ["error" => "back()", "info" => [], "back" => $_REQUEST];
        if ($_SESSION["info"] ?? false) {
            $array["info"] = $_SESSION["info"];
            unset($_SESSION["info"]);
        }
        die(json_encode($array));
    }
}

// Realiza a chamada de processamento de um View
function view(string $file, $args = [], $return = false)
{
    $result = null;
    $pathfile = realpath(__DIR__ . "/../app/View/$file.php");
    if (DataManager::exist($pathfile) == "FILE") {
        $result = (function (&$file, &$args) {
            if (is_array($args)) {
                extract($args, EXTR_SKIP);
            }
            ob_start();
            try {
                include $file;
            } catch (Throwable $e) {
                ob_get_clean();
                ob_start();
                dumpd(["message" => $e->getMessage(), "line" => $e->getLine(), "file" => $e->getFile()]);
            }
            return ob_get_clean();
        })($pathfile, $args);
    }
    if ($return) {
        return $result;
    }
    $out = new Out;
    if ($result !== null) {
        $out
            ->content($result)
            ->filename("$file.html")
            ->go();
    }
    $out->page404();    
}

// Retorna um hash gerado através do Argon2, Bcrypt ou Default
function hash_generate(string $text, string $alg = "default", array $options = [])
{
    if ($alg == "argon") {
        $alg = (@constant("PASSWORD_ARGON2ID") ?? @constant("PASSWORD_ARGON2I")) ?? false;
        if (!$alg) {
            throw new Exception("Argon not is suported");
        }
        if (count($options) === 0) {
            $options = [
                'memory_cost' => 2048,
                'time_cost'   => 4,
                'threads'     => 3,
            ];
        }
    } else if ($alg == "bcrypt") {
        $alg = @constant("PASSWORD_BCRYPT") ?? false;
        if (!$alg) {
            throw new Exception("Bcrypt not is suported");
        }
        if (count($options) === 0) {
            $options = ['cost' => 12];
        }
    } else if ($alg == "default") {
        $alg = PASSWORD_DEFAULT;
        $options = [];
    } else {
        throw new Exception("Write in alg (argon, bcrypt or default)");
    }
    return password_hash($text, $alg, $options);
}

// Retorna a verificação entre texto e hash, o resultado pode ser verdadeiro ou falso
function hash_verify(string $text, string $hash)
{
    return password_verify($text, $hash);
}

/*
    Inicia uma sessão com as informações do usuário a ser autenticado. A variável `$pathInit` armazena o
    nome da rota no qual o usuário deve ser redirecionado após fazer a autenticação, já `$pathEnd` contém
    o nome da rota em que o usuário deve ser redirecionado quando a sessão expirar. Em `$array` pode ser
    definido um vetor de informações referentes ao usuário, no `$seconds` é informado o tempo em segundos
    de duração da sessão, se o valor for “0”, significa que não terá tempo de expiração. Todas as informações
    ficam armazenadas em ` $_SESSION["auth"]`
*/
function auth_session(string $pathInit, string $pathEnd, array $array = [], int $seconds = 0)
{
    $array["-path-refresh-"] = true;
    $array["-path-init-"] = $pathInit;
    $array["-path-end-"] = $pathEnd;
    $_SESSION["auth"] = $array;
    auth_timer($seconds);
    // dumpd($_SESSION);
    return function() use($pathEnd) { auth_verify($pathEnd); };    
}

/*
    Define o tempo de expiração da sessão. Se o valor informado for "100" a sessão permanecerá ativa
    por cem segundos, para definir a sessão como sem tempo de expiração informe o valor "0". Para renovar
    o tempo em que a sessão deve está ativa informe o valor "-1", que faz com que seja recriado o tempo 
    total da sessão (time() + $seconds)
*/
function auth_timer(int $seconds = null)
{
    if (isset($_SESSION["auth"])) {        
        if ($seconds !== null) {
            if ($seconds > 0) {
                $_SESSION["auth"]["-count-timer-"]["follow"] = true;                
                $_SESSION["auth"]["-count-timer-"]["expire"] = time() + $seconds;
                $_SESSION["auth"]["-count-timer-"]["seconds"] = $seconds;
            } else if ($seconds == 0) {
                $_SESSION["auth"]["-count-timer-"]["follow"] = false;
                $_SESSION["auth"]["-count-timer-"]["expire"] = 0;
                $_SESSION["auth"]["-count-timer-"]["seconds"] = 0;
            } else {
                $_SESSION["auth"]["-count-timer-"]["expire"] = time() + $_SESSION["auth"]["-count-timer-"]["seconds"];
            }
        }
        $remaining = $_SESSION["auth"]["-count-timer-"]["expire"] - time();        
        if ($remaining < 0) {
            $remaining = 0;
        }
        $_SESSION["auth"]["-count-timer-"]["remaining"] = $remaining;
    }    
    return $_SESSION["auth"]["-count-timer-"];
}

/*
    Destroí a sessão atual e redireciona para o nome da rota `$pathEnd` que foi informado previamente na criação da sessão
*/
function auth_destroy(string $pathEnd = "home")
{
    if (isset($_SESSION["auth"]) && isset($_SESSION["auth"]["-path-end-"])) {        
        $pathEnd = $_SESSION["auth"]["-path-end-"];
        unset($_SESSION["auth"]);
    }
    session_unset();
    session_destroy();
    session_start();
    session_regenerate_id(true);
    redirect($pathEnd);
}

// Retorna um valor booleano (verdadeiro ou falso), que diz se o estado da sessão é válida ou inválida
function auth_valid()
{
    if (isset($_SESSION["auth"])) {
        auth_timer();
        if (!$_SESSION["auth"]["-count-timer-"]["follow"]) {
            return true;
        } else if ($_SESSION["auth"]["-count-timer-"]["follow"] && $_SESSION["auth"]["-count-timer-"]["remaining"] > 0) {
            return true;
        }
    }
    return false;
}

// Libera ou nega o acesso a determinada parte do sistema, levando em conta se a sessão válida ou não
function auth_verify(string $pathEnd = "home")
{
    if (!auth_valid()) {
        auth_destroy($pathEnd);
    } else if (isset($_SESSION["auth"]["-path-refresh-"])) {
        unset($_SESSION["auth"]["-path-refresh-"]);
        redirect($_SESSION["auth"]["-path-init-"]);
        //die("<script type=\"text/javascript\">window.location.href=\"" . action($_SESSION["auth"]["-path-init-"]) . "\"</script>");
    }
}

// START TAGS ------------------------------------------------------------------------------

// Retorna uma tag `input` oculta que contém o tipo de método que deve ser aceito no servidor
function tag_method(string $method)
{
    return "<input type=\"hidden\" name=\"_method\" value=\"" . strtoupper($method) . "\">\r\n";
}

// Retorna uma tag `input` oculta que contém o `código CSRF` que é esperado no servidor
function tag_csrf()
{
    return "<input type=\"hidden\" name=\"_csrf\" value=\"" . CSRF::get() . "\">\r\n";
}

// Retorna uma tag `link` que contém endereço do `arquivo de CSS`
function tag_css(string $file)
{
    return "<link rel=\"stylesheet\" href=\"" . URI::css($file) . "\">\r\n";
}

// Retorna uma tag `script` que contém endereço do `arquivo de JS`
function tag_js(string $file)
{
    return "<script type=\"text/javascript\" src=\"" . URI::js($file) . "\"></script>\r\n";
}

// Retorna as tags que incluem o favicon de uma página web
function tag_favicon(string $file, string $type = "x-icon")
{
    $img = URI::img($file);
    return "<link rel=\"icon\" href=\"$img\" type=\"image/$type\"/>
	<link rel=\"shortcut icon\" href=\"$img\" type=\"image/$type\"/>
    <link rel=\"apple-touch-icon\" href=\"$img\" type=\"image/$type\"/>\r\n";
}

// Retorna uma tag `img` que contém endereço do `arquivo de imagem`
function tag_img(string $file, array $attr = [])
{
    $attrs = "";
    foreach ($attr as $key => $value) {
        $attrs .= "$key=\"$value\" ";
    }
    return "<img ${attrs}src=\"" . URI::img($file) . "\">\r\n";
}

// Retorna uma tag `p` que contém uma mensagem que foi salva em `$_SESSION["info"]`
function tag_message(string $key_info, array $attr = [], string $tag = "p")
{
    $attrs = "";
    foreach ($attr as $key => $value) {
        $attrs .= "$key=\"$value\" ";
    }
    $message = message($key_info);
    if ($message === null || empty($message)) {
        return "";
    }
    return "<$tag ${attrs}>$message</$tag>\r\n";
}

// Retorna uma tag `a` que contém um link de uma rota
function tag_a(string $name, string $path, array $attr = [], ...$params)
{
    $link = Route::link($path, $params);
    $attrs = "";
    foreach ($attr as $key => $value) {
        $attrs .= "$key=\"$value\" ";
    }
    return "<a href=\"$link\" ${attrs}>$name</a>\r\n";
}

// STOP TAGS -------------------------------------------------------------------------------

// Retorna o caminho base da pasta `public`
function folder_public(string $file)
{
    return URI::public($file);
}

// Retorna o caminho base da pasta `storage`
function folder_storage(string $path = "")
{
    $path = DataManager::path(__DIR__ . "/../storage/$path");
    if (realpath($path) !== false) {
        $path = realpath($path);
    }
    if (DataManager::exist($path) == "FOLDER") {
        $path .=  "/";
    }
    return DataManager::path($path);
}

// Retorna a saída de um `var_export` com pré formatação
function var_export_format(&$data)
{
    $dump = var_export($data, true);
    $dump = preg_replace('#(?:\A|\n)([ ]*)array \(#i', '[', $dump); // Starts
    $dump = preg_replace('#\n([ ]*)\),#', "\n$1],", $dump); // Ends
    $dump = preg_replace('#=> \[\n\s+\],\n#', "=> [],\n", $dump); // Empties
    if (gettype($data) == 'object') { // Deal with object states
        $dump = str_replace('__set_state(array(', '__set_state([', $dump);
        $dump = preg_replace('#\)\)$#', "])", $dump);
    } else {
        $dump = preg_replace('#\)$#', "]", $dump);
    }
    return $dump;
}

// Imprime na tela os valores que foram passados nos parâmetros
function dumpl(...$array)
{
    // $array = func_get_args();
    echo !defined('CLI') ? "<style>.dumpd{font-weight:bolder;font-size:1.2em;background:#eee;}</style><pre class='dumpd'>" : '';
    foreach ($array as $key => $value) {
        echo !defined('CLI') ? "<hr>" : "\r\n";
        echo var_export_format($value);
        echo !defined('CLI') ? "<hr>" : "\r\n";
        unset($array[$key]);
    }
    unset($array);
    echo !defined('CLI') ? "</pre>" : "";
}

// Imprime na tela os valores que foram passados nos parâmetros e encerra a execução do código php
function dumpd(...$array)
{
    dumpl(...$array);
    die();
}

// Retorna a conversão de um objeto em array
function object_to_array($object)
{
    $output = [];
    foreach ((array) $object as $key => $value) {
        $output[preg_replace('/\000(.*)\000/', '', $key)] = $value;
    }
    return $output;
}

// Retorna a conversão de um array de objetos em array
function parse_array_object_to_array($array)
{
    foreach ($array as $key => $value) {
        if (is_object($value)) {
            $value = object_to_array($value);
            $array[$key] = $value;
        }
        if (is_array($value)) {
            $value = parse_array_object_to_array($value);
            $array[$key] = $value;
        }
    }
    return $array;
}

// Retorna o reseultado de um requisição HTTP por meio do método POST
function curl_http_post(string $action, array $data, bool $isJson = false)
{
    $cURL = curl_init();
    curl_setopt($cURL, CURLOPT_URL, $action);
    curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cURL, CURLOPT_POST, true);
    if ($isJson) {
        curl_setopt($cURL, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        $data = json_encode($data);
    }
    curl_setopt($cURL, CURLOPT_POSTFIELDS, $data);
    $output = curl_exec($cURL);
    curl_close($cURL);
    return json_decode($output) ?? $output;
}

// Retorna um texto de `CamelCase` para um todo em minúscilo separado por `Underline`
function decamelize(string $string)
{
    $string = preg_replace("/(?<=\\w)(?=[A-Z])/","_$1", $string);
    return strtolower($string);
}

// Retorna a conversão da string para número
function string_to_number($val)
{
    if (is_numeric($val)) {
        $int = (int) $val;
        $float = (float) $val;
        $val = ($int == $float) ? $int : $float;
    }
    return $val;
}

// Retorna o MimeType de um arquivo com base na sua extensão
function get_mime_type(string $ext)
{
    $ext = strtolower($ext);
    $mime = [
        'txt' => 'text/*; charset=utf-8',
        'htm' => 'text/html; charset=utf-8',
        'html' => 'text/html; charset=utf-8',
        'xhtml' => 'application/xhtml+xml; charset=utf-8',
        'php' => 'text/plain; charset=utf-8',
        'ino' => 'text/plain; charset=utf-8',
        'java' => 'text/plain; charset=utf-8',
        'c' => 'text/plain; charset=utf-8',
        'cpp' => 'text/plain; charset=utf-8',
        'kt' => 'text/plain; charset=utf-8',
        'sql' => 'application/sql',
        'php' => 'text/plain; charset=utf-8',
        'css' => 'text/css; charset=utf-8',
        'js' => 'application/javascript; charset=utf-8',
        'json' => 'application/json; charset=utf-8',
        'xml' => 'application/xml; charset=utf-8',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',

        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        'webp' => 'image/webp',

        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',

        // audio/video
        'wav' => 'audio/wav',
        'oga' => 'audio/ogg',
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'ogv' => 'video/ogg',
        'mov' => 'video/quicktime',
        'mp4' => 'video/mp4',
        'avi' => 'video/x-msvideo',
        'mpeg' => 'video/mpeg',
        'webm' => 'video/webm',

        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',

        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'docx' => 'application/msword',
        'xlsx' => 'application/vnd.ms-excel',
        'pptx' => 'application/vnd.ms-powerpoint',

        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',

        // font
        'otf' => 'font/otf',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
    ];
    foreach ($mime as $key => $value) {
        if ($key == $ext) {
            return $value;
        }
    }
    return 'application/octet-stream';
}
