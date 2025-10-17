<?php
namespace src\models;
require("../../vendor/autoload.php");
use src\config\Conn;
use src\models\NivelServico;

class InserirProdutoLocal
{


    private $txid;
    
    private function verificaCpf(){
        $sql = "SELECT txid FROM produtos_cliente WHERE txid = ?";
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $this->txid);
        $stmt->execute();

        if($stmt){
            if($stmt->rowCount() > 0){
                return true;
            }

            return false;
        }
    }


    public function inserir($cod_plano_ole, $cods_adicionais){

        $NivelServico = new NivelServico;
        $cod_plano = $NivelServico->titleCod();

        $cpf = new PegarDadosPessoaisWebHook;
        $txid = $cpf->pegarCpf();
        $this->txid = $txid;

        $ListarClienteOle = new ListarClienteOle;
        $id_cliente = $ListarClienteOle->listclient();


        if($this->verificaCpf() === true){
            return "";
        }

        $buscarIntegrador = new BuscarIntegrador;
        $idIntegrador = $buscarIntegrador->buscarIntegrador();

        $sql = "INSERT INTO produtos_cliente (idIntegra, title, txid, id_cliente, cod_plano_ole, cods_adicionais)
        VALUES (?, ?, ?, ?, ?, ?)
        ";

        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $idIntegrador[0]["id"]);
        $stmt->bindValue(2, $cod_plano);
        $stmt->bindValue(3, $this->txid);
        $stmt->bindValue(4, $id_cliente);
        $stmt->bindValue(5, $cod_plano_ole);
        $stmt->bindValue(6, json_encode($cods_adicionais));

        $stmt->execute();


        if($stmt){
            if($stmt->rowCount() > 0){
                return true;
            }
        }
    }
}


