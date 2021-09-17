<?php
namespace Hcode\Model;

use Hcode\DB\Sql;

class OrderStatus extends Model
{
    const EM_ABERTO =1;
    const AGUARDSNDO_PAGAMENTO = 2;
    const PAGO = 3;
    const ENTREGUE = 4;
}