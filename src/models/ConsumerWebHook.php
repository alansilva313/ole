<?php

namespace src\models;

require("../../vendor/autoload.php");

use src\models\InserirClienteLocalmente;
use src\models\InserirClienteNaOle;
use src\models\PegarDadosPessoaisWebHook;
use src\models\InserirLogDoCliente;





header('Content-Encoding: identity');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Username, Token, Password");
header("Access-Control-Allow-Credentials: true");

class ConsumerWebHook
{
    public static function replaceNullValues($data)
    {
        array_walk_recursive($data, function (&$value) {
            if (is_null($value)) {
                $value = '';
            }
        });
        return $data;
    }

    public static function processarWebHook()
    {
        $log = new InserirLogDoCliente();

        try {
            $input = file_get_contents('php://input');
            $headers = getallheaders();

            if ($input === false || empty($input)) {
                $log->insertlog("Erro ao receber WebHook", "Nenhum dado foi recebido ou o corpo da requisição está vazio.", "error");
                return json_encode([
                    "message" => "Nenhum dado ou dados inválidos foram recebidos no WebHook",
                    "data" => $input,
                    "statusCode" => 400
                ]);
            }

            $data = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $jsonError = json_last_error_msg();
                $log->insertlog("Erro ao decodificar JSON", "Erro: $jsonError", "error");

                return json_encode([
                    "message" => "Não foi possível decodificar os dados JSON: " . $jsonError,
                    "data" => $data,
                    "statusCode" => 400
                ]);
            }

            $log->insertlog("WebHook recebido com sucesso", "JSON decodificado corretamente", "info");

            $data = self::replaceNullValues($data);
            $formattedData = [
                "DataObject" => $data,
                "Headers" => $headers
            ];

            $logFile = __DIR__ . '/datawebhook.json';


            file_put_contents($logFile, json_encode($formattedData, JSON_PRETTY_PRINT));

            
            $log->insertlog("Dados WebHook salvos", "Arquivo salvo em datawebhook.json", "info");

            $StatusContrato = new StatusContrato();
            $statusContratoCliente = $StatusContrato->verificaStatusContrato();

            $log->insertlog("Status do contrato", "Status retornado: $statusContratoCliente", "info");

            if ($statusContratoCliente === "Normal") {
                $log->insertlog("Processo iniciado", "Status do contrato está normal", "info");


              

            
                $inserirClienteNaOle = new InserirClienteNaOle();
                $validarInsercao = $inserirClienteNaOle->insiranaole();

                  $inserirClienteLocal = new InserirClienteLocalmente();
                $clientelocal = $inserirClienteLocal->inserir();

                echo $clientelocal;



                $log->insertlog("Tentando inserir cliente na OLE", json_encode($validarInsercao), "info");


                if (isset($validarInsercao["message"]["error"]["outros"]) || isset($validarInsercao["message"]["error"]["telefone_numero"])) {
                    $log->insertlog("Telefone inválido detectado", json_encode($validarInsercao["message"]), "warning");
                    return;
                }

              

                

                $InserirWebHookCliente = new InserirWebHookCliente();
                $InserirWebHookCliente->inserirwebhook($formattedData["DataObject"]);


                $log->insertlog("Webhook inserido", "Dados do WebHook inseridos na base", "success");

                if (isset($validarInsercao["message"]["error"]["cpf_cnpj"])) {
                    $mensagemCPF = $validarInsercao["message"]["error"]["cpf_cnpj"];
                    $log->insertlog("CPF/CNPJ já cadastrado", "Mensagem: $mensagemCPF", "warning");

                    if ($mensagemCPF === "CPF/CNPJ já cadastrado!") {
                        $verificarContrato = new ListarContratoClienteOle();
                        $naoTemContrato = $verificarContrato->listcontrato();
                        $log->insertlog("Verificando contratos existentes", "Resposta: $naoTemContrato", "info");

                        if ($naoTemContrato === "Nenhum contrato encontrado.") {
                            $cpfCliente = new PegarDadosPessoaisWebHook();
                            $insertPlano = new InsertProduto();
                            $insertPlano->insertPr($cpfCliente->pegarCpf());

                            $log->insertlog("Plano inserido", "Cliente não tinha contrato, plano inserido", "success");
                        } else {
                            $verificarPlano = new VerificarPlanoExiste();
                            $verificarPlano->verificaPlano();
                            $log->insertlog("Plano existente", "Verificação de plano existente feita com sucesso", "info");
                        }
                    }
                }
            } else {
                $log->insertlog("Processo interrompido", "Status do contrato não é 'Normal'. Nenhuma ação foi realizada.", "warning");
                return;
            }

        } catch (\PDOException $e) {
            $log->insertlog("Erro de banco de dados", $e->getMessage(), "error");
            echo "Erro: " . $e->getMessage();
        } catch (\Exception $e) {
            $log->insertlog("Erro inesperado", $e->getMessage(), "error");
            echo "Erro geral: " . $e->getMessage();
        }
    }
}

// Início do processo WebHook
$respostaDoStatus = ConsumerWebHook::processarWebHook();
echo $respostaDoStatus;

