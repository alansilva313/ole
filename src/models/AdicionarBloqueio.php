<?php
namespace src\models;

use src\config\Conn;

require_once __DIR__ . '/../../vendor/autoload.php';

class AdicionarIdBloqueio
{
    private $idBloqueio;
    private $contratoOle;

    public function __construct($idBloqueio = null, $contratoOle = null)
    {
        $this->idBloqueio = $idBloqueio;
        $this->contratoOle = $contratoOle;
    }

    public function adicionar($idBloqueio = null, $contratoOle = null): bool
    {
        $log = new InserirLogDoCliente;

        $idBloqueio = $idBloqueio ?? $this->idBloqueio;
        $contratoOle = $contratoOle ?? $this->contratoOle;

        if (empty($idBloqueio) || empty($contratoOle)) {
            $log->insertlog("AdicionarIdBloqueio", "Parâmetros inválidos: idBloqueio={$idBloqueio}, contratoOle={$contratoOle}", "warning");
            return false;
        }

        try {
            $sql = "UPDATE oletv_contratos SET id_bloqueio = ? WHERE ole_contract_number = ?";
            $stmt = Conn::connect()->prepare($sql);
            $stmt->bindValue(1, $idBloqueio);
            $stmt->bindValue(2, $contratoOle);

            $log->insertlog("AdicionarIdBloqueio", "Executando SQL: {$sql} | id_bloqueio={$idBloqueio}, contrato={$contratoOle}", "info");

            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $log->insertlog("AdicionarIdBloqueio", "Sucesso: {$stmt->rowCount()} linha(s) atualizada(s) para contrato={$contratoOle}", "success");
                return true;
            } else {
                $log->insertlog("AdicionarIdBloqueio", "Nenhuma linha foi atualizada para contrato={$contratoOle}", "warning");
                return false;
            }
        } catch (\Throwable $th) {
            $log->insertlog("AdicionarIdBloqueio", "Erro: " . $th->getMessage(), "error");
            return false;
        }
    }

    public function remover($contratoOle = null): bool
    {
        $log = new InserirLogDoCliente;

        $contratoOle = $contratoOle ?? $this->contratoOle;

        if (empty($contratoOle)) {
            $log->insertlog("RemoverIdBloqueio", "Parâmetros inválidos: contratoOle={$contratoOle}", "warning");
            return false;
        }

        try {
            $sql = "UPDATE oletv_contratos SET id_bloqueio = NULL WHERE ole_contract_number = ?";
            $stmt = Conn::connect()->prepare($sql);
            $stmt->bindValue(1, $contratoOle);

            $log->insertlog("RemoverIdBloqueio", "Executando SQL: {$sql} | contrato={$contratoOle}", "info");

            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $log->insertlog("RemoverIdBloqueio", "Sucesso: {$stmt->rowCount()} linha(s) atualizada(s) para contrato={$contratoOle}", "success");
                return true;
            } else {
                $log->insertlog("RemoverIdBloqueio", "Nenhuma linha foi atualizada para contrato={$contratoOle}", "warning");
                return false;
            }
        } catch (\Throwable $th) {
            $log->insertlog("RemoverIdBloqueio", "Erro: " . $th->getMessage(), "error");
            return false;
        }
    }
}
