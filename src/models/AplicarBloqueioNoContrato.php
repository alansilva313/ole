<?php

namespace src\models;

use src\config\AuthOle;

require("../../vendor/autoload.php");


class AplicarBloqueioNoContrato
{

    public function aplicar(string | int $motivoSuspensao)
    {
        $listarContrato = new ListarContratoClienteOle;
        $contratonumber = $listarContrato->listcontrato()[0]["id_contrato"];
        $contratonumberAtivos = $listarContrato->listcontratoAtivos();
    

      
        $authOle = new AuthOle;



    
        
    
        $ch = curl_init($authOle->acessoole()["ole_endpoint"]."/contratos/bloqueio/".$contratonumberAtivos[0]['id_contrato']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);

      


        $data = [
            "keyapi" => $authOle->acessoole()["ole_token"],
            "login" => $authOle->acessoole()["ole_user"],
            "pass" => $authOle->acessoole()["ole_pass"],
            "motivo_suspensao" => $motivoSuspensao
        ];
        $formData = http_build_query($data);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded; charset=UTF-8"
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => $error];
        }

        curl_close($ch);

        $responseData = json_decode($response, true);

        echo json_encode($responseData);
    }
}