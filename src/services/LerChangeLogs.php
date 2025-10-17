<?php

namespace src\services;

use PDO;
use PDOException;
use src\config\Conn;

require("../../vendor/autoload.php");

// Definir cabeçalhos CORS
header("Access-Control-Allow-Origin: *"); // Permitir requisições de qualquer origem
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Cabeçalhos permitidos


class LerChangeLogs
{

    public function changelog(){


        try{

            $sql = "SELECT * FROM changelog ORDER BY id ASC";
            $stmt = Conn::connect()->prepare($sql);
            $stmt->execute();

            if($stmt && $stmt->rowCount() > 0){
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return json_encode([
                    "message" => "Listando changelogs",
                    "data" => $result
                ]);
            }

        }catch(PDOException $e){
            echo $e->getMessage();
        }

    }

}




$LerChangeLogs = new LerChangeLogs;
echo $LerChangeLogs->changelog();