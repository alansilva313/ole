<?php

namespace src\models;

use src\config\AuthOle;

require("../../vendor/autoload.php");


class ListarClienteOle extends PegarDadosPessoaisWebHook
{
    

    public function listclient()
    {

        $authOle = new AuthOle;
        $ch = curl_init($authOle->acessoole()["ole_endpoint"]."/clientes/buscacpfcnpj/" . $this->pegarCpf());
        

        
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
            'Content-Type: multipart/form-data'
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => $error];
        }

        curl_close($ch);

        // Decodifica a resposta JSON
        $responseData = json_decode($response, true);

        // Verifica se a chave "lista" existe e se contém dados
        if (isset($responseData['lista']) && !empty($responseData['lista'])) {
            // Retorna o ID do primeiro item da lista
            return $responseData['lista'][0]['id'];
        } else {
            return "ID não encontrado.";
        }
    }
}

