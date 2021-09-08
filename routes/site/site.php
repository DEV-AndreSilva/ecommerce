<?php

use \Hcode\Model\Cart;
use \Hcode\Model\Product;
use \Hcode\Pages\Page;

//rota GET - Pagina inicial ou index
$app->get('/', function() 
{
	//Mostrar todos os produtos na tela inicial da loja
	$products = Product::listAll();
	
	$page = new Page();
	$page->setTpl("site/index",[
		"products"=>Product::checkList($products)]);

});

//rota Get - Página do carrinho
$app->get('/cart', function(){

	$cart= Cart::getFromSession();

	$page = new Page();

	$page->setTpl("cart");
});