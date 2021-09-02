<?php

use Hcode\Model\User;
use Hcode\PageAdmin;

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
