<?php

namespace src\models;
require("../../vendor/autoload.php");

class TratarFormatoCep extends BuscarDadosComplementaresExternos
{
    public function tratar()
    {
        // Captura o CEP dos dados buscados
        $cepNormal = $this->buscadados()["postalCode"];

        

        // Remove qualquer caractere que não seja número
        $cepLimpo = preg_replace("/[^0-9]/", "", $cepNormal);

        // Verifica se o CEP possui exatamente 8 dígitos
        if (strlen($cepLimpo) === 8) {
            // Formata o CEP no padrão XXXXX-XXX
            $cepFormatado = substr($cepLimpo, 0, 5) . '-' . substr($cepLimpo, 5, 3);
            return $cepFormatado;
        } else {
            // Retorna uma mensagem de erro caso o CEP seja inválido
            throw new \Exception("CEP inválido: " . $cepNormal);
        }
    }
}


