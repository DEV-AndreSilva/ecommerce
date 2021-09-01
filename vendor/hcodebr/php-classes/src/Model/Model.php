<?php

namespace Hcode\Model;

//Classe genérica que auxiliar na criação dos getter e setter das outras classes
class Model
{
    //array que guarda os dados de um objeto de uma das classes
    private $values = [];

    
    /**
     * Método disparado ao invocar métodos indisponiveis no contexto do objeto,
     *
     * @param string $name - Nome do método que está sendo chamado
     * @param array $arguments - Parametros passados no método
     */
    public function __call($name, $arguments)
    {
        //descobrindo qual método o usuário deseja acessar
        $method= substr($name,0,3);
        //descobrindo qual campo o usuário deseja acessar
        $fieldName = substr($name,3,strlen($name));

        //retornando a ação desejada de acordo com o método passado
        switch($method)
        {
            case 'get':
               return (isset($this->values[$fieldName]))? $this->values[$fieldName]: null;
            break;

            case 'set':
                $this->values[$fieldName]=$arguments[0];
            break;
        }
    }

    /**
     * Método responsável por receber os dados do banco e preencher os valores do array $values
     * @param array $data - array que recebe os valores que preencheram os atributos do objeto
     */
    public function setData($data= array())
    {
        foreach($data as $key=>$value)
        {
            $this->{'set'.$key}($value);
        }
    }

    /**
     * Método responsável por retornar os dados do objeto
     * @return void
     */
    public function getValues()
    {
        return $this->values;
    }

}