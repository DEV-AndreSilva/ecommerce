<?php
namespace Hcode\Model;

use Error;
use Hcode\DB\Sql;
use Hcode\Model\Cart;

class Order extends Model
{
    const Error = "Erro";
    const Success= "Sucesso";
    const Alert = "SemAlteracao";

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
                e.desaddress,e.desdistrict,e.descity,e.desstate,e.descomplement,
                f.desperson, f.nrphone, f.desemail
                
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

    public static function listAll()
    {
       $sql = new Sql();
       
       $results =  $sql->select("SELECT a.idorder,a.idcart,a.iduser,a.idstatus,a.idaddress,a.vltotal,a.dtregister,
       b.deszipcode,b.vlfreight,
       d.desstatus,
       e.desaddress,e.desdistrict,e.descity,e.desstate,
       f.desperson
      
       FROM tb_orders a 
       JOIN tb_carts b ON b.idcart = a.idcart
       JOIN tb_users c ON c.iduser = a.iduser
       JOIN tb_ordersstatus d ON d.idstatus=a.idstatus
       JOIN tb_addresses e ON e.idaddress = a.idaddress
       JOIN tb_persons f ON f.idperson=a.iduser
       ORDER BY a.dtregister DESC");

        return $results;
    }

    public function delete()
    {
        $sql = new Sql();

        $sql->query("DELETE FROM tb_orders WHERE idorder=:idorder",[
            ":idorder"=>$this->getidorder()
        ]);

    }

    public function getCart():Cart
    {
        $cart = new Cart();

        $cart->get((int)$this->getidcart());

        return $cart;
    }

    public static function getError($error)
    {
        $message = isset($_SESSION[$error]) ? $_SESSION[$error] : "";
        Order::clearError($error);
        return $message;
    }

    public static function setError($error, $message)
    {
        $_SESSION[$error]= $message;
    }

    public static function clearError($error)
    {
        if(isset($_SESSION[$error]))
        {
            unset($_SESSION[$error]);
        }
    }

}