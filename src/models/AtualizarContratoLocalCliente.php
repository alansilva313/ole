<?php

namespace src\models;

use src\config\Conn;

require("../../vendor/autoload.php");

class AtualizarContratoLocalCliente
{

    public function atualizarcontrato(string $contratoNovo)
    {


        $pegarDadosCliente = new PegarDadosPessoaisWebHook;
        $cpfCliente = $pegarDadosCliente->pegarCpf();
        $contratoVoalle = $pegarDadosCliente->pegarNumeroContrato();

        /* $pegarContratoOle = new ListarContratoClienteOle;
        $idContratoOle = json_decode($pegarContratoOle->retornacontratocompleto(), true); */



        $sql = "UPDATE oletv_contratos SET ole_contract_number = ? WHERE voalle_contract_number = ? AND client_txid = ?";
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $contratoNovo);
        $stmt->bindValue(2, $contratoVoalle);
        $stmt->bindValue(3, $cpfCliente);

        $stmt->execute();

        if($stmt){
            if($stmt->rowCount()> 0){

                return true;
            }

            return false;
        }
    }
}

