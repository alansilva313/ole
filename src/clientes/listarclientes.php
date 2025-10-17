<?php

namespace src\clientes;

use src\services\BuscarClientes;

require("../../vendor/autoload.php");



$BuscarClientes = new BuscarClientes;
echo $BuscarClientes->buscarcliente();