<?php
//Será o nome do arquivo
$datetime = date("Y-m-d_H_i_s").".zip";
$zip = new ZipArchive();
//Tenta criar zip
if( $zip->open($datetime, ZipArchive::CREATE) === true){
    /*
    *Este trecho navega entre os diretórios desejados
    */
    $path = "repositorio/";
    $diretorio = dir($path); 
    while($arquivo = $diretorio->read()){
        if(
            $arquivo != "." && 
            $arquivo != ".." &&
            $arquivo != "index.php"
        ){
            $path1 = $path.$arquivo."/";
            $diretorio1 = dir($path1);
            while($arquivo1 = $diretorio1 -> read()){
                if( 
                    $arquivo1 != "." && 
                    $arquivo1 != ".." &&
                    $arquivo1 != "index.php"
                ){
                    $file_to_zip = $path1.$arquivo1;
                    //Adiciona o file com o path
                    $zip->addFile($file_to_zip, $file_to_zip);
                }                
            }        
            $diretorio1->close();
        }
    }
    $diretorio->close();    
    $zip->close();
}
if (!file_exists("backup/files/")) {
    mkdir("backup/files/", 0755);
}
//Move pro diretório que desejar
rename($datetime, "backup/files/".$datetime);