<?php

namespace src\services;

use src\config\Conn;

require("../../vendor/autoload.php");

// Cabeçalhos CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=utf-8");

// ✅ Resposta rápida para preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

class AlterarSenhaUsuario
{
    public function alterarSenha()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method !== "PUT") {
            http_response_code(405);
            echo json_encode([
                "message" => "Método não permitido. Use PUT.",
                "status" => 405
            ]);
            return;
        }

        $input = json_decode(file_get_contents("php://input"), true);

        $id = $input['id'] ?? null;
        $password = $input['password'] ?? null;

        if (empty($id) || empty($password)) {
            http_response_code(400);
            echo json_encode([
                "message" => "Parâmetros obrigatórios 'id' e 'password' não foram enviados.",
                "status" => 400
            ]);
            return;
        }

        $senhaHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $sql = "UPDATE users SET password = :password WHERE id = :id";
            $stmt = Conn::connect()->prepare($sql);
            $stmt->bindParam(":password", $senhaHash);
            $stmt->bindParam(":id", $id, \PDO::PARAM_INT);

            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Senha atualizada com sucesso!",
                    "status" => 200
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    "message" => "Erro ao atualizar senha.",
                    "status" => 500
                ]);
            }
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "message" => "Erro no banco de dados: " . $e->getMessage(),
                "status" => 500
            ]);
        }
    }
}

// Executar
$alterarSenha = new AlterarSenhaUsuario;
$alterarSenha->alterarSenha();
