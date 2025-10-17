<?php
namespace src\models;

use src\config\AuthOle;
use src\config\Conn;

require("../../vendor/autoload.php");

class ListarContratoClienteOle extends ListarClienteOle
{
    public function listcontrato()
    {
        $authOle = new AuthOle;
    
        // Realiza a requisição para listar os contratos
        $ch = curl_init($authOle->acessoole()["ole_endpoint"] . "/contratos/listar/" . $this->listclient());
    
        $data = [
            "keyapi" => $authOle->acessoole()["ole_token"],
            "login" => $authOle->acessoole()["ole_user"],
            "pass" => $authOle->acessoole()["ole_pass"],
        ];
    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: multipart/form-data'
        ]);
    
        $response = curl_exec($ch);
    
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => $error];
        }
    
        curl_close($ch);
    
        // Decodifica a resposta JSON em um array associativo
        $responseData = json_decode($response, true);
    
        // Verifica se a chave "contratos" existe e se contém dados
        if (isset($responseData['contratos']) && !empty($responseData['contratos'])) {
            $resultados = [];
    
            // Percorre todos os contratos
            foreach ($responseData['contratos'] as $contrato) {
                $idContrato = $contrato['id'];
                $assinaturas = $contrato['assinaturas']; // Pega as assinaturas deste contrato
    
                // Array para armazenar os resultados das assinaturas
                $assinaturasResult = [];
    
                // Percorre cada assinatura
                foreach ($assinaturas as $assinatura) {
                    $idAssinatura = $assinatura['id'];
                    $equipamentos = $assinatura['equipamentos'];
    
                    // Array para armazenar os IDs dos equipamentos
                    $idsEquipamentos = [];
    
                    // Percorre cada equipamento dentro da assinatura
                    foreach ($equipamentos as $equipamento) {
                        $idsEquipamentos[] = $equipamento['id'];
                    }
    
                    // Adiciona o resultado da assinatura no array
                    $assinaturasResult[] = [
                        'id_assinatura' => $idAssinatura,
                        'ids_equipamentos' => $idsEquipamentos
                    ];
                }
    
                // Adiciona o resultado do contrato no array final
                $resultados[] = [
                    'id_contrato' => $idContrato,
                    'assinaturas' => $assinaturasResult
                ];
            }
    
            // Retorna o array de resultados, não mais como JSON
            return $resultados;
        } else {
            return "Nenhum contrato encontrado.";
        }
    }




public function listcontratoAtivos()
{
    $authOle = new AuthOle;

    // 🔹 Requisição para listar os contratos do cliente
    $ch = curl_init($authOle->acessoole()["ole_endpoint"] . "/contratos/listar/" . $this->listclient());

    $data = [
        "keyapi" => $authOle->acessoole()["ole_token"],
        "login" => $authOle->acessoole()["ole_user"],
        "pass" => $authOle->acessoole()["ole_pass"],
    ];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: multipart/form-data'
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['error' => $error];
    }

    curl_close($ch);

    $responseData = json_decode($response, true);

    // 🔸 Verifica se há contratos retornados
    if (!isset($responseData['contratos']) || empty($responseData['contratos'])) {
        return "Nenhum contrato encontrado.";
    }

    $contratosFiltrados = [];

    foreach ($responseData['contratos'] as $contrato) {
        // ✅ Filtra exatamente pelo status "Ativo (Com Pendências)"
        if (isset($contrato['status']) && trim($contrato['status']) === 'Ativo (Com Pendências)') {
            $contratosFiltrados[] = [
                'id_contrato' => $contrato['id'],
                'status_contrato' => $contrato['status'],
                'codigo' => $contrato['codigo'],
                'servico' => $contrato['servico'],
                'assinaturas' => $contrato['assinaturas'] ?? []
            ];
        }
    }

    if (empty($contratosFiltrados)) {
        return "Nenhum contrato com status 'Ativo (Com Pendências)' encontrado.";
    }

    return $contratosFiltrados;
}










    public function verificarIdOleComIdVoalle()
    {
        $buscarIntegrador = new BuscarIntegrador;
        $idIntegrador = $buscarIntegrador->buscarIntegrador()[0]["id"];

        $pegarCpf = new PegarDadosPessoaisWebHook;
        $cpfCliente = $pegarCpf->pegarCpf();
        $contractCliente = $pegarCpf->pegarNumeroContrato();

        $sql = "SELECT * FROM oletv_contratos WHERE idIntegra = ? AND client_txid = ? AND voalle_contract_number = ?";
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $idIntegrador);
        $stmt->bindValue(2, $cpfCliente);
        $stmt->bindValue(3, $contractCliente);
        $stmt->execute();

        if($stmt){
            if($stmt->rowCount() > 0){
                $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                // Pegue o ole_contract_number do resultado
                return $results[0]['ole_contract_number'];
            }
        }

        return null; // Retorna null se nenhum contrato for encontrado
    }

    public function compararContratos()
    {
        // Obtém o ole_contract_number da função verificarIdOleComIdVoalle
        $oleContractNumber = $this->verificarIdOleComIdVoalle();

        // Se não encontrar nenhum contrato, retorna mensagem
        if ($oleContractNumber === null) {
            return 'Nenhum contrato encontrado.';
        }

        // Obtém os contratos da API
        $contratosApi = $this->listcontrato();

        // Verifica se a lista de contratos da API não está vazia
        if (is_array($contratosApi)) {
            // Cria um array de id_contrato da API para facilitar a busca
            $idsContratosApi = array_column($contratosApi, 'id_contrato');

            // Verifica se ole_contract_number está na lista de contratos
            if (in_array($oleContractNumber, $idsContratosApi)) {
                return $oleContractNumber;
            } else {
                return 'Contrato não encontrado.';
            }
        }

        return 'Nenhum contrato encontrado.';
    }


    public function retornacontratocompleto(){
        
        $authOle = new AuthOle;
        $ch = curl_init($authOle->acessoole()["ole_endpoint"]."/contratos/listar/" . $this->listclient());
        
        $data = [
           "keyapi" => $authOle->acessoole()["ole_token"],
            "login" => $authOle->acessoole()["ole_user"],
            "pass" => $authOle->acessoole()["ole_pass"],
        ];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);

        // Enviando os dados como form-data
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        // Definir cabeçalho indicando envio em form-data
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: multipart/form-data'
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => $error];
        }

        curl_close($ch);

        // Decodifica a resposta JSON
        $responseData = json_decode($response, true);

        return json_encode($responseData);
    }
}


$ListarContratoClienteOle = new ListarContratoClienteOle;
echo $ListarContratoClienteOle->retornacontratocompleto();
