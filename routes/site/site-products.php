<?php
use \Hcode\Model\Product;
use \Hcode\Pages\Page;
use \Hcode\Model\Cart;

//Rota  GET - Página de detalhes do Produto
$app->get("/products/:desurl", function($desurl){

    $product = new Product();
    $product->getFromUrl($desurl);

    $cart= Cart::getFromSession();

	$totalCart=$cart->getCalculateTotal();

	$page = new Page(['data'=>["vlprice"=>$totalCart['vlprice'], "nrqtd"=>$totalCart['nrqtd']]]);


    $page->setTpl("product-detail", [
        "product"=>$product->getValues(),
        "categories"=>$product->getCategories()
    ]);
});
