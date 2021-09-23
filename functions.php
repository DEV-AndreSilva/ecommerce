<?php

use \Hcode\Model\Cart;
use \Hcode\Model\User;

/**
 * Método responsável por formatar o preço no valor de moeda com separador de .
 *
 * @param float $price
 * @return float
 */
function formatPrice($price)
{
    if(!$price>0)
    {
        $price=0;
    }
    return number_format($price, 2, ",",".");

}

/**
 * Método responsável por verificar se o usuário está logado
 *
 * @param boolean $inadmin
 * @return bool
 */
function checkLogin($inadmin=true)
{
    return User::checkLogin($inadmin);
   
}

/**
 * Método responsável por retornar o nome do usuário
 *
 * @return string
 */
function getUserName()
{
    $user=User::getFromSession();
    return $user->getdesperson();
}

/**
 * Método responsável por retornar a quantidade total de produtos de um carrinho
 *
 * @return void
 */
function getCartNrqtd()
{
    $cart= Cart::getFromSession();

    $totals = $cart->getProductsTotals();

    return $totals['nrqtd'];
}

/**
 * Método responsável por retornar o preço do carrinho
 *
 * @return void
 */
function getCartPrice()
{
    $cart= Cart::getFromSession();

    $totals = $cart->getProductsTotals();

    return formatPrice($totals['vlprice']);
}

/**
 * Método responsável por retornar uma data formatada
 *
 * @param string $date
 * @return void
 */
function formatDate($date)
{

    return date('d/m/Y',strtotime($date));
}

/**
 * Método responsável por ajudar nos testes de variavel
 *
 * @param mixed $variavel
 * @return void
 */
function debug($variavel)
{
    echo "<pre>";
    var_dump($variavel);
    echo "</pre>";
}


