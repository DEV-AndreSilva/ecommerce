<?php

use Hcode\Model\Product;
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