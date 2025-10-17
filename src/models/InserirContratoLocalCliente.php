<?php

namespace src\models;

use src\config\Conn;

require("../../vendor/autoload.php");



class InserirContratoLocalCliente
{

    protected $voalle_contract_number;
    protected $ole_contract_number;
    protected $client_txid;
    protected $client_name;
    protected $client_mail;
    protected $client_phone;
    protected $client_mobile_phone;
    protected $voalle_contract_status;
    protected $voalle_contract_stage;
    protected $voalle_company_id;


    public function __construct($voalle_contract_number, $ole_contract_number, $client_txid, $client_name, $client_mail, $client_phone, $client_mobile_phone, $voalle_contract_status, $voalle_contract_stage, $voalle_company_id)
    {
        $this->voalle_contract_number = $voalle_contract_number;
        $this->ole_contract_number = $ole_contract_number;
        $this->client_txid = $client_txid;
        $this->client_name = $client_name;
        $this->client_mail = $client_mail;
        $this->client_phone = $client_phone;
        $this->client_mobile_phone = $client_mobile_phone;
        $this->voalle_contract_status = $voalle_contract_status;
        $this->voalle_contract_stage = $voalle_contract_stage;
        $this->voalle_company_id = $voalle_company_id;
        
    }

    public function verifcarExistContrato(){
        $sql = "SELECT client_txid FROM oletv_contratos WHERE client_txid = ?";
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $this->client_txid);
        $stmt->execute();

        if($stmt){
            if($stmt->rowCount() > 0){
                return true;
            }

            return false;
        }
    }

    public function inserircontrato(){

        $buscarIntegrador = new BuscarIntegrador;
        $idIntegrador = $buscarIntegrador->buscarIntegrador();
      

        if($this->verifcarExistContrato() === true){
           return [];
        }

        $sql = "INSERT INTO oletv_contratos
        (idIntegra, voalle_contract_number, ole_contract_number, 
        client_txid, client_name, client_mail, client_phone, 
        client_mobile_phone, voalle_contract_status, voalle_contract_stage, voalle_company_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $idIntegrador[0]["id"]);
        $stmt->bindValue(2, $this->voalle_contract_number);
        $stmt->bindValue(3, $this->ole_contract_number);
        $stmt->bindValue(4, $this->client_txid);
        $stmt->bindValue(5, $this->client_name);
        $stmt->bindValue(6, $this->client_mail);
        $stmt->bindValue(7, $this->client_phone);
        $stmt->bindValue(8, $this->client_mobile_phone);
        $stmt->bindValue(9, $this->voalle_contract_status);
        $stmt->bindValue(10, $this->voalle_contract_stage);
        $stmt->bindValue(11, $this->voalle_company_id);

        $stmt->execute();

        if($stmt){
            if($stmt->rowCount() > 0){
                return json_encode([
                    "message" => "Inserido com sucesso!",
                    "status" => 200
                ]);
            }
        }

    }
}





$ListarContratoClienteOle = new ListarContratoClienteOle;
$contratolenumber = $ListarContratoClienteOle->listcontrato()[0]["id_contrato"];





$DadosPessoa = new PegarDadosPessoaisWebHook;
$nomecliente = $DadosPessoa->pegarNomeCompleto();
$emailpessoa = $DadosPessoa->pegarEmail();
$phonepessoa = $DadosPessoa->pegarCelular();
$status = $DadosPessoa->pegarStatus();
$stage = $DadosPessoa->pegarStage();
$companyplace = $DadosPessoa->pegarCompanyPlace();
$numberContractVoalle = $DadosPessoa->pegarNumeroContrato();
$cpfclientenumber = $DadosPessoa->pegarCpf();




/* Chama a classe que insere contrato */

$InserirContratoLocalCliente = new InserirContratoLocalCliente($numberContractVoalle, $contratolenumber, $cpfclientenumber, 
$nomecliente, $emailpessoa, $phonepessoa, $phonepessoa, $status, $stage, $companyplace);

$InserirContratoLocalCliente->inserircontrato();