<?php

use \Hcode\Model\User;
use \Hcode\Model\Cart;
use \Hcode\Pages\Page;




//Rota GET - Perfil do usuário
$app->get("/profile", function(){

	User::verifyLogin(false);

	$user = User::getFromSession();

	$cart= Cart::getFromSession();

	$totalCart=$cart->getCalculateTotal();

	$page = new Page(['data'=>["vlprice"=>$totalCart['vlprice'], "nrqtd"=>$totalCart['nrqtd']]]);

	$page->setTpl('profile', [
		'user'=>$user->getValues(),
		'profileMsg'=>User::getError($_SESSION[User::SUCCESS]),
		'profileError'=>User::getError($_SESSION[User::ERROR])
	]);

});


//Rota POST - Atualização do perfil do usuário
$app->post("/profile", function(){

	User::verifyLogin(false);

	$user = User::getFromSession();

	//Verifica se o usuário não deixou o nome em branco
	if(!isset($_POST['desperson']) || $_POST['desperson'] ==='')
	{
		User::setError($_SESSION[User::ERROR], 'Preencha seu nome');
		header("Location: /profile");
		exit;
	}

	//Verifica se o usuário não deixou o email em branco
	if(!isset($_POST['desemail']) || $_POST['desemail'] ==='')
	{
		User::setError($_SESSION[User::ERROR], 'Preencha seu email');
		header("Location: /profile");
		exit;
	}

	//Verifica se o usuário for trocar de email se o outro email ja possui usuário
	if($_POST['desemail'] !== $user->getdesemail())
	{
		if(User::checkLoginExist($_POST['desemail']))
		{
			User::setError($_SESSION[User::ERROR],'Endereço de email ja está sendo utilizado por outro usuário');
			header("Location: /profile");
			exit;
		}
	}

	//Se o usuário descobrir esses parametros ele não consegue alterar
	$_POST['inadmin']=$user->getinadmin();
	$_POST['despassword']=$user->getdespassword();
	$_POST['deslogin']=$_POST['desemail'];

	$user->setData($_POST);

	//Atualiza as informações do usuário
	$user->update();
	
	//Atualização da mensagem com parametro de sucesso
	User::setError($_SESSION[User::SUCCESS],"Perfil atualizado com sucesso !");

	header('Location: /profile');
	exit;

});