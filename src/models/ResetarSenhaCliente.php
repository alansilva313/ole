<?php

namespace src\models;

use src\config\AuthOle;

require_once("../../vendor/autoload.php");

// Configuração CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Tratar requisição OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

class ResetarSenhaCliente
{
    public function reset()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method !== "GET") {
            $this->sendResponse(405, "Método não permitido!");
        }

        $contratoCliente = $_GET['contratoCliente'] ?? '';
        $emailCliente = $_GET['emailCliente'] ?? '';

        if (empty($contratoCliente)) {
            $this->sendResponse(400, "Parâmetro contratoCliente é obrigatório!");
        }

        if (empty($emailCliente)) {
            $this->sendResponse(400, "Parâmetro emailCliente é obrigatório!");
        }

        $authOle = new AuthOle();

        $url = $authOle->acessoole()["ole_endpoint"] . "/contratos/alterarusuario/" . $contratoCliente;
        $data = [
            "keyapi" => $authOle->acessoole()["ole_token"],
            "login" => $authOle->acessoole()["ole_user"],
            "pass" => $authOle->acessoole()["ole_pass"],
            "email_usuario" => $emailCliente
        ];

        // Inicia cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: multipart/form-data',
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            $this->sendResponse(500, "Erro ao se conectar ao servidor OleTV", ["error" => $error]);
        }

        curl_close($ch);

        // Tentar decodificar JSON
        $responseData = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Se não for JSON válido, salvar resposta bruta para depuração
            $this->sendResponse(500, "Resposta inválida da OleTV", ["raw_response" => $response]);
        }

        // Verifica se a API retornou erro
        if (isset($responseData["error"]) && !empty($responseData["error"])) {
            $this->sendResponse(200, $responseData["error"]);
        }

        // Se chegou aqui, significa que deu certo
        echo json_encode([
            "retorno_status" => true,
            "msg" => "Foi enviado para o e-mail {$emailCliente} as instruções de redefinição de senha.",
            "data" => $responseData
        ]);
    }

    private function sendResponse($statusCode, $message, $extra = null)
    {
        http_response_code($statusCode);
        $response = [
            "message" => $message,
            "status" => $statusCode
        ];

        if ($extra && is_array($extra)) {
            $response = array_merge($response, $extra);
        }

        echo json_encode($response);
        exit;
    }
}

$ResetarSenhaCliente = new ResetarSenhaCliente();
$ResetarSenhaCliente->reset();
