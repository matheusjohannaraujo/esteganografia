<?php

namespace App\Controllers;

use App\Services\MainService;
use Lib\AES_256;

class MainController
{

    private $generateRoutes;
    private $mainService;
    private $aes;

    public function __construct()
    {
        $this->mainService = new MainService;
        $this->aes = new AES_256;
    }

    public function hide_message_in_image(array $CONFIG = ["method" => "POST", "csrf" => true])
    {
        $file = input_file("image")[0];
        $message = input_post("message");
        $message = $this->aes->encrypt_cbc($message);
        return $this->mainService->hide_message_in_image($file, $message);
    }

    public function show_message_in_image(array $CONFIG = ["method" => "POST", "csrf" => true])
    {
        $file = input_file("image")[0];
        $message = $this->mainService->show_message_in_image($file);
        $message = $this->aes->decrypt_cbc($message);
        message("message", $message);
        return redirect()->action("home");
    }    

}
