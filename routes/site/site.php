<?php

use \Hcode\Model\Cart;
use \Hcode\Model\Product;
use \Hcode\Pages\Page;

//rota GET - Pagina inicial ou index
$app->get('/', function() 
{

	//Mostrar todos os produtos na tela inicial da loja
	$products = Product::listAll();
	
	$cart= Cart::getFromSession();

	$totalCart=$cart->getCalculateTotal();

	$page = new Page(['data'=>["vlprice"=>$totalCart['vlprice'], "nrqtd"=>$totalCart['nrqtd']]]);

	$page->setTpl("site/index",[
		"products"=>Product::checkList($products)]);

});



