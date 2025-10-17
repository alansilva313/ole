<?php

namespace src\services;

use PDO;
use PDOException;
use src\config\Conn;

require("../../vendor/autoload.php");
// Definir cabeçalhos CORS
header("Access-Control-Allow-Origin: *"); // Permitir requisições de qualquer origem
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization, idIntegra"); // Cabeçalhos permitidos
header("Content-Type: application/json"); // Definindo o retorno como JSON

class CostPlan {

    // Função para receber o idIntegra como parâmetro
    public function cost($idIntegra) {
        // Verificar se o parâmetro foi fornecido
        if (empty($idIntegra)) {
            echo json_encode([
                "message" => "Parâmetro idIntegra é obrigatório",
                "StatusCode" => 404,
            ]);
            return;
        }

        try {
            $sql = "SELECT 
                        (SELECT COUNT(*) 
                         FROM clientes 
                         WHERE integraId = ?) AS total_clientes,
                        c.plan,
                        c.cost
                    FROM 
                        cost c
                    WHERE 
                        (SELECT COUNT(*) 
                         FROM clientes 
                         WHERE integraId = ?)
                    LIMIT 1";

            // Preparar e executar a consulta
            $stmt = Conn::connect()->prepare($sql);
            $stmt->bindValue(1, $idIntegra);
            $stmt->bindValue(2, $idIntegra);
            $stmt->execute();

            // Verificar se há resultados
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return json_encode($result);
            }

            // Caso não haja resultados, retornar array vazio
            return json_encode([]);

        } catch (PDOException $e) {
            echo json_encode([
                "message" => "Erro no banco de dados: " . $e->getMessage(),
                "StatusCode" => 500,
            ]);
        }
    }
}

// Verificar se o idIntegra foi passado pela URL como parâmetro GET
if (isset($_GET['idIntegra'])) {
    $idIntegra = $_GET['idIntegra'];

    $CostPlan = new CostPlan();
    echo $CostPlan->cost($idIntegra);
} else {
    echo json_encode([
        "message" => "Parâmetro idIntegra não foi fornecido",
        "StatusCode" => 400,
    ]);
}
