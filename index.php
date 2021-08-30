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

$app->run();

 ?>