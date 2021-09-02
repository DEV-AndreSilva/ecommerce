<?php

use \Hcode\Page;

//rota GET - Pagina inicial ou index
$app->get('/', function() 
{
	$page = new Page();
	$page->setTpl("index");

});