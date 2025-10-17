<?php

namespace src\models;

use src\config\Conn;

require("../../vendor/autoload.php");

class InserirLogDoCliente
{
    

    public function insertlog(string $title, string $action, string $codeLog)
    {

        date_default_timezone_set('America/Sao_Paulo');
        $BuscarIntegrador = new BuscarIntegrador;
        $idIntegrador = $BuscarIntegrador->buscarIntegrador()[0]["id"];

        $Buscarcpf = new PegarDadosPessoaisWebHook;
        $documentClient = $Buscarcpf->pegarCpf();

        $sql = "INSERT INTO logs_insert (idIntegra, codeLog, title, acao, id_cliente, created_at) VALUES (?, ?, ?, ?, ?,now())";
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $idIntegrador);
        $stmt->bindValue(2, $codeLog);
        $stmt->bindValue(3, $title);
        $stmt->bindValue(4, $action);
        $stmt->bindValue(5, $documentClient);

        $stmt->execute();

        if ($stmt) {
            if ($stmt->rowCount() > 0) {
                return true;
            }
            return false;
        }
    }
}
