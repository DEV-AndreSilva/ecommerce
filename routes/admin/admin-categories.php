<?php

use \Hcode\Pages\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;

//Rota GET - Página de exibição das categorias
$app->get('/admin/categories',function(){

	User::verifyLogin();

	$search = isset($_GET['search']) ? $_GET['search'] : "";
	$pageAtual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

	//se o usuário esta buscando algum registro
	if($search != "")
	{
		$pagination = Category::getPaginationSearch($search,$pageAtual);
	}
	else
	{
		$pagination = Category::getPagination($pageAtual);
	}

	$pages = [];

	//constroi os links das paginas
	for($x = 0 ; $x<$pagination['totalPages']; $x++)
	{
		array_push($pages, [
			"href"=>"/admin/categories?".http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			"text"=>$x+1
		]);
	}


	$page= new PageAdmin();

	$page->setTpl("categories", array(
		"categories"=>$pagination['pageData'],
		"search"=>$search,
		"pages"=>$pages
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

//Rota GET - Página de relação de produtos de uma categoria
$app->get('/admin/categories/:idcategory/products', function($idcategory){

	USER::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);


	$page = new PageAdmin();

	$page->setTpl("categories-products",[

		"category"=>$category->getValues(),
		"productsRelated"=>$category->getProducts(),
		"productsNotRelated"=>$category->getProducts(false)
	]);
});

//Rota GET - Adiciona um produto da lista de não relacionados aos produtos relacionados a categoria
$app->get('/admin/categories/:idcategory/products/:idproduct/add', function($idcategory, $idproduct){

	USER::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);

	$product = new Product();
	$product->get((int)$idproduct);

	$category->addProduct($product);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;

});

//Rota GET - Remove um produto da lista de produtos relacionados a uma categoria especifica
$app->get('/admin/categories/:idcategory/products/:idproduct/remove', function($idcategory,$idproduct){

	USER::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);
	
	$product = new Product();
	$product->get((int)$idproduct);

	$category->removeProduct($product);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;

});
