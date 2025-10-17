<?php


namespace src\services;

use src\config\Conn;

require("../../vendor/autoload.php");

// Definir cabeçalhos CORS
header("Access-Control-Allow-Origin: *"); // Permitir requisições de qualquer origem
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Cabeçalhos permitidos


class ListarProdutoPorCliente
{

    public function buscarprodutosporcliente()
    {


        $method = $_SERVER["REQUEST_METHOD"];

        if($method !== "GET"){
            return json_encode([
                "message" => "Acesso bloqueado!"
            ]);
        }
        $headers = getallheaders();


      
        if (isset($headers['idIntegra']) && isset($headers['documento_cliente'])) {
            $idIntegra = $headers['idIntegra'];
            $documento_cliente = $headers['documento_cliente'];
    
        } else {
            echo json_encode([
                "message" => "Parametros obrigatorios não foram enviados",
                "StatusCode" => 404,
            ]);

            return;
        }
        
        $sql = "SELECT * FROM produtos_cliente WHERE idIntegra = ? AND txid = ?";
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $idIntegra);
        $stmt->bindValue(2, $documento_cliente);
        $stmt->execute();

        if($stmt){
            if($stmt->rowCount() > 0){
                $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                return json_encode([
                    "message" => "produtos",
                     "status" => 200,
                    "data" => $results
                ]);
            }

            return json_encode([
                "message" => "nenhum produto encontrado!",
                "status" => 200
            ]);
        }
    }

}



$ListarProdutoPorCliente = new ListarProdutoPorCliente;
echo $ListarProdutoPorCliente->buscarprodutosporcliente();