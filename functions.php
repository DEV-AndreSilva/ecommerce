<?php

use \Hcode\Model\User;

/**
 * Método responsável por formatar o preço no valor de moeda com separador de .
 *
 * @param float $price
 * @return float
 */
function formatPrice($price)
{
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