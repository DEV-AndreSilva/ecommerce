<?php

use Hcode\Model\Product;
use \Hcode\Pages\Page;

//rota GET - Pagina inicial ou index
$app->get('/', function() 
{
	$products = Product::listAll();
	$page = new Page();
	$page->setTpl("site/index",[
		"products"=>Product::checkList($products)]);

});