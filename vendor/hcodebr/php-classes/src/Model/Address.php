<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model\User;

class Address extends Model
{

    public static function getCep($nrcep)
    {
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

        $data = json_decode($result,true);

        //Finalizando variavel CURL
        curl_close($ch);

        return $data;
    }

    public function loadFromCEP($nrcep)
    {
        $data =  Address::getCep($nrcep);

        if(!isset($data['erro']))
        {
            $this->setdesaddress($data['logradouro']);
            $this->setdescomplement($data['complemento']);
            $this->setdesdistrict($data['bairro']);
            $this->setdescity($data['localidade']);
            $this->setdesstate($data['uf']);
            $this->setdescountry('Brasil');
            $this->setnrzipcode($nrcep);
        }

    }
   

}


