<?php

namespace Hcode\Model;

use Exception;
use \Hcode\Model\Model;
use \Hcode\DB\Sql;
use \Hcode\Mailer\Mailer;

class User extends Model
{
    const SESSION = "User";
    const SECRET = "AndrePHP7_Secret";
	const SECRET_IV = "AndrePHP7_Secret_IV";
	const ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";
	const SUCCESS = "UserSucesss";


    /**
     * Método responsável por retornar os dados do usuário pela sessão
     *
     * @return User
     */
    public static function getFromSession()
    {
        $user = new User();

        //Verifica se a sessão está ativa e o id do usuário está nela
        if(isset($_SESSION[User::SESSION]) && (int) $_SESSION[User::SESSION]["iduser"]>0)
        {
            $user->setData($_SESSION[User::SESSION]);
        }

        return $user;
    }

    /**
     * método responsável por verificar se o usuário está logado
     *
     * @param boolean $inadmin
     * @return bool
     */
    public static function checkLogin($inadmin=true)
    {
  
        if( !isset($_SESSION[user::SESSION]) ||                      //Se a sessão existe
            !$_SESSION[user::SESSION] ||                             //Se a sessão não está vazia ou é nula
            !(int)$_SESSION[user::SESSION]['iduser']>0)              //Se há um id de usuário nessa sessão
           {
          
            //Não está Logado
            return true;
            
           }

        else //Está logado
        {
            
            //Verifica se é uma rota da administraçao, se a sessão contem os dados de um administrador
            if($inadmin===true && (bool)$_SESSION[User::SESSION]['inadmin']===true)
            {
               
                return false;
            }

            //Está logado mas não é um administrador
            else if($inadmin===false)
            {
                return false;
            }

            //Não está logado
            else
            {
              
                return true;
            }
        }
    }

    /**
     * Método responsável por realizar o Login do usuário
     * @param string $login - Login do usuário
     * @param string $password - Senha do usuário
     * @return mixed
     */
    public static function login($login, $password)
    {   

     
        $sql=new Sql();

        //Procura o usuário na base de dados
        $result= $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE a.deslogin = :login", array(
            ":login"=>$login
        ));

        //Verifica se foi encontrado o usuário
        if(count($result)===0)
        {
            throw new \Exception("Usuário inexistente ou senha invalida.");
        }

        //Guarda os dados do usuário encontrado
        $data= $result[0];
       
        //Verifica se a senha inserida corresponde a senha do usuário
        if(password_verify($password, $data['despassword']))
        {

            //Cria um objeto usuário
            $user= new User();

            $data['desperson']= utf8_encode($data['desperson']);

            //Preenche um array com os dados desse objeto
            $user->setData($data);

            //Cria uma sessão com os dados do usuário criado
            $_SESSION[User::SESSION] = $user->getValues();
        }
        else
        {
            throw new \Exception("Usuário inexistente ou senha invalida.");
        }

    }

    /**
     * Método responsável por verificar se o usuário administrador está logado e pode acessar o painel de administração
     * @param boolean $inadmin
     * @return void
     */
    public static function verifyLogin($inadmin=true)
    {
        //Verificação de administrador
        if(User::checkLogin($inadmin))
        {
            if($inadmin)
            {
                header("Location: /admin/login");
                exit;
            }
            else
            {
                header("Location: /login");
                exit;
            }
        }
    }

    /**
     * Método responsável por verificar se o usuario deslogou
     *
     * @return void
     */
    public static function verifyLogout()
    {
        //Se existe a sessão de usuário
        if(isset($_SESSION[user::SESSION]) )    
        {
            if((bool)$_SESSION[user::SESSION]['inadmin'] === true)
            {
                header("Location: /admin");
                exit;
            }
            else
            {
                header("Location: /");
                exit;
            }
        }
    }

    /**
     * Método responsável por realizar o Logout do Usuário
     * @return void
     */
    public static function logout()
    {
        //Faz a sessão do usuário ser nula
        $_SESSION[User::SESSION]= null;
    }

    /**
     * Método responsável por listar todos os usuários do sistema
     *
     * @return array
     */
    public static function listAll()
    {
        $sql= new Sql();
        $result =$sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) order by b.desperson");
        return $result;
    }

    /**
     * Método responsável por salvar registros no banco de dados
     *
     * @return void
     */
    public function save()
    {

        $sql= new Sql();

        $result=$sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",array(
            ":desperson"=>utf8_decode($this->getdesperson()),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>User::getPasswordHash($this->getdespassword()),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
            ));

            $this->setData($result[0]);

    }

    public static function getPasswordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT,['cost'=>12]);
    }

    /**
     * Método responsável pela busca e preenchimento de um objeto usuário do banco de dados
     *
     * @param integer $iduser
     * @return void
     */
    public function get($iduser)
    {
        $sql = new sql();
        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser =:iduser", 
        array(
            ":iduser"=>$iduser
        ));
        $this->setData($results[0]);
    }

    /**
     * Método responsável por atualizar um registro de usuário no banco de dados
     *
     * @return void
     */
    public function update()
    {       
        $sql= new Sql();
   
        //procedure de atualização
        $result=$sql->select("CALL sp_usersupdate_save (:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)"
        ,[
            ":iduser"=>$this->getiduser(),
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
        ]);

        if(count($result)>0)
        {
            $this->setData($result[0]);
            
            //Atualiza uma sessão com os dados do usuário modificados
            $_SESSION[User::SESSION] = $this->getValues();
        }
            
    }

    /**
     * Método responsável por excluir um registro do banco de dados
     *
     * @return void
     */
    public function delete()
    {
        $sql= new sql();
        $sql->query("CALL sp_users_delete(:iduser)", array(
            ":iduser"=>$this->getiduser()
        ));
    }

    /**
     * Método responsável por validar o email de quem vai recuperar a senha e chamar o envio de email
     *
     * @param string $email
     * @param boolean $inadmin
     * @return void
     */
    public static function getForgot($email, $inadmin = true)
    {
        $sql = new Sql();

        //Procura o usuario com esse email no sistema
        $results = $sql ->select(
            "SELECT *
            FROM tb_persons a
            INNER JOIN tb_users b USING(idperson)
            WHERE a.desemail = :email;",
            array(
                ":email"=>$email
            )
        );
        
        //Verifica se o usuario existe
        if(count($results)===0)
        {
           throw new \Exception("Não foi possivel recuperar a senha",1);
        }
        else
        {
            //Recebe os dados do usuário
            $data= $results[0];

            //Cria um registro de recuperação do usuário
            $recovery = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser,:desip)", array(
                ":iduser"=>$data['iduser'],
                ":desip"=>$_SERVER['REMOTE_ADDR']
            ));

            //Verifica se foi criado o registro da recuperação
            if(count($recovery)===0)
            {
                throw new \Exception("Não foi possivel recuperar a senha",1);
            }
            //Cria o link com o código de recuperação que será enviado por email e envia o email
            else
            {
                self::sendEmail($recovery[0],$inadmin,$data);
            }
        }

    }

    /**
     * Método responsável por enviar email
     *
     * @param array $dataRecovery
     * @param bool $inadmin
     * @param array $dataUser
     * @return void
     */
    private static function sendEmail($dataRecovery,$inadmin,$dataUser)
    {
        //Criptografia dos dados
        $code = openssl_encrypt($dataRecovery['idrecovery'],'AES-128-CBC',pack("a16",User::SECRET),0,pack('a16',User::SECRET_IV));
        $code = base64_encode($code);;

        if($inadmin===true)
        {
            //Link de recuperação de senha do administrador
            $link = "http://www.andrecommerce.com.br/admin/forgot/reset?code=$code";
        }

        else
        {
            //Link de recuperação de senha do usuário comum
            $link = "http://www.andrecommerce.com.br/forgot/reset?code=$code";
        }
        
        //Instanciando classe que realizara o envio do email
        $mailer = new Mailer($dataUser['desemail'],$dataUser['desperson'],"Redefinir a senha da HCODE Store", "forgot", array(
            "name"=>$dataUser['desperson'],
            "link"=>$link
        ));

        //Enviando Email de recuperação para o usuário
        $mailer->send();
    }

    /**
     * Método responsavel por validar o código de recuperação
     *
     * @param string $code
     * @return mixed
     */
    public static function validForgotDecrypt($code)
    {
    
        $idRecovery=openssl_decrypt(base64_decode($code),'AES-128-CBC',pack("a16",USER::SECRET),0,pack("a16",USER::SECRET_IV));
        
        $sql = new Sql();

        $result = $sql->select("SELECT * FROM tb_userspasswordsrecoveries a
                     INNER JOIN tb_users b USING(iduser)
                     INNER JOIN tb_persons c USING(idperson)
                     WHERE a.idrecovery = :idrecovery AND
                     a.dtrecovery is null AND
                     DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();"
                    , array(
                        ":idrecovery"=>$idRecovery
                    ));
        
        //Se não houver resultados o código é invalido
        if(count($result)===0)
        {
            throw new \Exception("Não foi possivel recuperar a senha",1);
        }
        else
        {
            return $result[0];
        }
    }

    /**
     * Método responsável por invalidar um código de recuperação ja usado
     *
     * @param integer $idRecovery
     * @return void
     */
    public static function setForgotUsed($idRecovery)
    {
        $sql= new Sql();

        $sql->query("UPDATE tb_userspasswordsrecoveries set dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
            ":idrecovery"=>$idRecovery
        ));
    }

    /**
     * Método responsável por trocar a senha do usuário no banco de dados
     *
     * @param string $password
     * @return void
     */
    public function setPassword($password)
    {
       
        $password = User::getPasswordHash($password);
        
        $sql = new Sql();
        $sql->query("UPDATE tb_users set despassword = :password WHERE iduser= :iduser", array(
            ':password'=>$password,
            ':iduser'=>$this->getiduser()
        ));

        $this->setdespassword($password);

        $_SESSION[User::SESSION] = $this->getValues();

    }

    /**
     * Método responsável por atualizar uma mensagem de erro
     *
     * @param string $error
     * @param string $message
     * @return void
     */
    public static function setError(&$error,$message)
    {
        $error=$message;
    }

    /**
     * Método responsável por retornar uma mensagem de erro
     *
     * @param string $error
     * @return string
     */
    public static function getError(&$error)
    {
        $message= (isset($error) && $error)? $error : '';

        User::clearError($error);

        return $message;
    }

    /**
     * Método responsável por limpar a mensagem de erro para que ela não seja exibida na tela
     *
     * @param string $error
     * @return void
     */
    public static function clearError(&$error)
    {
        $error = NUll;
    }

    /**
     * Método responsável por verificar se um usuário ja existe no banco de de dados
     *
     * @param string $login
     * @return bool
     */
    public static function checkLoginExist($login)
    {
        $sql = new Sql();

        $results = $sql->select('SELECT * FROM tb_users WHERE deslogin=:deslogin', [
            ':deslogin'=>$login
        ]);

        return (count($results)>0);
    }


    public function getOrders()
    {
         $sql = new Sql();

        $results=$sql->select
        ("SELECT a.idorder,a.idcart,a.iduser,a.idstatus,a.idaddress,a.vltotal,a.dtregister,
                 b.deszipcode,b.vlfreight,
                 d.desstatus,
                 e.desaddress,e.desdistrict,e.descity,e.desstate
                 
              FROM tb_orders a 
              JOIN tb_carts b ON b.idcart = a.idcart
              JOIN tb_users c ON c.iduser = a.iduser
              JOIN tb_ordersstatus d ON d.idstatus=a.idstatus
              JOIN tb_addresses e ON e.idaddress = a.idaddress
              JOIN tb_persons f ON f.idperson=a.iduser
              WHERE a.iduser=:iduser",[
                 ":iduser"=>$this->getiduser()
             ]);
        
        if(count($results)>0)
        {
            return $results;
        }
    }


}