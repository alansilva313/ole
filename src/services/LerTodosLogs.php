<?php


namespace src\services;

use src\config\Conn;

require("../../vendor/autoload.php");


// Definir cabeçalhos CORS
header("Access-Control-Allow-Origin: *"); // Permitir requisições de qualquer origem
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Cabeçalhos permitidos


class LerTodosLogs
{

    public function buscarlogs(string $idIntegra)
    {


        $method = $_SERVER["REQUEST_METHOD"];

        if($method !== "GET"){
            return json_encode([
                "message" => "Acesso bloqueado!"
            ]);
        }

        $headers = getallheaders();

        if(!isset($headers["idIntegra"]) || $headers["idIntegra"] === ""){
            echo json_encode([
                "message" => "Parametro obrigatorio não enviado na requisição!",
                "status" => 404
            ]);

            exit;
        }
                
        $sql = "SELECT * FROM v_logs_insert WHERE idIntegra = ?";
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $headers["idIntegra"]);
        $stmt->execute();

        if($stmt){
            if($stmt->rowCount() > 0){
                $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                return json_encode([
                    "message" => "logs",
                     "status" => 200,
                    "data" => $results
                ]);
            }

            return json_encode([
                "message" => "nenhum log encontrado!",
                "status" => 200
            ]);
        }
    }

}



$LerTodosLogs = new LerTodosLogs;
echo $LerTodosLogs->buscarlogs("2");