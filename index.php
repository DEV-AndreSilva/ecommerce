<?php 
if(!isset($_SESSION))
{
	session_start();
}

//Inclusão dos arquivos de autoload do composer
require_once("vendor/autoload.php");

//Uso das classes na pagina
use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);

//rota GET - Pagina inicial ou index
$app->get('/', function() 
{
	$page = new Page();
	$page->setTpl("index");

});

//rota GET - Painel de administração
$app->get('/admin',function()
{
	User::verifyLogin();

	$page=new PageAdmin();
	$page->setTpl("index");
});

//rota GET - Página de login
$app->get('/admin/login',function()
{
	$page= new PageAdmin([
		'header'=>false,
		'footer'=>false
	]);

	$page->setTpl("login");
});

//rota GET - Realizar logout
$app->get('/admin/logout', function()
{
	User::logout();
	header('Location: /admin/login');
	exit;
});

//rota POST - Realizar o Login
$app->post('/admin/login', function()
{
	User::login($_POST['login'], $_POST['password']);
	header('Location: /admin');
	exit;
});

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

//Executa a rota
$app->run();

 ?>