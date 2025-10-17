<?php
// Captura os dados recebidos no WebHook (POST/JSON)
$inputData = file_get_contents('php://input');

// Captura os cabeçalhos da requisição
$headers = getallheaders();

// Define o destino (ConsumerWebHook.php)
$destinationUrl = 'https://webhooks.sysprov.com.br/ole/src/models/ConsumerWebHook.php';

// Inicializa o cURL para fazer a requisição para ConsumerWebHook.php
$ch = curl_init($destinationUrl);

// Configura o cURL para uma requisição POST
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $inputData);  // Passa os dados recebidos no corpo original

// Repassa os cabeçalhos originais
$headerArray = [];
foreach ($headers as $key => $value) {
    $headerArray[] = "$key: $value";
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);

// Define outras opções necessárias
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Executa a requisição
$response = curl_exec($ch);

// Verifica se ocorreu algum erro
if ($response === false) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro ao processar o redirecionamento', 'error' => curl_error($ch)]);
} else {
    // Envia a resposta de volta ao cliente original
    echo $response;
}

// Fecha a sessão do cURL
curl_close($ch);
