<?php

namespace src\services;

use src\config\Conn;

require("../../vendor/autoload.php");

// Definir cabeçalhos CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, idintegra");
header('Content-Type: application/json; charset=utf-8'); // importante para resposta JSON

class ListarUsuarios
{
    public function buscarusuarios()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method !== "GET") {
            http_response_code(405); // Method Not Allowed
            return json_encode([
                "message" => "Método não permitido. Use GET.",
                "status" => 405
            ]);
        }

        // Preferência 1: Receber o idIntegra via query string GET
        $idIntegra = isset($_GET['idIntegra']) ? trim($_GET['idIntegra']) : null;

        // Alternativa: receber via header (descomente se quiser usar)
        /*
        $headers = getallheaders();
        $idIntegra = $headers['idintegra'] ?? null;
        */

        if (empty($idIntegra)) {
            http_response_code(400); // Bad Request
            return json_encode([
                "message" => "Parâmetro 'idIntegra' obrigatório não enviado na requisição!",
                "status" => 400
            ]);
        }

        $sql = "SELECT id, integraId, name, email, username, isActive, created_at FROM users WHERE integraId = ? AND dev != 1";
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $idIntegra);
        $stmt->execute();

        if ($stmt) {
            if ($stmt->rowCount() > 0) {
                $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                http_response_code(200);
                return json_encode([
                    "message" => "Listando usuários",
                    "quantidade" => count($results),
                    "status" => 200,
                    "data" => $results,
                ]);
            } else {
                http_response_code(200);
                return json_encode([
                    "message" => "Não há usuários cadastrados!",
                    "quantidade" => 0,
                    "status" => 200,
                    "data" => [],
                ]);
            }
        } else {
            http_response_code(500);
            return json_encode([
                "message" => "Erro ao executar consulta no banco de dados",
                "status" => 500,
            ]);
        }
    }
}

$ListarUsuarios = new ListarUsuarios;
echo $ListarUsuarios->buscarusuarios();
