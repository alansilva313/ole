<?php

namespace src\models;
require("../../vendor/autoload.php");
use src\config\Conn;

class DeleteProdutoCliente
{
    public function buscarCliente()
    {
        $PegaCpfSemPontuacao = new PegarDadosPessoaisWebHook;
        $cpf = $PegaCpfSemPontuacao->pegarCpf();

        // Corrigir a consulta SQL removendo a vírgula extra
        $sql = "SELECT id FROM produtos_cliente WHERE txid = ?";
        $stmt = Conn::connect()->prepare($sql);

        // Comparar o CPF com o campo txid
        $stmt->bindValue(1, $cpf);
        $stmt->execute();

        $response = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($response) {
            return $response["id"];
        }

        // Retornar null se não encontrar nenhum cliente
        return null;
    }

    public function deleteproduto()
    {
        $id = $this->buscarCliente();
        
        if ($id) {
            $sql = "DELETE FROM produtos_cliente WHERE id = ?";
            $stmt = Conn::connect()->prepare($sql);
            $stmt->bindValue(1, $id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return "Produto deletado com sucesso!";
            }
        }

        return false;
    }
}

