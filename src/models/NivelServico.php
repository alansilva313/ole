<?php

namespace src\models;
require("../../vendor/autoload.php");
use src\config\Conn;

class NivelServico
{
    public function pegarProdutos()
    {
        $logFilePath = '../models/datawebhook.json';

        // Lê o conteúdo do arquivo de log JSON
        $logJson = file_get_contents($logFilePath);

        // Decodifica o JSON recebido
        $logData = json_decode($logJson, true);

        // Verifica se os dados foram recebidos corretamente
        if (!$logData) {
            die("Erro ao ler ou decodificar o arquivo JSON.");
        }

        // Retorna a lista de serviços
        return $logData['DataObject']['Services'];
    }

    public function nivel()
    {
        $sql = "SELECT cod_voalle AS servico, @nivel := @nivel + 1 AS nivel, cod_ole FROM oletv_produtos, 
            (SELECT @nivel := 0) AS init WHERE tipo = ?";
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, "P");
        $stmt->execute();

        if ($stmt) {
            if ($stmt->rowCount()) {
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                return $result; // Retorna o resultado como array associativo
            }
        }
        return []; // Retorna um array vazio se não houver resultados
    }

    public function encontrarNivelMaisAlto()
    {
        $servicos = $this->pegarProdutos();
        $niveis = $this->nivel();

        // Cria um array associativo para acessar rapidamente os níveis e cod_ole
        $nivelMap = [];
        foreach ($niveis as $nivel) {
            $nivelMap[$nivel['servico']] = [
                'nivel' => $nivel['nivel'],
                'cod_ole' => $nivel['cod_ole']
            ];
        }

        // Encontra o serviço com o nível mais alto
        $maxLevel = 0;
        $maxService = '';
        $maxCodOle = '';
        foreach ($servicos as $servico) {
            $integrationCode = $servico['IntegrationCode'];
            if (isset($nivelMap[$integrationCode])) {
                $currentLevel = $nivelMap[$integrationCode]['nivel'];
                if ($currentLevel > $maxLevel) {
                    $maxLevel = $currentLevel;
                    $maxService = $integrationCode;
                    $maxCodOle = $nivelMap[$integrationCode]['cod_ole'];
                }
            }
        }

        // Retorna o cod_ole do serviço com o nível mais alto
        return $maxCodOle;
    }



    public function titleCod()
    {
        $servicos = $this->pegarProdutos();
        $niveis = $this->nivel();

        // Cria um array associativo para acessar rapidamente os níveis
        $nivelMap = [];
        foreach ($niveis as $nivel) {
            $nivelMap[$nivel['servico']] = $nivel['nivel'];
        }

        // Encontra o serviço com o nível mais alto
        $maxLevel = 0;
        $maxService = '';
        foreach ($servicos as $servico) {
            $integrationCode = $servico['IntegrationCode'];
            if (isset($nivelMap[$integrationCode])) {
                $currentLevel = $nivelMap[$integrationCode];
                if ($currentLevel > $maxLevel) {
                    $maxLevel = $currentLevel;
                    $maxService = $integrationCode;
                }
            }
        }

        // Retorna o serviço com o nível mais alto
        return  $maxService;
    }
}


