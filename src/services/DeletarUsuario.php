<?php

namespace src\services;

use src\config\Conn;

require("../../vendor/autoload.php");

// Definir cabeçalhos CORS
header("Access-Control-Allow-Origin: *"); // Permitir requisições de qualquer origem
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization, iduser"); // Cabeçalhos permitidos

// Lidar com requisições OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Retornar OK para requisição preflight
    exit;
}

class DeletarUsuario
{
    public function deletar()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method !== "DELETE") {
            http_response_code(405); // Método não permitido
            echo json_encode([
                "message" => "Acesso bloqueado!"
            ]);
            exit;
        }

        $headers = getallheaders();

        if (!isset($headers["iduser"]) || $headers["iduser"] === "") {
            http_response_code(400); // Bad Request
            echo json_encode([
                "message" => "Parâmetro obrigatório não enviado na requisição!",
                "status" => 400
            ]);
            exit;
        }

        $idUser = $headers["iduser"];

        try {
            $sql = "DELETE FROM users WHERE id = ?";
            $stmt = Conn::connect()->prepare($sql);
            $stmt->execute([$idUser]);

            if ($stmt->rowCount() > 0) {
                http_response_code(200); // OK
                echo json_encode([
                    "message" => "Usuário deletado com sucesso!",
                    "status" => 200
                ]);
            } else {
                http_response_code(404); // Não encontrado
                echo json_encode([
                    "message" => "Usuário não encontrado!",
                    "status" => 404
                ]);
            }
        } catch (\PDOException $e) {
            http_response_code(500); // Erro interno do servidor
            echo json_encode([
                "message" => "Erro ao deletar usuário: " . $e->getMessage(),
                "status" => 500
            ]);
        }
    }
}

$DeletarUsuario = new DeletarUsuario();
$DeletarUsuario->deletar();
