<?php

namespace src\models;

use src\config\Conn;

require("../../vendor/autoload.php");

class PegarPatrimonios
{
    public function pegarMacJson() {
        $logFilePath = '../models/datawebhook.json';

        // Lê o conteúdo do arquivo de log JSON
        $logJson = file_get_contents($logFilePath);

        // Decodifica o JSON recebido
        $logData = json_decode($logJson, true);

        // Verifica se os dados foram recebidos corretamente
        if (!$logData) {
            die("Erro ao ler ou decodificar o arquivo JSON.");
        }

        $contract = $logData["DataObject"]["Contract"];
        $patrimonies = isset($logData['DataObject']['Patrimonies']) ? $logData['DataObject']['Patrimonies'] : [];

        // Coletar todos os endereços MAC com seus respectivos IntegrationCodes
        $macsWithIntegration = [];
        foreach ($patrimonies as $index => $patrimony) {
            if (isset($patrimony['Mac']) && isset($patrimony['IntegrationCode'])) {
                $macsWithIntegration[] = [
                    "mac" => $patrimony['Mac'],
                    "integrationCode" => $patrimony['IntegrationCode']
                ];
            }
        }

        // Criar a estrutura principal e adicionais
        $macsOrganizados = [];
        if (count($macsWithIntegration) > 0) {
            // Define o primeiro como principal
            $macsOrganizados["principal"] = $macsWithIntegration[0]['mac'];

            // Adiciona os restantes como adicionais
            for ($i = 1; $i < count($macsWithIntegration); $i++) {
                $macsOrganizados["adicional" . $i] = $macsWithIntegration[$i]['mac'];
            }
        }

        return [
            "macsOrganizados" => $macsOrganizados,
            "contract" => $contract["Number"],
            "macsWithIntegration" => $macsWithIntegration
        ];
    }

    public function listarMacsComCodigoOle() {
        // Obtenha os dados do JSON
        $dataFromJson = $this->pegarMacJson();

        // Coletar os códigos de integração do JSON
        $integrationCodes = array_column($dataFromJson['macsWithIntegration'], 'integrationCode');

        // Preparar consulta SQL para buscar os códigos no banco
        if (!empty($integrationCodes)) {
            $sql = "SELECT * FROM tipo_dispositivos WHERE codigo_voalle IN ('" . implode("','", $integrationCodes) . "')";
            $stmt = Conn::connect()->prepare($sql);
            $stmt->execute();

            // Verificar se encontrou resultados
            if ($stmt && $stmt->rowCount() > 0) {
                $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                // Associar os resultados aos IntegrationCodes do JSON
                $finalResult = [];
                foreach ($dataFromJson['macsWithIntegration'] as $index => $item) {
                    foreach ($results as $result) {
                        if ($item['integrationCode'] == $result['codigo_voalle']) {
                            $categoria = ($index === 0) ? 'principal' : 'adicional' . $index;
                            $finalResult[] = [
                                "mac" => $item['mac'],
                                "codigo_ole" => $result['codigo_ole'],
                                "categoria" => $categoria,
                                "contract" => $dataFromJson['contract']
                            ];
                        }
                    }
                }

                // Retorna o resultado final ou array vazio
                return !empty($finalResult) ? $finalResult : [];
            }
        }

        return [];
    }
}

