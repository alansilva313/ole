<?php

namespace src\services;

use src\config\Conn;
use PDO;
use Exception;

require("../../vendor/autoload.php");

// Definir cabeçalhos CORS
header("Access-Control-Allow-Origin: *"); // Permitir requisições de qualquer origem
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Cabeçalhos permitidos


class AutenticarUsuario
{
    private string $email;
    private string $password;
    private string $isAdmin;
    private ?string $userName = null; 
    private ?string $userEmail = null; 
    private ?string $idIntegrador = null;

    public function __construct()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data["email"]) || empty($data["email"])) {
            echo json_encode([
                "message" => "O email do usuário é obrigatório!",
                "status" => 400,
                "success" => false
            ]);
            exit;
        }

        if (!isset($data["password"]) || empty($data["password"])) {
            echo json_encode([
                "message" => "A senha do usuário é obrigatória!",
                "status" => 400,
                "success" => false
            ]);
            exit;
        }

        $this->email = $data["email"];
        $this->password = $data["password"];
    }

    public function verificarSenha(): bool
    {
        try {
            $sql = "SELECT id, integraId, isAdmin, name, email, password FROM users WHERE email = ? AND isActive = 1";
            $stmt = Conn::connect()->prepare($sql);
            $stmt->bindValue(1, $this->email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

               
                if (password_verify($this->password, $user["password"])) {
                    
                    $this->id = $user["id"];
                    $this->userName = $user["name"];
                    $this->userEmail = $user["email"];
                    $this->idIntegrador = $user["integraId"];
                    $this->isAdmin = $user["isAdmin"];
                    return true;
                } else {
                    echo json_encode([
                        "message" => "Falha na autenticação.",
                        "status" => 401,
                        "success" => false,
                    ]);
                    exit;
                }
            } else {
                echo json_encode([
                    "message" => "Falha na autenticação.",
                    "status" => 404,
                    "success" => false,
                ]);
                exit;
            }
        } catch (Exception $e) {
            echo json_encode([
                "message" => "Erro ao processar a solicitação: " . $e->getMessage(),
                "status" => 500,
                "success" => false,
            ]);
            exit;
        }
    }






public function dadosAutenticador() {
    try {
        error_log("ID Integrador: " . $this->idIntegrador);
        $sql = "SELECT user, pass, token FROM oletv_integrador WHERE id = ?";
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, (int)$this->idIntegrador, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            error_log("Nenhum dado encontrado para id=" . $this->idIntegrador);
            return null;
        }
    } catch (\PDOException $e) {
        error_log("Erro PDO: " . $e->getMessage());
        return null;
    }
}

    

    public function autenticar()
    {
        if ($this->verificarSenha()) {
            echo json_encode([
                "message" => "Usuário autenticado com sucesso!",
                "data" => [
                    "user" => [
                        "id" => $this->id,
                        "name" => $this->userName,
                        "email" => $this->userEmail,
                        "integrador" => $this->idIntegrador,
                        "isAdmin" => $this->isAdmin
                    ],
                    "access" => $this->dadosAutenticador(),
                    "accessToken" => $this->dadosAutenticador()["token"] ?? null,
                    "refreshToken" => null // se não tiver refresh token pode deixar null
                ],
                "success" => true,
                "status" => 200
            ]);

        } else {
            echo json_encode([
                "message" => "Falha na autenticação.",
                "status" => 401,
                "success" => false
            ]);
        }
    }
}


$autenticauser = new AutenticarUsuario();
$autenticauser->autenticar();
