<?php

use \Hcode\Pages\Page;
use \Hcode\Model\User;

//Rota GET - Página de Login
$app->get("/login", function(){
	
	USER::verifyLogout();
	$page = new Page();

	$page->setTpl("login",[
		'error'=>User::getError( $_SESSION[User::ERROR]),
		'error_register'=>User::getError($_SESSION[User::ERROR_REGISTER]),
		'register_values'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : 
		['name'=>'', 'email'=>'', 'phone'=>'']
	]);
});

//Rota Post Login do usuário
$app->post("/login", function()
{
	try
	{
		$user = User::login($_POST['login'],$_POST['password']);
		header("Location: /cart");
		exit;
	}
	catch(Exception $ex)
	{
		User::setError($_SESSION[User::ERROR] , $ex->getMessage());
		header("Location: /login");
		exit;
	}

});

//rota GET - Realizar logout usuário
$app->get('/logout', function()
{
	User::logout();
	header("Location: /login");
	exit;
});

//rota POST - Criação de um usuário da loja
$app->post('/register', function(){

	//Sessão para guardar os valores que o usuário ja digitou
	$_SESSION['registerValues']= $_POST;

	if(!isset($_POST['name']) || $_POST['name']=='')
	{
		User::setError($_SESSION[User::ERROR_REGISTER], "Preencha o campo nome");	
		header("Location: /login");
		exit;
	}

	if(!isset($_POST['email']) || $_POST['email']=='')
	{
		User::setError($_SESSION[User::ERROR_REGISTER], "Preencha o campo email");	
		header("Location: /login");
		exit;
		
	}

	if(!isset($_POST['password']) || $_POST['password']=='')
	{
		User::setError($_SESSION[User::ERROR_REGISTER], "Preencha o campo senha");	
		header("Location: /login");
		exit;
	}

	//Criação do objeto usuário
	$user = new User();

	$user->setData([
		'inadmin'=>0,
		'deslogin'=>$_POST['email'],
		'desperson'=>$_POST['name'],
		'desemail'=>$_POST['email'],
		'despassword'=>$_POST['password'],
		'nrphne'=>$_POST['phone']

	]);

	//Verificação de usuário, se ele ja existe no sistema
	if(!User::checkLoginExist($_POST['email']))
	{
		$user->save();	
		User::login($_POST['email'], $_POST['password']);

		header('Location: /cart');
		exit;
	}
	else
	{
		User::setError($_SESSION[User::ERROR_REGISTER],"Esse endereço de email ja pertence a outro usuário");
		
		header('Location: /login');
		exit;
	}
});
