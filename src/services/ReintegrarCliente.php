<?php

namespace src\services;

require("../../vendor/autoload.php");

use PDO;
use PDOException;
use src\config\Conn;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Username, Token, Password");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}



class ReintegrarCliente
{
    public function reintegrarcliente()
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'];

            if ($method !== "GET" && $method !== "POST") {
                return $this->sendResponse(200, "Método não permitido!");
            }

            // Obter parâmetros da requisição
            $idIntegra = isset($_GET['idIntegra']) ? $_GET['idIntegra'] : (isset($_POST['idIntegra']) ? $_POST['idIntegra'] : '');
            $nome_cliente = isset($_GET['nome_cliente']) ? $_GET['nome_cliente'] : (isset($_POST['nome_cliente']) ? $_POST['nome_cliente'] : '');

            // Verificar parâmetros obrigatórios
            if (empty($idIntegra)) {
                return $this->sendResponse(200, "Parâmetro 'idIntegra' obrigatório não enviado na requisição!", [
                    "parametro" => 'idIntegra'
                ]);
            }

            // Preparar a consulta
            $sql = "SELECT * FROM ole_webhook_cliente WHERE webhook LIKE ? AND idIntegra = ? ORDER BY created_at DESC LIMIT 1";
            $stmt = Conn::connect()->prepare($sql);

            // Passar os valores para a consulta
            $stmt->bindValue(1, "%$nome_cliente%", PDO::PARAM_STR);
            $stmt->bindValue(2, $idIntegra, PDO::PARAM_STR); // Considerando que idIntegra pode ser uma string

            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("Resultado da consulta: " . print_r($result, true)); // Log do resultado da consulta
                
                if ($result) {
                    $webhookData = json_decode($result["webhook"], true);
                    
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return json_encode($webhookData); // Retorne os dados decodificados
                    } else {
                        return $this->sendResponse(200, "Erro ao decodificar o JSON armazenado: " . json_last_error_msg());
                    }
                }
            } else {
                return $this->sendResponse(200, "Nenhum resultado encontrado.", null);
            }
        } catch (PDOException $e) {
            return $this->sendResponse(500, $e->getMessage(), null);
        }
    }

    public function reenviarWebhook()
    {
        // Pega os parâmetros idIntegra e nome_cliente do FormData
        $idIntegra = isset($_POST['idIntegra']) ? $_POST['idIntegra'] : '';
        $nome_cliente = isset($_POST['nome_cliente']) ? $_POST['nome_cliente'] : '';

        // Verifica se os parâmetros obrigatórios estão presentes
        if (empty($idIntegra) || empty($nome_cliente)) {
            error_log("Parâmetros idIntegra ou nome_cliente ausentes.");
            return $this->sendResponse(400, "Parâmetros obrigatórios ausentes.", [
                "parametro" => empty($idIntegra) ? "idIntegra" : "nome_cliente"
            ]);
        }

        // Decodifica a resposta do método reintegrarcliente
        $inputData = json_decode($this->reintegrarcliente(), true);

        // Se a resposta já foi enviada, não prosseguir
        if (isset($inputData['status'])) {
            return;
        }
          
        $url = "https://hub.sysprov.com.br/integraoletv/webhook/voalle.php";

        // Obtém os dados de autenticação do FormData
        $headers = [];
        if (isset($_POST['Username'])) {
            $headers[] = "Username: " . $_POST['Username'];
        }
        if (isset($_POST['Token'])) {
            $headers[] = "Token: " . $_POST['Token'];
        }
        if (isset($_POST['Password'])) {
            $headers[] = "Password: " . $_POST['Password'];
        }

        // Log dos cabeçalhos de autenticação
        error_log("Cabeçalhos de autenticação: " . print_r($headers, true));

        // Constrói a URL com os parâmetros
        $url .= "?idIntegra=" . urlencode($idIntegra) . "&nome_cliente=" . urlencode($nome_cliente);

        // Inicializa o cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Configura os cabeçalhos de autenticação na requisição cURL
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, [
            'Content-Type: application/json', // Cabeçalho Content-Type
            'Accept: application/json', // Cabeçalho Accept
        ]));

        curl_setopt($ch, CURLOPT_POST, true); // Define que será uma requisição POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($inputData)); // Envia os dados como JSON

        // Log dos dados enviados
        error_log("Dados enviados: " . json_encode($inputData));

        $response = curl_exec($ch);

        // Verifica se houve algum erro
        if ($response === false) {
            error_log('Erro cURL: ' . curl_error($ch)); // Log do erro
            echo json_encode(['Erro' => curl_error($ch)]);
        } else {
            error_log('Resposta do webhook: ' . $response); // Log da resposta do webhook
            echo $response; // Retorna a resposta para o cliente
        }

        // Fecha a sessão cURL
        curl_close($ch);
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

$ReintegrarCliente = new ReintegrarCliente();
echo $ReintegrarCliente->reenviarWebhook();
