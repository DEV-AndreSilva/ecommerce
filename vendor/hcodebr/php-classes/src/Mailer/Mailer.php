<?php
 namespace Hcode\Mailer;

//inclusão dos arquivos de autoload e credenciais do email
require_once 'vendor/autoload.php';
require_once 'config.php';
 
use Rain\Tpl;

 class Mailer
 {

    private $mail;

    /**
     * Método construtor da classe de email
     *
     * @param string $toAddress -- Endereço destinatário
     * @param string $toName -- Nome destinatário 
     * @param string $Subject -- Assunto do email
     * @param string $tplName -- Nome do template que será renderizado com Rain tpl
     * @param array $data -- Dados que preencherão o email durante a renderização
     */
    public function __construct($toAddress,$toName,$Subject,$tplName,$data= array())
    {
        //Utilizando da biblioteca do PHPmailer para estudar envio de Emails com PHP


        //Configuração do TPL, local das paginas e local onde sera salvo o cache da página renderizada
        $config = array(
        "tpl_dir"=>$_SERVER['DOCUMENT_ROOT']."/views/email/",
        "cache_dir"=>$_SERVER['DOCUMENT_ROOT']."/cache/",
        "debug"=>false
        );
        
        //Configurando o TPL
        tpl::configure($config);
                
        //Instanciando objeto da classe TPL
        $tpl = new Tpl;

        //Criando as variaveis que preencherão á pagina que será enviado no email
        foreach($data as $key =>$value)
        {
            $tpl->assign($key,$value);
        }

        //Desenhando a página 
        $html = $tpl->draw($tplName,true);

        //Instanciando a classe que controla os emails
        $this->mail = new \PHPMailer();

        //Tell PHPMailer to use SMTP
        $this->mail->isSMTP();

        //Desabilitando Debug
        $this->mail->SMTPDebug = 0;

        //Hostname do servidor
        $this->mail->Host = 'smtp.gmail.com';


        //Definindo número da porta SMTP
        $this->mail->Port = 587;

        //Definindo tipo de mecanisnmo de criptografia
        $this->mail->SMTPSecure = "tls";

        //Definindo se devemos usar autenticação SMTP
        $this->mail->SMTPAuth = true;

        //Definindo email do usuário
        $this->mail->Username = USER;

        //Definindo senha do usuário
        $this->mail->Password = PASSWORD;

        //Definindo origem do email
        $this->mail->setFrom(USER, 'Suporte Hcode Store -Andre');


        //Definindo quem recebera o email
        $this->mail->addAddress($toAddress, $toName);

        //Definindo assunto do email
        $this->mail->Subject = utf8_decode(" $Subject - André");

        //Convertendo arquivo HTML como corpo do email
        $this->mail->msgHTML($html);

        //Altbody do email
        $this->mail->AltBody = 'Esse é o altbody';

        //Anexando arquivo de imagem
        //$this->mail->addAttachment('images/phpmailer_mini.png');
    }

    public function send()
    {
                //Enviando email
                if ($result = !$this->mail->send())
                {
                    echo 'Falha ao enviar email: ' . $this->mail->ErrorInfo;
                } 
                else
                {
                    echo 'Email enviado!';
                }
                return $result;
    }
 }