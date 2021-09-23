<?php

use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;
use \Hcode\Pages\PageAdmin;
use \Hcode\Model\User;

$app->get("/admin/orders/:idorder/delete", function($idorder){

    User::verifyLogin();

    $order = new Order();

    $order->get((int)$idorder);

    $order->delete();

    header("Location: /admin/orders");
    exit;


});

$app->get("/admin/orders/:idorder/status", function($idorder){

    User:: verifyLogin();

    $order = new Order();

    $order->get((int)$idorder);

    $page = new PageAdmin();

    $page->setTpl("order-status",[
        "order"=>$order->getValues(),
        "msgSuccess"=>Order::getError(Order::Success),
        "msgError"=>Order::getError(Order::Error),
        "msgAlert"=>Order::getError(Order::Alert),
        "status"=>OrderStatus::listAll()

    ]);
});

$app->post("/admin/orders/:idorder/status", function($idorder){

    User:: verifyLogin();

    if(!isset($_POST['idstatus']) || !(int)$_POST['idstatus']>0)
    {
        Order::setError(Order::Error, "Informe o status Atual !");
        header('Location: /admin/orders/'.$idorder."/status");
        exit;

    }

    $order = new Order();

    $order->get((int)$idorder);

    if((int)$order->getidstatus()===(int)$_POST['idstatus'])
    {
        Order::setError(Order::Alert, "Não houve alteração no status da ordem");
        header("Location: /admin/orders/".$idorder."/status");
        exit; 
    }

    $order->setidstatus((int)$_POST['idstatus']);

    $order->save();

    Order::setError(Order::Success, "Status da ordem Atualizado com sucesso");
    header("Location: /admin/orders/".$idorder."/status");
    exit;

});

$app->get("/admin/orders/:idorder", function($idorder){

    User::verifyLogin();

    $order = new Order();

    $order->get((int)$idorder);

    $cart=$order->getCart();
  
    $page = new PageAdmin();

    $page->setTpl("order",[
        "order"=>$order->getValues(),
        "cart"=>$cart->getValues(),
        "products"=>$cart->getProducts()
    ]);


});

$app->get("/admin/orders", function(){

    User::verifyLogin();

    $page = new PageAdmin();

    
    $search = isset($_GET['search']) ? $_GET['search'] : "";
	$pageAtual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

	//se o usuário esta buscando algum registro
	if($search != "")
	{
		$pagination = Order::getPaginationSearch($search,$pageAtual);
	}
	else
	{
		$pagination = Order::getPagination($pageAtual);
	}

	$pages = [];

	//constroi os links das paginas
	for($x = 0 ; $x<$pagination['totalPages']; $x++)
	{
		array_push($pages, [
			"href"=>"/admin/orders?".http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			"text"=>$x+1
		]);
	}

    $page->setTpl("orders",[
        "orders"=>$pagination['pageData'],
        "search"=>$search,
        "pages"=>$pages
    ]);
});

