<?php

namespace App\Services;

use App\Services\ImageService as Image;

class MainService
{

    private function validate_file_image(array $file)
    {
        $message = "";
        if ($file["error"] !== 0) {
            $message .= "error " . $file["error"] . ", ";            
        }
        if ($file["size"] <= 0) {
            $message .=  "size " . $file["size"] . ", ";
        }
        if (strpos($file["type"], "image/") === false) {
            $message .=  "type " . $file["type"] . ", ";
        }
        if (!empty($message)) {
            message("error_hide_message_in_image", "File ($message)");
            redirect()->back();
        }
    }
    
    public function hide_message_in_image(array $file, string $message)
    {
        $this->validate_file_image($file);
        $name = $file["name"];
        $path = $file["tmp_name"];
        $image = new Image($name, $path);
        if ($image->setMessage($message) === false) {
            message("error_hide_message_in_image", "Falha ao ocultar mensagem.");
            redirect()->back();
        }
        return $image->download();
    }

    public function show_message_in_image(array $file)
    {
        $this->validate_file_image($file);
        $name = $file["name"];
        $path = $file["tmp_name"];
        $image = new Image($name, $path);
        $message = $image->getMessage();
        if ($message === null) {
            message("error_show_message_in_image", "A imagem não possui mensagem oculta.");
            redirect()->back();
        }
        return $message;
    }    

}
