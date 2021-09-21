<?php
namespace Hcode\Model;

use Hcode\DB\Sql;

class Order extends Model
{
    public function save()
    {
        $sql= new Sql();

        $results = $sql->select("CALL sp_orders_save(:idorder,:idcart,:iduser,:idstatus,:idaddress,:vltotal)",[
            ':idorder'=>$this->getidorder(),
            ':idcart'=>$this->getidcart(),
            ':iduser'=>$this->getiduser(),
            ':idstatus'=>$this->getidstatus(),
            ':idaddress'=>$this->getidaddress(),
            ':vltotal'=>$this->getvltotal()
            ]
        );

        if(count($results)>0)
        {
            $this->setData($results[0]);
        }
   

    }
    public function get($idOrder)
    {
        $sql = new Sql();

        $results=$sql->select("SELECT a.idorder,a.idcart,a.iduser,a.idstatus,a.idaddress,a.vltotal,a.dtregister,
         b.deszipcode,b.vlfreight,
         d.desstatus,
         e.desaddress,e.desdistrict,e.descity,e.desstate
         
      FROM tb_orders a 
      JOIN tb_carts b ON b.idcart = a.idcart
      JOIN tb_users c ON c.iduser = a.iduser
      JOIN tb_ordersstatus d ON d.idstatus=a.idstatus
      JOIN tb_addresses e ON e.idaddress = a.idaddress
      JOIN tb_persons f ON f.idperson=a.iduser
      WHERE a.idorder= :idorder",[
             ":idorder"=>$idOrder
         ]);
        
        
        if(count($results)>0)
        {
            $this->setData($results[0]);
        }
    }
}