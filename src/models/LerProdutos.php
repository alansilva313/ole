<?php

namespace src\models;

use src\config\Conn;
use src\models\NivelServico;

require("../../vendor/autoload.php");

class LerProdutos
{
    private $resultado;


    public function __construct()
    {
        // Inicializa os dados ao criar a instância
        $this->resultado = $this->produtos();
    }

    private function produtos()
    {
        $NivelServico = new NivelServico;
        $codVoalle = $NivelServico->titleCod();

        // Primeira consulta: busca o ID e outras informações do produto principal com base no código Voalle
        $sql = "SELECT id, cod_voalle, cod_ole FROM oletv_produtos WHERE cod_voalle = ?";
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $codVoalle);
        $stmt->execute();

        if ($stmt && $stmt->rowCount() > 0) {
            $produtoPrincipal = $stmt->fetch(\PDO::FETCH_ASSOC);
            $idProdutoPrincipal = intval($produtoPrincipal["id"]);  // Converte o ID para inteiro
        } else {
          

            echo json_encode([
                "message" => "Produto não encontrado!",
                "StatusCode" => 200,
                "Content" => [
                    "Event" => [
                        "Text" => "Verifique se o produto está sendo enviado corretamente!"
                    ]
                ]
            ]);

            return;
            
        }

        // Segunda consulta: busca todos os produtos adicionais que têm o código principal igual ao ID do produto principal
        $sqlListaAdicionais = "SELECT * FROM oletv_produtos WHERE codigo_principal = ?";
        $stmtAdicionais = Conn::connect()->prepare($sqlListaAdicionais);
        $stmtAdicionais->bindValue(1, $idProdutoPrincipal);
        $stmtAdicionais->execute();

        $resultado = [];
        $resultado['produto_principal'] = $produtoPrincipal;

        if ($stmtAdicionais && $stmtAdicionais->rowCount() > 0) {
            $produtosAdicionais = $stmtAdicionais->fetchAll(\PDO::FETCH_ASSOC);
            $resultado['produtos_adicionais'] = $produtosAdicionais;
        } else {
            $resultado['produtos_adicionais'] = [];
        }

        return $resultado;
    }

    // Método para acessar o produto principal
    public function getProdutoPrincipal()
    {
        return $this->resultado['produto_principal'] ?? null;
    }

    // Método para determinar se o produto adicional é automático
    public function getAutomatico()
    {
        $produtosAdicionais = $this->getProdutosAdicionais();

        if (!empty($produtosAdicionais)) {
            return $produtosAdicionais[0]["secundario_automatico"] === "S" ? "S" : "N";
        }

        return "N"; // Retorna "N" se não houver produtos adicionais
    }

    // Método para calcular a quantidade total dos produtos adicionais automáticos
   // Método para calcular as quantidades individuais dos produtos adicionais automáticos
public function getQuantidade()
{
    $automatico = $this->getAutomatico();
    $quantidades = [];

    if ($automatico === "S") {
        $produtosAdicionais = $this->getProdutosAdicionais();

        if (!empty($produtosAdicionais)) {
            foreach ($produtosAdicionais as $adicional) {
                $quantidades[] = [
                    'cod_ole' => $adicional['cod_ole'],
                    'quantidade' => $adicional["auto_adicionais"]
                ];
            }
        }
    }

    return $quantidades; // Retorna as quantidades individuais para cada produto adicional
}

    // Método para acessar os produtos adicionais
    public function getProdutosAdicionais()
    {
        return $this->resultado['produtos_adicionais'] ?? [];
    }

    public function retornaApenasoIdPrincipal(){
        $NivelServico = new NivelServico;
        $codVoalle = $NivelServico->titleCod();

        // Primeira consulta: busca o ID e outras informações do produto principal com base no código Voalle
        $sql = "SELECT id, cod_voalle, cod_ole FROM oletv_produtos WHERE cod_voalle = ?";
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $codVoalle);
        $stmt->execute();

        if ($stmt && $stmt->rowCount() > 0) {
            $produtoPrincipal = $stmt->fetch(\PDO::FETCH_ASSOC);
            $idProdutoPrincipal = intval($produtoPrincipal["id"]);

            return $idProdutoPrincipal;
        } else {

            $jsonLerProdutos = json_encode([
                "message" => "Produto não encontrado!",
                "StatusCode" => 200,
                "Content" => [
                    "Event" => [
                        "Text" => "Verifique se o produto está sendo enviado corretamente!"
                    ]
                ]
                    ]);

                   
        }


            $log = new InserirLogDoCliente;
            $log->insertlog("Produto não encontrado!", $jsonLerProdutos, "error");


            return $jsonLerProdutos;
    }

    public function contarAdicionaisPorProdutoPrincipal()
{
    $NivelServico = new NivelServico;
    $codVoalle = $NivelServico->titleCod();
     

    
    $sql = "SELECT * FROM oletv_produtos WHERE codigo_principal = ?  AND secundario_automatico = ? AND ativo = ? AND qtde_adicionais > ?";
    $stmt = Conn::connect()->prepare($sql);
    $stmt->bindValue(1, $this->retornaApenasoIdPrincipal());
    $stmt->bindValue(2, "S");
    $stmt->bindValue(3, "S");
    $stmt->bindValue(4, 0);
    $stmt->execute(); // Execute a consulta

    // Verifique se a consulta foi executada corretamente
    if ($stmt && $stmt->rowCount() > 0) {
        $rs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Arrays para armazenar os dados
        $data = [];
        $codsAdicionais = [];

        // Itera sobre os resultados
        foreach ($rs as $i => $produto) {
            $codOle = $produto["cod_ole"];
            $autoAdicionais = $produto["auto_adicionais"];

            // Adiciona os valores de cod_ole no array data multiplicado pela quantidade de auto_adicionais
            for ($j = 0; $j < $autoAdicionais; $j++) {
                $data["id_plano_adicional[" . count($data) . "]"] = $codOle;
                $codsAdicionais[] = $codOle;
            }
        }

        
        return json_encode($data);
    }

    // Se não houver resultados, retorne uma mensagem de erro ou um array vazio
    return json_encode([]);
}


public function quantidadeEquipamentos() {
    // Obtenha os dados adicionais
    $qtde = $this->contarAdicionaisPorProdutoPrincipal();

    // Verifique se $qtde é uma string JSON
    if (is_string($qtde)) {
        // Tente decodificar o JSON
        $qtdeArray = json_decode($qtde, true);

        // Verifique se a decodificação foi bem-sucedida
        if (json_last_error() === JSON_ERROR_NONE) {
            // Contar o número de itens no array
            return count($qtdeArray);
        } else {
            // Trate o erro de decodificação JSON
            return "Erro: Dados adicionais não puderam ser decodificados.";
        }
    } elseif (is_array($qtde)) {
        // Se já for um array, conte o número de itens
        return count($qtde);
    } else {
        // Trate o erro se $qtde não for um array ou string JSON
        return "Erro: Dados adicionais não são um array ou string JSON.";
    }
}




public function gerarArrayAdicionais() {
    
    $adicionais = $this->contarAdicionaisPorProdutoPrincipal();


    // Verifique se $adicionais é uma string JSON e decodifique
    if (is_string($adicionais)) {
        $adicionaisArray = json_decode($adicionais, true);

        // Verifique se a decodificação foi bem-sucedida
        if (json_last_error() === JSON_ERROR_NONE) {
            // Extraia os valores do array
            $valores = array_values($adicionaisArray);
            return $valores;
        } else {
            // Trate o erro de decodificação JSON
            echo "Erro: Dados adicionais não puderam ser decodificados.";
            return [];
        }
    } elseif (is_array($adicionais)) {
        // Se já for um array, extraia os valores
        $valores = array_values($adicionais);
        return $valores;
    } else {
        // Trate o erro se $adicionais não for um array ou string JSON
        echo "Erro: Dados adicionais não são um array ou string JSON.";
        return [];
    }
}

public function pegarAdicionaisManual() {
    $logFilePath = '../models/datawebhook.json';

    // Lê o conteúdo do arquivo de log JSON
    $logJson = file_get_contents($logFilePath);

    // Decodifica o JSON recebido
    $logData = json_decode($logJson, true);

    // Verifica se os dados foram recebidos corretamente
    if (!$logData) {
        die("Erro ao ler ou decodificar o arquivo JSON.");
    }

    $produtoAdicional = $logData['DataObject']['Services'];

    // Seleciona todos os produtos adicionais que correspondem ao código principal e tipo
    $sql = "SELECT * FROM oletv_produtos WHERE codigo_principal = ? AND tipo = ?";
    $stmt = Conn::connect()->prepare($sql);
    $stmt->bindValue(1, 4);
    $stmt->bindValue(2, 'S');

    $stmt->execute();

    if($stmt && $stmt->rowCount() > 0){
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $output = [];

        // Itera sobre cada produto adicional do JSON
        foreach($produtoAdicional as $index => $pa){
            $integrationCode = $pa['IntegrationCode'];

            // Verifica se o `cod_voalle` existe nos resultados da consulta
            foreach($result as $produto){
                if($produto['cod_voalle'] === $integrationCode){
                    // Adiciona ao array de saída com o índice correto
                    $output['id_plano_adicional[' . $index . ']'] = $produto['cod_ole'];
                    break; // Sai do loop interno se encontrar a correspondência
                }
            }
        }

        // Retorna a estrutura como JSON
        return json_encode($output);
    } else {
        return json_encode(['message' => 'Nenhum produto correspondente encontrado.']);
    }
}

  
    
}




