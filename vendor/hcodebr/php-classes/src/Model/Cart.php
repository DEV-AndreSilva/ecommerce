<?php
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model\Product;

class Cart extends Model
{
    const SESSION = "Cart";
    const SESSION_ERROR="CartError";

    /**
     * Método responsável por recuperar os dados do carrinho pelos dados de uma sessão
     *
     * @return void
     */
    public static function getFromSession():Cart
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

    /**
     * Método responsável por retornar todos os produtos de um carrinho
     *
     * @return void
     */
    public function getProducts()
    {
        $sql = new Sql();

        $rows = $sql->select(
            "SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl,
            COUNT(*) AS nrqtd,
            SUM(b.vlprice) AS vltotal
            FROM tb_cartsproducts a
            INNER JOIN tb_products b ON a.idproduct = b.idproduct
            WHERE a.idcart = :idcart 
            AND a.dtremoved IS NULL
            GROUP BY b.idproduct
            ORDER BY b.desproduct",
             [
                 ":idcart"=>$this->getidcart()
             ]);

        return Product::checkList($rows);
    }

    /**
     * Método responsável por adicionar um produto a um carrinho
     *
     * @param Product $product
     * @return void
     */
    public function addProduct(Product $product)
    {
        $sql= new sql();
        $sql->query('INSERT INTO tb_cartsproducts(idcart,idproduct) VALUES(:idcart, :idproduct)',[
        ":idcart"=>$this->getidcart(),
        ":idproduct"=>$product->getidproduct()
        ]);

        if( ($this->getMsgError())!=null)
        {
            $this->clearMsgError();
        }
        //$this->getCalculateTotal();
      
    }

    /**
     * Método responsável por remover 1 ou todos os produtos de um carrinho
     *
     * @param Product $product
     * @param boolean $all
     * @return void
     */
    public function removeProduct(Product $product, $all = false)
    {
        $sql = new Sql();

        if($all) //Remove todos os produtos daquele tipo
        {
            $sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart=:idcart  AND idproduct=:idproduct AND dtremoved IS NULL",
            [
                ":idcart"=>$this->getidcart(),
                ":idproduct"=>$product->getidproduct()
            ]);
        }
        else //Remove 1 daquele tipo de produto
        {
            $sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart=:idcart
            AND idproduct=:idproduct
            AND dtremoved IS NULL
            LIMIT 1",
            [
                ":idcart"=>$this->getidcart(),
                ":idproduct"=>$product->getidproduct()
            ]);
        }

        if( ($this->getMsgError())!=null)
        {
            $this->clearMsgError();
        }

       // $this->getCalculateTotal();
    }

    public function getProductsTotals()
    {
        $sql= new Sql();

        $results = $sql->select(
       "SELECT SUM(a.vlprice) AS vlprice, SUM(a.vlwidth) AS vlwidth, SUM(a.vlheight) AS vlheight, SUM(a.vllength) AS vllength, SUM(a.vlweight) AS vlweight, COUNT(*) AS nrqtd
        FROM tb_products a 
        INNER JOIN tb_cartsproducts b ON a.idproduct=b.idproduct
        WHERE b.idcart = :idcart AND b.dtremoved IS NULL ",
        [
            ":idcart"=>$this->getidcart()
        ]);

        if($results[0]['vlprice'] != null)
        {
            return $results[0];            
        }
        else
        {
            return [
                'vllength'=> 0,
                'vlprice' => 0,
                'vlwidth' => 0,
                'vlheight' => 0,
                'vlweight' => 0,
                'nrqtd' => 0
            ];
        }

    }

    /**
     * Método responsável pela consulta do web service do correio e calculo do frete
     *
     * @param string $nrzipcode
     * @return void
     */
    public function setFreight(string $nrzipcode)
    {
        $nrzipcode =  str_replace("-","", $nrzipcode);
        $total = $this->getProductsTotals();
        {
            $total['vllength'] = ($total['vllength'] >15 && $total['vllength']<100) ? $total['vllength'] : 50;
            $total['vlheight'] = ($total['vlheight']>1 && $total['vlheight']<100) ? $total['vlheight'] : 50;
            $total['vlwidth'] = ($total['vlwidth']>10 && $total['vlwidth']<100) ? $total['vlwidth']: 50;
        
            $qs = http_build_query([
            "nCdEmpresa"=>"",
            "sDsSenha"=>"",
            "nCdServico"=>"40010",
            "sCepOrigem"=> '15440000',
            "sCepDestino"=>$nrzipcode,
            "nVlPeso"=>$total['vlweight'],
            "nCdFormato"=>"1",
            "nVlComprimento"=>$total['vllength'],
            "nVlAltura"=>$total['vlheight'],
            "nVlLargura"=>$total['vlwidth'],
            "nVlDiametro"=>0.0,
            "sCdMaoPropria"=>"S",
            "nVlValorDeclarado"=>$total['vlprice'],
            "sCdAvisoRecebimento"=>"S"
            ]);

            //função para ler xml
            $xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);
                
            $result = $xml->Servicos->cServico;
             
            if($result->MsgErro != "")
            {
                Cart::setMsgError((string)$result->MsgErro);
                                
            }
            else
            {
                Cart::clearMsgError();
            }
                    
            $this->setnrdays($result->PrazoEntrega);
            $this->setvlfreight(CART::formatValueToDecimal($result->Valor));
            $this->setdeszipcode($nrzipcode);
                    
            $this->save();
            return $result;
         
        }
        
    }

    
    /**
     * Método responsável por retornar o valor ao formato decimal
     *
     * @param $value
     * @return void
     */
    public static function formatValuetoDecimal($value):float
    {
        $value = str_replace(".","",$value);
        $value = str_replace(",",".",$value);

        return $value;
    }

    /**
     * Método responsável por definir a mensagem de erro
     *
     * @param string $message
     * @return void
     */
    public static function setMsgError($message)
    {
        $_SESSION[Cart::SESSION_ERROR]= $message;
    }

    /**
     * Método responsável por retornar a mensagem de erro
     *
     * @return void
     */
    public static function getMsgError()
    {
        return isset($_SESSION[Cart::SESSION_ERROR]) ? $_SESSION[Cart::SESSION_ERROR] : null;
    }

    /**
     * Método responsável por limpar o valor da mensagem de erro
     *
     * @return void
     */
    public static function clearMsgError()
    {
        $_SESSION[Cart::SESSION_ERROR]= null;
    }

    /**
     * Método responsável por atualizar o valor do frete
     *
     * @return void
     */
    public function updateFreight()
    {
        if($this->getdeszipcode() != null &&  $this->getdeszipcode() != '')
        {
           $this->setFreight($this->getdeszipcode()); 
        }
    }
    
    /**
     * Método responsável por retornar os valores da compra com total e subtotal
     *
     * @return void
     */
    public function getValues()
    {
        $this->getCalculateTotal();

        return parent::getValues();
    }

    /**
     * Método responsavél por calcular os valores de total e subtotal da compra
     *
     * @return void
     */
    public function getCalculateTotal()
    {
        //$this->updateFreight();
        $totals = $this->getProductsTotals();
        $this->setvlsubtotal($totals['vlprice']);
        $this->setvltotal($totals['vlprice']+ $this->getvlfreight());

    }


 
}