<?php

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\DB\Sql;

class Category extends Model{  

    /* Método para listar categorias */
    public static function listAll(){
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
    }

    /* Método para salvar a categoria */
    public function save(){
        $sql = new Sql();
        /* chamada da procedure. os campos devem ser enviados na mesma ordem da procedure */
        $results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
            ":idcategory"=>$this->getidcategory(),
            ":descategory"=>$this->getdescategory()
        ));
        /* apesar de ser um array, é necessário apenas a primeira posição, ou seja, zero. Sendo assim, é enviado para o 
        setData a posição zero do results. */
        $this->setData($results[0]);
    }
    /* Método que verifica se o id ainda está presente no banco de dados */
    public function get($idcategory){
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory",[
            ':idcategory'=>$idcategory
        ]);
        $this->setData($results[0]);
    }
    /* Método que apaga o registro no banco */
    public function delete(){
        $sql = new Sql();
        /* Como o objeto já estará carregado, não é necessário enviar o ID por parametro. No momento da exclusão no entanto
        é necessário que se passe o código para não apagar todos os registros do banco. Nesse caso, o método getidcategory 
        pode ser utilizado. Como não seria uma instrução de seleção, nesse caso basta fazer uma query direto para execução
        em banco. */
        $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory",[
            ':idcategory'=>$this->getidcategory()
        ]);
    }
}   

?>