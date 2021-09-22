<?php

use \Hcode\Model\User;
use \Hcode\Model\Cart;
use Hcode\Model\Order;
use \Hcode\Pages\Page;






//Rota GET - Perfil do usuário
$app->get("/profile", function(){

	User::verifyLogin(false);

	$user = User::getFromSession();

	$page = new Page();

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

//Rota GET - Pagina de pedidos de usuário
$app->get('/profile/orders', function(){
	User::verifyLogin(false);

	$user=User::getFromSession();

	$page = new Page();

	$page->setTpl("profile-orders",[
		"orders"=>$user->getOrders()

	]);
});

//Rota GET - Detalhes do Pedido
$app->get("/profile/orders/:idorder", function($idorder){

	User::verifyLogin(false);

	$order = new Order();
	$order->get((int)$idorder);

	$cart = new Cart();
	$cart->get((int)$order->getidcart());

	$cart->getCalculateTotal();
	
	$page = new page();


	$page->setTpl('profile-orders-detail',[
		"order"=>$order->getValues(),
		"products"=>$cart->getProducts(),
		"cart"=> $cart->getValues()
	]);

});

$app->get("/profile/change-password", function(){

	User::verifyLogin(false);

	$page = new Page();

	$page->setTpl("profile-change-password", [
		"changePassError"=>User::getError($_SESSION[User::ERROR]),
		"changePassSuccess"=>User::getError($_SESSION[User::SUCCESS])
	]);

});

$app->post("/profile/change-password", function(){

	User::verifyLogin(false);

	if(!isset($_POST['current_pass']) || $_POST['current_pass']==='')
	{
		User::setError($_SESSION[User::ERROR], "Preencha o campo senha atual ");
		header("Location: /profile/change-password");
		exit;
	}

	if(!isset($_POST['new_pass']) || $_POST['new_pass']==='')
	{
		User::setError($_SESSION[User::ERROR], "Preencha o campo nova senha!");
		header("Location: /profile/change-password");
		exit;

	}

	if(!isset($_POST['new_pass_confirm']) || $_POST['new_pass_confirm']==='')
	{
		User::setError($_SESSION[User::ERROR], "Preencha o campo confirmar nova senha!");
		header("Location: /profile/change-password");
		exit;
	}

	if($_POST['current_pass']===$_POST['new_pass'])
	{
		User::setError($_SESSION[User::ERROR], "Sua nova senha deve ser diferente da atual");
		header("Location: /profile/change-password");
		exit;
	}

	if($_POST['new_pass']!==$_POST['new_pass_confirm'])
	{
		User::setError($_SESSION[User::ERROR], "Os campos senha e nova senha possuem valores diferentes");
		header("Location: /profile/change-password");
		exit;
	}

	$user= User::getFromSession();

	if(password_verify($_POST['current_pass'], $user->getdespassword()))
	{
		$user->setdespassword($_POST['new_pass']);

		$user->setPassword($user->getdespassword());
	
		User::setError($_SESSION[User::SUCCESS], "Senha atualizado com Sucesso!");
		header("Location: /profile/change-password");
		exit;
	}
	else
	{
		User::setError($_SESSION[User::ERROR], "Senha atual invalida!");
		header("Location: /profile/change-password");
		exit;

	}



	

});
