<?php

namespace App\Service;
        
use App\Service\ConverterService as Converter;

class ImageService
{
    
    private $image;
    private $name;
    private $path;
    private $width;
    private $height;    

    public function __construct(string $name, string $path)
    {
        $this->name = pathinfo($name)["filename"];
        $this->path = $path;
        list($this->width, $this->height) = getimagesize($this->path);
        $this->image = imagecreatefromstring(file_get_contents($this->path));
    }

    private function getPixel(int $x, int $y) :array
    {
        $index = imagecolorat($this->image, $x, $y);
        $pixel = imagecolorsforindex($this->image, $index);
        array_splice($pixel, 3, 1);
        return $pixel;
    }

    private function setPixel(int $x, int $y, int $red, int $green, int $blue) :bool
    {
        $color = imagecolorallocate($this->image, $red, $green, $blue);
        return imagesetpixel($this->image, $x, $y, $color);
    }

    public function setMessage(string $message) :bool
    {
        $initContent = "{{mInit}}";
        $endContent = "{{mEnd}}";
        $message = $initContent . base64_encode($message) . $endContent;
        $position = 0;
        for ($x = 0; $x < $this->width; $x++) { 
            for ($y = 0; $y < $this->height; $y++) {                 
                if (!isset($message[$position])) {
                    return true;
                }
                $pixel = (object) $this->getPixel($x, $y);
                $redBin = Converter::decToBin($pixel->red);
                $greenBin = Converter::decToBin($pixel->green);
                $blueBin = Converter::decToBin($pixel->blue);
                $char = $message[$position];
                $charBin = Converter::charToBin($char);
                $redBin = substr($redBin, 0, 5) . substr($charBin, 0, 3);
                $greenBin = substr($greenBin, 0, 6) . substr($charBin, 3, 2);
                $blueBin = substr($blueBin, 0, 5) . substr($charBin, 5, 3);
                $red = Converter::binToDec($redBin);
                $green = Converter::binToDec($greenBin);
                $blue = Converter::binToDec($blueBin);
                if ($this->setPixel($x, $y, $red, $green, $blue)) {
                    $position++;
                } else {
                    return false;
                }
            }    
        }
        return false;
    }

    public function getMessage() :?string
    {
        $initContent = "{{mInit}}";
        $endContent = "{{mEnd}}";
        $lenContent = strlen($initContent . $endContent);
        $indexInit = false;
        $indexEnd = false;
        $message = "";
        $countPixels = 0;
        for ($x = 0; $x < $this->width; $x++) { 
            for ($y = 0; $y < $this->height; $y++) {                 
                $pixel = (object) $this->getPixel($x, $y);
                $countPixels++;
                $redBin = Converter::decToBin($pixel->red);
                $greenBin = Converter::decToBin($pixel->green);
                $blueBin = Converter::decToBin($pixel->blue);
                $charBin = substr($redBin, 5, 3) . substr($greenBin, 6, 2) . substr($blueBin, 5, 3);
                $char = Converter::binToChar($charBin);
                $message .= $char;
                if ($indexInit === false) {
                    $indexInit = strpos($message, $initContent);
                }
                if ($indexEnd === false) {
                    $indexEnd = strripos($message, $endContent);                    
                }
                if ($countPixels > $lenContent && $indexInit === false && $indexEnd === false) {
                    return $message = null;
                }
                if ($indexInit !== false && $indexEnd !== false) {
                    return $message = base64_decode(substr($message, $indexInit + strlen($initContent), $indexEnd - strlen($endContent) + 1));
                }
            }
        }
        return $message;
    }

    public function download()
    {        
        ob_clean();
        ob_start();        
        imagepng($this->image);
        $content = ob_get_clean();
        imagedestroy($this->image);
        output()
            ->content($content)
            ->name($this->name . ".png")
            ->download(2)
            ->go();
    }

}
