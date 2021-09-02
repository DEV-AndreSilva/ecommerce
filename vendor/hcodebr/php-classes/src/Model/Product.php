<?php

namespace Hcode\Model;

use GdImage;
use Hcode\DB\Sql;

class Product extends Model
{
    /**
     * Método responsável por retornar todos os produtos do banco de dados
     *
     * @return array
     */
    public static function listAll()
    {
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");
    }

      /**
     * Método responsável pela busca de um produto do banco de dados, preenchendo id e URL do objeto produto
     *
     * @param integer $idproduct
     * @return void
     */
    public function get($idproduct)
    {
        $sql = new sql();
        $results = $sql->select("SELECT * FROM tb_products WHERE idproduct =:idproduct", 
        array(
            ":idproduct"=>$idproduct
        ));
        $this->setData($results[0]);
    }


    /**
     * Método responsável por criar um novo produto ou atualizar caso ele ja exista, utilizando os atributos do objeto
     *
     * @return void
     */
    public  function save()
    {
        $sql = new sql();

        $results = $sql->select("CALL sp_products_save (:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
            ":idproduct"=>$this->getidproduct(),
            ":desproduct"=>$this->getdesproduct(),
            ":vlprice"=>$this->getvlprice(),
            ":vlwidth"=>$this->getvlwidth(),
            ":vlheight"=>$this->getvlheight(),
            ":vllength"=>$this->getvllength(),
            ":vlweight"=>$this->getvlweight(),
            ":desurl"=>$this->getdesurl()
        ));

        $this->setData($results[0]);
    }

    /**
     * Método responsável por excluir um registro de produto do banco de dados
     *
     * @return void
     */
    public function delete()
    {
        $sql= new sql();
        $sql->query("DELETE FROM  tb_products WHERE idproduct = :idproduct", array(
            ":idproduct"=>$this->getidproduct()
        ));
    }

    /**
     * Método responsável por verificar se a imagem existe e criar a URL do caminho dessa imagem
     *
     * @return void
     */
    public function checkPhoto()
    {
        //URL padrão
        $url= "/resources/site/img/products/product.jpg";

        //Se a imagem existir mudo o  URL para o caminho da imagem
        if(file_exists($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.
        "resources".DIRECTORY_SEPARATOR.
        "site".DIRECTORY_SEPARATOR.
        "img".DIRECTORY_SEPARATOR.
        "products".DIRECTORY_SEPARATOR.
        $this->getidproduct().".jpg"))
        {
            $url= "/resources/site/img/products/".$this->getidproduct().".jpg";
        }

        //cria o atributo desphoto do produto
        return $this->setdesurl($url);
    }

    public function getValues()
    {
        $this->checkPhoto();

        $values = parent::getValues();

        return $values;
    }

    /**
     * Método responsável por atualizar uma imagem na pasta resources 
     *
     * @param array $file
     * @return void
     */
    public function updatePhoto($file)
    {
        $extension = explode('.',$file['name']);
        $extension = end($extension);
        
        switch($extension)
        {
            case "jpg":
            case "jpeg":
                $image= imagecreatefromjpeg($file['tmp_name']);
            break;

            case "gif":
                $image = imagecreatefromgif($file['tmp_name']);
            break;

            case "png":
                $image = imagecreatefrompng($file['tmp_name']);
            break;

        }

        //Caminho e nome da imagem
        $fileName=$_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.
        "resources".DIRECTORY_SEPARATOR.
        "site".DIRECTORY_SEPARATOR.
        "img".DIRECTORY_SEPARATOR.
        "products".DIRECTORY_SEPARATOR.
        $this->getidproduct().".jpg";

        //Cria a imagem
        imagejpeg($image,$fileName);

        //Destroi o arquivo temporário de imagem
        imagedestroy($image);

        //Verifica se a imagem existe e atualiza seu caminho
        $this->checkPhoto();

    }
}