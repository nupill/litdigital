<?php
require_once(dirname(__FILE__) . '/../../../application/config/general.php');
require_once(APPLICATION_PATH . "/controllers/ControleAutores.php");
require_once(APPLICATION_PATH . "/controllers/ControleLocalizacao.php");

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    
    case 'add':
    case 'update':
        
        $nome_completo = isset($_POST['nome_completo']) ? $_POST['nome_completo'] : '';
        $pseudonimo = isset($_POST['pseudonimo']) ? $_POST['pseudonimo'] : '';
        $nome_usual = isset($_POST['nome_usual']) ? $_POST['nome_usual'] : '';
        $ano_nascimento = isset($_POST['ano_nascimento']) ? $_POST['ano_nascimento'] : '';
        $seculo_nascimento = isset($_POST['seculo_nascimento']) ? $_POST['seculo_nascimento'] : '';
        $paisN_id = isset($_POST['paisN_id']) ? $_POST['paisN_id'] : NULL;
        $estadoN_id = isset($_POST['estadoN_id']) ? $_POST['estadoN_id'] : NULL;
        $cidadeN_id = isset($_POST['cidadeN_id']) ? $_POST['cidadeN_id'] : NULL;
        $paisM_id = isset($_POST['paisM_id']) ? $_POST['paisM_id'] : NULL;
        $estadoM_id = isset($_POST['estadoM_id']) ? $_POST['estadoM_id'] : NULL;
        $cidadeM_id = isset($_POST['cidadeM_id']) ? $_POST['cidadeM_id'] : NULL;
        $detalhes_nasc = isset($_POST['detalhes_nasc']) ? $_POST['detalhes_nasc'] : '';
        $detalhes_morte = isset($_POST['detalhes_morte']) ? $_POST['detalhes_morte'] : '';
        
        
   //     $regiao_nascimento = isset($_POST['regiao_nasc']) ? $_POST['regiao_nasc'] : '';
  //      $regiao_nascimento_alt = isset($_POST['regiao_nasc_alt']) ? $_POST['regiao_nasc_alt'] : '';
  //      if ($regiao_nascimento_alt){
  //      	$regiao_nascimento = $regiao_nascimento_alt;
  //      }
        
 //       $pais_nascimento = isset($_POST['pais_nasc']) ? $_POST['pais_nasc'] : '';
  //      $local_morte = isset($_POST['loc_morte']) ? $_POST['loc_morte'] : '';
        
  //      $regiao_morte = isset($_POST['regiao_morte']) ? $_POST['regiao_morte'] : '';
 //       $regiao_morte_alt = isset($_POST['regiao_morte_alt']) ? $_POST['regiao_morte_alt'] : '';
 //		if ($regiao_morte_alt){
  //      	$regiao_morte = $regiao_morte_alt;
 //       }
 //        
//        $pais_morte = isset($_POST['pais_morte']) ? $_POST['pais_morte'] : '';
        $catarinense = isset($_POST['catarinense']) ? true : false;
        $piauiense = isset($_POST['piauiense']) ? true : false;
        $ano_morte = isset($_POST['ano_morte']) ? $_POST['ano_morte'] : '';
        $seculo_morte = isset($_POST['seculo_morte']) ? $_POST['seculo_morte'] : '';
        $fontes = isset($_POST['fontes']) ? $_POST['fontes'] : array();
        $descricao = isset($_POST['descricao']) ? $_POST['descricao'] : '';
        $sexo = isset($_POST['sexo']) ? $_POST['sexo'] : '';
        
        $controller = ControleAutores::getInstance();
        if ($action == 'add') {
            exit($controller->add($nome_completo, $pseudonimo, $nome_usual, $ano_nascimento,
                                  $seculo_nascimento, $cidadeN_id, $estadoN_id, $paisN_id, $detalhes_nasc, $ano_morte, 
                                  $seculo_morte, $cidadeM_id, $estadoM_id, $paisM_id, $detalhes_morte, $fontes, $catarinense, $piauiense, $descricao, $sexo));
        }
        else {
            $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';            
            exit($controller->update($id, $nome_completo, $pseudonimo, $nome_usual, $ano_nascimento, 
                                     $seculo_nascimento, $cidadeN_id, $estadoN_id, $paisN_id, $detalhes_nasc, $ano_morte, 
                                     $seculo_morte, $cidadeM_id, $estadoM_id, $paisM_id, $detalhes_morte, $fontes, $catarinense, $piauiense, $descricao, $sexo));
        }
        break;
        
    case 'del':
        $ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : '';
        $controller = ControleAutores::getInstance();
        exit($controller->del($ids));
        break;
        
    case 'getTableData':
        $controller = ControleAutores::getInstance();
        $_GET['sTable'] = 'Autor';
        exit($controller->getTableData($_GET));
        break;
        
    case 'search_autor': //Called from forms at Documentos
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
        
    case 'getEstados':
        $paisid = isset($_GET['paisid']) ? $_GET['paisid'] : '';
        $controller = ControleLocalizacao::getInstance();
        exit(json_encode($controller->getEstadosPais($paisid)));
        break;
     case 'getCidades':
     	$estadoid = isset($_REQUEST['estadoid']) ? $_REQUEST['estadoid'] : '';
     	$paisid = isset($_GET['paisid']) ? $_GET['paisid'] : '';
     	if ($paisid!=1)
     		$estadoid=NULL;
     	$controller = ControleLocalizacao::getInstance();
     	exit(json_encode($controller->getCidades($paisid,$estadoid)));
     	break;
     	
     default:
        require_once(APPLICATION_PATH . '/include/TemplateHandler.php');
        $template = new TemplateHandler();
        $template->set_css_files(array(
                                'admin.css',
        						'jquery.dataTables.css'
        						));
        $template->set_js_files(array(
        						'jquery.dataTables.min.js',
                                'jquery.loadTable.js'
        						));
        $template->set_content_file('admin/autores/autores.php');
        $template->set_authenticated_only(true);
        $template->set_authentication_list(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID));
        $template->set_header_file('header_admin.php');
        $template->set_body_file('body_admin.php');
        $template->set_navigation_file('navigation_admin.php');
        $template->set_active_nav_item(ADMIN_AUTORES_ID);
        $template->show();
}