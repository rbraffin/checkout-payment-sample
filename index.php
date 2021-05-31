<?php

require 'vendor/autoload.php';

MercadoPago\SDK::setAccessToken('APP_USR-334491433003961-030821-12d7475807d694b645722c1946d5ce5a-725736327');
MercadoPago\SDK::setIntegratorId("dev_24c65fb163bf11ea96500242ac130004");

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

switch($path){
    case '':
    case '/':
        require 'index.html';
        break;
    case '/create_preference':     
        $json = file_get_contents("php://input");
        $data = json_decode($json);

        $preference = new MercadoPago\Preference();

        // Definindo o pagador
        $payer = new MercadoPago\Payer();
        $payer->name = "Lalo";
        $payer->surname = "Landa";
        $payer->email = "test_user_92801501@testuser.com";
        $payer->phone = array(
            "area_code" => "55",
            "number" => "98529-8743"
        );
        $payer->address = array(
            "street_name" => "Insurgentes Sur",
            "street_number" => 1602,
            "zip_code" => "78134-190"
        );
        $preference->payer = $payer;

        // Definindo o item
        $item = new MercadoPago\Item();
        $item->id ="1234";
        $item->title = $data->title;
        $item->description = $data->description;
        $item->quantity = $data->quantity;
        $item->unit_price = $data->price;
        $item->picture_url = $data->image;
        $preference->items = array($item);

        $preference->external_reference = "romulo@marks.agency";

        $preference->payment_methods = array(
            "excluded_payment_methods" => array(
                array("id" => "amex")
            ),
            "installments" => 6
        );
    
        $preference->back_urls = array(
            "success" => $_SERVER['SERVER_NAME'] . "/feedback",
            "failure" => $_SERVER['SERVER_NAME'] . "/failure", 
            "pending" => $_SERVER['SERVER_NAME'] . "/pending"
        );

        $preference->auto_return = "approved"; 

//        $preference->notification_url = "https://rbraffin-mp-commerce-php.herokuapp.com/notification.php";

        $preference->save();

        $response = array(
            'id' => $preference->id,
        ); 
        echo json_encode($response);
        break;        
    case '/feedback':
        $resposta = array(
            'Payment' => $_GET['payment_id'],
            'Status' => $_GET['status'],
            'MerchantOrder' => $_GET['merchant_order_id']        
        ); 
        echo json_encode($resposta);
        break;
    //Server static resources
    default:
        $file = $path;
        $extension = end(explode('.', $path));
        $content = 'text/html';
        switch($extension){
            case 'js': $content = 'application/javascript'; break;
            case 'css': $content = 'text/css'; break;
            case 'png': $content = 'image/png'; break;
            case 'jpg': $content = 'image/jpeg'; break;
        }
        header('Content-Type: '.$content);
        readfile($file);          
}