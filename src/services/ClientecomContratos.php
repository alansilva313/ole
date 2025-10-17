<?php

namespace src\services;

use src\config\Conn;

require("../../vendor/autoload.php");


header("Access-Control-Allow-Origin: *"); // Permitir requisições de qualquer origem
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Cabeçalhos permitidos



class ClientecomContratos
{
    public function buscarcliente()
    {
        $method = $_SERVER['REQUEST_METHOD'];
    
        if ($method !== "GET") {
            $this->sendResponse(405, "Método não permitido!");
        }
    
        $idIntegra = isset($_GET['idIntegra']) ? $_GET['idIntegra'] : '';
    
        if (empty($idIntegra)) {
            $this->sendResponse(400, "Parâmetro obrigatório não enviado na requisição!", [
                "parametro" => $idIntegra
            ]);
        }
    
        $sql = "SELECT 
    integraId, 
    SUM(CASE WHEN status_contrato = 'Possui Contrato' THEN 1 ELSE 0 END) AS nao_nulos,
    SUM(CASE WHEN status_contrato = 'Não Possui Contrato' THEN 1 ELSE 0 END) AS nulos
FROM 
    v_clientes_com_sem_contratos 
WHERE 
    integraId = ?
GROUP BY 
    integraId";
    
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $idIntegra);
        $stmt->execute();
    
        if ($stmt) {
            if ($stmt->rowCount() > 0) {
                $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $this->sendResponse(200, "Listando clientes", [
                    "quantidade" => count($results),
                    "data" => $results
                ]);
            } else {
                $this->sendResponse(200, "Não há clientes cadastrados!");
            }
        } else {
            $this->sendResponse(500, "Erro interno do servidor!");
        }
    }
    




    private function sendResponse($statusCode, $message, $data = null)
    {
        http_response_code($statusCode);
        $response = [
            "message" => $message,
            "status" => $statusCode
        ];

        if ($data) {
            $response = array_merge($response, $data);
        }

        echo json_encode($response);
        exit;
    }
}


$ClientecomContratos = new ClientecomContratos();
$ClientecomContratos->buscarcliente();
