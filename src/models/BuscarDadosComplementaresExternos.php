<?php

namespace src\models;

use src\config\AuthOle;

require("../../vendor/autoload.php");

class BuscarDadosComplementaresExternos extends GerarTokenVoalle
{
    public function buscadados()
    {
        $TratarCpfCliente = new TratarCpfCliente();
        $tx_client = $TratarCpfCliente->cpfSemPontuacao();

        $authOle = new AuthOle;
        $url = rtrim($authOle->acessoole()["voalle_endpoint"], '/');

        $ch = curl_init($url.":45715/external/integrations/thirdparty/people/txid/" . $tx_client);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->gerartoken()
        ));

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return json_encode(['error' => $error]);
        }

        curl_close($ch);

        $responseData = json_decode($response, true);


        /* echo json_encode($responseData); */

        if (json_last_error() !== JSON_ERROR_NONE) {
            return json_encode(['error' => 'Erro ao decodificar o JSON da resposta.', 'raw_response' => $response]);
        }

        // Verifica se o objeto `response` existe e contém os dados esperados
        $primaryObject = $responseData["response"] ?? [];


        

        // Extrai apenas a parte desejada
        $desiredObject = [
            "id" => $primaryObject["id"] ?? null,
            "name" => $primaryObject["name"] ?? '',
            "name2" => $primaryObject["name2"] ?? '',
            "txId" => $primaryObject["txId"] ?? '',
            "email" => $primaryObject["email"] ?? '',
            "status" => $primaryObject["status"] ?? 0,
            "mainAddress" => $primaryObject["mainAddress"] ?? []
        ];

        
        return $desiredObject["mainAddress"];
    }


    public function buscadadosNascimento()
    {
        $TratarCpfCliente = new TratarCpfCliente();
        $tx_client = $TratarCpfCliente->cpfSemPontuacao();

        $authOle = new AuthOle;

        $ch = curl_init($authOle->acessoole()["voalle_endpoint"].":45715/external/integrations/thirdparty/people/txid/" . $tx_client);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->gerartoken()
        ));

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return json_encode(['error' => $error]);
        }

        curl_close($ch);

        $responseData = json_decode($response, true);

        

        if (json_last_error() !== JSON_ERROR_NONE) {
            return json_encode(['error' => 'Erro ao decodificar o JSON da resposta.', 'raw_response' => $response]);
        }

        // Verifica se o objeto `response` existe e contém os dados esperados
        $primaryObject = $responseData["response"] ?? [];
       
        $dataOriginal = $primaryObject["birthDate"]; // "1984-11-07T00:00:00"
        $dataFormatada = date('d/m/Y', strtotime($dataOriginal));
      

     

      
        return $dataFormatada;
    }
}

