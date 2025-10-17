<?php

namespace src\models;
require("../../vendor/autoload.php");
use src\models\PegarDadosPessoaisWebHook;

class TratarCpfCliente
{
    public function cpfSemPontuacao()
    {
        $PegarDadosPessoaisWebHook = new PegarDadosPessoaisWebHook();
        $cpf = $PegarDadosPessoaisWebHook->pegarCpf();
    
        // Remove todos os caracteres que não são dígitos
        $cpfSemPontuacao = preg_replace('/\D/', '', $cpf);
    
        return $cpfSemPontuacao;
    }
    
}


