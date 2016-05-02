<?php
require_once(dirname(__FILE__) . '/../../../application/config/general.php');
require_once(APPLICATION_PATH . '/controllers/ControleBuscaAutor.php');
require_once(APPLICATION_PATH . "/controllers/ControleAutores.php");

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    
    case 'getTableData':
        $controller = ControleBuscaAutor::getInstance();
        $_GET['sTable'] = 'Autor';
        exit($controller->getTableData($_GET));
        break;
        
    case 'getForm':
        include('autor.php');
        exit();
        break;

    case 'autocomplete': //Called from forms at Documentos
        $controller = ControleAutores::getInstance();
        $term = isset($_REQUEST['term']) ? $_REQUEST['term'] : '';
        $results = $controller->get(null, array('id', 'nome_completo', 'pseudonimo'), 0, 15, $term);
        $response = array();
        foreach ($results as $key=>$result) {
           $response[$key]['id'] = $result['id'];
           $response[$key]['label'] = $result['nome_completo'];
           $response[$key]['value'] = $result['nome_completo'];
           $response[$key]['desc'] = $result['pseudonimo'];
        }
        exit(json_encode($response));
        break;
        
    default:
        
        require_once(APPLICATION_PATH . '/include/TemplateHandler.php');
        $template = new TemplateHandler();
        $template->set_css_files(array(
                                'busca.css',
        						'jquery.dataTables.css'
        						));
        $template->set_js_files(array(
        						'jquery.dataTables.min.js',
                                'jquery.loadTable.js'
        						));
        
        if (isset($_REQUEST['nome'])) {
            $template->set_content_file('busca/autor/resultados.php');
        }
        else {
            header('Location: ../');
            exit();
        }
        $template->set_authenticated_only(false);
        $template->set_active_nav_item(BUSCA_ID);
        $template->show(); 
}

