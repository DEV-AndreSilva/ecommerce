<?php

/**
 * Método responsável por formatar o preço no valor de moeda brasileira
 *
 * @param float $price
 * @return float
 */
function formatPrice(float $price)
{
    return number_format($price, 2, ",",".");
}