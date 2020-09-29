<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/makemvcss
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2020-06-23
*/

namespace Lib;

use Lib\Route;

class CSRF
{

    public static function create(){
        $_SESSION["_csrf"] = hash("sha256", uniqid() . "_simple_mvcs_" . uniqid());
    }
    
    public static function get(){
        if (!isset($_SESSION["_csrf"])) {
            self::create();
        }
        return $_SESSION["_csrf"];
    }
    
    public static function valid($csrf = false)
    {
        $in = Route::$in;
        $valid = false;
        if ($csrf) {
            $valid = self::get() == $csrf;
        } else {            
            $valid = self::get() == $in->paramReq("_csrf", $in->paramJson("_csrf"));
        }
        if ($valid && $in->paramEnv("CSRF_REGENERATE", false)) {
            self::create();
        }
        return $valid;
    }

}
