<?php

    namespace Hcode\Model;

    use \Hcode\DB\Sql;
    use \Hcode\Model;
    use \Hcode\Model\Cart;

    class Order extends Model{

        const ERROR = "Order-Error";
        const SUCCESS = "Order-Success";

        /* Método para salvar disparar a procedure para gravar os dados */
        public function save(){
            $sql = new Sql();
            $results = $sql -> select("CALL sp_orders_save (
                :idorder,
                :idcart,
                :iduser,
                :idstatus,
                :idaddress,
                :vltotal
            )",[
                ':idorder'=>$this->getidorder(),
                ':idcart'=>$this->getidcart(),
                ':iduser'=>$this->getiduser(),
                ':idstatus'=>$this->getidstatus(),
                ':idaddress'=>$this->getidaddress(),
                ':vltotal'=>$this->getvltotal()
            ]);
            if(count($results) > 0){
                $this->setData($results[0]);
            }
        }
        /* Método para recuperar as informações da ordem de compra/pedido */
        public function get($idorder){
            $sql = new Sql();
            $results = $sql -> select("SELECT *
                FROM tb_orders a
                INNER JOIN tb_ordersstatus b
                /* USING é utilizado apenas com mysql, se for SQLServer, utilizar o ON */
                USING (idstatus) 
                INNER JOIN tb_carts c
                USING (idcart)
                INNER JOIN tb_users d
                ON d.iduser = a.iduser 
                INNER JOIN tb_addresses e
                USING (idaddress)
                INNER JOIN tb_persons f
                ON f.idperson = d.idperson
                WHERE a.idorder = :idorder
                ",[
                    ':idorder'=>$idorder
                ]
            );
            if (count($results) > 0){
                $this->setData($results[0]);
            }
        }
        /* Método para listar todos os pedidos */
        public static function listAll(){
            $sql = new Sql();
            return $sql -> select("SELECT *
                FROM tb_orders a
                INNER JOIN tb_ordersstatus b
                /* USING é utilizado apenas com mysql, se for SQLServer, utilizar o ON */
                USING (idstatus) 
                INNER JOIN tb_carts c
                USING (idcart)
                INNER JOIN tb_users d
                ON d.iduser = a.iduser 
                INNER JOIN tb_addresses e
                USING (idaddress)
                INNER JOIN tb_persons f
                ON f.idperson = d.idperson
                ORDER BY a.dtregister DESC
            ");
        }
        /* Método para deletar um pedido */
        public function delete(){
            $sql = new Sql();
            /* O metodo de deletar não precisa receber o parametro do id do pedido uma vez que vai capturar essa informação
            diretamente do objeto com o getidorder */
            $sql -> query("DELETE 
                FROM tb_orders
                WHERE idorder = :idorder
                ", [
                    ':idorder'=>$this->getidorder()
                ]
            );
        }
        /* Método para buscar as informações do carrinho. Esse método vai retornar uma instância da classe Cart, portanto, o :Cart */
        public function getCart():Cart{
            $cart = new Cart();
            $cart -> get((int)$this->getidcart());
            return $cart;
        }
        /* Método para setar a mensagem de erro */
        public static function setError($msg){
            $_SESSION[Order::ERROR] = $msg;
        }
        /* Método para recuperar a mensagem de erro */
        public static function getError(){
            /* Verifica se a sessão foi definida e se possui algum conteúdo */
            $msg = (isset($_SESSION[Order::ERROR]) && $_SESSION[Order::ERROR]) ? $_SESSION[Order::ERROR] : '';
            Order::clearError();
            return $msg;
        }
        /* Método para limpar a mensagem de erro */
        public static function clearError(){
            $_SESSION[Order::ERROR] = NULL;    
        }
        /* Método para setar uma mensagem */
        public static function setSuccess($msg){
            $_SESSION[Order::SUCCESS] = $msg;
        }
        /* Método para recuperar a mensagem */
        public static function getSuccess(){
            /* Verifica se a sessão foi definida e se possui algum conteúdo */
            $msg = (isset($_SESSION[Order::SUCCESS]) && $_SESSION[Order::SUCCESS]) ? $_SESSION[Order::SUCCESS] : '';
            Order::clearSuccess();
            return $msg;
        }
        /* Método para limpar a mensagem */
        public static function clearSuccess(){
            $_SESSION[Order::SUCCESS] = NULL;    
        }
    }
?>