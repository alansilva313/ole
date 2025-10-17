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

class ClientesPorDia {

    public function diario() {
        $headers = getallheaders();
    
        // Verificar se o cabeçalho 'idIntegra' está presente
        if (isset($headers['idIntegra'])) {
            $idIntegra = $headers['idIntegra'];
        } else {
            echo json_encode([
                "message" => "Parametros obrigatórios não foram enviados",
                "StatusCode" => 404,
            ]);
            return;
        }
    
        // Obtendo os parâmetros de startDate e endDate da URL
        $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
        $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;
    
        try {
            // Verifica se as datas estão definidas
            if ($startDate && $endDate) {
                $sql = "SELECT DATE(created_at) AS dia, COUNT(*) AS quantidade_clientes FROM 
                        clientes WHERE integraId = ? AND DATE(created_at) BETWEEN ? AND ? 
                        GROUP BY DATE(created_at) ORDER BY dia ASC";
                $stmt = Conn::connect()->prepare($sql);
                $stmt->bindValue(1, $idIntegra);
                $stmt->bindValue(2, $startDate);
                $stmt->bindValue(3, $endDate);
            } else {
                // Se não houver datas, executa a consulta sem filtro de data
                $sql = "SELECT DATE(created_at) AS dia, COUNT(*) AS quantidade_clientes FROM 
                        clientes WHERE integraId = ? GROUP BY DATE(created_at) ORDER BY dia ASC";
                $stmt = Conn::connect()->prepare($sql);
                $stmt->bindValue(1, $idIntegra);
            }
    
            $stmt->execute(); // Executa a consulta
    
            // Verifica se há resultados
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($result); // Retornando o resultado como JSON
            } else {
                echo json_encode(["message" => "Nenhum dado encontrado", "StatusCode" => 204]);
            }
    
        } catch (PDOException $e) {
            echo json_encode(["error" => $e->getMessage(), "StatusCode" => 500]);
        }
    }
    
}

$ClientesPorDia = new ClientesPorDia;
$ClientesPorDia->diario();
