<?php

namespace src\services;

use src\config\Conn;
use PDOException;

require("../../vendor/autoload.php");
// Definir cabeçalhos CORS
header("Access-Control-Allow-Origin: *"); // Permitir requisições de qualquer origem
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Cabeçalhos permitidos


if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

class LogsDistintosClientes
{
    public function getlogsclientes()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method !== "GET") {
            $this->sendResponse(405, "Método não permitido!");
        }

        $idIntegra = isset($_GET['idIntegra']) ? $_GET['idIntegra'] : '';

        // Verificar se o parâmetro idIntegra está presente e não está vazio
        if (empty($idIntegra)) {
            $this->sendResponse(400, "Parâmetro obrigatório não enviado na requisição!", [
                "parametro" => $idIntegra
            ]);
        }

        $documentClient = isset($_GET['documentClient']) ? $_GET['documentClient'] : '';

        try {
            $sql = "SELECT * FROM v_logs_insert WHERE idIntegra = ?";

            // Se existir o documentClient, adicionar a condição ao SQL
            if (!empty($documentClient)) {
                $sql .= " AND clientes.cpf_cnpj = ?";
            }

            $stmt = Conn::connect()->prepare($sql);
            $stmt->bindValue(1, $idIntegra);  // Bind do idIntegra

            // Se o documentClient estiver definido, atribuir ao segundo bindValue
            if (!empty($documentClient)) {
                $stmt->bindValue(2, $documentClient);
            }

            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $resultados = $stmt->fetchAll(); // Processar os resultados
                $this->sendResponse(200, "Listando log", [
                    "message" => "log do(s) cliente(s)",
                    "total" => count($resultados),
                    "success" => true,
                    "data" => $resultados
                ]);
            } else {
                $this->sendResponse(404, "Nenhum log encontrado para o cliente.");
            }
        } catch (PDOException $e) {
            $this->sendResponse(500, "Erro ao buscar logs: " . $e->getMessage());
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

        // Corrigido: echo para exibir a resposta
        echo json_encode($response);
        exit;
    }
}


$LogsDistintosClientes = new LogsDistintosClientes();
$LogsDistintosClientes->getlogsclientes(); // Não precisa de 'echo' aqui
