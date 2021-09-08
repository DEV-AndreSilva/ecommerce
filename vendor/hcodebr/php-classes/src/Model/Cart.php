<?php
namespace Hcode\Model;

use Hcode\DB\Sql;
use \Hcode\Model\Product;

class Cart extends Model
{
    const SESSION = "Cart";

    /**
     * Método responsável por recuperar os dados do carrinho pelos dados de uma sessão
     *
     * @return void
     */
    public static function getFromSession()
    {
        $cart = new Cart();

        //Verifica se existe uma sessão e o id do carrinho para recuperar o carrinho
        if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart']>0 )
        {
            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
        }
        else
        {
            //Verifica se o Id da sessão ainda esta ativo para recuperar o carrinho
            $cart->getFromSessionID();

            //Se não há Id da sessão cria um novo objeto carrinho
            if(!(int)$cart->getidcart()>0)
            {
                //recebe um novo session id para o carrinho
                $data = [
                    "dessessionid"=>session_id()
                ];

                //Recebe os dados de um usuário pela sessão caso ela exista
                $user= User::getFromSession();

                //Verifica o Login do usuário
                if($user::checkLogin(false)==true)
                {
                    $user= User::getFromSession();
                    $data['iduser']= $user->getiduser();
                }

                //preenche o carrinho com os dados da sessão e o id do usuário caso ele exista
                $cart->setData($data);

                //Cria um novo registro de carrinho no banco de dados
                $cart->save();

                //Instancia uma sessão com os dados objeto carrinho
                $cart->setToSession();
            }
        }

        return $cart;

    }

    /**
     * Cria a sessão do carrinho
     *
     * @return void
     */
    public function setToSession()
    {
        $_SESSION[Cart::SESSION]=$this->getValues();
    }

    /**
     * Método responsável por obter um carrinho pelo seu id caso ele exista
     *
     * @param integer $idcart
     * @return void
     */
    public function get(int $idcart)
    {
        $sql = new Sql();

        $results= $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
            ":idcart"=>$idcart
        ]);

        if(count($results)>0)
        {
            $this->setData($results[0]);
        }
    }

    /**
     * Método responsável por obeter um carrinho pelo id da sessão caso ele exista
     *
     * @return void
     */
    public function getFromSessionID()
    {
    
         $sql = new Sql();
    
        $results= $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
            ":dessessionid"=>session_id()
        ]);
        
        if(count($results)>0)
        {
            $this->setData($results[0]);
        }
        
        
    }

    /**
     * Método responsável por criar um registro de carrinho no BD ou atualizar um caso ele ja exista
     *
     * @return void
     */
    public function save()
    {
        $sql = new Sql();

        $result = $sql->select("CALL sp_carts_save(:pidcart,:pdessessionid,:piduser,:pdeszipcode,:pvlfreight,:pnrdays)", [
            ":pidcart"=>$this->getidcart(),
            ":pdessessionid"=>$this->getdessessionid(),
            ":piduser"=>$this->getiduser(),
            ":pdeszipcode"=>$this->getdeszipcode(),
            ":pvlfreight"=>$this->getvlfreight(),
            ":pnrdays"=>$this->getnrdays(),
        ]);

        $this->setData($result[0]);
    }

 
}