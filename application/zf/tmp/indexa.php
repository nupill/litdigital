<?php 

require(dirname(__FILE__).'/../controllers/ControleDocumentos.php');

$id_documento = $_SERVER["argv"][1];
$arquivo = $_SERVER["argv"][2];

$controllerdoc = ControleDocumentos::getInstance();
$controllerdoc->index_one_document($id_documento,$arquivo);

?>