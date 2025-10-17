<?php

namespace src\models;

use src\config\AuthOle;
use src\config\Conn;
use src\models\LerProdutos;
use src\models\InserirProdutoLocal;
use src\models\NivelServico;
use src\models\CancelarContratoClienteOle;

require("../../vendor/autoload.php");

class InsertProduto extends LerProdutos
{
    protected $documentoCliente;
    protected $produtoCode = "";
    protected $adicionaisDados = [];

    public function pegarCpf($cpf)
    {
        $this->documentoCliente = $cpf;
    }

    public function lerUser($cpfCliente)
    {
        $authOle = new AuthOle;
        $ch = curl_init($authOle->acessoole()["ole_endpoint"] . "/clientes/buscacpfcnpj/" . $cpfCliente);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);

        $data = [
            "keyapi" => $authOle->acessoole()["ole_token"],
            "login" => $authOle->acessoole()["ole_user"],
            "pass" => $authOle->acessoole()["ole_pass"],
        ];
        $formData = http_build_query($data);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded; charset=UTF-8"
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => $error];
        }

        curl_close($ch);

        $responseData = json_decode($response, true);

        return isset($responseData['lista'][0]['id']) ? $responseData['lista'][0]['id'] : ['error' => 'ID não encontrado'];
    }

    public function pegarAdicionais()
    {
        $ad1 = $this->contarAdicionaisPorProdutoPrincipal();
        $ad2 = $this->pegarAdicionaisManual();

        $ad1 = json_decode($ad1, true);
        $ad2 = json_decode($ad2, true);

        $finalValues = [];

        if (!empty($ad1)) {
            $finalValues = array_merge($finalValues, array_values($ad1));
        }

        if (!empty($ad2)) {
            $finalValues = array_merge($finalValues, array_values($ad2));
        }

        return $finalValues;
    }

    public function insertPr($cpfCliente)
    {
        try {
            $idCliente = $this->lerUser($cpfCliente);
               
            if (isset($idCliente['error'])) {
                return json_encode(['error' => 'Falha ao obter ID do cliente']);
            }

            $authOle = new AuthOle;
            $ch = curl_init($authOle->acessoole()["ole_endpoint"] . "/contratos/inserir");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_POST, true);

            $logFilePath = '../models/datawebhook.json';
            $logJson = file_get_contents($logFilePath);
            $logData = json_decode($logJson, true);

            if (!$logData) {
                die("Erro ao ler ou decodificar o arquivo JSON.");
            }

            $contratoOrigem = $logData['DataObject']['Contract']['Number'];
            $BuscarApenasEmail = new PegarDadosPessoaisWebHook;
            $emailUsuario = $BuscarApenasEmail->pegarEmailAleatorio();
            $NivelServico = new NivelServico;
            $codService = $NivelServico->encontrarNivelMaisAlto();
            $this->produtoCode = $codService;
            $authOle = new AuthOle;

          /*   // Instanciando a classe e chamando o método
            $PegarPatrimonios = new PegarPatrimonios();

            // Pega os MACs organizados (principal e adicionais)
            $dataFromJson = $PegarPatrimonios->pegarMacJson(); */

            $data = [
                "keyapi" => $authOle->acessoole()["ole_token"],
                "login" => $authOle->acessoole()["ole_user"],
                "pass" => $authOle->acessoole()["ole_pass"],
                "id_cliente" => $idCliente,
                "id_contrato_origem" => $contratoOrigem,
                "id_plano_principal" => $codService,
                "mac[]" => "",
                "email_usuario" => $BuscarApenasEmail->pegarEmail(),
            ];

            // Lógica para adicionar equipamentos
            $quantidadeEquipamentos = $this->quantidadeEquipamentos();
            for ($i = 0; $i < $quantidadeEquipamentos; $i++) {
                $data["id_modelo[$i]"] = 7; // Defina o valor conforme necessário
            }

            // Função para adicionar dados adicionais
            $adicionais = $this->contarAdicionaisPorProdutoPrincipal();
            $adicionaisManuais = $this->pegarAdicionaisManual();

            // Adicionar dados adicionais ao array $data
            if (!empty($adicionais)) {
                $this->adicionarDadosAdicionais($data, $adicionais);
            }

            if (!empty($adicionaisManuais)) {
                $this->adicionarDadosAdicionais($data, $adicionaisManuais);
            }

            $formData = http_build_query($data);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/x-www-form-urlencoded; charset=UTF-8"
            ]);

            $response = curl_exec($ch);

            if ($response === false) {
                $error = curl_error($ch);
                curl_close($ch);
                return json_encode(['error' => $error]);
            }

            curl_close($ch);

            $responseData = json_decode($response, true);


          

            $error = json_encode($responseData["retorno_status"]);
       

            if($error === "false"){

                $falhaproduto =  json_encode($responseData["error"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                $CancelarContratoClienteOles = new CancelarContratoClienteOle();
                $CancelarContratoClienteOles->cancela();
           
                $InserirLogDoCliente = new InserirLogDoCliente;
                $InserirLogDoCliente->insertlog("Falha ao inserir produto!", $falhaproduto, "error");



                exit();
            }


          


            $produtoInseridoComSucesso =  json_encode($responseData["retorno_status"]);

           

            if($produtoInseridoComSucesso === "true"){

                $InserirProdutoLocal = new InserirProdutoLocal();
                $InserirProdutoLocal->inserir($this->produtoCode, $this->pegarAdicionais());

                


              
                $novoContrato = $responseData["id"];

                  
                $AtualizarContratoLocalCliente = new AtualizarContratoLocalCliente;
             
                
                $CodigoAtualizado = $AtualizarContratoLocalCliente->atualizarcontrato($novoContrato);
                $this->updatedProcess($cpfCliente);
                if($CodigoAtualizado === true){
                       
                 

                        $alteracaoDeProduto =  json_encode([
                            "message" => "Produto criado com sucesso!",
                            "StatusCode" => 200,
                            "Content" => [
                                "Event" => [
                                    "Text" => "Produto foi atualizado com sucesso!"
                                ]
                            ]
                        ]); 

                      

                        $InserirLogDoCliente = new InserirLogDoCliente;
                        $InserirLogDoCliente->insertlog("Produto foi atualizado com sucesso!",  $alteracaoDeProduto, "success");
                       

                        return;
                }

              
               
                $inseridoComSucesso =  json_encode([
                    "message" => "Produto criado com sucesso!",
                    "StatusCode" => 200,
                    "Content" => [
                        "Event" => [
                            "Text" => "Novo produto adicionado ao cliente!"
                        ]
                    ]
                ]); 


              
                require("./InserirContratoLocalCliente.php");

                $InserirLogDoCliente = new InserirLogDoCliente;
                $InserirLogDoCliente->insertlog("Novo produto criado", $inseridoComSucesso, "success");
            }

          

           
        } catch (\PDOException $e) {
            echo "Error " . $e->getMessage();
        }
    }




    // Função auxiliar para adicionar dados adicionais ao array $data
    public function adicionarDadosAdicionais(&$data, $adicionais)
    {
        if (is_string($adicionais)) {
            $adicionaisArray = json_decode($adicionais, true);

            if (json_last_error() === JSON_ERROR_NONE && !empty($adicionaisArray)) {
                foreach ($adicionaisArray as $key => $value) {
                    $data[$key] = $value;
                }
            } elseif (json_last_error() !== JSON_ERROR_NONE) {
                echo "Erro: Dados adicionais não puderam ser decodificados.";
            }
        } elseif (is_array($adicionais) && !empty($adicionais)) {
            foreach ($adicionais as $key => $value) {
                $data[$key] = $value;
            }
        } else {
            echo "Erro: Dados adicionais não são um array ou string JSON.";
        }
    }

    public function updatedProcess($cpfCliente)
    {
        try {
            $sql = "UPDATE clientes SET processed = 1 WHERE cpf_cnpj = ?";
            $stmt = Conn::connect()->prepare($sql);
            $stmt->bindValue(1, $cpfCliente);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}



