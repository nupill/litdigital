<?php
require_once(dirname(__FILE__) . '/../../../../application/config/general.php');
require_once(APPLICATION_PATH . '/controllers/ControleComprovantesEdicoes.php');

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {

    case 'add':
    case 'update':
        //Atributos do Documento:
        $titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
		$generos = isset($_POST['generos']) ? $_POST['generos'] : array();
        $idiomas = isset($_POST['idiomas']) ? $_POST['idiomas'] : array();        
        $categoria = isset($_POST['categoria']) ? $_POST['categoria'] : '';
        $autores = isset($_POST['autores']) ? $_POST['autores'] : array();
        $fontes = isset($_POST['fontes']) ? $_POST['fontes'] : array();
        $id_material = isset($_POST['id_material']) ? $_POST['id_material'] : '';
        $editoras = isset($_POST['editoras']) ? $_POST['editoras'] : array();
        $abrangencia = isset($_POST['abrangencia']) ? $_POST['abrangencia'] : '';
        $direitos = isset($_POST['direitos']) ? $_POST['direitos'] : '';
        $acervo_id = isset($_POST['acervo_id']) ? $_POST['acervo_id'] : '';
        $localizacao = isset($_POST['localizacao']) ? $_POST['localizacao'] : '';
        $estado = isset($_POST['estado']) ? $_POST['estado'] : '';
        $descricao = isset($_POST['descricao']) ? $_POST['descricao'] : '';
        $ano_producao = isset($_POST['ano_producao']) ? $_POST['ano_producao'] : '';
        $dimensao = isset($_POST['dimensao']) ? $_POST['dimensao'] : '';

        //Atributos do Comprovante de Crítica:
        $volume = isset($_POST['volume']) ? $_POST['volume'] : '';
        $num_paginas = isset($_POST['num_paginas']) ? $_POST['num_paginas'] : '';
        $local_publicacao = isset($_POST['local_publicacao']) ? $_POST['local_publicacao'] : '';
        $edicao = isset($_POST['edicao']) ? $_POST['edicao'] : '';
        $organizador = isset($_POST['organizador']) ? $_POST['organizador'] : '';
        $capitulo = isset($_POST['capitulo']) ? $_POST['capitulo'] : '';
        $pag_inicial = isset($_POST['pag_inicial']) ? $_POST['pag_inicial'] : '';
        $pag_final = isset($_POST['pag_final']) ? $_POST['pag_final'] : '';
        $tradutor = isset($_POST['tradutor']) ? $_POST['tradutor'] : '';

        //Atributos da Mídia
        $arquivos = isset($_POST['arquivos']) ? $_POST['arquivos'] : array();
        $arquivos_substituidos = isset($_POST['arquivos_substituidos']) ? $_POST['arquivos_substituidos'] : array();
        $titulos_arquivos = isset($_POST['titulos_arquivos']) ? $_POST['titulos_arquivos'] : array();
        $descricoes_arquivos = isset($_POST['descricoes_arquivos']) ? $_POST['descricoes_arquivos'] : array();
        $fontes_arquivos = isset($_POST['fontes_arquivos']) ? $_POST['fontes_arquivos'] : array();

        $controller = ControleComprovantesEdicoes::getInstance();
        if ($action == 'add') {
            exit($controller->add(//Atributos do Documento:
                            $titulo, $autores, $generos, $categoria, $fontes, $id_material,
                            $editoras, $abrangencia, $direitos, $acervo_id,
                            $localizacao, $estado, $descricao, $ano_producao, $dimensao, $idiomas,
                            //Atributos do Comprovante de Crítica:
                            $volume, $num_paginas, $local_publicacao, $edicao,
                            $organizador, $capitulo, $pag_inicial, $pag_final,
                            $tradutor,
                            //Atributos das Mídias:
                            $arquivos, $titulos_arquivos, $descricoes_arquivos,
                            $fontes_arquivos));
        } else {
            $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
            exit($controller->update(//Atributos do Documento:
                            $id, $titulo, $autores, $generos, $categoria, $fontes, $id_material,
                            $editoras, $abrangencia, $direitos, $acervo_id,
                            $localizacao, $estado, $descricao, $ano_producao, $dimensao, $idiomas,
                            //Atributos do Comprovante de Crítica:
                            $volume, $num_paginas, $local_publicacao, $edicao,
                            $organizador, $capitulo, $pag_inicial, $pag_final,
                            $tradutor,
                            //Atributos das Mídias:
                            $arquivos, $arquivos_substituidos, $titulos_arquivos, $descricoes_arquivos,
                            $fontes_arquivos));
        }
        break;

    case 'del':
        $ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : '';
        $controller = ControleComprovantesEdicoes::getInstance();
        exit($controller->del($ids));
        break;

     case 'upload':
        $xhr = false;
        $file = null;
        if (isset($_GET['qqfile'])){
            $xhr = true;
        } elseif (isset($_FILES['qqfile'])){
            $file = $_FILES['qqfile'];
        }

        $controller = ControleComprovantesEdicoes::getInstance();
        exit($controller->upload($xhr, $file));
        break;

    case 'getTableData':
        $controller = ControleComprovantesEdicoes::getInstance();
        $_GET['sTable'] = 'ComprovanteEdicao'; //Nome da tabela
        $_GET['iTypeId'] = DOCUMENTOS_COMPROVANTES_EDICOES_ID; //ID do tipo do documento
        exit($controller->getTableData($_GET));
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
        $template->set_content_file('admin/documentos/comprovantes-edicoes/comprovantes-edicoes.php');
        $template->set_authenticated_only(true);
        $template->set_header_file('header_admin.php');
        $template->set_body_file('body_admin.php');
        $template->set_navigation_file('navigation_admin.php');
        $template->set_active_nav_item(ADMIN_DOCUMENTOS_ID);
        $template->show();
}