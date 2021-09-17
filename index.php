<?php 
if(!isset($_SESSION))
{
	session_start();
}

//Inclusão dos arquivos de autoload do composer
require_once("vendor/autoload.php");

//Uso da classe slim para configuração das rotas
use \Slim\Slim;

$app = new Slim();

$app->config('debug', true);

require_once('functions.php');

//Inclusão dos arquivos de rotas do site
require_once('routes'.DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'site.php');
require_once('routes'.DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'site-login.php');
require_once('routes'.DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'site-forgot.php');
require_once('routes'.DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'site-userProfile.php');
require_once('routes'.DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'site-cart.php');
require_once('routes'.DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'site-finalizarCompra.php');
require_once('routes'.DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'site-categories.php');
require_once('routes'.DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'site-products.php');

//Inclusão dos arquivos de rotas do administrador
require_once('routes'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'admin-login.php');
require_once('routes'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'admin-users.php');
require_once('routes'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'admin-forgot.php');
require_once('routes'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'admin-categories.php');
require_once('routes'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'admin-products.php');

//Inclusão dos arquivos de rotas do Site



//Executa a rota
$app->run();

 ?>