<?php

namespace src\models;

use PDOException;
use src\config\Conn;

require("../../vendor/autoload.php");



class EditarStatusContrato{


    public function alterarStatus(string $status){

        try{

            $pessoa = new PegarDadosPessoaisWebHook();


            $sql = "UPDATE oletv_contratos SET voalle_contract_status = ? WHERE client_txid = ?";
            $stmt = Conn::connect()->prepare($sql);
            $stmt->bindValue(1, $status);
            $stmt->bindValue(2, $pessoa->pegarCpf());
            $stmt->execute();

           

        }catch(PDOException $e){

            echo $e->getMessage();
        }

    }
}