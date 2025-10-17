<?php

namespace src\services;

use src\config\Conn;

require("../../vendor/autoload.php");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

class ListarContratos
{
    public function buscarcontratos()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method !== "GET") {
            echo json_encode([
                "message" => "Acesso bloqueado!"
            ]);
            exit;
        }

        // Obter parâmetro da URL
        $idIntegra = isset($_GET['idIntegra']) ? $_GET['idIntegra'] : '';

        if (empty($idIntegra)) {
            echo json_encode([
                "message" => "Parametro obrigatorio não enviado na requisição!",
                "status" => 404
            ]);
            exit;
        }

        $sql = "SELECT * FROM oletv_contratos WHERE idIntegra = ?";
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $idIntegra);
        $stmt->execute();

        if ($stmt) {
            if ($stmt->rowCount() > 0) {
                $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                echo json_encode([
                    "message" => "Listando contratos",
                    "quantidade" => count($results),
                    "status" => 200,
                    "data" => $results,
                ]);
            } else {
                echo json_encode([
                    "message" => "Não há contratos cadastrados!",
                    "status" => 200
                ]);
            }
        }
    }
}

$ListarContratos = new ListarContratos();
echo $ListarContratos->buscarcontratos();
