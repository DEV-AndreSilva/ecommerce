<?php
namespace Hcode\Model;

use \Hcode\DB\Sql;

class Category extends Model
{
    /**
     * Método responsável por listar todos as categorias de produtos do sistema
     *
     * @return array
     */
    public static function listAll()
    {
        $sql= new Sql();
        $result =$sql->select("SELECT * from tb_categories  order by descategory");
        return $result;
    }

    /**
     * Método responsável por criar ou atualizar um registro de categoria no banco de dados
     *
     * @return void
     */
    public function save()
    {
        
        $sql= new Sql();

        $result=$sql->select("CALL sp_categories_save(:idcategory, :descategory)",array(
            ":idcategory"=>$this->getidcategory(),
            ":descategory"=>$this->getdescategory()
            ));

            $this->setData($result[0]);

            Category::updateFile();
    }

    /**
     * Método responsável por preencher as informações de um objeto categoria de acordo com o id indicado 
     *
     * @param int $idCategory
     * @return void
     */
    public function get($idCategory)
    {
        $sql = new Sql();

        $result = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", array(
            ":idcategory"=>$idCategory
        ));

        $this->setData($result[0]);

    }

    /**
     * Método resposável por excluir uma categoria do banco de dados
     *
     * @return void
     */
    public function delete()
    {
        $sql = new sql();
        $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", array(
            ":idcategory"=>$this->getidcategory()
        ));

        Category::updateFile();
    }

    /**
     * Método responsável por construir dinamicamente os links do menu de categorias na pagina views/categories-menu.html
     *
     * @return void
     */
    public static function updateFile()
    {
        $categories= Category::listAll();

        $html = array();

        foreach($categories as $row)
        {
            array_push($html,"<li><a href='/categories/".$row["idcategory"]."'>".$row["descategory"]."</a></li>");
        }

        file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."categories-menu.html",implode("",$html));
    }

}