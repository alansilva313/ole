<?php


namespace src\models;


use src\config\AuthOle;

require("../../vendor/autoload.php");


class ListarBloqueioContrato extends ListarContratoClienteOle
{

    public function listarbloqueio(){
        $authOle = new AuthOle;
        
        $ch = curl_init($authOle->acessoole()["ole_endpoint"]."/contratos/listarbloqueios/".$this->listcontratoAtivos()[0]["id_contrato"]."/true");

        
      
        $data = [
            "keyapi" => $authOle->acessoole()["ole_token"],
            "login" => $authOle->acessoole()["ole_user"],
            "pass" => $authOle->acessoole()["ole_pass"],
            
        ];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);

        // Enviando os dados como form-data
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        // Definir cabeçalho indicando envio em form-data
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: multipart/form-data',
            
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => $error];
        }

        curl_close($ch);

        $responseData = json_decode($response, true);

      
        

        return $responseData["bloqueios"][0]["id"];

       
        
    }
}

