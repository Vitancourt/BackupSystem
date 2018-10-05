<?php

/**
* Classe de conexão ao banco de dados usando PDO no padrão Singleton.
* Modo de Usar:
* require_once './Database.class.php';
* $db = Database::conectar();
* E agora use as funções do PDO (prepare, query, exec) em cima da variável $db.
*/

require_once("root.php");

class Database
{

    # Variável que guarda a conexão PDO.
    protected static $db;

    # Private construct - garante que a classe só possa ser instanciada internamente.
    private function __construct()
    {
        $db_host = "localhost";

        $db_nome = "DBNOME";
    
        $db_usuario = "root";

        $db_senha= "root";        

        $db_driver = "mysql";

        $sistema_titulo = "system";

        
        $sistema_email = "";
        try {

            # Atribui o objeto PDO à variável $db.
            self::$db = new PDO("$db_driver:host=$db_host; dbname=$db_nome", $db_usuario, $db_senha);

            # Garante que o PDO lance exceções durante erros.
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            # Garante que os dados sejam armazenados com codificação UTF-8.
            self::$db->exec('SET NAMES utf8');
            
        } catch (PDOException $e) {

            echo ($e->getMessage());

            die("Connection Error: " . $e->getMessage());

        }

    }

    # Método estático - acessível sem instanciação.
    public static function conectar()
    {

        # Garante uma única instância. Se não existe uma conexão, criamos uma nova.
        if (!self::$db)
        {

            //echo"conectado";
            new Database();

        }
        
        # Retorna a conexão.
        return self::$db;
    }

}

require_once("code/models/Database.php");
$db = Database::conectar();	

//Trecho responsável por nome arquivo e criar diretório
$data_hoje = DATE("d-H_i_s");
$data_pasta = date("Y-m");
if (!file_exists("backup/".$data_pasta)) {
    mkdir("backup/".$data_pasta, 0755);
}
$nome_arquivo = "backup/".$data_pasta."/".$data_hoje.".sql";
//Open File
$back = fopen($nome_arquivo, "w");

//Trecho que navega pelo DB, tabelas e create tables e dados para inserir no arquivo
$sql = 
    "SHOW TABLES
";
$consulta = $db->prepare($sql);
try{
    $consulta->execute();
    if($consulta->rowCount() > 0){
        $arr_tables = $consulta->fetchAll(PDO::FETCH_ASSOC);
    }
}catch(PDOException $e){
    echo $e->getMessage();
}

foreach ($arr_tables as $table) {
    foreach ($table as $tb) {
        $sql = 
            "SHOW CREATE TABLE ".$tb;
        $consulta = $db->prepare($sql);
        try{
            $consulta->execute();
            if($consulta->rowCount() > 0){
                $arr_create = $consulta->fetchAll(PDO::FETCH_ASSOC);
                if ($arr_create) {
                    foreach ($arr_create as $create) {
                        $string = "-- Criando tabela: ".$create["Table"]."\n\n";
                        fwrite($back, $string);
                        $string = " -- ".$create["Create Table"]." --\n\n";
                        fwrite($back, $string);
                        $sql = 
                            "SELECT * FROM ".$tb;
                        $consulta = $db->prepare($sql);
                        try{
                            $consulta->execute();
                            if($consulta->rowCount() > 0){
                                $arr_dados = $consulta->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($arr_dados as $dados) {
                                    unset($data);
                                    foreach ($dados as $r => $s) {
                                        if (
                                            empty($s) ||
                                            $s == ""
                                        ){
                                            $data[] = "NULL";
                                        } else {
                                            $data[] = "'".$s."'";
                                        }                                        
                                    }
                                    $data = implode(", ", $data);
                                    $string =  "INSERT INTO ".$tb." VALUES (".$data.");\n\n\n";
                                    fwrite($back, $string);
                                }                                
                            }
                        }catch(PDOException $e){
                            echo $e->getMessage();
                        }
                       
                    }
                }                
            }
        }catch(PDOException $e){
            echo $e->getMessage();
        }
    }        
}

fclose($back);
