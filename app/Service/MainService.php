<?php

namespace App\Service;

use App\Service\ImageService as Image;

class MainService
{

    private function validate_file_image(array $file)
    {
        if ($file["error"] !== 0) {
            back(["error_hide_message_in_image" => "File error (" . $file["error"] . ")"]);    
        }
        if ($file["size"] <= 0) {
            back(["error_hide_message_in_image" => "File error (" . $file["size"] . ") size"]);
        }
        if (strpos($file["type"], "image/") === false) {
            back(["error_hide_message_in_image" => "File error (" . $file["type"] . ") type"]);
        }
    }
    
    public function hide_message_in_image(array $file, string $message)
    {
        $this->validate_file_image($file);
        $name = $file["name"];
        $path = $file["tmp_name"];
        $image = new Image($name, $path);
        if ($image->setMessage($message) === false) {
            back(["error_hide_message_in_image" => "Falha ao ocultar mensagem."]);            
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
            back(["error_show_message_in_image" => "A imagem não possui mensagem oculta."]);
        }
        return $message;
    }    

}
