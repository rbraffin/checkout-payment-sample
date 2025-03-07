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
            "success" => $_SERVER['SERVER_NAME'] . "/success",
            "failure" => $_SERVER['SERVER_NAME'] . "/failure", 
            "pending" => $_SERVER['SERVER_NAME'] . "/pending"
        );

        $preference->auto_return = "approved"; 

        $preference->notification_url = $_SERVER['SERVER_NAME'] . "/notification?source_news=webhooks";

        $preference->save();

        $response = array(
            'id' => $preference->id,
        ); 
        echo json_encode($response);
        break;        
    case '/success':
        require 'success.php';
        break;
    case '/pending':
        require 'pending.php';
        break;
    case '/failure':
        require 'failure.php';
        break;
    case '/notification':
	http_response_code(200);
	file_put_contents("post-type.txt", $_POST["type"]);
        switch($_POST["type"]) {
            case "payment":
                $payment = MercadoPago\Payment.find_by_id($_POST["id"]);
		file_put_contents("switch.txt", "payment");
                break;
            case "plan":
                $plan = MercadoPago\Plan.find_by_id($_POST["id"]);
		file_put_contents("switch.txt", "plan");
                break;
            case "subscription":
                $plan = MercadoPago\Subscription.find_by_id($_POST["id"]);
		file_put_contents("switch.txt", "sub");
                break;
            case "invoice":
                $plan = MercadoPago\Invoice.find_by_id($_POST["id"]);
		file_put_contents("switch.txt", "invoice");
                break;
        }
        file_put_contents("latest-payment.txt", $payment);
        $json = file_get_contents("php://input");
        file_put_contents("latest-json.txt", $json);
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
