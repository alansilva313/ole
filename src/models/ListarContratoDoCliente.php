<?php

namespace src\models;
require("../../vendor/autoload.php");

class ListarContratoDoCliente
{
    public function listarcontrato()
    {
        $ListarContrato = new ListarContratoClienteOle;

        // Obtém o array de contratos diretamente
        $contratoData = $ListarContrato->retornacontratocompleto();

        // Verifica se a chave 'contratos' existe e se há contratos disponíveis
        if (isset($contratoData['contratos']) && count($contratoData['contratos']) > 0) {
            // Pega o status do primeiro contrato
            $dadoscontrato = $contratoData['contratos'];
            return json_encode($dadoscontrato);
        }

        // Se não houver contratos ou a chave 'contratos' não existir
        return "Status não encontrado";
    }
}

