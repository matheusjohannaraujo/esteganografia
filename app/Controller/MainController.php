<?php

namespace App\Controller;

use App\Service\MainService;

class MainController
{

    /*

        Creating routes from the methods of a controller dynamically
        ------------------------------------------------------------------------------------------------
        This array below configures how the route works
        ------------------------------------------------------------------------------------------------
        array $CONFIG = [
            'method' => 'POST',
            'csrf' => false,
            'jwt' => false,
            'name' => 'test.create'
        ]
        ------------------------------------------------------------------------------------------------
        To use the route, it is necessary to inform the name of the Controller, the name of the Method 
        and the value of its parameters, the `array parameter $CONFIG` being only for configuration
        ------------------------------------------------------------------------------------------------
        Examples of use the routes:

            Controller = MainController
            Method = action
            Call = MainController@action(...params)
        ------------------------------------------------------------------------------------------------
            | HTTP Verb | MainController@method   | PATH ROUTE
        ------------------------------------------------------------------------------------------------
            | GET       | MainController@index    | /main/index
            | POST      | MainController@create   | /main/create
            | GET       | MainController@new      | /main/new
            | GET       | MainController@edit     | /main/edit/1
            | GET       | MainController@show     | /main/show/1
            | PUT       | MainController@update   | /main/update/1
            | DELETE    | MainController@destroy  | /main/destroy/1
        ------------------------------------------------------------------------------------------------
            
    */

    // This variable informs that the public methods of this controller must be automatically mapped in routes
    private $generateRoutes;
    private $mainService;

    public function __construct()
    {
        $this->mainService = new MainService;
    }

    public function hide_message_in_image(array $CONFIG = ["method" => "POST", "csrf" => true])
    {
        $file = input_file("image")[0];
        $message = input_post("message");        
        return $this->mainService->hide_message_in_image($file, $message);
    }

    public function show_message_in_image(array $CONFIG = ["method" => "POST", "csrf" => true])
    {
        $file = input_file("image")[0];
        $message = $this->mainService->show_message_in_image($file);        
        info("message", $message);
        return redirect("home");
    }    

}
