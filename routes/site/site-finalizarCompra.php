<?php

use \Hcode\Pages\Page;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;
use \Hcode\Model\Address;
use Hcode\Model\Boleto;
use \Hcode\Model\Cart;
use \Hcode\Model\User;


//Rota GET - Checkout de usuário para fazer a compra
$app->get("/checkout/finalizarcompra", function(){


	//Verifica se o usuário está Logado
	User::verifyLogin(false);

	//Instancia objeto da classe que recebrea o endereço e dados do cliente
	$address= new Address();

	//Carrega os dados do carrinho pela sessão
	$cart =  Cart::getFromSession();

	//Verifica se o campo de CEP que preenche o zip code foi preenchido
	if(isset($_GET['zipcode']) || !empty($cart->getdeszipcode()))
	{
		$zipcode = $_GET['zipcode'] ?? $cart->getdeszipcode();

		//Carregar endereço de entrega
		$address->loadFromCEP($zipcode);

		//Carregar valor do frete
		$cart->setFreight($zipcode);

		//Atualiza o endereço do cep do carrinho
		$cart->setdeszipcode($zipcode);

		//Salva o carrinho banco de dados
		$cart->save();
	}
	else
	{	
		//Mensagem de Erro caso o usuário deixo o CEP em branco
		Address::setError(Address::ERROR,"Antes de finalizar a compra informe o seu CEP");

		//Redirecionamento para mostrar o erro
		header("Location: /checkout/finalizarcompra");
		exit;
	}

	//Salvamento de informações caso usuário altere seus dados de entrega
	if(isset($_SESSION['dadosFinalizar']))
	{
		$address->setData($_SESSION['dadosFinalizar']);
		unset($_SESSION['dadosFinalizar']);
	}
	
	//calcula o preço do icone do carrinho
	$page = new Page();

	//Renderiza o template
	$page->setTpl("checkout",[
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Address::getError(Address::ERROR)
	]);
});

//Rota GET - Checkout de usuário que verifica a compra e valor do frete
$app->get("/checkout", function(){

	User::verifyLogin(false);

	$cart =  Cart::getFromSession();

	//Verifica se o usuário preencheu o campo CEP
	if(isset($_GET['zipcode']))
	{
		//Carregar valor do frete
		$cart->setFreight($_GET['zipcode']);
		
		//Se existe um Erro redireciona o usuário para o carrinho com a mensagem de erro
		if(isset($_SESSION[Cart::ERROR]))
		{
			header("Location: /cart");
			exit;
		}

		//Atualiza o CEP do carrinho 
		$cart->setdeszipcode($_GET['zipcode']);

		//Salva os dados no banco de dados
		$cart->save();

		//Redireciona o usuário para a pagina de finalização de compra
		header("Location: /checkout/finalizarcompra");
	}
	else
	{	
		//Caso CEP esteja em branco  atualiza a pagina mostrando o erro
		Cart::setError(Cart::ERROR,"Antes de finalizar a compra informe o seu CEP para calcularmos os frete");
		header("Location: /cart");
		exit;
	}
	

});

//Rota POST - Rota de finalizar compra
$app->Post("/checkout", function(){

	User::verifyLogin(false);

	$cart=Cart::getFromSession();
	$totals = $cart->getCalculateTotal();

	$_SESSION['dadosFinalizar']= $_POST;

	if(!isset($_POST['zipcode']) || $_POST['zipcode']==='')
	{
		Address::setError(Address::ERROR, "Informe seu CEP");
		header("Location: /checkout/finalizarcompra");
		exit;
	}
	
	if(!isset($_POST['desaddress']) || $_POST['desaddress']==='')
	{
		Address::setError(Address::ERROR, "Informe seu Endereço");
		header("Location: /checkout/finalizarcompra");
		exit;
	}
	
	if(!isset($_POST['desdistrict']) || $_POST['desdistrict']==='')
	{
		Address::setError(Address::ERROR, "Informe seu Bairro");
		header("Location: /checkout/finalizarcompra");
		exit;
	}

	if(!isset($_POST['desstate']) || $_POST['desstate']==='')
	{
		Address::setError(Address::ERROR, "Informe seu estado");
		header("Location: /checkout/finalizarcompra");
		exit;
	}

	if(!isset($_POST['descountry']) || $_POST['descountry']==='')
	{
		Address::setError(Address::ERROR, "Informe seu Pais");
		header("Location: /checkout/finalizarcompra");
		exit;
	}


	$user = User::getFromSession();

	$address = new Address();

	$_POST['deszipcode']= $_POST['zipcode'];
	$_POST['idperson']=$user->getidperson();

	$address->validateCamp($_POST);


	$address->setData($_POST);

	$address->save();

	//Salvamento de informações caso usuário altere seus dados de entrega
	if(isset($_SESSION['dadosFinalizar']))
	{
		unset($_SESSION['dadosFinalizar']);
	}

	$order = new Order();

	$order->setData([
		"idcart"=>$cart->getidcart(),
		"idaddress"=>$address->getidaddress(),
		"iduser"=>$user->getiduser(),
		"idstatus"=>OrderStatus::EM_ABERTO,
		"vltotal"=>$totals['vlprice'] + $cart->getvlfreight()
	]);

	$order->save();

	header("Location: /order/".$order->getidorder());
	exit;

});

//Rota GET - página de exibição do boleto
$app->get('/order/:idorder', function($idOrder){
	
	User::verifyLogin(false);
	$order= new Order;

	$order->get((int) $idOrder);

	$page = new Page();
	$page->setTpl("payment",[
		"order"=>$order->getValues()

	]);
});

//Rota GET - IFRAME do boleto com os dados do cliente e pedido
$app->get("/boleto/:idorder", function($idOrder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idOrder);

	$dias = 5;

	$valor_cobrado =  $order->getvltotal();

	$dados_boleto = [
		"nosso_numero"=>$order->getidorder(),
		"numero_documento"=>$order->getidorder(),
		"sacado"=>$order->getdesperson(),
		"endereco1"=>$order->getdesaddress()." ".$order->getdesdistrict(),
		"endereco2"=>$order->getdescity()." - ".$order->getdesstate()." ".$order->getdescountry()." ".$order->getdeszipcode(),
	];

	$boleto = new Boleto($dias,$valor_cobrado,$dados_boleto);


	$path =  $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."resources".DIRECTORY_SEPARATOR."boletophp"
			.DIRECTORY_SEPARATOR."include".DIRECTORY_SEPARATOR;
	
	//Variavel do template de boleto
	$dadosboleto = $boleto->getDadoBoleto();
	
	require_once($path."funcoes_itau.php");
	require_once($path."layout_itau.php");
	
});




