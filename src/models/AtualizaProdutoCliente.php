<?php

namespace src\models;
require("../../vendor/autoload.php");


class AtualizaProdutoCliente extends CancelarContratoClienteOle
{

    public function atualizar(){

        $DeleteProdutoCliente = new DeleteProdutoCliente;
        $deletedProdutoLocal = $DeleteProdutoCliente->deleteproduto();
        return json_encode([
            "message" => "Contrato do cliente cancelado",
            "messageApi" => $this->cancela(),
            "messageDeleteProdutoLocal" => $deletedProdutoLocal,
            "messageNovoProduto" => "Novo produto adicionado com sucesso!"
        ]);
    }

}


