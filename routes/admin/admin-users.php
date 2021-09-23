<?php

use \Hcode\Pages\PageAdmin;
use \Hcode\Model\User;

//rota GET - Pagina de exibição de todos os usuários
$app->get('/admin/users', function()
{
	User::verifyLogin();

	$search = isset($_GET['search']) ? $_GET['search'] : "";
	$pageAtual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

	//se o usuário esta buscando algum registro
	if($search != "")
	{
		$pagination = User::getPaginationSearch($search,$pageAtual);
	}
	else
	{
		$pagination = User::getPagination($pageAtual);
	}

	$pages = [];

	for($x = 0 ; $x<$pagination['totalPages']; $x++)
	{
		array_push($pages, [
			"href"=>"/admin/users?".http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			"text"=>$x+1
		]);
	}

	$page = new PageAdmin();
	$page->setTpl("users",array(
		"users"=>$pagination["pageData"],
		"search"=>$search,
		"pages"=>$pages
	));

});

//Rota GET - Pagina de criação de um usuário
$app->get('/admin/users/create', function()
{
	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl("users-create");

});

//Rota POST - Criação de um novo usuário
$app->post('/admin/users/create',function()
{
	User::verifyLogin();

	$user= new User();

	$_POST['inadmin']=(isset($_POST['inadmin']))?1 :0;

	$user->setData($_POST);
	$user->save();

	header("Location: /admin/users");
	exit;
});

//Rota GET - Excluir um usuário
$app->get('/admin/users/:iduser/delete', function($iduser)
{
	User::verifyLogin();

	$user = new user();
	$user->get((int)$iduser);
	$user->delete();

	header("location: /admin/users");
	exit;

});

//Rota POST - Alteração de um usuário
$app->post('/admin/users/:iduser',function($iduser)
{
	User::verifyLogin();
	$user = new User();
	$user->get((int)$iduser);

	$_POST['inadmin']=isset($_POST['inadmin']) ? 1 :0;

	//Se o administrador alterou a senha do usuário
	if((isset($_POST['despassword']) && !empty($_POST['despassword'])))
	 {
		$_POST['despassword']= USER::getPasswordHash($_POST['despassword']); 
	 }
	 else
	 {
		$_POST['despassword'] = $user->getdespassword();
	 } 

	
	$user->setData($_POST);
	$user->update();

	header("Location: /admin/users");
	exit;
});



//Rota GET - Pagina de alteração do usuário
$app->get('/admin/users/:iduser', function($iduser)
{
	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);


	$page = new PageAdmin();
	$page->setTpl("users-update",array(
		"user"=>$user->getValues()
	));

});
