<?php

namespace src\models;

use src\config\AuthOle;

require("../../vendor/autoload.php");

class DesbloquearContratoDocliente
{

    public function removerbloqueio($idbloqueio)
    {
        $listarContrato = new ListarContratoClienteOle;
        $contratonumber = $listarContrato->listcontratoAtivos()[0]["id_contrato"];

      

        $ListarBloqueioContrato = new ListarBloqueioContrato;
        $bloqueio = $ListarBloqueioContrato->listarbloqueio();
        if($bloqueio === []){
            return;
        };

       

    

        $uridesbloqueio = "/contratos/desbloqueio/".$contratonumber."/".$idbloqueio;

      

        $authOle = new AuthOle;
        $ch = curl_init($authOle->acessoole()["ole_endpoint"].$uridesbloqueio);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);

        


        $data = [
            "keyapi" => $authOle->acessoole()["ole_token"],
            "login" => $authOle->acessoole()["ole_user"],
            "pass" => $authOle->acessoole()["ole_pass"],
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
		$log = new InserirLogDoCliente;
            $log->insertlog("Erro ao Desbloquear cliente!", $error, "warning");
            return ['error' => $error];
        }

        curl_close($ch);

        $responseData = json_decode($response, true);
 	$log = new InserirLogDoCliente;
        $log->insertlog("Cliente desbloqueado com sucesso!", "Desbloqueio realizado", "success");
        return json_encode($responseData);
    }

}



