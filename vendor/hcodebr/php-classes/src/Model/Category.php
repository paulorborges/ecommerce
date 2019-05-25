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
        /* Após salvar uma categoria, necessário chamar o método para atualização das categorias na página principal da loja */
        Category::updateFile();
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
        /* Após deletar uma categoria, necessário chamar o método para atualização das categorias na página principal da loja */
        Category::updateFile();
    }
    /* Método para atualizar menu de categorias na página principal da loja. Fica no Rodapé */
    public static function updateFile(){
        /* Cerrega as caterias atuais do banco de dados */
        $categories = Category::listAll();
        /* Montar o HTML com as informações do banco de dados */
        /* Cria variavel do tipo array */
        $html = [];
        /* Através do foreach, é possível selecionar cada uma das informações e fazer um push diretamente na variável array
        para preenchimento dos campos */
        foreach ($categories as $row) {
            array_push ($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
        }
        /* com o array pronto, precisamos salvar o arquivo com as informações, para isso, utiliza o fileputcontents
        conforme abaixo. O $SERVER é uma variavel de ambiente que entre outras informações possui o diretorio onde o projeto
        foi salvo, nesse caso, a variavel superglobal é a document_root. Como a informação a ser adicionada precisa ser uma string
        utiliza-se o implote para buscar os dados do array.*/
        file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."categories-menu.html", 
        implode('',$html));
    }
    /* Método para listar todos os produtos. Recebe um booleano para facilitar análise dos produtos relacionados ou não
    com determinada categoria */
    public function getProducts ($related = true){
        $sql = new Sql();
        if($related === true){
            return $sql -> select("SELECT * FROM tb_products 
                WHERE idproduct IN (
                    SELECT a.idproduct
                    FROM tb_products a
                    INNER JOIN tb_productscategories b 
                    ON a.idproduct = b.idproduct
                    WHERE b.idcategory = :idcategory
                );
            ", [
                ':idcategory'=>$this->getidcategory()
            ]);
        } else {
            return $sql -> select("SELECT * FROM tb_products 
                WHERE idproduct NOT IN (
                    SELECT a.idproduct
                    FROM tb_products a
                    INNER JOIN tb_productscategories b 
                    ON a.idproduct = b.idproduct
                    WHERE b.idcategory = :idcategory
                );
            ", [
                ':idcategory'=>$this->getidcategory()
            ]);
        }
    }
    /* Método para acicionar produtos em uma categoria */
    public function addProduct(Products $product){
        $sql = new Sql();
        /* Como não é necessário nenhum retorno, podemos utilizar a função query diretamente */
        $sql -> query("INSERT INTO tb_productscategories (idcategory, idproduct)
            VALUES (:idcategory,:idproduct)
        ", [
            ':idcategory'=>$this->getidcategory(),
            ':idproduct'=>$product->getidproduct()
        ]);
    }
    /* Método para remover produtos de uma categoria */
    public function removeProduct(Products $product){
        $sql = new Sql();
        /* Como não é necessário nenhum retorno, podemos utilizar a função query diretamente */
        $sql -> query("DELETE FROM tb_productscategories 
            WHERE idcategory = :idcategory
            AND idproduct = :idproduct
        ", [
            ':idcategory'=>$this->getidcategory(),
            ':idproduct'=>$product->getidproduct()
        ]);
    }
}   

?>