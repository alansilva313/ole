<?php

namespace src\models;

use PDO;
use PDOException;
use src\config\AuthOle;
use src\config\Conn;

require("../../vendor/autoload.php");

// Definir cabeçalhos CORS
header("Access-Control-Allow-Origin: *"); // Permitir requisições de qualquer origem
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type"); // Cabeçalhos permitidos

class EditarClienteOle
{
    private $data;

    public function __construct()
    {
        // Recebe os dados da requisição
        $this->data = json_decode(file_get_contents("php://input"), true);
        
        // Verifica se o ID está presente
        if (!isset($this->data['id']) || empty($this->data['id'])) {
            echo json_encode([
                "message" => "O ID do cliente é obrigatório!",
                "status" => 404
            ]);
            exit;
        }

        // Verifica se os dados obrigatórios estão presentes
        $requiredFields = ['nome', 'cpf_cnpj', 'email', 'endereco_cep', 'endereco_logradouro', 'endereco_numero', 'endereco_bairro', 'telefone_ddd', 'telefone_numero', 'cobranca_logradouro', 'cobranca_numero', 'cobranca_bairro'];
        foreach ($requiredFields as $field) {
            if (!isset($this->data[$field]) || empty($this->data[$field])) {
                echo json_encode([
                    "message" => "O campo $field é obrigatório!",
                    "status" => 404
                ]);
                exit;
            }
        }
    }

    public function edit()
    {
        try {
            $sql = "UPDATE clientes SET
                nome = :nome,
                cpf_cnpj = :cpf_cnpj,
                data_nascimento = :data_nascimento,
                email = :email,
                endereco_cep = :endereco_cep,
                endereco_logradouro = :endereco_logradouro,
                endereco_numero = :endereco_numero,
                endereco_bairro = :endereco_bairro,
                telefone_ddd = :telefone_ddd,
                telefone_numero = :telefone_numero,
                cobranca_cep = :cobranca_cep,
                cobranca_logradouro = :cobranca_logradouro,
                cobranca_numero = :cobranca_numero,
                cobranca_bairro = :cobranca_bairro
                WHERE id = :id";

            $stmt = Conn::connect()->prepare($sql);
            
            // Bind dos parâmetros
            $stmt->bindParam(':nome', $this->data['nome']);
            $stmt->bindParam(':cpf_cnpj', $this->data['cpf_cnpj']);
            $stmt->bindParam(':data_nascimento', $this->data['data_nascimento']);
            $stmt->bindParam(':email', $this->data['email']);
            $stmt->bindParam(':endereco_cep', $this->data['endereco_cep']);
            $stmt->bindParam(':endereco_logradouro', $this->data['endereco_logradouro']);
            $stmt->bindParam(':endereco_numero', $this->data['endereco_numero']);
            $stmt->bindParam(':endereco_bairro', $this->data['endereco_bairro']);
            $stmt->bindParam(':telefone_ddd', $this->data['telefone_ddd']);
            $stmt->bindParam(':telefone_numero', $this->data['telefone_numero']);
            $stmt->bindParam(':cobranca_cep', $this->data['endereco_cep']);
            $stmt->bindParam(':cobranca_logradouro', $this->data['endereco_logradouro']);
            $stmt->bindParam(':cobranca_numero', $this->data['endereco_numero']);
            $stmt->bindParam(':cobranca_bairro', $this->data['endereco_bairro']);
            $stmt->bindParam(':id', $this->data['id']);

            // Executa a consulta
            $stmt->execute();

            if ($stmt && $stmt->rowCount() > 0) {

                $this->reintegrar();
                
                
                
            } else {
                echo json_encode([
                    "message" => "Nenhuma alteração foi feita.",
                    "status" => 204
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode([
                "message" => "Erro ao atualizar o cliente: " . $e->getMessage(),
                "status" => 500
            ]);
        }
    }

    public function buscarDadosLocal()
    {
        try {
            $sql = "SELECT * FROM clientes WHERE id = ?";
            $stmt = Conn::connect()->prepare($sql);
            $stmt->bindValue(1, $this->data['id']);
            $stmt->execute();

            if ($stmt && $stmt->rowCount() > 0) {
                $result =  $stmt->fetch(PDO::FETCH_ASSOC);

                return $result;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        return null;
    }


    public function listidcliente()
    {
        $authOle = new AuthOle;
    
        // Realiza a requisição para listar os contratos
        $ch = curl_init("https://api.oletv.net.br/clientes/buscacpfcnpj/".$this->data['cpf_cnpj']);
    
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


        return $responseData["lista"][0]["id"];
    
       
    }

    public function reintegrar()
    {
        try {
            $authOle = new AuthOle;
            $dadosLocal = $this->buscarDadosLocal();

            

            $data = [
                "login" => $authOle->acessoole()["ole_user"],
                "pass" => $authOle->acessoole()["ole_pass"],
                "keyapi" => $authOle->acessoole()["ole_token"],
                "nome" => $this->data['nome'],
                "tipo_pessoa" => 1,
                "nome_fantasia" => $dadosLocal["nome_fantasia"] ?? '',
                "cpf_cnpj" => $this->data['cpf_cnpj'],
                "inscricao_estadual" => $dadosLocal["inscricao_estadual"] ?? '',
                "data_nascimento" => $this->data['data_nascimento'] ?? '',
                "endereco_cep" => $this->data['endereco_cep'],
                "endereco_logradouro" => $this->data['endereco_logradouro'],
                "endereco_numero" => $this->data['endereco_numero'],
                "endereco_bairro" => $this->data['endereco_bairro'],
                "endereco_complemento_tipo" => $dadosLocal["endereco_complemento_tipo"] ?? '',
                "endereco_complemento_valor" => $dadosLocal["endereco_complemento_valor"] ?? '',
                "contato" => $dadosLocal["contato"] ?? '',
                "telefone_ddd[]" => $this->data['telefone_ddd'],
                "telefone_numero[]" => $this->data['telefone_numero'],
                "telefone_ramal" => $dadosLocal["telefone_ramal"] ?? '',
                "telefone_tipo[]" => 3,
                "email[]" => $this->data['email'] ?? '',
                "dia_vencimento" => 7,
                "endereco_cobranca" => "c",
                "cobranca_cep" => $this->data['endereco_cep'],
                "cobranca_logradouro" => $this->data['endereco_logradouro'],
                "cobranca_numero" => $this->data['endereco_numero'],
                "cobranca_bairro" => $this->data['endereco_bairro'],
                "cobranca_complemento_tipo" => $dadosLocal["cobranca_complemento_tipo"] ?? '',
                "cobranca_complemento_valor" => $dadosLocal["cobranca_complemento_valor"] ?? ''
            ];

            $url = "https://api.oletv.net.br/clientes/alterar/".$this->listidcliente();

           

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);

                // Passa o array diretamente para http_build_query
                $formData = http_build_query($data);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/x-www-form-urlencoded; charset=UTF-8"
                ]);

                $response = curl_exec($ch);

            if (curl_errno($ch)) {
                echo json_encode([
                    "message" => "Erro ao editar cliente: " . curl_error($ch),
                    "status" => 500
                ]);
            } else {
                echo json_encode([
                    "message" => "Cliente atualizado com sucesso!",
                    "response" => json_decode($response, true),
                    "status" => 200
                ]);
            }

            curl_close($ch);

        } catch (PDOException $e) {
            echo json_encode([
                "message" => "Erro ao editar cliente: " . $e->getMessage(),
                "status" => 500
            ]);
        }
    }
}

$editarCliente = new EditarClienteOle();
$editarCliente->edit(); 
