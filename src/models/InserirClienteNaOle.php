<?php

namespace src\models;
require("../../vendor/autoload.php");

use src\config\AuthOle;
use src\models\PegarDadosPessoaisWebHook;
use src\models\BuscarDadosComplementaresExternos;


class InserirClienteNaOle
{
    public function insiranaole()
    {
        $PegarDadosPessoaisWebHook = new PegarDadosPessoaisWebHook();
        $BuscarDadosComplementaresExternos = new BuscarDadosComplementaresExternos();
        $TratarFormatoCep = new TratarFormatoCep();
        $PegarDadosPessoaisWebHook = new PegarDadosPessoaisWebHook();
        $email = $PegarDadosPessoaisWebHook->pegarEmail();
        $GetAniversary = new BuscarDataNascimento;

        $tratarNumeroTelefone = new PegarDadosPessoaisWebHook;
        $AuthOle = new AuthOle;
       
      

        // Coleta e prepara os dados
        $data = [
            "login" => $AuthOle->acessoole()["ole_user"],
            "pass" =>  $AuthOle->acessoole()["ole_pass"],
            "keyapi" =>  $AuthOle->acessoole()["ole_token"],
            "nome" => $PegarDadosPessoaisWebHook->pegarNomeCompleto(),
            "tipo_pessoa" => $PegarDadosPessoaisWebHook->tipoPessoa(),
            "nome_fantasia" => $PegarDadosPessoaisWebHook->tipoPessoa() === 2 ? $PegarDadosPessoaisWebHook->pegarNomeCompleto() : "",
            "cpf_cnpj" => $PegarDadosPessoaisWebHook->pegarCpf(),
            "inscricao_estadual" => "",
            "data_nascimento" => $BuscarDadosComplementaresExternos->buscadadosNascimento(),
            "endereco_cep" => $TratarFormatoCep->tratar(),
            "endereco_logradouro" => $BuscarDadosComplementaresExternos->buscadados()["street"],
            "endereco_numero" => $BuscarDadosComplementaresExternos->buscadados()["number"],
            "endereco_bairro" => $BuscarDadosComplementaresExternos->buscadados()["neighborhood"],
            "endereco_ponto_referencia" => "",
            "endereco_complemento_tipo" => "",
            "endereco_complemento_valor" => "",
            "contato" => "",
            "telefone_ddd[]" => $tratarNumeroTelefone->tratarNumeroTelefone()["ddd"],
            "telefone_numero[]" => $tratarNumeroTelefone->tratarNumeroTelefone()["numero"],
            "telefone_ramal[]" => "",
            "telefone_tipo[]" => 3,
            "email" => $email,
            "dia_vencimento" => 7,
            "endereco_cobranca" => "c",
            "cobranca_cep" => $TratarFormatoCep->tratar(),
            "cobranca_logradouro" => $BuscarDadosComplementaresExternos->buscadados()["street"],
            "cobranca_numero" => $BuscarDadosComplementaresExternos->buscadados()["number"],
            "cobranca_bairro" => $BuscarDadosComplementaresExternos->buscadados()["neighborhood"],
            "cobranca_complemento_tipo" => "",
            "cobranca_complemento_valor" => $BuscarDadosComplementaresExternos->buscadados()["addressComplement"]
        ];

        $ch = curl_init($AuthOle->acessoole()["ole_endpoint"]."/clientes/inserir");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);

        // Passa o array diretamente para http_build_query
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

        // Decodifica a resposta principal para verificar o conteúdo
        $responseData = json_decode($response, true);


         if(isset($responseData["error"]["endereco_cep"]) === "CEP invalido!"){
            $pegarCep = new TratarFormatoCep;
            $log = new InserirLogDoCliente;
            $log->insertlog("Erro ao inserir cliente!", $responseData["error"]["endereco_cep"] ." ". $pegarCep->tratar(), "warning");
            
            echo json_encode(
                [
                    "message" => "Erro ao inserir cliente na Olé, CEP inválido"
                ]
            );

            

            return;
         };

         if(isset($responseData["error"]["cobranca_cep"])){
            echo json_encode(
                [
                    "message" => "Erro ao inserir cliente na Olé, CEP de cobrança inválido"
                ]
            );

            $pegarCep = new TratarFormatoCep;
            $log = new InserirLogDoCliente;
            $log->insertlog("Erro ao inserir cliente!", json_encode($responseData["error"]["cobranca_cep"] ." ". $pegarCep->tratar(), JSON_UNESCAPED_UNICODE), "warning");

            return;
         }


   
       echo json_encode($responseData);
       
        

        
       
       

        return ["message" => $responseData, "StatusCode" => 200];
    }
}


$inserir = new InserirClienteNaOle;
$inserir->insiranaole();



