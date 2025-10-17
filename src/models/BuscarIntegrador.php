<?php 

namespace src\models;

use src\config\Conn;

require("../../vendor/autoload.php");

class BuscarIntegrador
{
    public function getHeaders(){
        $logFilePath = '../models/datawebhook.json';
    
        // Lê o conteúdo do arquivo de log JSON
        $logJson = file_get_contents($logFilePath);
    
        // Decodifica o JSON recebido
        $logData = json_decode($logJson, true);
    
        // Verifica se os dados foram recebidos corretamente
        if (!$logData) {
            die("Erro ao ler ou decodificar o arquivo JSON.");
        }
    
        // Força a capitalização correta das chaves
        $headers = $logData['Headers'];
        
        $username = isset($headers['Username']) ? $headers['Username'] : (isset($headers['username']) ? $headers['username'] : null);
        $password = isset($headers['Password']) ? $headers['Password'] : (isset($headers['password']) ? $headers['password'] : null);
        $token = isset($headers['Token']) ? $headers['Token'] : (isset($headers['token']) ? $headers['token'] : null);
    
        // Verifica se todos os valores foram encontrados
        if (!$username || !$password || !$token) {
            die("Valores de autenticação ausentes no JSON.");
        }
    
        return [
            "username" => $username,
            "password" => $password,
            "token" => $token
        ];
    }
    

    public function buscarIntegrador(){

        $headers = $this->getHeaders();



        $sql = "SELECT id, user, pass, token FROM oletv_integrador WHERE user = ? AND active = 1";
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $headers["username"]);
        $stmt->execute();

        if($stmt){
            if($stmt->rowCount() > 0){
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                return $result;
            }

            echo "Os dados do integrador está incorreto ou não existem!";
        }
    }
}



