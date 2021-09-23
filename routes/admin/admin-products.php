<?php

use \Hcode\Pages\PageAdmin;
use \Hcode\Model\Product;
use \Hcode\Model\User;


//Rota GET - Página de visualização de todos os produtos 
$app->get('/admin/products', function(){

    User::verifyLogin();

    $search = isset($_GET['search']) ? $_GET['search'] : "";
	$pageAtual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

	//se o usuário esta buscando algum registro
	if($search != "")
	{
		$pagination = Product::getPaginationSearch($search,$pageAtual);
	}
	else
	{
		$pagination = Product::getPagination($pageAtual);
	}

	$pages = [];

	//constroi os links das paginas
	for($x = 0 ; $x<$pagination['totalPages']; $x++)
	{
		array_push($pages, [
			"href"=>"/admin/products?".http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			"text"=>$x+1
		]);
	}

    $page = new PageAdmin();

    $page->setTpl("products",array(
        "products"=>$pagination['pageData'],
        "search"=>$search,
        "pages"=>$pages
    ));
});

//Rota GET - Página de criação de um novo produto 
$app->get('/admin/products/create', function(){

    User::verifyLogin();

    $page = new PageAdmin();

    $page->setTpl("products-create");
});

//Rota POST - Criação de um novo produto 
$app->post('/admin/products/create', function(){

    User::verifyLogin();

    $product = new Product();
    $product->setData($_POST);
    $product->save();

    header('Location: /admin/products');
    exit;
});



//Rota GET - Página de atualização de um produto 
$app->get('/admin/products/:idproduct', function($idproduct){

    User::verifyLogin();

    $product = new Product();
    $product->get((int)$idproduct);

    $page = new PageAdmin();

    $page->setTpl("products-update", array(
        "product"=>$product->getValues()
    ));

  
});


//Rota POST - Atualização do produto 
$app->post('/admin/products/:idproduct', function($idproduct){

    User::verifyLogin();

    $product = new Product();

    //Preenche os atributos com o valores do banco
    $product->get((int)$idproduct);

    //Atualiza os atributos do objeto
    $product->setData($_POST);

    //Atualiza os valores do produto no banco de dados
    $product->save();

    $product_image=$_FILES['file'];

    //Se a imagem foi alterada atualiza o arquivo de imagem
    if($product_image['error']!==4)
    {
        $product->updatePhoto($product_image);
    }

    header('Location: /admin/products');
    exit;
});

//Rota GET - Deletando um produto
$app->get('/admin/products/:idproduct/delete',function($idproduct){

    USER::verifyLogin();

    $product = new Product();

    $product->get((int)$idproduct);

    $product->delete();

    header('Location: /admin/products');
    exit;
});

