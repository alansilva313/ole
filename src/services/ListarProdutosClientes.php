<?php


namespace src\services;

use src\config\Conn;

require("../../vendor/autoload.php");


// Definir cabeçalhos CORS
header("Access-Control-Allow-Origin: *"); // Permitir requisições de qualquer origem
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Cabeçalhos permitidos


class ListarProdutosClientes
{

    public function buscartodososprodutos()
    {


        $method = $_SERVER["REQUEST_METHOD"];

        if($method !== "GET"){
            return json_encode([
                "message" => "Acesso bloqueado!"
            ]);
        }
        $headers = getallheaders();


        if (isset($headers['idIntegra'])) {
            $idIntegra = $headers['idIntegra'];
         
    
        } else {
            echo json_encode([
                "message" => "Parametros obrigatorios não foram enviados",
                "StatusCode" => 404,
            ]);

            return;
        }
        
        $sql = "SELECT * FROM produtos_cliente WHERE idIntegra = ?";
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $idIntegra);
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



$ListarProdutosClientes = new ListarProdutosClientes;
echo $ListarProdutosClientes->buscartodososprodutos();