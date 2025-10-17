<?php
namespace src\models;

use src\config\AuthOle;
use src\models\InserirLogDoCliente;
use src\models\AdicionarIdBloqueio;

class DesbloquearForcado
{
    public function desbloquear(string $idContrato, string $idBloqueio): array
    {
        try {
            $AuthOle = new AuthOle();

            $data = [
                "login"  => $AuthOle->acessoole()["ole_user"],
                "pass"   => $AuthOle->acessoole()["ole_pass"],
                "keyapi" => $AuthOle->acessoole()["ole_token"],
            ];

            // Corrigido: usar idContrato e idBloqueio
            $url = $AuthOle->acessoole()["ole_endpoint"] . "/contratos/desbloqueio/{$idContrato}/{$idBloqueio}";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/x-www-form-urlencoded; charset=UTF-8"
            ]);

            $response = curl_exec($ch);

            if ($response === false) {
                $error = curl_error($ch);
                curl_close($ch);

                $log = new InserirLogDoCliente();
                $log->insertlog("Erro CURL", $error, "error");

                return [
                    "message"    => "Erro ao conectar na Olé",
                    "error"      => $error,
                    "statusCode" => 500
                ];
            }

            curl_close($ch);

            $log = new InserirLogDoCliente();
            $log->insertlog("Desbloqueio executado", $response, "info");
            $adicionarIdBloqueio = new AdicionarIdBloqueio();
            $adicionarIdBloqueio->remover($idContrato);
            return [
                "message"    => "Desbloqueio solicitado com sucesso",
                "response"   => $response,
                "statusCode" => 200
            ];

        } catch (\Throwable $th) {
            return [
                "message"    => "Erro ao desbloquear",
                "error"      => $th->getMessage(),
                "statusCode" => 500
            ];
        }
    }
}
