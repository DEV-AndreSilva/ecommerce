<?php

use Hcode\PageAdmin;
use Hcode\Model\User;
use Hcode\Model\Category;

//Rota GET - Página de exibição das categorias
$app->get('/admin/categories',function(){

	User::verifyLogin();

	$categories= Category::listAll();

	$page= new PageAdmin();

	$page->setTpl("categories", array(
		"categories"=>$categories
	));

});


//Rota GET - Página de criação de uma categoria
$app->get('/admin/categories/create',function(){

	User::verifyLogin();

	$page= new PageAdmin();

	$page->setTpl("categories-create");

});


//Rota POST - Criando uma nova categoria
$app->post('/admin/categories/create',function(){

	User::verifyLogin();

	$category = new Category();

	$category->setData($_POST);

	$category->save();
	
	header("Location: /admin/categories");
	exit;
});

//Rota GET - Exclusão de uma categoria
$app->get('/admin/categories/:idcategory/delete',function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);
	
	$category->delete();

	header('Location: /admin/categories');
	exit;

});

//Rota GET - Página de alteração de uma categoria
$app->get('/admin/categories/:idcategory',function($idcategory){

	User::verifyLogin();

	$category = new category();

	$category->get((int)$idcategory);

	$page= new PageAdmin();

	$page->setTpl("categories-update",array(
		'category'=>$category->getValues())
	);

});

//Rota POST - Atualização de uma categoria
$app->post('/admin/categories/:idcategory',function($idcategory){

	User::verifyLogin();

	$category = new category();

	$category->get((int)$idcategory);

	$category->setData($_POST);

	$category->save();
	header('Location: /admin/categories');
	exit;

});
