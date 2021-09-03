<?php

use Hcode\Pages\Page;
use Hcode\Model\Category;
use Hcode\Model\Product;


$app->get('/categories/:idcategory', function($idcategory){

	//verificar login

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();
	$page->setTpl("category",[
		"category"=>$category->getValues(),
		"products"=>Product::checkList($category->getProducts())
	]);
});
