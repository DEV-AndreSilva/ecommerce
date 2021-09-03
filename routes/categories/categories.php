<?php

use Hcode\Pages\Page;
use Hcode\Model\Category;


$app->get('/categories/:idcategory', function($idcategory){

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();
	$page->setTpl("category",[
		"category"=>$category->getValues(),
		"products"=>[]
	]);
});