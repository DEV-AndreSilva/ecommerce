<?php


use \Hcode\Model\Cart;
use \Hcode\Pages\Page;
use \Hcode\Model\User;

//Rota GET - Pagina de esqueci a senha
$app->get('/forgot', function(){

	$page= new Page([]);

	$page->setTpl("forgot");
});

//Rota POST - Envio do email do usuário que vai recuperar a senha
$app->post("/forgot", function()
{

	User::getForgot($_POST['email'], false);
	header("location: /forgot/sent");
	exit;

});

//Rota GET - Página de notificação de email enviado
$app->get("/forgot/sent",function(){
	$page= new Page([]);

	$page->setTpl("forgot-sent");
});

//Rota GET - Página de alteração de senha acessado pelo link do email
$app->get('/forgot/reset', function(){

	$user = User::validForgotDecrypt($_GET['code']);
	$page= new Page([]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user['desperson'],
		"code"=>$_GET['code']
	));

});

//Rota POST - Efetivando a troca de senha e exibindo ao usuario o estado da operação
$app->post("/forgot/reset", function(){

	$forgot= User::validForgotDecrypt($_POST['code']);

	User::setForgotUsed($forgot['idrecovery']);

	$user = new User();

	//recebendo o id do usuário
	$user->get((int)$forgot['iduser']);

	//recebendo nova senha
	$password = $_POST['password'];

	//Trocando a senha do usuário
	$user->setPassword($password);

	$page= new Page([]);

	$page->setTpl("forgot-reset-success");

});
