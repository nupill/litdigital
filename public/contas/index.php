<?php
require_once(dirname(__FILE__) . '/../../application/config/general.php');
require_once(APPLICATION_PATH . "/include/Auth.php");
require_once(APPLICATION_PATH . "/controllers/ControleUsuarios.php");

$action = isset($_GET['action']) ? $_GET['action'] : '';
$controller = ControleUsuarios::getInstance();

switch ($action) {
    
    case 'login':
        $usuario = isset($_POST['usuario']) ? $_POST['usuario'] : '';
        $senha = isset($_POST['senha']) ? $_POST['senha'] : '';
        exit(Auth::login($usuario, $senha));
        break;
        
    case 'logout':
        Auth::logout();
        header('Location: ' . HOME_URI);
        break;

    case 'add':
    case 'update':

        $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $url = isset($_POST['url']) ? $_POST['url'] : '';
        $login = isset($_POST['login_cadastro']) ? $_POST['login_cadastro'] : '';
        $senha = isset($_POST['senha_cadastro']) ? $_POST['senha_cadastro'] : '';
        $repete_senha = isset($_POST['repete_senha']) ? $_POST['repete_senha'] : '';
        $profissao = isset($_POST['profissao']) ? $_POST['profissao'] : '';
        $personalizacao = isset($_POST['personalizacao']) ? true : false;
        $anotacao = isset($_POST['anotacao']) ? true : false;

        if ($action == 'add') {
            exit($controller->add_leitor($nome, $email, $login, $senha,
                                         $repete_senha, $profissao, $url));
        }
        else {
            exit($controller->update_leitor($nome, $email, $senha, $repete_senha,
                                            $profissao, $url, $anotacao, $personalizacao));
        }
        break;

    case 'remove_genero':
        $genero = isset($_GET['genero']) ? $_GET['genero'] : '';
        $id =  isset($_GET['id']) ? $_GET['id'] : '';
        $controller->remove_genero_preferido($id, $genero);
        header("Location: editar/");
        exit();
    break;

    case 'remove_autor':
        $autor = isset($_GET['autor']) ? $_GET['autor'] : '';
        $id =  isset($_GET['id']) ? $_GET['id'] : '';
        $controller->remove_autor_preferido($id, $autor);
        header("Location: editar/");
        exit();
    break;
	
	case 'remove_obra_visualizada':
        $obra = isset($_GET['obra']) ? $_GET['obra'] : '';
        $id =  isset($_GET['id']) ? $_GET['id'] : '';
        $controller->remove_obra_visualizada($id, $obra);
        header("Location: editar/");
        exit();
    break;
    
    case 'forgot_1':
    	$login = isset($_POST['login']) ? $_POST['login'] : '';
    	exit($controller->password_redefinition_step_1($login));
    	break;
    	
    case 'forgot_2':
    	$code = isset($_POST['code']) ? $_POST['code'] : '';
    	$password = isset($_POST['password']) ? $_POST['password'] : '';
    	$password_check = isset($_POST['password_check']) ? $_POST['password_check'] : '';
    	exit($controller->password_redefinition_step_2($code, $password, $password_check));
    	break;
}