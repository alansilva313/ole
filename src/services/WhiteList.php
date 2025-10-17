<?php

namespace src\services;

use PDOException;
use src\config\Conn;

require("../../vendor/autoload.php");

// Definir cabeçalhos CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

class WhiteList
{
    public function create()
    {
        $idIntegra = isset($_GET['idIntegra']) ? $_GET['idIntegra'] : '';
        $inputData = json_decode(file_get_contents("php://input"), true);

        $nome = isset($inputData['nome']) ? $inputData['nome'] : '';
        $documento = isset($inputData['documento']) ? $inputData['documento'] : '';

        if (empty($nome) || empty($documento) || empty($idIntegra)) {
            echo json_encode([
                "message" => "Parâmetro obrigatório não encontrado!",
                "success" => false,
                "status" => 400
            ]);
            return;
        }

        try {
            // Verificar se o documento já existe
            $sqlCheck = "SELECT * FROM oletv_whitelist WHERE documento = ? AND idIntegra = ?";
            $stmtCheck = Conn::connect()->prepare($sqlCheck);
            $stmtCheck->bindValue(1, $documento);
            $stmtCheck->bindValue(2, $idIntegra);
            $stmtCheck->execute();

            if ($stmtCheck->rowCount() > 0) {
                // Documento já existe, retornar mensagem
                echo json_encode([
                    "message" => "O documento já está na lista.",
                    "success" => false,
                    "status" => 409 // Código de status para conflito
                ]);
                return;
            }

            // Inserir dados no banco de dados se o documento não existir
            $sql = "INSERT INTO oletv_whitelist (nome, documento, idIntegra) VALUES (?, ?, ?)";
            $stmt = Conn::connect()->prepare($sql);
            $stmt->bindValue(1, $nome);
            $stmt->bindValue(2, $documento);
            $stmt->bindValue(3, $idIntegra);
            $stmt->execute();

            if ($stmt && $stmt->rowCount() > 0) {
                echo json_encode([
                    "message" => "Cadastrado com sucesso!",
                    "success" => true,
                    "status" => 200
                ]);
            } else {
                echo json_encode([
                    "message" => "Falha ao cadastrar, tente novamente mais tarde!",
                    "success" => false,
                    "status" => 500
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode([
                "message" => "Erro no servidor: " . $e->getMessage(),
                "success" => false,
                "status" => 500
            ]);
        }
    }
}

$WhiteList = new WhiteList;
echo $WhiteList->create();
