<?php

namespace Hcode\Model;

use Exception;
use \Hcode\Model\Model;
use \Hcode\DB\Sql;

class User extends Model
{
    const SESSION = "User";

    /**
     * Método responsável por realizar o Login do usuário
     * @param string $login - Login do usuário
     * @param string $password - Senha do usuário
     * @return void
     */
    public static function login($login, $password)
    {
        $sql=new Sql();

        //Procura o usuário na base de dados
        $result= $sql->select("select * from tb_users where deslogin = :LOGIN", array(
            ":LOGIN"=>$login
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
            //Preenche um array com os dados desse objeto
            $user->setData($data);

            //Cria uma sessão com os dados do usuário criado
            $_SESSION[User::SESSION] = $user->getValues();
            return $user;
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
        if(!isset($_SESSION[user::SESSION]) ||                      //Se a sessão existe
           !$_SESSION[user::SESSION] ||                             //Se a sessão não está vazia ou é nula
           !(int)$_SESSION[user::SESSION]['iduser']>0 ||            //Se não existe um id de usuário
           (bool)$_SESSION[User::SESSION]['inadmin'] !== $inadmin)  //Se o usuário é um administrador
        {
            header("Location: /admin/login");
            exit;
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

}