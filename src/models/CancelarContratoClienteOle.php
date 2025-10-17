<?php

namespace src\models;

use src\config\AuthOle;
use src\models\EditarStatusContrato;
use src\models\ListarContratoClienteOle;
use src\models\InserirLogDoCliente;

require("../../vendor/autoload.php");



class CancelarContratoClienteOle
{
   
    public function cancela()
{
    $ListarContratoClienteOle = new ListarContratoClienteOle;
    $contratosData = json_decode($ListarContratoClienteOle->retornacontratocompleto(), true);

    // Garantir que existe ao menos um contrato
    if (
        !isset($contratosData['contratos']) ||
        !is_array($contratosData['contratos']) ||
        empty($contratosData['contratos'])
    ) {
        return json_encode([
            'status' => false,
            'mensagem' => 'Nenhum contrato encontrado para cancelamento',
            'resposta_api' => $contratosData
        ]);
    }

    // Aqui você escolhe qual contrato cancelar (exemplo: sempre o primeiro)
    $idContrato = $contratosData['contratos'][0]['id'];

    echo "Id do contrato encontrado: " . $idContrato;

    $authOle = new AuthOle;
    $ch = curl_init($authOle->acessoole()["ole_endpoint"] . "/contratos/cancelar/" . $idContrato);

    $data = [
        "keyapi" => $authOle->acessoole()["ole_token"],
        "login" => $authOle->acessoole()["ole_user"],
        "pass" => $authOle->acessoole()["ole_pass"],
    ];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: multipart/form-data'
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return json_encode(['error' => $error]);
    }

    curl_close($ch);

    $responseData = json_decode($response, true);

    // Atualiza status no sistema
    $InserirLogDoCliente = new InserirLogDoCliente;
    $InserirLogDoCliente->insertlog("Contrato cliente!", "O contrato foi cancelado com sucesso!", "success");
    $EditarStatusContrato = new EditarStatusContrato;
    $EditarStatusContrato->alterarStatus("Cancelado");

    return json_encode($responseData);
}

}







