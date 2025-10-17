<?php

namespace src\models;

use src\config\AuthOle;

require("../../vendor/autoload.php");

class GerarTokenVoalle
{

    
    protected static $grant_type;
    protected static $scope;
    protected static $client_id;
    protected static $client_secret;
    protected static $syndata;





    public function __construct()
    {

        $authOle = new AuthOle;

        self::$grant_type = "client_credentials";
        self::$scope = "syngw";
        self::$client_id = $authOle->acessoole()["voalle_client_id"];
        self::$client_secret = $authOle->acessoole()["voalle_client_secret"];
        self::$syndata = $authOle->acessoole()["voalle_syndata"];
        
    }



    public static function gerartoken(): string
    {
        $authOle = new AuthOle;
        $url = rtrim($authOle->acessoole()["voalle_endpoint"], '/');
        $ch = curl_init($url.":45700/connect/token");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/x-www-form-urlencoded"
        ));

        $data = http_build_query(array(
            "grant_type" => self::$grant_type,
            "scope" => self::$scope,
            "client_id" => self::$client_id,
            "client_secret" => self::$client_secret,
            "syndata" => self::$syndata
        ));

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return "cURL Error: " . $error;
        }

        curl_close($ch);

        // Assumindo que $response é uma string JSON, você pode decodificá-la
        $responseData = json_decode($response, true);
        
        if (isset($responseData['access_token'])) {
            return $responseData['access_token'];
        } else {
            return "Error: Token não encontrado na resposta.";
        }
    }

}


