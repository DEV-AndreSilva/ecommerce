<?php

use Hcode\PageAdmin;
use Hcode\Model\User;

//rota GET - Pagina de exibição de todos os usuários
$app->get('/admin/users', function()
{
	User::verifyLogin();

	$users = User::listAll();

	$page = new PageAdmin();
	$page->setTpl("users",array(
		"users"=>$users
	));

});

//Rota GET - Pagina de criação de um usuário
$app->get('/admin/users/create', function()
{
	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl("users-create");

});

//Rota POST - Criação de um novo usuário
$app->post('/admin/users/create',function()
{
	User::verifyLogin();

	$user= new User();

	$_POST['inadmin']=(isset($_POST['inadmin']))?1 :0;

	$_POST['despassword']=password_hash($_POST['despassword'], PASSWORD_DEFAULT, ["cost" =>12]);
	$user->setData($_POST);
	$user->save();

	header("Location: /admin/users");
	exit;
});

//Rota GET - Excluir um usuário
$app->get('/admin/users/:iduser/delete', function($iduser)
{
	User::verifyLogin();

	$user = new user();
	$user->get((int)$iduser);
	$user->delete();

	header("location: /admin/users");
	exit;

});

//Rota POST - Alteração de um usuário
$app->post('/admin/users/:iduser',function($iduser)
{
	User::verifyLogin();

	$_POST['inadmin']=(isset($_POST['inadmin']))?1 :0;

	$user = new User();
	$user->get((int)$iduser);
	$user->setData($_POST);
	$user->update();

	header("Location: /admin/users");
	exit;
});



//Rota GET - Pagina de alteração do usuário
$app->get('/admin/users/:iduser', function($iduser)
{
	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);


	$page = new PageAdmin();
	$page->setTpl("users-update",array(
		"user"=>$user->getValues()
	));

});
