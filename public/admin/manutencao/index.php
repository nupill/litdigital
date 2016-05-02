<?php

$action = isset($_GET['id']) ? $_GET['id'] : '';
switch ($action) {

    case '1':
        require_once(dirname(__FILE__) . '/../../../application/config/general.php');
        require_once(APPLICATION_PATH . "/controllers/ControleDocumentoConsulta.php");
        $controller = ControleDocumentoConsulta::getInstance();
        $controller->reset();
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        break;
    case '2':
        echo "teste";
        $command ="cd ../../../application/zf ; php indexarDocumentos.php > index.log 2>&1 </dev/null &";
        $output = shell_exec($command);
        echo $output;
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        break;
}
?>