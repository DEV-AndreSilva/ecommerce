<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model\User;

class Address extends Model
{
    const ERROR="ADDRESS_ERROR";
    
    /**
     * Método responsável por atualizar uma mensagem de erro
     *
     * @param string $error
     * @param string $message
     * @return void
     */
    public static function setError($typeError,$message)
    {
        $_SESSION[$typeError]=$message;
    }

    /**
     * Método responsável por retornar uma mensagem de erro
     *
     * @param string $error
     * @return string
     */
    public static function getError($typeError)
    {
        
        $message= (isset($_SESSION[$typeError]) && $_SESSION[$typeError])? $_SESSION[$typeError] : '';
        Cart::clearError($typeError);
        return $message;
    }

    /**
     * Método responsável por limpar a mensagem de erro para que ela não seja exibida na tela
     *
     * @param string $typeError
     * @return void
     */
    public static function clearError($typeError)
    {
        $_SESSION[$typeError]=null;
    }


    /**
     * Método responsável pela consulta no webservice dos correios para pegar as informaçoes de endereço
     *
     * @param string $nrcep
     * @return array
     */
    public static function getCep(string $nrcep)
    {
        //Tira o - caso tenha sido informado
        $nrcep = str_replace("-","", $nrcep);

        //Iniciando variavel CURL
        $ch = curl_init();

        //passando url da consulta
        curl_setopt($ch,CURLOPT_URL,"https://viacep.com.br/ws/$nrcep/json/");

        //Parametro de espera retorno do resultado
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //Não exigir autenticação de SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);

        //Recebendo resultado da busca em Json
        $result = curl_exec($ch);

        //Retornando o resultado dessa consulta como um array
        $data = json_decode($result,true);

        //Finalizando variavel CURL
        curl_close($ch);

        return $data;
    }

    /**
     * Método responsável por preencher o objeto endereço com as informações de endereço do cliente
     *
     * @param string $nrcep
     * @return void
     */
    public function loadFromCEP(string $nrcep)
    {
        $this->setdesaddress("");
        $this->setdescomplement("");
        $this->setdesdistrict("");
        $this->setdescity("");
        $this->setdesstate("");
        $this->setdescountry("");
        $this->setdeszipcode("");

        $data =  Address::getCep($nrcep);

        //Verifica se a consulta retornou um erro
        if(isset($data))
        {
            if(isset($data['erro']))
            {
                Address::setError(Address::ERROR, "Endereço invalido, digite outro CEP para finalizar a compra");
            }
            else
            {
                $this->setdesaddress($data['logradouro']);
                $this->setdescomplement($data['complemento']);
                $this->setdesdistrict($data['bairro']);
                $this->setdescity($data['localidade']);
                $this->setdesstate($data['uf']);
                $this->setdescountry('Brasil');
                $this->setdeszipcode($nrcep);
            }
        }
        else 
        {
            Address::setError(Address::ERROR, "Endereço invalido, Digite um número de CEP");
        }
     
    }

    public function save()
    {
        $sql = new sql();

        $results = $sql->select("CALL sp_addresses_save (:idaddress, :idperson, :desaddress, :descomplement, :descity, :desstate, :descountry, :deszipcode, :desdistrict)",
        [
            ":idaddress"=>$this->getidaddress(),
            ":idperson"=>$this->getidperson(),
            ":desaddress"=>utf8_decode($this->getdesaddress()),
            ":descomplement"=>utf8_decode($this->getdescomplement()),
            ":descity"=>utf8_decode($this->getdescity()),
            ":desstate"=>utf8_decode($this->getdesstate()),
            ":descountry"=>utf8_decode($this->getdescountry()),
            ":deszipcode"=>$this->getdeszipcode(),
            ":desdistrict"=>utf8_decode($this->getdesdistrict())
        ]);

        if(count($results)>0)
        {
            $this->setData($results[0]);
        }

    }

    
   

}


