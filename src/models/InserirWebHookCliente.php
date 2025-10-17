<?php 



namespace src\models;

use src\config\Conn;

require("../../vendor/autoload.php");


class InserirWebHookCliente {

    public function inserirwebhook(string | array $webhook)
    {


        $integra = new BuscarIntegrador;
        $idIntegrador = $integra->buscarIntegrador()[0]["id"];

        $webhooktext = $webhook;

        $pegarDadosCliente = new PegarDadosPessoaisWebHook;
        $cpfCliente = $pegarDadosCliente->pegarCpf();


        $sql = "INSERT INTO ole_webhook_cliente (idIntegra, webhook, documento_cliente, nome_cliente) VALUES (?, ?, ?, ?)";
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $idIntegrador);
        $stmt->bindValue(2, json_encode($webhooktext));
        $stmt->bindValue(3, $cpfCliente);
        $stmt->bindValue(4, $pegarDadosCliente->pegarNomeCompleto());

        $stmt->execute();

        if($stmt){
            if($stmt->rowCount() > 0){

                return true;
            }

            return false;
        }
    }
}


