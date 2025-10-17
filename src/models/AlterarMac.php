<?php

namespace src\models;

use src\config\AuthOle;
use src\config\Conn;

require("../../vendor/autoload.php");

class AlterarMac
{
    public function buscarcontratolocal()
    {
        $PegarDadosPessoaisWebHook = new PegarDadosPessoaisWebHook;
        $pegarcontratovoalle = $PegarDadosPessoaisWebHook->pegarNumeroContrato();
      
        $sql = "SELECT ole_contract_number FROM oletv_contratos WHERE voalle_contract_number = ?";
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $pegarcontratovoalle);
        $stmt->execute();

        if ($stmt) {
            if ($stmt->rowCount() > 0) {
                $contratoole = $stmt->fetch(\PDO::FETCH_ASSOC);
                return $contratoole["ole_contract_number"];
            }
            return false;
        }
        return false;
    }

    public function lercontratocompletocliente()
    {
        // Buscar contrato local
        $contratoLocal = $this->buscarcontratolocal();
    
        if (!$contratoLocal) {
            return 'Nenhum contrato encontrado no sistema local.';
        }
    
        // Pegar o contrato completo do cliente pela API
        $contratoCompleto = new ListarContratoClienteOle;
        $contratosCliente = $contratoCompleto->retornacontratocompleto();
        $contratosCliente = json_decode($contratosCliente, true);
    
        // Verifica se a decodificação foi bem-sucedida
        if (json_last_error() !== JSON_ERROR_NONE) {
            return 'Erro na decodificação do JSON: ' . json_last_error_msg();
        }
    
        // Inicializa um array para armazenar os IDs dos equipamentos
        $equipamentoIDs = [];
    
        // Organiza os equipamentos de todas as assinaturas
        foreach ($contratosCliente['contratos'] as $contrato) {
            if (isset($contrato['id']) && $contrato['id'] == $contratoLocal) {
                foreach ($contrato['assinaturas'] as $assinatura) {
                    foreach ($assinatura['equipamentos'] as $equipamento) {
                        // Adiciona o ID do equipamento à lista
                        $equipamentoIDs[] = $equipamento['id'];
                    }
                }
                break;
            }
        }
    
        // Agora, iteramos sobre os IDs dos equipamentos e chamamos a função de atualização do MAC
        foreach ($equipamentoIDs as $equipamentoID) {
            $this->inserirAtualizaoMac($equipamentoID);
            sleep(1); // Espera 1 segundo antes de processar o próximo equipamento
        }
    
        return 'Processo concluído.';
    }

    public function inserirAtualizaoMac($idEquipamento)
    {
        $AuthOle = new AuthOle;
    
        $contratoLocal = $this->buscarcontratolocal();
    
        if (!$contratoLocal) {
            return 'Nenhum contrato encontrado no sistema local.';
        }

        // Gera um MAC aleatório
        $novoMac = $this->gerarMacAleatorio();

        $data = [
            "login" => $AuthOle->acessoole()["ole_user"],
            "pass" => $AuthOle->acessoole()["ole_pass"],
            "keyapi" => $AuthOle->acessoole()["ole_token"],
            "id_modelo" => "16960", // Substitua pelo valor correto se necessário
            "mac" => $novoMac
        ];
    
        // Use o $idEquipamento recebido no endpoint da URL
        $ch = curl_init($AuthOle->acessoole()["ole_endpoint"]."/contratos/mac/" . $contratoLocal . "/" . $idEquipamento);
        $formData = http_build_query($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded; charset=UTF-8"
        ]);
    
        $response = curl_exec($ch);
    
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return json_encode(['error' => $error], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    
        curl_close($ch);
    
        // Verifica se a requisição foi bem-sucedida
        $responseData = json_decode($response, true);
    
        if ($responseData && isset($responseData['status']) && $responseData['status'] == 'success') {
            echo "MAC atualizado com sucesso para o equipamento ID: " . $idEquipamento . " com MAC: " . $novoMac . "\n";
        } else {
            echo "Erro ao atualizar o MAC para o equipamento ID: " . $idEquipamento . "\n";
        }
    }

    // Função para gerar um MAC aleatório
    private function gerarMacAleatorio()
    {
        $chars = '0123456789abcdef';
        $mac = '';
        for ($i = 0; $i < 12; $i++) {
            $mac .= $chars[rand(0, 15)];
        }
        return $mac;
    }
}

/* $AlterarMac = new AlterarMac;
echo $AlterarMac->lercontratocompletocliente();
 */