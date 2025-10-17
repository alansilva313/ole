<?php

namespace src\models;

require("../../vendor/autoload.php");

use src\config\Conn;

class VerificarPlanoExiste 
{
    public $cpfComPontuacao;
    public $cpfSemPontuacao;

    public function __construct()
    {
        try {
            $PegaCpfSemPontuacao = new PegarDadosPessoaisWebHook();
            $cpf = $PegaCpfSemPontuacao->pegarCpf();
            $cpfSemPontuacao = $PegaCpfSemPontuacao->pegarCpfSemPontuacao();

            $this->cpfComPontuacao = $cpf;
            $this->cpfSemPontuacao = $cpfSemPontuacao;

           
        } catch (\Exception $e) {
            echo json_encode([
                "message" => "Erro ao pegar CPF",
                "error" => $e->getMessage()
            ]);
        }
    }

    protected function pegarCodPlano()
    {
        try {
            $titlePlano = new NivelServico;
            $codPlano = $titlePlano->titleCod();

            
            return $codPlano;
        } catch (\Exception $e) {
            echo json_encode([
                "message" => "Erro ao pegar o código do plano",
                "error" => $e->getMessage()
            ]);

            $InserirLogDoCliente = new InserirLogDoCliente;
            $InserirLogDoCliente->insertlog("Erro ao pegar o código do plano", $e->getMessage(), "error");


        }
    }

    public function verificaPlano()
    {
        try {
            $sql = "SELECT id, txid, title FROM produtos_cliente WHERE txid = ?";
            $stmt = Conn::connect()->prepare($sql);
            $stmt->bindValue(1, $this->cpfComPontuacao);
            $stmt->execute();

            $response = $stmt->fetch(\PDO::FETCH_ASSOC);

      

            // Verifica se houve um retorno válido
            if (!$response || !is_array($response)) {
                $nenhumProdutoAssociado = json_encode([
                    "message" => "Nenhum produto encontrado para o cliente.",
                    "StatusCode" => 200,
                    "Content" => [
                        "Event" => [
                            "Text" => "Nenhum produto associado ao cliente."
                        ]
                    ]
                ]);

               

                $InserProduto = new InsertProduto();
               
                $InserProduto->insertPr($this->cpfComPontuacao);

                $InserirLogDoCliente = new InserirLogDoCliente;
                $InserirLogDoCliente->insertlog("Nenhum produto associado ao cliente.", $nenhumProdutoAssociado, "info");

                return $nenhumProdutoAssociado;
            }

            // Verifica se o produto existe
            if (!isset($response["title"])) {
                $InserProduto = new InsertProduto();
               
                json_encode($InserProduto->insertPr($this->cpfComPontuacao));

                $InserirLogDoCliente = new InserirLogDoCliente;
                $InserirLogDoCliente->insertlog("Produto inserido com sucesso!", "O produto foi adicionado ao cliente com sucesso!", "success");

                echo json_encode([
                    "message" => "Produto inserido com sucesso!",
                    "StatusCode" => 200,
                    "Content" => [
                        "Event" => [
                            "Text" => "O produto foi adicionado ao cliente com sucesso!"
                        ]
                    ]
                ]);

                return;
            } else if ($response["title"] === $this->pegarCodPlano()) {
                $produtosSaoIguais = json_encode([
                    "message" => "Os produtos são iguais!",
                    "StatusCode" => 200,
                    "Content" => [
                        "Event" => [
                            "Text" => "O cliente já possui o mesmo produto!"
                        ]
                    ]
                ]);

                


                require("./InserirContratoLocalCliente.php");
            

                $InserirLogDoCliente = new InserirLogDoCliente;
                $InserirLogDoCliente->insertlog("O cliente já possui o mesmo produto!", $produtosSaoIguais, "info");

                
                return $produtosSaoIguais;
            } else if ($response["title"] !== $this->pegarCodPlano()) {
               

                $CancelarContratoClientOle = new CancelarContratoClienteOle();
                $contratoCancelado = $CancelarContratoClientOle->cancela();

                if ($contratoCancelado) {
                    

                    $DeleteProdutoCliente = new DeleteProdutoCliente();
                    $DeleteProdutoCliente->deleteproduto();

                    $InserProduto = new InsertProduto();
                 
                     $InserProduto->insertPr($this->cpfComPontuacao);
                    return;
                } else {
                    echo "Ocorreu algum erro ao cancelar o contrato.\n";
                    return;
                }
            }
        } catch (\Exception $e) {
            echo json_encode([
                "message" => "Erro ao verificar o plano",
                "error" => $e->getMessage()
            ]);
        }
    }
}


$verificar = new VerificarPlanoExiste;
$verificar->verificaPlano();