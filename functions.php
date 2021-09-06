<?php

/**
 * Método responsável por formatar o preço no valor de moeda com separador de .
 *
 * @param float $price
 * @return float
 */
function formatPrice(float $price)
{
    return number_format($price, 2, ",",".");
}