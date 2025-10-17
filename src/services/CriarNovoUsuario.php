<?php

namespace src\services;

use src\config\Conn;

require("../../vendor/autoload.php");


// Definir cabeçalhos CORS
header("Access-Control-Allow-Origin: *"); // Permitir requisições de qualquer origem
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Cabeçalhos permitidos




class CriarNovoUsuario
{
    private string $email;
    private string $password;

    public function __construct()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data["email"]) || empty($data["email"])) {
            echo json_encode([
                "message" => "O Email do usuário é obrigátorio!",
                "status" => 404
            ]);
            exit;
        }

        if (!isset($data["password"]) || empty($data["password"])) {
            echo json_encode([
                "message" => "A senha do usuário é obrigátoria!",
                "status" => 404
            ]);
            exit;
        }

        $this->email = $data["email"];
        $this->password = $data["password"];
    }

    protected function verificarUsuarioExist()
    {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $this->email);
        $stmt->execute();

        if ($stmt && $stmt->rowCount() > 0) {
            return true;
        }

        return false;
    }

    public function criptpass()
    {
        return password_hash($this->password, PASSWORD_DEFAULT);
    }

    public function novousuario()
    {
        $method = $_SERVER["REQUEST_METHOD"];

        if ($method !== "POST") {
            echo json_encode([
                "message" => "Acesso bloqueado!"
            ]);
            exit;
        }

  
        $integraId = isset($_GET['integraId']) ? $_GET['integraId'] : '';

        if (empty($integraId)) {
            echo json_encode([
                "message" => "Parametro obrigatorio não enviado na requisição!",
                "status" => 404
            ]);
            exit;
        }

        if ($this->verificarUsuarioExist()) {
            echo json_encode([
                "message" => "email indisponivel para cadastro!",
                "status" => 400
            ]);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data["name"]) || empty($data["name"])) {
            echo json_encode([
                "message" => "O nome do usuário é obrigátorio!",
                "status" => 404
            ]);
            exit;
        }

        $sql = "INSERT INTO users (integraId, name, email, username, password, isActive, isAdmin) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $integraId);
        $stmt->bindValue(2, $data["name"]);
        $stmt->bindValue(3, $data["email"]);
        $stmt->bindValue(4, $data["username"]);
        $stmt->bindValue(5, $this->criptpass());
        $stmt->bindValue(6, $data["isActive"]);
        $stmt->bindValue(7, $data["isAdmin"]);

        $stmt->execute();

        if ($stmt && $stmt->rowCount() > 0) {
            echo json_encode([
                "message" => "Novo usuário criado com sucesso!",
                "data" => [
                    "nome" => $data["name"],
                    "email" => $data["email"],
                    "ativo" => $data["isActive"]
                ],
                "status" => 200
            ]);
        } else {
            echo json_encode([
                "message" => "Falha ao criar usuário, tente novamente!",
                "status" => 400
            ]);
        }
    }
}

$criarUsuario = new CriarNovoUsuario;
echo $criarUsuario->novousuario();
