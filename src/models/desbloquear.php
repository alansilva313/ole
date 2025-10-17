<?php
require __DIR__ . '/../../vendor/autoload.php';

use src\models\DesbloquearForcado;

// Sempre defina o Content-Type primeiro
header('Content-Type: application/json; charset=utf-8');

// Definir cabeçalhos CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Se for requisição OPTIONS (preflight), apenas finalize
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Dados podem vir via POST (form-data ou JSON)
$idContrato = $_POST['idContrato'] ?? null;
$idBloqueio = $_POST['idBloqueio'] ?? null;

if (!$idContrato || !$idBloqueio) {
    echo json_encode([
        "message"    => "Parâmetros inválidos: informe idContrato e idBloqueio",
        "statusCode" => 400
    ]);
    exit;
}

try {
    $desbloquear = new DesbloquearForcado();
    $result = $desbloquear->desbloquear($idContrato, $idBloqueio);

    echo json_encode([
        "message"    => "Desbloqueio solicitado com sucesso",
        "response"   => $result,
        "statusCode" => 200
    ]);
} catch (Throwable $th) {
    http_response_code(500);
    echo json_encode([
        "message"    => "Erro interno ao processar desbloqueio",
        "error"      => $th->getMessage(),
        "statusCode" => 500
    ]);
}
exit; // 🔑 importante para evitar enviar HTML do Apache
