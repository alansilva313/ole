<?php

namespace src\models;

use Exception;

require("../../vendor/autoload.php");

class PegarDadosPessoaisWebHook {

    public string $DataObject;
    public string $Chave;
    public string $Dados;
    public $numberFone;

    public function processarDados(string $DataObject, string $Chave, string $Dados)
    {
        $this->DataObject = $DataObject;
        $this->Chave = $Chave;
        $this->Dados = $Dados;

        $logFilePath = '../models/datawebhook.json';
        $logJson = file_get_contents($logFilePath);

        if (!$logJson) {
            throw new Exception("Erro ao ler o arquivo JSON.");
        }

        $logData = json_decode($logJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Erro ao decodificar o JSON: " . json_last_error_msg());
        }

        $retornaDadoDesejado = $logData[$this->DataObject][$this->Chave][$this->Dados];

        return $retornaDadoDesejado;
    }

    /* PEGA O NOME COMPLETO DO CLIENTE */
    public function pegarNomeCompleto()
    {
        $nome = $this->processarDados("DataObject", "Client", "Name");
        return $nome;
    }

    /* PEGA O EMAIL DO CLIENTE */
    public function pegarEmail()
    {
        $email = $this->processarDados("DataObject", "Client", "Email");
        return $email;
    }

    public function pegarEmailAleatorio()
    {
        $numerosAleatorios = mt_rand(12523, 232355); // Gera número aleatório
        $email = $this->processarDados("DataObject", "Client", "Email"); // Obtém o email

        // Concatena o número aleatório com o email e converte para string
        return (string) $numerosAleatorios . $email;
    }

    /* PEGA O CPF DO CLIENTE */
    public function pegarCpf()
    {
        $cpf = $this->processarDados("DataObject", "Client", "TxId");
        return $cpf;
    }

    public function tipoPessoa() {
        $documento = $this->pegarCpf();
    
        // Remove qualquer caractere que não seja número
        $documento = preg_replace('/\D/', '', $documento);
    
        // Verifica se é CPF (11 dígitos) ou CNPJ (14 dígitos)
        if (strlen($documento) <= 11) {
            return 1; // CPF
        } elseif (strlen($documento) >= 14) {
            return 2; // CNPJ
        }
    
        
    }
    

    public function pegarCpfSemPontuacao()
    {
        // Captura os dados processados
        $dados = $this->processarDados("DataObject", "Client", "TxId");

        // Verifica se o CPF está presente nos dados
        if (isset($dados['cpf_cnpj'])) {
            // Remove todos os caracteres que não são números
            $cpf = preg_replace('/\D/', '', $dados['cpf_cnpj']);
            return $cpf;
        }

        // Caso o CPF não esteja presente, retorna uma string vazia ou outra forma de tratamento
        return '';
    }

    public function pegarTelefone()
    {
        // Tenta pegar o celular, se não existir, retorna o telefone fixo
        $telefone = $this->processarDados("DataObject", "Client", "CellPhone");
        $fixo = $this->processarDados("DataObject", "Client", "Phone");

        // Retorna o celular ou o fixo caso o celular esteja vazio
        return !empty($telefone) ? $telefone : $fixo;
    }

    public function pegarCelular()
    {
        // Obtém o celular ou telefone com DDD e código do país
        $telefone = $this->pegarTelefone();

        // Remove código de país e DDD
        $nm = $this->removerCodigoEPaisEDDD($telefone);

        // Se o número tiver 9 dígitos, formata como XXXXX-XXXX
        if (strlen($nm) == 9) {
            return substr($nm, 0, 5) . "-" . substr($nm, 5);
        }
        // Se o número tiver 8 dígitos, formata como XXXX-XXXX
        elseif (strlen($nm) == 8) {
            return substr($nm, 0, 4) . "-" . substr($nm, 4);
        }

        // Retorna o número não formatado se tiver outro comprimento
        return $nm;
    }

    private function removerCodigoEPaisEDDD($telefone)
    {
        // Remove qualquer caractere que não seja número
        $telefone = preg_replace('/\D/', '', $telefone);

        // Se o número começar com 55 (código do país), remover
        if (substr($telefone, 0, 2) === '55') {
            $telefone = substr($telefone, 2);
        }

        // Se o número tiver 11 dígitos (ex: 9 + DDD + 8 dígitos)
        if (strlen($telefone) == 11) {
            return substr($telefone, 2); // remove o DDD e mantém os últimos 9 dígitos
        }
        // Se o número tiver 10 dígitos (ex: DDD + 8 dígitos)
        elseif (strlen($telefone) == 10) {
            return substr($telefone, 2); // remove o DDD e mantém os últimos 8 dígitos
        }

        // Retorna o número original se não bater com os comprimentos esperados
        return $telefone;
    }

    public function tratarNumeroTelefone()



    {
        $dddPadrao = '00'; 
        $numero = $this->pegarTelefone();

        // Remove todos os caracteres não numéricos
        $numero = preg_replace('/\D/', '', $numero);

        // Remove o DDI (55) se presente
        if (substr($numero, 0, 2) === '55') {
            $numero = substr($numero, 2);
        }

        // Remove o 0 à esquerda (ex: '018')
        if (substr($numero, 0, 1) === '0') {
            $numero = substr($numero, 1);
        }

        // Se o número tiver 11 dígitos (DDD + celular com 9 dígitos)
        if (strlen($numero) === 11) {
            return [
                'ddd' => substr($numero, 0, 2),   // Os dois primeiros dígitos são o DDD
                'numero' => substr($numero, 2),   // Remove o DDD, ficando apenas com os 9 dígitos
            ];
        }

        // Se o número tiver 10 dígitos (DDD + fixo com 8 dígitos)
        if (strlen($numero) === 10) {
            return [
                'ddd' => substr($numero, 0, 2),   // Os dois primeiros dígitos são o DDD
                'numero' => substr($numero, 2),   // Remove o DDD, ficando apenas com os 8 dígitos
            ];
        }

        // Se o número tiver 9 dígitos (sem DDD), assume DDD padrão
        if (strlen($numero) === 9) {
            return [
                'ddd' => $dddPadrao,              // Usa o DDD padrão
                'numero' => $numero               // Número de 9 dígitos
            ];
        }

        // Se o número tiver 8 dígitos (sem DDD), assume DDD padrão
        if (strlen($numero) === 8) {
            return [
                'ddd' => $dddPadrao,              // Usa o DDD padrão
                'numero' => $numero               // Número de 8 dígitos
            ];
        }

        // Se o número tiver menos de 8 dígitos, considera inválido e retorna erro
        if (strlen($numero) < 8) {
            return [
                'ddd' => null,
                'numero' => "Número inválido"     // Retorna erro
            ];
        }

        // Caso não encaixe em nenhuma regra, retorna o número como está
        return [
            'ddd' => null,
            'numero' => $numero
        ];
    }

    public function pegarNumeroContrato()
    {
        $contratoNumero = $this->processarDados("DataObject", "Contract", "Number");
        return $contratoNumero;
    }

    public function pegarCompanyPlace()
    {
        $companyplace = $this->processarDados("DataObject", "CompanyPlace", "Id");
        return $companyplace;
    }

    public function pegarStage()
    {
        $companyplace = $this->processarDados("DataObject", "Stage", "Description");
        return $companyplace;
    }

    public function pegarStatus()
    {
        $status = $this->processarDados("DataObject", "Status", "Description");
        return $status;
    }
}



$PegarDadosPessoaisWebHook = new PegarDadosPessoaisWebHook();
echo $PegarDadosPessoaisWebHook->tipoPessoa();