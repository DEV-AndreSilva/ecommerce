<?php

use \Hcode\Model\Address;
use \Hcode\Model\Cart;
use \Hcode\Model\Product;
use \Hcode\Pages\Page;
use \Hcode\Model\User;

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


//rota Get - Página do carrinho
$app->get('/cart', function(){

	$cart= Cart::getFromSession();

	$totalCart=$cart->getCalculateTotal();

	$page = new Page(['data'=>["vlprice"=>$totalCart['vlprice'], "nrqtd"=>$totalCart['nrqtd']]]);
	
	$page->setTpl("cart",[
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Cart::getMsgError()
	]);
});

//rota GET - Adição de produtos ao carrinho
$app->get("/cart/:idproduct/add", function($idProduct){

	$product = new Product();

	$product->get((int)$idProduct);
	
	$cart= Cart::getFromSession();

	$quantity = (isset($_GET['quantity']))? (int)$_GET["quantity"] : 1;

	for($i = 0; $i < $quantity; $i++)
	{
		$cart->addProduct($product);
	}

	header("Location: /cart");
	exit;

});

//rota GET - Remoção de 1 unidade de produto do carrinho
$app->get("/cart/:idproduct/minus", function($idProduct){

	$product = new Product();

	$product->get((int)$idProduct);
	
	$cart= Cart::getFromSession();

	$cart->removeProduct($product);

	header("Location: /cart");
	exit;

});

//Rota GET - Remoção de todos os produtos do carrinho
$app->get("/cart/:idproduct/remove", function($idProduct){

	$cart = new Cart();
	$product = new Product();

	$product->get((int)$idProduct);
	
	$cart= Cart::getFromSession();

	$cart->removeProduct($product,true);

	header("Location: /cart");
	exit;

});

//Rota POST - Calculo do frete
$app->post("/cart/freight", function(){

	$cart = Cart::getFromSession();

	$cart->setFreight($_POST['zipcode']);

	$cart->getCalculateTotal();

	header("location: /cart");
	exit;

});

//Rota GET - Checkout de usuário logado para fazer a compra
$app->get("/checkout", function(){

	User::verifyLogin(false);

	$address= new Address();
	$cart =  Cart::getFromSession();

	$totalCart=$cart->getCalculateTotal();

	$page = new Page(['data'=>["vlprice"=>$totalCart['vlprice'], "nrqtd"=>$totalCart['nrqtd']]]);

	$page->setTpl("checkout",[
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues()
	]);
});



//Rota GET - Página de Login
$app->get("/login", function(){
	
	USER::verifyLogout();
	$page = new Page();

	$page->setTpl("login",[
		'error'=>User::getError( $_SESSION[User::ERROR]),
		'error_register'=>""
	]);
});

//Rota Post Login do usuário
$app->post("/login", function()
{
	try
	{
		$user = User::login($_POST['login'],$_POST['password']);
		header("Location: /checkout");
		exit;
	}
	catch(Exception $ex)
	{
		User::setError($_SESSION[User::ERROR] , $ex->getMessage());
		header("Location: /login");
		exit;
	}

});

//rota GET - Realizar logout usuário
$app->get('/logout', function()
{
	User::logout();
	header("Location: /login");
	exit;
});