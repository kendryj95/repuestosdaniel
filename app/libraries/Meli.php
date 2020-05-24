<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* CREADO POR KENDRY
*/

class Meli
{
    public function __construct() {
    }

    public function __get($var) {
        return get_instance()->$var;
    }
    
    public function getAccessToken($code)
    {
        $cliente = curl_init();
        curl_setopt($cliente, CURLOPT_URL, "https://api.mercadolibre.com/oauth/token?grant_type=authorization_code&client_id={$this->Settings->ml_id}&client_secret={$this->Settings->ml_secretkey}&code=$code&redirect_uri={$this->Settings->ml_redirecturi}");
        curl_setopt($cliente, CURLOPT_POST , 1);
        curl_setopt($cliente, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($cliente);
        curl_close($cliente);

        return json_decode($response, true);
    }

    public function getCategories()
    {
        $cliente = curl_init();
        curl_setopt($cliente, CURLOPT_URL, "https://api.mercadolibre.com/sites/MLA/categories");
        curl_setopt($cliente, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($cliente);
        curl_close($cliente);

        return json_decode($response, false);
    }

    public function publish($access_token, $data, $photos = array())
    {
        $brand = [
            "id" => $data['brand_ml'] ? explode("_",$data['brand_ml'])[0] : "-1",
            "name" => $data['brand_ml'] ? explode("_",$data['brand_ml'])[1] : null
        ];

        $datos = [
            "title" => $data['titulo_ml'],
            "category_id" => $data['category_ml'],
            "price" => $this->sma->formatDecimal($data['priceml'], 2),
            "currency_id" => "ARS",
            "available_quantity" => intval($data['quantity']),
            "buying_mode" => "buy_it_now",
            "condition" => "new",
            "listing_type_id" => "gold_special", // PUBLICACIÓN CLASICA
            "description" => strip_tags($data['product_details']),
            "pictures" => [],
            "attributes" => [
                [
                    "id" => "BRAND",
                    "value_id" => $brand['id'],
                    "value_name" => $brand['name']
                ]
            ]
        ];

        if (count($photos) > 0) {
            foreach ($photos as $photo) {
                $datos['pictures'][] = [
                    "source" => "https:www.repuestosdaniel.com/nuevafacturacion/assets/uploads/" . $photo
                ];
            }
        }

        $cliente = curl_init();
        curl_setopt($cliente, CURLOPT_URL, "https://api.mercadolibre.com/items?access_token=$access_token");
        curl_setopt($cliente, CURLOPT_POST , 1);
        @curl_setopt($cliente, CURLOPT_POSTFIELDS, json_encode($datos));
        curl_setopt($cliente, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($cliente);
        curl_close($cliente);

        return json_decode($response, true);

    }

    public function getProductById($id)
    {
        $cliente = curl_init();
        curl_setopt($cliente, CURLOPT_URL, "https://api.mercadolibre.com/items/$id");
        curl_setopt($cliente, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($cliente);
        curl_close($cliente);

        return json_decode($response, true);
    }

    public function updateProduct($datos, $id, $access_token)
    {
        $cliente = curl_init();
        curl_setopt($cliente, CURLOPT_URL, "https://api.mercadolibre.com/items/$id?access_token=$access_token");
        curl_setopt($cliente, CURLOPT_CUSTOMREQUEST , "PUT");
        @curl_setopt($cliente, CURLOPT_POSTFIELDS, json_encode($datos));
        curl_setopt($cliente, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($cliente);
        curl_close($cliente);
        
        return json_decode($response, true);
    }
}