<?php

namespace src\models;

use src\config\AuthOle;

require("../../vendor/autoload.php");

class VerificarStatusContratoOle
{

    
    public function listarBloqueios()
    {
        $listarContrato = new ListarContratoClienteOle;
        $contratonumber = $listarContrato->listcontrato();
        $authOle = new AuthOle;
        $ch = curl_init($authOle->acessoole()["ole_endpoint"]."/contratos/listarbloqueios/" . $contratonumber . "/1");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
    
        
    
        $data = [
            "keyapi" => $authOle->acessoole()["ole_token"],
            "login" => $authOle->acessoole()["ole_user"],
            "pass" => $authOle->acessoole()["ole_pass"],
        ];
        $formData = http_build_query($data);
    
        curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded; charset=UTF-8"
        ]);
    
        $response = curl_exec($ch);
    
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => $error];
        }
    
        curl_close($ch);
    
        $responseData = json_decode($response, true);
    
        // Verificar se a resposta foi decodificada corretamente
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "Erro ao decodificar JSON: " . json_last_error_msg();
            return;
        }
    
        $statusWebHook = strtolower((new PegarDadosPessoaisWebHook)->pegarStatus());
    
        // Se a lista de bloqueios estiver vazia, ainda precisamos verificar o status do WebHook
        if (!isset($responseData['bloqueios']) || count($responseData['bloqueios']) === 0) {
            return "Lista de bloqueios vazia. Verificando status via WebHook.";
    
            // Verifica se o status do WebHook requer bloqueio
            switch ($statusWebHook) {
                case "bloqueio administrativo":
                    (new AplicarBloqueioNoContrato)->aplicar(2);
                    break;
                case "bloqueio financeiro":
                    (new AplicarBloqueioNoContrato)->aplicar(1);
                    break;
                default:
                    return "Status do WebHook é 'normal', sem necessidade de aplicar bloqueio.";
                    break;
            }
            return;
        }
    
        // Se há bloqueios, verificar se algum está ativo
        $verificaSeFoiFinalizado = $responseData["bloqueios"][0]["status_nome"];
    
        // Filtrar apenas o bloqueio com status_nome "Ativo"
        $bloqueioAtivo = array_filter($responseData['bloqueios'], function($bloqueio) {
            return isset($bloqueio['status_nome']) && $bloqueio['status_nome'] === 'Ativo';
        });
    
        // Extrair apenas o campo 'id'
        $idsAtivos = array_column($bloqueioAtivo, 'id');
    
        $idDoBloqueio = "";
        if (!empty($idsAtivos)) {
            $idDoBloqueio = $idsAtivos[0];
        }
    
        // Se houver bloqueios e nenhum está finalizado, verificar o tipo de bloqueio
        if (count($responseData["bloqueios"]) > 0 && $verificaSeFoiFinalizado !== "Inativo (Finalizado)") {
            $tipo = $responseData["bloqueios"][0]["tipo_id"];
    
            if ($tipo !== "1") {
                $bloqueioole = strtolower("Bloqueio Administrativo");
    
                if ($bloqueioole === $statusWebHook) {
                     $blIgual = "Os bloqueios são iguais!";
                }
            } else {
                $bloqueioole = strtolower("Bloqueio Financeiro");
    
                if ($bloqueioole === $statusWebHook) {
                    $blIgual =  "Os bloqueios são iguais!";
                } else {
                    $blDiferente =  "bloqueios diferentes!";
                }
            }
        }
    
        // Se o bloqueio foi finalizado, realizar as ações necessárias
        if ($verificaSeFoiFinalizado === "Inativo (Finalizado)") {
            switch ($statusWebHook) {
                case "bloqueio administrativo":
                    (new AplicarBloqueioNoContrato)->aplicar(2);
                    break;
                case "bloqueio financeiro":
                    (new AplicarBloqueioNoContrato)->aplicar(1);
                    break;
                default:
                    (new DesbloquearContratoDocliente)->removerbloqueio($idDoBloqueio);
                    break;
            }
        }
    }
    
        
    

}



