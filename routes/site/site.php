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
	
	$errorCep=isset($_SESSION[Cart::ERROR])? Cart::getError(Cart::ERROR): '';
	$errorFrete =isset($_SESSION[Cart::SESSION_ERROR])? Cart::getError(Cart::SESSION_ERROR): '';

	$page->setTpl("cart",[
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>$errorFrete,
		'errorCEP'=>$errorCep
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



	if(isset($_GET['zipcode']))
	{
		$result = $cart->setFreight($_GET['zipcode']);

		if(isset ($_SESSION[Cart::SESSION_ERROR]))
		{
			Cart::clearError(Cart::SESSION_ERROR);

			Cart::setError(Cart::ERROR,"Antes de finalizar a compra informe um CEP valido para calcularmos os frete");

			header("Location: /cart");
			exit;
		}
		
		$address->loadFromCEP($_GET['zipcode']);

		$cart->setdeszipcode($_GET['zipcode']);

		$cart->save();
	}
	else
	{	
		Cart::setError(Cart::ERROR,"Antes de finalizar a compra informe o seu CEP para calcularmos os frete");

		header("Location: /cart");
		exit;
	}
	
	$totalCart=$cart->getCalculateTotal();

	$page = new Page(['data'=>["vlprice"=>$totalCart['vlprice'], "nrqtd"=>$totalCart['nrqtd']]]);


	$page->setTpl("checkout",[
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues()
	]);
});



$app->Post("/checkout", function(){


});


//Rota GET - Página de Login
$app->get("/login", function(){
	
	USER::verifyLogout();
	$page = new Page();

	$page->setTpl("login",[
		'error'=>User::getError( $_SESSION[User::ERROR]),
		'error_register'=>User::getError($_SESSION[User::ERROR_REGISTER]),
		'register_values'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : 
		['name'=>'', 'email'=>'', 'phone'=>'']
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

//rota POST - Criação de um usuário da loja
$app->post('/register', function(){

	//Sessão para guardar os valores que o usuário ja digitou
	$_SESSION['registerValues']= $_POST;

	if(!isset($_POST['name']) || $_POST['name']=='')
	{
		User::setError($_SESSION[User::ERROR_REGISTER], "Preencha o campo nome");	
		header("Location: /login");
		exit;
	}

	if(!isset($_POST['email']) || $_POST['email']=='')
	{
		User::setError($_SESSION[User::ERROR_REGISTER], "Preencha o campo email");	
		header("Location: /login");
		exit;
		
	}

	if(!isset($_POST['password']) || $_POST['password']=='')
	{
		User::setError($_SESSION[User::ERROR_REGISTER], "Preencha o campo senha");	
		header("Location: /login");
		exit;
	}

	//Criação do objeto usuário
	$user = new User();

	$user->setData([
		'inadmin'=>0,
		'deslogin'=>$_POST['email'],
		'desperson'=>$_POST['name'],
		'desemail'=>$_POST['email'],
		'despassword'=>$_POST['password'],
		'nrphne'=>$_POST['phone']

	]);

	//Verificação de usuário, se ele ja existe no sistema
	if(!User::checkLoginExist($_POST['email']))
	{
		$user->save();	
		User::login($_POST['email'], $_POST['password']);

		header('Location: /cart');
		exit;
	}
	else
	{
		User::setError($_SESSION[User::ERROR_REGISTER],"Esse endereço de email ja pertence a outro usuário");
		
		header('Location: /login');
		exit;
	}
});

//Rota GET - Pagina de esqueci a senha
$app->get('/forgot', function(){

	$page= new Page([]);

	$page->setTpl("forgot");
});

//Rota POST - Envio do email do usuário que vai recuperar a senha
$app->post("/forgot", function()
{

	User::getForgot($_POST['email'], false);
	header("location: /forgot/sent");
	exit;

});

//Rota GET - Página de notificação de email enviado
$app->get("/forgot/sent",function(){
	$page= new Page([]);

	$page->setTpl("forgot-sent");
});

//Rota GET - Página de alteração de senha acessado pelo link do email
$app->get('/forgot/reset', function(){

	$user = User::validForgotDecrypt($_GET['code']);
	$page= new Page([]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user['desperson'],
		"code"=>$_GET['code']
	));

});

//Rota POST - Efetivando a troca de senha e exibindo ao usuario o estado da operação
$app->post("/forgot/reset", function(){

	$forgot= User::validForgotDecrypt($_POST['code']);

	User::setForgotUsed($forgot['idrecovery']);

	$user = new User();

	//recebendo o id do usuário
	$user->get((int)$forgot['iduser']);

	//recebendo nova senha
	$password = $_POST['password'];

	//Trocando a senha do usuário
	$user->setPassword($password);

	$page= new Page([]);

	$page->setTpl("forgot-reset-success");

});

//Rota GET - Perfil do usuário
$app->get("/profile", function(){

	User::verifyLogin(false);

	$user = User::getFromSession();

	$cart= Cart::getFromSession();

	$totalCart=$cart->getCalculateTotal();

	$page = new Page(['data'=>["vlprice"=>$totalCart['vlprice'], "nrqtd"=>$totalCart['nrqtd']]]);

	$page->setTpl('profile', [
		'user'=>$user->getValues(),
		'profileMsg'=>User::getError($_SESSION[User::SUCCESS]),
		'profileError'=>User::getError($_SESSION[User::ERROR])
	]);

});

//Rota POST - Atualização do perfil do usuário
$app->post("/profile", function(){

	User::verifyLogin(false);

	$user = User::getFromSession();

	//Verifica se o usuário não deixou o nome em branco
	if(!isset($_POST['desperson']) || $_POST['desperson'] ==='')
	{
		User::setError($_SESSION[User::ERROR], 'Preencha seu nome');
		header("Location: /profile");
		exit;
	}

	//Verifica se o usuário não deixou o email em branco
	if(!isset($_POST['desemail']) || $_POST['desemail'] ==='')
	{
		User::setError($_SESSION[User::ERROR], 'Preencha seu email');
		header("Location: /profile");
		exit;
	}

	//Verifica se o usuário for trocar de email se o outro email ja possui usuário
	if($_POST['desemail'] !== $user->getdesemail())
	{
		if(User::checkLoginExist($_POST['desemail']))
		{
			User::setError($_SESSION[User::ERROR],'Endereço de email ja está sendo utilizado por outro usuário');
			header("Location: /profile");
			exit;
		}
	}

	//Se o usuário descobrir esses parametros ele não consegue alterar
	$_POST['inadmin']=$user->getinadmin();
	$_POST['despassword']=$user->getdespassword();
	$_POST['deslogin']=$_POST['desemail'];

	$user->setData($_POST);

	//Atualiza as informações do usuário
	$user->update();
	
	//Atualização da mensagem com parametro de sucesso
	User::setError($_SESSION[User::SUCCESS],"Perfil atualizado com sucesso !");

	header('Location: /profile');
	exit;

});


