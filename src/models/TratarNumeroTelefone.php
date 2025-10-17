<?php

namespace src\models;

require("../../vendor/autoload.php");

use src\models\PegarDadosPessoaisWebHook;

class TratarNumeroTelefone 
{
    public function pegarDDD() {
        $PegarDadosPessoaisWebHook = new PegarDadosPessoaisWebHook();
        $telefoneCompleto = $PegarDadosPessoaisWebHook->pegarTelefone();

        // Remove o código de país se existir
        $telefone = $this->removerCodigoPais($telefoneCompleto);

        // Retorna os primeiros 2 dígitos após remover o código de país (DDD)
        $ddd = substr($telefone, 0, 2);

        return $ddd;
    }

    public function retornaNumero() {
        $PegarDadosPessoaisWebHook = new PegarDadosPessoaisWebHook();
        $telefoneCompleto = $PegarDadosPessoaisWebHook->pegarTelefone();

        // Remove o código de país se existir
        $telefone = $this->removerCodigoPais($telefoneCompleto);

        // Se o número tiver mais de 8 dígitos, remove o DDD e mantém os últimos 8
        if (strlen($telefone) > 8) {
            // Remove os primeiros dígitos, mantendo apenas os últimos 8
            $telefone = substr($telefone, -8);
        }

        return $telefone;
    }

    public function retornaCelular() {
        $PegarDadosPessoaisWebHook = new PegarDadosPessoaisWebHook();
        $celularCompleto = $PegarDadosPessoaisWebHook->pegarCelular();

        // Remove o código de país se existir
        $celular = $this->removerCodigoPais($celularCompleto);

        // Se o número tiver mais de 8 dígitos, remove o DDD e mantém os últimos 8
        if (strlen($celular) > 8) {
            // Remove os primeiros dígitos, mantendo apenas os últimos 8
            $celular = substr($celular, -8);
        }

        return $celular;
    }

    private function removerCodigoPais($telefone) {
        // Remove qualquer caractere que não seja número
        $telefone = preg_replace('/\D/', '', $telefone);

        // Se o número começar com '55' (código do Brasil), remove os primeiros 2 dígitos
        if (substr($telefone, 0, 2) === '55') {
            return substr($telefone, 2);
        }

        return $telefone;
    }
}


