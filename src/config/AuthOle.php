<?php

namespace src\config;

use src\models\BuscarIntegrador;

require("../../vendor/autoload.php");

namespace src\config;

use src\models\BuscarIntegrador;

require("../../vendor/autoload.php");

class AuthOle
{
    public function acessoole()
    {
        $BuscarIntegrador = new BuscarIntegrador;
        $integradorData = $BuscarIntegrador->buscarIntegrador();
        
        if (!$integradorData) {
            return ["error" => "Nenhum dado retornado pela função buscarIntegrador."];
        }
        
        if (empty($integradorData) || !isset($integradorData[0]["id"])) {
            return ["error" => "Dados do integrador não encontrados ou índice 'id' não está definido."];
        }
        
        $idIntegrador = $integradorData[0]["id"];
        
        $sql = "SELECT * FROM oletv_integrador WHERE id = ?";
        $stmt = Conn::connect()->prepare($sql);
        $stmt->bindValue(1, $idIntegrador);
        $stmt->execute();
        
        if ($stmt && $stmt->rowCount() > 0) {
            $results = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $results;
        }
        
        return ["error" => "Nenhum resultado encontrado para o id fornecido."];
    }
    
}


