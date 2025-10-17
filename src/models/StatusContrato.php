<?php

namespace src\models;
use src\config\AuthOle;
require("../../vendor/autoload.php");

class StatusContrato {


    public function verificaStatusContrato(){

        $PegarDadosPessoaisWebHook = new PegarDadosPessoaisWebHook();
        $status = $PegarDadosPessoaisWebHook->pegarStatus();

        
        
       $ct = strpos(strtolower($status), "bloqueio");
       
        switch (strtolower($status)) {
            case "bloqueio financeiro":
                 json_encode([
                    "message" => "Contrato diferente de normal!",
                    "StatusCode" => 200,
                    "Content" => [
                        "Event" => [
                            "Text" => "O contrato do cliente possui algum tipo de bloqueio, por este motivo não foi possivel prosseguir!"
                        ]
                    ]
                ]);
                
                $aplicarBloqueio = new AplicarBloqueioNoContrato;
                $aplicarBloqueio->aplicar(1);
                $EditarStatusContrato = new EditarStatusContrato;
                $EditarStatusContrato->alterarStatus("Bloqueio Financeiro");
                
                $log = new InserirLogDoCliente;
                $log->insertlog("Contrato bloqueado", "O contrato do cliente foi bloqueado com sucesso", "success");
                 $log = new InserirLogDoCliente;
                $log->insertlog("Bloqueio Financeiro", "O contrato do cliente possui algum tipo de bloqueio, por este motivo não foi possivel prosseguir!", "error");
                
                  


               
                return;
                break;

            case "bloqueio administrativo":
                 json_encode([
                    "message" => "Contrato diferente de normal!",
                    "StatusCode" => 200,
                    "Content" => [
                        "Event" => [
                            "Text" => "O contrato do cliente possui algum tipo de bloqueio, por este motivo não foi possivel prosseguir!"
                        ]
                    ]
                ]);


                $aplicarBloqueio = new AplicarBloqueioNoContrato;
                $aplicarBloqueio->aplicar(2);

                $EditarStatusContrato = new EditarStatusContrato;
                $EditarStatusContrato->alterarStatus("Bloqueio Administrativo");

                $log = new InserirLogDoCliente;
                $log->insertlog("Bloqueio Administrativo", "O contrato do cliente possui algum tipo de bloqueio, por este motivo não foi possivel prosseguir!", "error");


                
                return;
                break;


                case "cancelado":
                    json_encode([
                       "message" => "Contrato diferente de normal!",
                       "StatusCode" => 200,
                       "Content" => [
                           "Event" => [
                               "Text" => "O contrato do cliente está cancelado, por este motivo não foi possivel prosseguir!"
                           ]
                       ]
                   ]);
   
   
                  
                

                    $ListarContratoClienteOle = new ListarContratoClienteOle;
                    $contratosQuantidade = $ListarContratoClienteOle->listcontrato();

                    if($contratosQuantidade !== "Nenhum contrato encontrado."){
                        $cancelarNaOle = new CancelarContratoClienteOle;
                        $cancelarNaOle->cancela();

                        $EditarStatusContrato = new EditarStatusContrato;
                        $EditarStatusContrato->alterarStatus("Cancelado");
                        $log = new InserirLogDoCliente;
                        $log->insertlog("Contrato Cancelado", "O contrato do cliente foi cancelado com sucesso!", "success");
                        return;
                    }


                   $log = new InserirLogDoCliente;
                   $log->insertlog("Contrato Cancelado", "Nenhum contrato foi encontrado na Olé para cancelar!", "error");
   
   
                   
                   return;
                   break;
              
            case $ct !== false:
                 json_encode([
                    "message" => "Contrato diferente de normal!",
                    "StatusCode" => 200,
                    "Content" => [
                        "Event" => [
                            "Text" => "O contrato do cliente possui algum tipo de bloqueio, por este motivo não foi possivel prosseguir!"
                        ]
                    ]
                ]);

              

              
                return;
                break;

            
            default:

                
       /*   $bloqueio = new ListarBloqueioContrato();
         $bb = $bloqueio->listarbloqueio(); */

        


        

       


            
      /*       $desbloquearContrato = new DesbloquearContratoDocliente;
            $desbloqueio = $desbloquearContrato->removerbloqueio($bb);

                $removerBloqueioId = new AdicionarIdBloqueio;
                $removerBloqueioId->removeridbloqueio(); */
                $log = new InserirLogDoCliente;
                $log->insertlog("Bloqueio removido", "Bloqueio removido com sucesso", "success");

            $EditarStatusContrato = new EditarStatusContrato;
            $EditarStatusContrato->alterarStatus("Normal");
                return trim("Normal");
                break;
             


        }

    }
    
}

