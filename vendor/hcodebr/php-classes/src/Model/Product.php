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
     * Método resposável por criar a lista de produtos que preenchera as informações do site
     *
     * @param array $list
     * @return array
     */
    public static function checkList($list)
    {
        //Adiciona a lista os caminhos das imagens dos produtos
        foreach($list as &$row)
        {
            $p = new Product();
            $p->setData($row);
            $row= $p->getValues();
        }
     
        return $list;
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

        //Se a imagem existir mudo a URL para o caminho da imagem
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
        return $this->setdesphoto($url);
    }

    /**
     * Método responsável por retornar os atributos do objeto produto com o caminho da foto
     *
     * @return array
     */
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

    /**
     * Método responsável pela busca de um produto pela sua URL
     *
     * @param string $desurl
     * @return array
     */
    public function getFromUrl($desurl)
    {
        $sql = new Sql();

        $row = $sql->select('SELECT * FROM tb_products WHERE desurl=:desurl LIMIT 1 ', [
            ":desurl"=>$desurl
        ]);

    
        $this->setData($row[0]);
    }

    /**
     * Método responsável pela busca das categorias de um produto
     *
     * @return array
     */
    public function getCategories()
    {
        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_categories a 
                                INNER JOIN tb_categoriesproducts b
                                ON a.idcategory = b.idcategory
                                WHERE b.idproduct=:idproduct", [
                                    ":idproduct"=>$this->getidproduct()
                                ]);
    }


}