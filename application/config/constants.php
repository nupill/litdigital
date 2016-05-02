<?php 
require_once(dirname(__FILE__) . '/../include/FirePHPCore/fb.php');
// agora testando commit push outro
//Template:
define('DEFAULT_TITLE', 'Literatura Digital');
define('DEFAULT_GLOBAL_FILE', 'global.php');
define('DEFAULT_HEADER_FILE', 'header.php');
define('DEFAULT_BODY_FILE', 'body.php');
define('DEFAULT_FOOTER_FILE', 'footer.php');
define('DEFAULT_NAVIGATION_FILE', 'navigation.php');
define('DEFAULT_FORBIDDEN_FILE', 'forbidden.php');
define('DEFAULT_ERROR_FILE', 'error.php');

//Paths:
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../'));
define('PUBLIC_PATH', realpath(dirname(__FILE__) . '/../../public/'));
define('DOCUMENTS_PATH', PUBLIC_PATH . '/_documents/');
define('CRITICAS_PATH', PUBLIC_PATH . '/_criticas/');
define('MIME_PATH', 'C://wamp/bin/apache/apache2.4.9/conf');     // quando no Windows/wamp
// define('MIME_PATH', '/etc');                                  // quando no Linux
define('ZEND_PATH', realpath(dirname(__FILE__) . '/../zf'));


//Global URIs:
// $ROOT_URI = explode('/public', $_SERVER['REQUEST_URI']);
// $ROOT_URI = $ROOT_URI[0] . '/public/';
define('ROOT_URI', 'http://' . htmlentities($_SERVER['SERVER_NAME'] . '/pronex/public/'));
//define('ROOT_URI', 'http://' . htmlentities($_SERVER['SERVER_NAME']) . '/~biblio/literaturadigital/mirror/public/');

//define('STATIC_URI', 'http://static.' . htmlentities($_SERVER['SERVER_NAME']) . '/literatura_digital/');
define('CSS_URI', ROOT_URI . '_css/');
//define('CSS_URI', STATIC_URI . '_css/');
define('JS_URI', ROOT_URI . '_js/');
//define('JS_URI', STATIC_URI . '_js/');
define('IMAGES_URI', ROOT_URI . '_images/');
//define('IMAGES_URI', STATIC_URI . '_images/');
define('DOCUMENTS_URI', ROOT_URI . '_documents/');
define('DOCUMENTS_FORMAT_URI', 'http://www.dlnotes2.ufsc.br/document/convert/');
define('CRITICAS_URI', ROOT_URI . '_criticas/');

//Sections URIs
define('HOME_URI', ROOT_URI);
define('BUSCA_URI', ROOT_URI . 'busca/');
define('SOBRE_URI', ROOT_URI . 'sobre/');
define('NAVEGACAO_URI', ROOT_URI . 'navegacao/');
define('NAVEGACAO_AUTOR_URI', NAVEGACAO_URI . 'autor/');
define('NAVEGACAO_EDITORAS_URI', NAVEGACAO_URI . 'editoras/');
define('NAVEGACAO_DOCUMENTO_URI', NAVEGACAO_URI . 'documento/');
define('ADAPTABILIDADE_URI', ROOT_URI . 'adaptabilidade/');
define('CONTAS_URI', ROOT_URI . 'contas/');
define('AUTORES_URI', ROOT_URI . 'autores/');
define('DOCUMENTOS_URI', ROOT_URI . 'documentos/');
define('CRITICAS_AUTOR_URI', ROOT_URI . 'criticas/autor/');
define('CRITICAS_OBRA_URI', ROOT_URI . 'criticas/obra/');
define('RECOMENDACOES_URI', ROOT_URI . 'recomendacao/');
define('TUTORIAL_URI', ROOT_URI . 'tutorial/tutorial.pdf');
define('EDITORAS_URI', ROOT_URI . 'editoras/');


//Restricted Area URIs:
define('ADMIN_URI', ROOT_URI . 'admin/');
define('ADMIN_ESTATISTICAS_URI', ADMIN_URI . 'estatisticas/');
define('ADMIN_AUTORES_URI', ADMIN_URI . 'autores/');
define('ADMIN_DOCUMENTOS_URI', ADMIN_URI . 'documentos/');
define('ADMIN_CRITICAS_URI', ADMIN_URI . 'criticas/');
define('ADMIN_CUSTOM_URI', ADMIN_URI . 'customizacao/');
define('ADMIN_FATOS_HISTORICOS_URI', ADMIN_URI . 'fatos_historicos/');
define('ADMIN_EDITORAS_URI', ADMIN_URI . 'editoras/');
define('ADMIN_FONTES_URI', ADMIN_URI . 'fontes/');
define('ADMIN_COMENTARIOS_URI', ADMIN_URI . 'comentarios/');
define('ADMIN_USUARIOS_URI', ADMIN_URI . 'usuarios/');
define('ADMIN_ACERVOS_URI', ADMIN_URI . 'acervos/');
define('ADMIN_GENEROS_URI', ADMIN_URI . 'generos/');
define('ADMIN_IDIOMAS_URI', ADMIN_URI . 'idiomas/');
define('ADMIN_PAISES_URI', ADMIN_URI . 'paises/');
define('ADMIN_CIDADES_URI', ADMIN_URI . 'cidades/');
define('ADMIN_ESTADOS_URI', ADMIN_URI . 'estados/');
define('ADMIN_MANUTENCAO_URI', ADMIN_URI . 'manutencao/');

define('ADMIN_DOCUMENTOS_AUDIOVISUAIS_URI', ADMIN_DOCUMENTOS_URI . 'audiovisuais/');
define('ADMIN_DOCUMENTOS_BIBLIOTECA_URI', ADMIN_DOCUMENTOS_URI . 'biblioteca/');
define('ADMIN_DOCUMENTOS_COMPROVANTES_ADAPTACOES_URI', ADMIN_DOCUMENTOS_URI . 'comprovantes-adaptacoes/');
define('ADMIN_DOCUMENTOS_COMPROVANTES_CRITICA_URI', ADMIN_DOCUMENTOS_URI . 'comprovantes-critica/');
define('ADMIN_DOCUMENTOS_COMPROVANTES_EDICOES_URI', ADMIN_DOCUMENTOS_URI . 'comprovantes-edicoes/');
define('ADMIN_DOCUMENTOS_CORRESPONDENCIAS_URI', ADMIN_DOCUMENTOS_URI . 'correspondencia/');
define('ADMIN_DOCUMENTOS_ESBOCOS_NOTAS_URI', ADMIN_DOCUMENTOS_URI . 'esbocos-notas/');
define('ADMIN_DOCUMENTOS_HISTORIA_EDITORIAL_URI', ADMIN_DOCUMENTOS_URI . 'historia-editorial/');
define('ADMIN_DOCUMENTOS_ILUSTRACOES_URI', ADMIN_DOCUMENTOS_URI . 'ilustracoes/');
define('ADMIN_DOCUMENTOS_MEMORABILIA_URI', ADMIN_DOCUMENTOS_URI . 'memorabilia/');
define('ADMIN_DOCUMENTOS_OBJETOS_ARTE_URI', ADMIN_DOCUMENTOS_URI . 'objetos-arte/');
define('ADMIN_DOCUMENTOS_OBRA_URI', ADMIN_DOCUMENTOS_URI . 'obra/');
define('ADMIN_DOCUMENTOS_OBRA_LITERARIA_URI', ADMIN_DOCUMENTOS_URI . 'obra-literaria/');
define('ADMIN_DOCUMENTOS_ORIGINAIS_URI', ADMIN_DOCUMENTOS_URI . 'originais/');
define('ADMIN_DOCUMENTOS_PUBLICACOES_IMPRENSA_URI', ADMIN_DOCUMENTOS_URI . 'publicacoes-imprensa/');
define('ADMIN_DOCUMENTOS_VIDA_URI', ADMIN_DOCUMENTOS_URI . 'vida/');

define('ADMIN_CRITICAS_OBRA_URI', ADMIN_CRITICAS_URI . 'obra/');
define('ADMIN_CRITICAS_AUTOR_URI', ADMIN_CRITICAS_URI . 'autor/');

define('ADMIN_COMENTARIOS_AUTOR_URI', ADMIN_COMENTARIOS_URI . 'autor/');
define('ADMIN_COMENTARIOS_DOCUMENTO_URI', ADMIN_COMENTARIOS_URI . 'documento/');
define('ADMIN_COMENTARIOS_DENUNCIAS_URI', ADMIN_COMENTARIOS_URI . 'denuncias/');

//IDs:
define('HOME_ID', 1);
define('BUSCA_ID', 2);
define('SOBRE_ID', 3);
define('NAVEGACAO_ID', 4);
define('ADAPTABILIDADE_ID', 5);
define('RECOMENDACAO_ID', 6);

//IDs Restricted Area (can be used to give permissions to users)
define('ADMIN_ESTATISTICAS_ID', 1);
define('ADMIN_AUTORES_ID', 2);
define('ADMIN_DOCUMENTOS_ID', 4);
define('ADMIN_CRITICAS_ID', 8);
define('ADMIN_FATOS_HISTORICOS_ID', 16);
define('ADMIN_FONTES_ID', 32);
define('ADMIN_USUARIOS_ID', 64);
define('ADMIN_COMENTARIOS_ID', 128);
define('ADMIN_EDITORAS_ID', 256);
define('ADMIN_ACERVOS_ID', 512);
define('ADMIN_CIDADES_ID', 1024);
define('ADMIN_ESTADOS_ID', 2048);

//IDs of document types (must match data from TipoDocumento table)
define('DOCUMENTOS_AUDIOVISUAIS_ID', 1);
define('DOCUMENTOS_BIBLIOTECA_ID', 2);
define('DOCUMENTOS_COMPROVANTES_ADAPTACOES_ID', 3);
define('DOCUMENTOS_COMPROVANTES_EDICOES_ID', 4);
define('DOCUMENTOS_CORRESPONDENCIAS_ID', 5);
define('DOCUMENTOS_COMPROVANTES_CRITICA_ID', 6);
define('DOCUMENTOS_HISTORIA_EDITORIAL_ID', 7);
define('DOCUMENTOS_ILUSTRACOES_ID', 8);
define('DOCUMENTOS_MEMORABILIA_ID', 9);
define('DOCUMENTOS_ESBOCOS_NOTAS_ID', 10);
define('DOCUMENTOS_OBJETOS_ARTE_ID', 11);
define('DOCUMENTOS_OBRA_LITERARIA_ID', 12);
define('DOCUMENTOS_ORIGINAIS_ID', 13);
define('DOCUMENTOS_PUBLICACOES_IMPRENSA_ID', 14);
define('DOCUMENTOS_VIDA_ID', 15);
define('DOCUMENTOS_OBRA_ID', 16);

//IDs of user types (must match data from Papel table)
define('PAPEL_ADMINISTRADOR_ID', 1);
define('PAPEL_CADASTRADOR_ID', 2);
define('PAPEL_LEITOR_ID', 3);

//Log:
define('LOG_VERBOSITY_NONE', 1);
define('LOG_VERBOSITY_NORMAL', 2);
define('LOG_VERBOSITY_DEBUG', 3);
define('LOG_VERBOSITY_DEFAULT', LOG_VERBOSITY_DEBUG);

//idioma
//define('LANG','pt_BR');
define('LANG','fr');

//Config:

define('MAX_FILE_SIZE', 500);
