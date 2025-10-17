<?php

namespace src\models;

use PDOException;
use src\config\Conn;

require("../../vendor/autoload.php");



class AdicionarIdBloqueio{


    public function idbloqueio(string $id){

        try{

            $pessoa = new PegarDadosPessoaisWebHook();
 

            $sql = "UPDATE oletv_contratos SET id_bloqueio = ? WHERE client_txid = ?";
            $stmt = Conn::connect()->prepare($sql);
            $stmt->bindValue(1, $id);
            $stmt->bindValue(2, $pessoa->pegarCpf());
            $stmt->execute();

           

        }catch(PDOException $e){

            echo $e->getMessage();
        }

    }


    public function removeridbloqueio(){

        try{

            $pessoa = new PegarDadosPessoaisWebHook();
 

            $sql = "UPDATE oletv_contratos SET id_bloqueio = ? WHERE client_txid = ?";
            $stmt = Conn::connect()->prepare($sql);
            $stmt->bindValue(1, null);
            $stmt->bindValue(2, $pessoa->pegarCpf());
            $stmt->execute();

           

        }catch(PDOException $e){

            echo $e->getMessage();
        }

    }
}