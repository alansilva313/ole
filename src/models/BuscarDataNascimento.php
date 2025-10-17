<?php



namespace src\models;

use src\config\AuthOle;

require("../../vendor/autoload.php");

class BuscarDataNascimento
{
    
 
    protected function fetchData()
    {
      

        $authOle = new AuthOle;

        $url = $authOle->acessoole()["oletv_erp_url"];
        

        
        $ch = curl_init($url);
        $cpfDoCLiente = new TratarCpfCliente();
        $data = http_build_query([
            "codigo" => $cpfDoCLiente->cpfSemPontuacao(),
            "token" => $authOle->acessoole()["voalle_token_pbx"]
        ]);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);

       

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => $error];
        }

        curl_close($ch);

        $responseData = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Erro ao decodificar o JSON da resposta.'];
        }

        return $responseData;
    }

    public function getDataNascimento()
    {
        $data = $this->fetchData();

        if (isset($data['error'])) {
            echo "Erro ao buscar dados: " . $data['error'] . "\n";
            return null;
        }

        if (isset($data['clients'][0]['client_birth_date'])) {
            $formattedDate = $data['clients'][0]['client_birth_date'];
            return $formattedDate;
        } else {
            echo "Data de nascimento não encontrada na resposta.\n";
            $log = new InserirLogDoCliente;
            $log->insertlog("Data de nascimento", "Data de nascimento não encontrada na resposta.\n", "warning");
            return;
        }
    }

    public function process()
    {


        $authOle = new AuthOle;

       

        $logFilePath = "./datawebhook.json";
        if (!file_exists($logFilePath)) {
            die("Arquivo datawebhookuser.json não encontrado.");
        }

        $logJson = file_get_contents($logFilePath);
        $logData = json_decode($logJson, true);

        if (!$logData) {
            die("Erro ao ler ou decodificar o arquivo JSON.");
        }

       
        $dataOriginal = $this->getDataNascimento();

        if ($dataOriginal) {
            try {
                // Verifica o formato da data e a ajusta se necessário
                $datan = new \DateTime($dataOriginal);
                $convertData = $datan->format('d/m/Y'); // Formata para 'd/m/Y'
                
                // Adiciona a data de nascimento ao logData
                $logData['DataObject']['Client']['BirthDate'] = $convertData;
                
                // Grava o conteúdo atualizado de volta no arquivo JSON
                $updatedLogJson = json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                file_put_contents($logFilePath, $updatedLogJson);

                return $convertData;
            } catch (\Exception $e) {
                return "Erro ao formatar a data: " . $e->getMessage();
            }
        } else {
            return "Falha ao receber a data de nascimento!";
        }
    }
}



