<?php

use Hcode\Pages\Page;
use Hcode\Model\Category;

//Rota GET - Página de exibição de todos os produtos de uma categoria
$app->get('/categories/:idcategory', function($idcategory){

	$currentPage = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	$category = new Category();
	$category->get((int)$idcategory);

	$pagination = $category->getProductsPagination($currentPage);


	$page = new Page();
	$page->setTpl("category",[
		"category"=>$category->getValues(),
		"products"=>$pagination['pageData'],
		"pages"=>$pagination['pages'],
		"next"=>$pagination['next'],
		"previous"=>$pagination['previous']
	]);
});
