<?php

namespace src\services;

use src\config\Conn;

require("../../vendor/autoload.php");

// Cabeçalhos CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// 🔹 Responde imediatamente requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}

class EditarUsuario
{
    private $data;

    public function __construct()
    {
        $this->data = json_decode(file_get_contents("php://input"), true);

        if (empty($this->data)) {
            echo json_encode([
                "message" => "Nenhum dado foi enviado!",
                "status" => 400
            ]);
            exit;
        }
    }

    private function usuarioExiste($id)
    {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function editar()
    {
        $method = $_SERVER["REQUEST_METHOD"];
        if ($method !== "PUT") {
            echo json_encode([
                "message" => "Método não permitido. Use PUT para editar usuários.",
                "status" => 405
            ]);
            exit;
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            echo json_encode([
                "message" => "ID do usuário é obrigatório para edição.",
                "status" => 400
            ]);
            exit;
        }

        if (!$this->usuarioExiste($id)) {
            echo json_encode([
                "message" => "Usuário não encontrado.",
                "status" => 404
            ]);
            exit;
        }

        $camposPermitidos = ['name', 'email', 'username', 'isActive', 'isAdmin'];
        $updates = [];
        $params = [];

        foreach ($camposPermitidos as $campo) {
            if (isset($this->data[$campo]) && $this->data[$campo] !== "") {
                $updates[] = "$campo = ?";
                $params[] = $this->data[$campo];
            }
        }

        if (empty($updates)) {
            echo json_encode([
                "message" => "Nenhum campo foi enviado para atualização.",
                "status" => 400
            ]);
            exit;
        }

        $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
        $stmt = Conn::connect()->prepare($sql);
        foreach ($params as $index => $value) {
            $stmt->bindValue($index + 1, $value);
        }
        $stmt->bindValue(count($params) + 1, $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                "message" => "Usuário atualizado com sucesso!",
                "status" => 200
            ]);
        } else {
            echo json_encode([
                "message" => "Nenhuma alteração foi feita ou erro ao atualizar.",
                "status" => 400
            ]);
        }
    }
}

$editarUsuario = new EditarUsuario();
$editarUsuario->editar();
