<?php

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\DB\Sql;

class Products extends Model{  

    /* Método para listar produtos */
    public static function listAll(){
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");
    }

    /* Método para salvar o produto */
    public function save(){
        $sql = new Sql();
        /* chamada da procedure. os campos devem ser enviados na mesma ordem da procedure */
        $results = $sql->select("CALL sp_products_save
            (:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
            ":idproduct"=>$this->getidproduct(),
            ":desproduct"=>$this->getdesproduct(),
            ":vlprice"=>$this->getvlprice(),
            ":vlwidth"=>$this->getvlwidth(),
            ":vlheight"=>$this->getvlheight(),
            ":vllength"=>$this->getvllength(),
            ":vlweight"=>$this->getvlweight(),
            ":desurl"=>$this->getdesurl()
        ));
        /* apesar de ser um array, é necessário apenas a primeira posição, ou seja, zero. Sendo assim, é enviado para o 
        setData a posição zero do results. */
        $this->setData($results[0]);
    }
    /* Método que verifica se o id ainda está presente no banco de dados */
    public function get($idproduct){
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct",[
            ':idproduct'=>$idproduct
        ]);
        /* apesar de ser um array, é necessário apenas a primeira posição, ou seja, zero. Sendo assim, é enviado para o 
        setData a posição zero do results. */
        $this->setData($results[0]);
    }
    /* Método que apaga o registro no banco */
    public function delete(){
        $sql = new Sql();
        /* Como o objeto já estará carregado, não é necessário enviar o ID por parametro. No momento da exclusão no entanto
        é necessário que se passe o código para não apagar todos os registros do banco. Nesse caso, o método getidcategory 
        pode ser utilizado. Como não seria uma instrução de seleção, nesse caso basta fazer uma query direto para execução
        em banco. */
        $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct",[
            ':idproduct'=>$this->getidproduct()
        ]);
    }
    /* Método para verificação de uma foto */
    public function checkPhoto(){
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . 
            DIRECTORY_SEPARATOR . "resource" . 
            DIRECTORY_SEPARATOR . "site" . 
            DIRECTORY_SEPARATOR . "img" . 
            DIRECTORY_SEPARATOR . "products" .
            DIRECTORY_SEPARATOR . $this->getidproduct() . ".jpg"
        )){
            /* No caso do return abaixo, se trata de uma URL e não do endereço de diretorios do sistema operacional. Nesse
            caso precisa ser utilizado a barra diretamente */
            $url = "/resource/site/img/products/" . $this->getidproduct() . ".jpg";
        } else {
            /* Para o caso da foto nao existir, retorna uma foto padrão */
            $url = "/resource/site/img/product.jpg";
        }
        /* Utilizando o retorno com o setdesphoto, o objeto passa a conter a imagem dentro de seus parametros */
        return $this->setdesphoto($url);
    }
    /* Método para reescrita do getValues. Se a foto foi passada por parametro no metodo checkPhoto, qual o motivo da 
    reescrtia do getValues? A vantagem é realizar o checkPhoto apenas nos casos onde getValues da classe produto for chamado
    e não a todo momento que o getValues for chamado. */
    public function getValues(){
        /* Verifica se existe uma foto. Caso não exista, carrega uma foto default para evitar o erro */
        $this->checkPhoto();
        /* parent::getValues vai executar todas as funcionalidades que o método da classe pai executa */
        $values = parent::getValues();
        /* Agora adicionamos a última coluna que estava faltando ao método da classe pai */
        return $values;
    }    
    /* Método para upload das fotos */
    public function setPhoto($file){
        /* Apesar do padrão da imagem ser JPG, o usuário pode acabar fazendo upload de outro formato de imagem. Para evitar
        bloquear o processo para aceitar apenas JPG, podemos utilizar a biblioteca do GD para converter o arquivo em uma 
        imagem JPG. */
        /* Para o explode abaixo, procura-se o ponto para identificar a extensão */
        $extension = explode('.', $file['name']);
        /* Pega novamente a variável e ajusta a extensão para a última posição do array */
        $extension = end($extension);
        switch ($extension){
            case "jpg":
            case "jpeg":
                /* Função imagecreatefromjpeg é uma função do GD e o tmp_name é o campo do array com o nome 
                temporário do arquivo que está no servidor.*/
                $image = imagecreatefromjpeg($file["tmp_name"]);
            break;
            case "gif":
                $image = imagecreatefromgif($file["tmp_name"]);
            break;
            case "png":
                $image = imagecreatefrompng($file["tmp_name"]);
            break;
        }
        /* Agora que a imagem está dentro do imagem, podemos garantir que é uma imagem. Deste ponto em diante, podemos
        vincular como uma imagem jpg e mover para o diretorio onde ficará armazenada em definitivo */
        $dest = $_SERVER['DOCUMENT_ROOT'] . 
            DIRECTORY_SEPARATOR . "resource" . 
            DIRECTORY_SEPARATOR . "site" . 
            DIRECTORY_SEPARATOR . "img" . 
            DIRECTORY_SEPARATOR . "products" .
            DIRECTORY_SEPARATOR . $this->getidproduct() . ".jpg";
        imagejpeg($image, $dest);
        imagedestroy($image);
        /* Para que o data fique carregado, segue abaixo */
        $this->checkPhoto();
    }    
}   

?>