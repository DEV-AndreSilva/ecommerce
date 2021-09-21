<?php

use \Hcode\Pages\Page;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;
use \Hcode\Model\Address;
use Hcode\Model\Boleto;
use \Hcode\Model\Cart;
use \Hcode\Model\User;




//Rota GET - Checkout de usuário para fazer a compra (botao validar cep carrinho)
$app->get("/checkout/cep", function(){


	//Verifica se o usuário está Logado
	User::verifyLogin(false);

	//Carrega o Usuário pela sessao
	$user = User::getFromSession();

	//Carrega os dados do carrinho pela sessão
	$cart =  Cart::getFromSession();

	//Verifica se o campo de CEP que preenche o zip code foi preenchido
	if(isset($_GET['zipcode']) || !empty($cart->getdeszipcode()))
	{
		$address = new Address();
		$zipcode = $_GET['zipcode'] ?? $cart->getdeszipcode();

		//Carregar endereço de entrega
		$address->loadFromCEP($zipcode);

		//Carregar valor do frete
		$cart->setFreight($zipcode);
		//Carrega o Id do usuário no carrinho
		$cart->setiduser($user->getiduser());

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
		header("Location: /cart");
		exit;
	}

	header("Location: /checkout");
	exit;

});



//Rota GET - Página de checkout de usuário para fazer a compra 
$app->get("/checkout", function(){

	//Verifica se o usuário está Logado
	User::verifyLogin(false);

	//Recebe o usuário vindo da Sessão
	$user = User::getFromSession();
	
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

		//Carrega o id do usuário no carrinho
		$cart->setiduser($user->getiduser());

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
		header("Location: /checkout");
		exit;
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

//Rota POST - Rota acionada ao clicar no botão finalizar compra do checkout
$app->Post("/checkout", function(){

	//Verifica o LOgin
	User::verifyLogin(false);

	//Recebe o usuário e o carrinho pela sessão
	$user = User::getFromSession();
	$cart=Cart::getFromSession();

	//Calcula o total do carrinho
	$totals = $cart->getCalculateTotal();

	//Valida os campos antes de emitir o boleto
	if(!isset($_POST['zipcode']) || $_POST['zipcode']==='')
	{
		Address::setError(Address::ERROR, "Informe seu CEP");
		header("Location: /checkout");
		exit;
	}
	
	if(!isset($_POST['desaddress']) || $_POST['desaddress']==='')
	{
		Address::setError(Address::ERROR, "Informe seu Endereço");
		header("Location: /checkout");
		exit;
	}
	
	if(!isset($_POST['desdistrict']) || $_POST['desdistrict']==='')
	{
		Address::setError(Address::ERROR, "Informe seu Bairro");
		header("Location: /checkout");
		exit;
	}

	if(!isset($_POST['desstate']) || $_POST['desstate']==='')
	{
		Address::setError(Address::ERROR, "Informe seu estado");
		header("Location: /checkout");
		exit;
	}

	if(!isset($_POST['descountry']) || $_POST['descountry']==='')
	{
		Address::setError(Address::ERROR, "Informe seu Pais");
		header("Location: /checkout");
		exit;
	}


	//Atualiza o POST dos campos para evitar ataques
	$_POST['deszipcode']= $_POST['zipcode'];
	$_POST['idperson']=$user->getidperson();


	//Cria um obejto de endereço e salva no banco de dados
	$address = new Address();
	$address->setData($_POST);
	$address->save();

	//Cria um objeto de order e salva no banco de dados
	$order = new Order();
	$order->setidaddress($address->getidaddress());
	$order->setData([
		"idcart"=>$cart->getidcart(),
		"iduser"=>$user->getiduser(),
		"idstatus"=>OrderStatus::EM_ABERTO,
		"vltotal"=>$totals['vlprice'] + $cart->getvlfreight()
	]);
	$order->save();

	//Redireciona para página de exibição do boleto
	header("Location: /order/".$order->getidorder());
	exit;

});

//Rota GET - página de exibição do boleto
$app->get('/order/:idorder', function($idOrder){
	
	//Verifica o Login do usuário
	User::verifyLogin(false);

	//carrega o objeto order daquele usuário
	$order= new Order;
	$order->get((int) $idOrder);

	//Carrega a página do boleto
	$page = new Page();
	$page->setTpl("payment",[
		"order"=>$order->getValues()

	]);
});

//Rota GET - IFRAME do boleto com os dados do cliente e pedido
$app->get("/boleto/:idorder", function($idOrder){

	//Verifica o Login do usuário
	User::verifyLogin(false);

	//Carrega a order do usuário
	$order = new Order();
	$order->get((int)$idOrder);


	//carrega os dados do boleto
	$dias = 5;
	$valor_cobrado =  $order->getvltotal();
	$dados_boleto = [
		"nosso_numero"=>$order->getidorder(),
		"numero_documento"=>$order->getidorder(),
		"sacado"=>$order->getdesperson(),
		"endereco1"=>$order->getdesaddress()." ".$order->getdesdistrict(),
		"endereco2"=>$order->getdescity()." - ".$order->getdesstate()." ".$order->getdescountry()." ".$order->getdeszipcode(),
	];

	//Cria um boleto
	$boleto = new Boleto($dias,$valor_cobrado,$dados_boleto);


	$path =  $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."resources".DIRECTORY_SEPARATOR."boletophp"
			.DIRECTORY_SEPARATOR."include".DIRECTORY_SEPARATOR;
	
	//Variavel do template de boleto
	$dadosboleto = $boleto->getDadoBoleto();

	//Exibe o boleto na tela
	require_once($path."funcoes_itau.php");
	require_once($path."layout_itau.php");
	
});




