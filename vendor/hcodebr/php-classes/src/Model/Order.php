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

    /**
     * Método responsável por cadastrar uma nova ordem de serviço no banco de dados ou atualizar ela caso ja exista
     *
     * @return void
     */
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

    /**
     * Método responsável pelo retorno de uma ordem de serviço especifica pelo seu ID
     *
     * @param int $idOrder
     * @return void
     */
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

    /**
     * Método responsável pela listagem de todas as ordens de serviço
     *
     * @return array
     */
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

    /**
     * Apaga um registro de ordens de serviço
     */
    public function delete()
    {
        $sql = new Sql();

        $sql->query("DELETE FROM tb_orders WHERE idorder=:idorder",[
            ":idorder"=>$this->getidorder()
        ]);

    }

    /**
     * Retorna um carrinho de uma ordem de serviço
     *
     * @return Cart
     */
    public function getCart():Cart
    {
        $cart = new Cart();

        $cart->get((int)$this->getidcart());

        return $cart;
    }

    /**
     * Retorna uma mensagem 
     *
     * @param string $error
     * @return void
     */
    public static function getError($error)
    {
        $message = isset($_SESSION[$error]) ? $_SESSION[$error] : "";
        Order::clearError($error);
        return $message;
    }

    /**
     * Atualiza a informação de uma mensagem
     *
     * @param string $error
     * @param string $message
     * @return void
     */
    public static function setError($error, $message)
    {
        $_SESSION[$error]= $message;
    }

    /**
     * Limpa o valor de uma mensagem
     *
     * @param string $error
     * @return void
     */
    public static function clearError($error)
    {
        if(isset($_SESSION[$error]))
        {
            unset($_SESSION[$error]);
        }
    }

}