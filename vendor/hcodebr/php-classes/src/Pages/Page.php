<?php

namespace Hcode\Pages;
use Rain\Tpl;

class Page
{
    private $tpl; //Objeto que guarda a instancia do TPL
    private $options=[]; //Array que guarda as opções(dados que serão renderizados)
    private $defaults = [
        "header"=>true,
        "footer"=>true,
        "data"=>[]
    ]; //Opções padrões

    /**
     * Método construtor das Páginas da Aplicação
     *
     * @param array $opts - Dados a ser renderizados
     */
    public function __construct($opts= array(),$tpl_dir="/views/site/")
    {
        //Mesclando array padrão com os parametros recebidos
        $this->options = array_merge($this->defaults, $opts);

        //Configuração do TPL, local das paginas e local onde sera salvo o cache da página renderizada
        $config = array(
            "tpl_dir"=>$_SERVER['DOCUMENT_ROOT'].$tpl_dir,
            "cache_dir"=>$_SERVER['DOCUMENT_ROOT']."/cache/",
            "debug"=>false
        );

        //Configurando o TPL
        tpl::configure($config);
        
        //Instanciando objeto da classe TPL
        $this->tpl = new Tpl;

        //Percorrendo parametros recebidos e criando as variaveis com dados para o TPL
        $this->setData($this->options["data"]);

        //Desenhando cabeçalho da página se o header for true
       if ($this->options['header']) $this->tpl->draw("header");
    }

    /**
     * Método responsável por criar as variaveis que serão utilizadas no template
     *
     * @param array $data
     * @return void
     */
    private function setData($data=array())
    {
        //Percorre o array e para cada resultado cria a variavel e associa o valor a ela
        foreach ($data as $key =>$value)
        {
            $this->tpl->assign($key,$value);
        }
    }

    public function setTpl($name, $data = array(), $returnHTML= false)
    {
        $this->setData($data);
        
        return $this->tpl->draw($name,$returnHTML);
    }

    /**
     * Método de destruição, chamado quando se destroy o objeto
     */
    public function __destruct()
    {
        //Desenhando rodapé da página se o footer for true
        if ($this->options['footer']) $this->tpl->draw("footer");
    }
}

