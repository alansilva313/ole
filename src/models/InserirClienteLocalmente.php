<?php

namespace src\models;
require("../../vendor/autoload.php");

use Exception;
use src\config\AuthOle;
use src\config\Conn;
use src\models\PegarDadosPessoaisWebHook;
use src\models\BuscarDadosComplementaresExternos;
use src\models\TratarNumeroTelefone;

class InserirClienteLocalmente
{
    public function processarDados()
    {
        $PegarDadosPessoaisWebHook = new PegarDadosPessoaisWebHook();
        $BuscarDadosComplementaresExternos = new BuscarDadosComplementaresExternos();
        $TratarNumeroTelefone = new TratarNumeroTelefone();

        $authOle = new AuthOle;
        $GetAniversary = new BuscarDataNascimento;

        $tratarNumeroTelefone = new PegarDadosPessoaisWebHook;
            
                    
        $data = [
            "login" => $authOle->acessoole()["ole_user"],
            "pass" => $authOle->acessoole()["ole_pass"],
            "keyapi" => $authOle->acessoole()["ole_token"],
            "nome" => $PegarDadosPessoaisWebHook->pegarNomeCompleto(),
            "tipo_pessoa" => $PegarDadosPessoaisWebHook->tipoPessoa(),
            "nome_fantasia" => $PegarDadosPessoaisWebHook->tipoPessoa() === 2 ? $PegarDadosPessoaisWebHook->pegarNomeCompleto() : "",
            "cpf_cnpj" => $PegarDadosPessoaisWebHook->pegarCpf(),
            "inscricao_estadual" => "",
            "data_nascimento" => $BuscarDadosComplementaresExternos->buscadadosNascimento(),
            "endereco_cep" => $BuscarDadosComplementaresExternos->buscadados()["postalCode"],
            "endereco_logradouro" => $BuscarDadosComplementaresExternos->buscadados()["street"],
            "endereco_numero" => $BuscarDadosComplementaresExternos->buscadados()["number"],
            "endereco_bairro" => $BuscarDadosComplementaresExternos->buscadados()["neighborhood"],
            "endereco_ponto_referencia" => "",
            "endereco_complemento_tipo" => "",
            "endereco_complemento_valor" => "",
            "contato" => "",
            "telefone_ddd" => $tratarNumeroTelefone->tratarNumeroTelefone()["ddd"],
            "telefone_numero" => $tratarNumeroTelefone->tratarNumeroTelefone()["numero"],
            "telefone_ramal" => "",
            "telefone_tipo" => 3,
            "email" => "",
            "dia_vencimento" => 7,
            "endereco_cobranca" => "c",
            "cobranca_cep" => $BuscarDadosComplementaresExternos->buscadados()["postalCode"],
            "cobranca_logradouro" => $BuscarDadosComplementaresExternos->buscadados()["street"],
            "cobranca_numero" => $BuscarDadosComplementaresExternos->buscadados()["number"],
            "cobranca_bairro" => $BuscarDadosComplementaresExternos->buscadados()["neighborhood"],
            "cobranca_complemento_tipo" => "",
            "cobranca_complemento_valor" => $BuscarDadosComplementaresExternos->buscadados()["addressComplement"]
        ];

        return $data;
    }



    protected function verificarCliente()
    {

        $pegarCpfDoCLiente = new PegarDadosPessoaisWebHook;
        $cpfcliente = $pegarCpfDoCLiente->pegarCpf();

        $sql = "SELECT * FROM clientes WHERE cpf_cnpj = ?";
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $cpfcliente);
        $stmt->execute();

        if($stmt){
            
            if($stmt->rowCount() > 0){

                return true;
            }

            return false;
        }
    }

    public function inserir()
    {

        try {

            if($this->verificarCliente() === true){
                return;
            }

            $status = new PegarDadosPessoaisWebHook;

            if($status->pegarStatus() === "Cancelado"){
                return;
            }


            $sql = "INSERT INTO clientes (
                integraId, nome, tipo_pessoa, nome_fantasia, cpf_cnpj, inscricao_estadual, data_nascimento, 
                endereco_cep, endereco_logradouro, endereco_numero, endereco_bairro, endereco_ponto_referencia, 
                endereco_complemento_tipo, endereco_complemento_valor, contato, telefone_ddd, telefone_numero, 
                telefone_ramal, telefone_tipo, email, dia_vencimento, endereco_cobranca, cobranca_cep, 
                cobranca_logradouro, cobranca_numero, cobranca_bairro, cobranca_complemento_tipo, cobranca_complemento_valor, processed
            ) VALUES (
                :integraId, :nome, :tipo_pessoa, :nome_fantasia, :cpf_cnpj, :inscricao_estadual, :data_nascimento, 
                :endereco_cep, :endereco_logradouro, :endereco_numero, :endereco_bairro, :endereco_ponto_referencia, 
                :endereco_complemento_tipo, :endereco_complemento_valor, :contato, :telefone_ddd, :telefone_numero, 
                :telefone_ramal, :telefone_tipo, :email, :dia_vencimento, :endereco_cobranca, :cobranca_cep, 
                :cobranca_logradouro, :cobranca_numero, :cobranca_bairro, :cobranca_complemento_tipo, :cobranca_complemento_valor, :processed
            )";
    
            $stmt = Conn::connect()->prepare($sql);
    
            // Processa os dados
            $data = $this->processarDados();
            
            $BuscarIntegrador = new BuscarIntegrador();
            $PegarDadosPessoaisWebHook = new PegarDadosPessoaisWebHook();
            $email = $PegarDadosPessoaisWebHook->pegarEmail();

            $cep = new TratarFormatoCep;
            $cpfCliente = new PegarDadosPessoaisWebHook;      
            
                
            // Bind values
            $stmt->bindValue(':integraId', $BuscarIntegrador->buscarIntegrador()[0]["id"]);
            $stmt->bindValue(':nome', $data["nome"] ?? '');
            $stmt->bindValue(':tipo_pessoa', $data["tipo_pessoa"]);
            $stmt->bindValue(':nome_fantasia', $data["nome_fantasia"]);
            $stmt->bindValue(':cpf_cnpj', $cpfCliente->pegarCpf() ?? '');
            $stmt->bindValue(':inscricao_estadual', $data["inscricao_estadual"]);
            $stmt->bindValue(':data_nascimento', $data["data_nascimento"] ?? '');
            $stmt->bindValue(':endereco_cep', $cep->tratar());
            $stmt->bindValue(':endereco_logradouro', $data["endereco_logradouro"] ?? '');
            $stmt->bindValue(':endereco_numero', $data["endereco_numero"] ?? '');
            $stmt->bindValue(':endereco_bairro', $data["endereco_bairro"] ?? '');
            $stmt->bindValue(':endereco_ponto_referencia', $data["endereco_ponto_referencia"] ?? '');
            $stmt->bindValue(':endereco_complemento_tipo', $data["endereco_complemento_tipo"] ?? '');
            $stmt->bindValue(':endereco_complemento_valor', $data["endereco_complemento_valor"] ?? '');
            $stmt->bindValue(':contato', $data["contato"]);
            $stmt->bindValue(':telefone_ddd', $data["telefone_ddd"]);
            $stmt->bindValue(':telefone_numero', $data["telefone_numero"] ?? '');
            $stmt->bindValue(':telefone_ramal', $data["telefone_ramal"] ?? '');
            $stmt->bindValue(':telefone_tipo', $data["telefone_tipo"]);
            $stmt->bindValue(':email',  $email ?? '');
            $stmt->bindValue(':dia_vencimento', $data["dia_vencimento"]);
            $stmt->bindValue(':endereco_cobranca', $data["endereco_cobranca"] ?? '');
            $stmt->bindValue(':cobranca_cep', $cep->tratar());
            $stmt->bindValue(':cobranca_logradouro', $data["cobranca_logradouro"]);
            $stmt->bindValue(':cobranca_numero', $data["cobranca_numero"] ?? '');
            $stmt->bindValue(':cobranca_bairro', $data["cobranca_bairro"] ?? '');
            $stmt->bindValue(':cobranca_complemento_tipo', $data["cobranca_complemento_tipo"]);
            $stmt->bindValue(':cobranca_complemento_valor', $data["cobranca_complemento_valor"]);
            $stmt->bindValue(':processed', 0);
    
            $stmt->execute();
           

          if($stmt){
            if($stmt->rowCount() > 0){
                  // Verificação simples de sucesso
            return json_encode([
                "message" => "Cliente inserido com sucesso!",
                "StatusCode" => 200
            ]);
            }

            return json_encode([
                "message" => "Cliente não inserido, ele já existe!",
                "StatusCode" => 200
            ]);

          }
        } catch (Exception $e) {
            // Log do erro para investigação
            error_log("Erro ao inserir cliente: " . $e->getMessage());
    
            // Retorna a mensagem de erro no formato JSON
            return json_encode([
                "message" => "Erro ao inserir dados: " . $e->getMessage(),
                "StatusCode" => 500
            ]);
        }
    }
    
}


