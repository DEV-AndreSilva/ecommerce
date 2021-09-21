<?php
use \Hcode\Model\Product;
use \Hcode\Pages\Page;
use \Hcode\Model\Cart;

//Rota  GET - Página de detalhes do Produto
$app->get("/products/:desurl", function($desurl){

    $product = new Product();
    $product->getFromUrl($desurl);

	$page = new Page();
    
    $page->setTpl("product-detail", [
        "product"=>$product->getValues(),
        "categories"=>$product->getCategories()
    ]);
});
