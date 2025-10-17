<?php

namespace src\config;

require("../../vendor/autoload.php");
class Conn
{
    // Propriedades estáticas
    public static $conn;
    public static $host;
    public static $username;
    public static $dbname;
    public static $password;

    public function __construct()
    {
    


       /*  self::$host = "localhost" ?? null;
        self::$username = "root" ?? null;
        self::$dbname = "frionline" ?? null;
        self::$password = "" ?? null; */
        
        self::$host = "api.sysprov.com.br" ?? null;
        self::$username = "sysprovolefrionline" ?? null;
        self::$dbname = "sysprovolefrionline" ?? null;
        self::$password = "A984ssME3Jp5MwKIZDhD" ?? null;



  
       

        // Verificação se todas as variáveis foram corretamente carregadas
        if (!self::$host || !self::$username || !self::$dbname) {
            die("Erro: Uma ou mais variáveis de ambiente não foram carregadas corretamente.");
        }
    }

    public static function connect()
    {
       
        if (!isset(self::$conn)) {
            try {
                // Conectando ao banco de dados usando PDO
                self::$conn = new \PDO(
                    'mysql:host=' . self::$host . ';dbname=' . self::$dbname, 
                    self::$username, 
                    self::$password
                );
                
                // Definindo o modo de erro do PDO para exceções
                self::$conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            } catch (\PDOException $e) {
                die("Erro ao conectar ao banco de dados: " . $e->getMessage());
            }
        }

        // Retornando a conexão
        return self::$conn;
    }
}


$Conn = new Conn;
$Conn->connect();