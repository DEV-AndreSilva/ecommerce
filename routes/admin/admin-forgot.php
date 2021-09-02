<?php

use Hcode\Model\User;
use Hcode\PageAdmin;

//Rota GET - Pagina de esqueci a senha
$app->get('/admin/forgot', function(){

	$page= new PageAdmin([
		'header'=>false,
		'footer'=>false
	]);

	$page->setTpl("forgot");
});

//Rota POST - Envio do email do usuário que vai recuperar a senha
$app->post("/admin/forgot", function()
{

	User::getForgot($_POST['email']);
	header("location: /admin/forgot/sent");
	exit;

});

//Rota GET - Página de notificação de email enviado
$app->get("/admin/forgot/sent",function(){
	$page= new PageAdmin([
		'header'=>false,
		'footer'=>false
	]);

	$page->setTpl("forgot-sent");
});

//Rota GET - Página de alteração de senha acessado pelo link do email
$app->get('/admin/forgot/reset', function(){

	$user = User::validForgotDecrypt($_GET['code']);
	$page= new PageAdmin([
		'header'=>false,
		'footer'=>false
	]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user['desperson'],
		"code"=>$_GET['code']
	));

});

//Rota POST - Efetivando a troca de senha e exibindo ao usuario o estado da operação
$app->post("/admin/forgot/reset", function(){

	$forgot= User::validForgotDecrypt($_POST['code']);

	User::setForgotUsed($forgot['idrecovery']);

	$user = new User();

	//recebendo o id do usuário
	$user->get((int)$forgot['iduser']);

	//criptografando nova senha
	$password = password_hash($_POST['password'],PASSWORD_DEFAULT,array(
		"cost"=>12
	));

	//Trocando a senha do usuário
	$user->setPassword($password);

	$page= new PageAdmin([
		'header'=>false,
		'footer'=>false
	]);

	$page->setTpl("forgot-reset-success");

});
