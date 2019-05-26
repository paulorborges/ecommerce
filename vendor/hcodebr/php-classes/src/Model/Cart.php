<?php

    namespace Hcode\Model;

    use \Hcode\Model;
    use \Hcode\Mailer;
    use \Hcode\DB\Sql;
    use \Hcode\Model\User;

    class Cart extends Model{
        /* Quando o usuário insere o primeiro item no carrinho, ele basicamente está criando o carrinho. A partir do momento que
        ele exclui ou inclui novos, ao inves do insert, precisamos utilizar o update. Desta forma, como vamos saber que carrinho
        alterar? Para isso, necessário trabalhar com a sessão do usuário e dessa forma, enquanto a sessão estiver ativa, 
        utilizaremos sempre o mesmo carrinho que foi instanciado na sessão vigente. Para isso, vamos utilizar uma constante
        para permitir a seleção em vários lugares diferentes do projeto */
        const SESSION = "Cart";
        /* Método para verificar se é necessáiro inserir um carrinho novo, se devemos apenas editar o carrinho existente, 
        se a sessão foi perdida por causa do tempo mas se eu ainda tenho o ID do carrinho em questão, etc. */
        public static function getFromSession(){
            $cart = new Cart();
            /* Primeiro, devemos verificar se o carrinho já está na sessão. Isset(session(cartSession)) = verifica se
            a sessão (nome da sessão) do carrinho já existe. Caso exista, verifica-se se o ID da sessão já foi definido,
            uma vez que apesar da sessão ter sido definida, pode ser que ela esteja vazia. */
            if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0){
                /* Caso a sessão exista e o id da sessão seja maior que zero, vamos carregar o carrinho */
                $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
            } else {
                /* Se a sessão ou o id não existirem, primeiro verificarmos se a partir do sessionId conseguimos recuperar
                o carrinho */
                $cart -> getFromSessionID();
                /* Caso o retorno do getFromSessionID esteja vazio, precisamos carregar um novo carrinho. Dessa forma, podemos
                testar se o retorno não for maior que zero, ou seja, ele não conseguiu recuperar nenhuma informação */
                if(!(int)$cart->getidcart() > 0){
                    /* Nesse ponto, não foi retornado nenhuma informação e criamos um novo carrinho */
                    $data = [
                        /* função session_id pertence ao proprio PHP */
                        'dessessionid'=>session_id()
                    ];
                    /* Para verificar se o usuário está logado, posso utilizar o método checklogin. Como o processo em 
                    questão é uma função do site e não da administração, o parâmetor a ser enviado é false conforme 
                    abaixo. Se o retorno da função for true quer dizer que está loado e entra no if para realização das
                    funções */
                    if(User::checkLogin(false)){
                        /* Nesse ponto, o usuário pode ser desconhecido e estar apenas manipulando o carrinho, portanto, 
                        por hora não seria obrigatório identificar o usuário logado mas seria muito interessante. Se 
                        eu souber que usuário está logado, posso por exemplo enviar um e-mail pra ele avisando que ele 
                        esqueceu de finalizar a compra e enviar os itens do carrinho. Para isso, posso tentar identificar 
                        que usuário está logado utilizado o seguinte código. */
                        $user = User::getFromSession();
                        /* Como o usuári está logado, posso nesse ponto enviar para o data o id do usuário */
                        $data['iduser'] = $user->getiduser();
                    }
                    /* Após as validações do usuário, fica pendente passar para o carrinho quais são os dados gravados
                    no array data e dessa forma, ou ida da sessão e um usuário válido ou o id da sessão */
                    $cart->setData($data);
                    /* Após carregar as informações no objeto cart, precisamos salvar as mesmas no banco */
                    $cart->save();
                    /* Como estamos falando de um carrinho novo, precisamos colocar na sessão porque da próxima vez que 
                    passar por esse método o mesmo vai pegar as informações da sessão e não criar outro carrinho */
                    $cart -> setToSession();
                }
            }
            /* Após as validações, retorna-se o carrinho existe ou um novo carrinho */
            return $cart;
        }
        /* Metodo para setar o objeto cart na sessão de atual. Como nesse caso vamos utilizar a variável this, o método
        não pode ser estático */
        public function setToSession(){
            $_SESSION[Cart::SESSION] = $this->getValues();
        }

        /* Método para carregar o id da sessão. Esse método não precisa de parametro uma vez que o PHP possui a função que
        realiza essa ação (sessionID) */
        public function getFromSessionID(){
            $sql = new Sql();
            $results = $sql->select("SELECT *
                FROM tb_carts
                WHERE dessessionid = :dessessionid",[
                    ':dessessionid'=>session_id()
                ]
            );
            /* Como o id do carrinho pode ser vazio, para evitar o erro de enviar um result sem nenhuma informação, pode
            ser utilizado o count */
            if(count($results) > 0){
                /* O resultado na posição zero é adicionado ao objeto */
                $this->setData($results[0]);
            }
        }
        /* Método para carregar a sessão e o id do carrinho */
        public function get(int $idcart){
            $sql = new Sql();
            $results = $sql->select("SELECT *
                FROM tb_carts
                WHERE idcart = :idcart",[
                    ':idcart'=>$idcart
                ]
            );
            /* Como o id do carrinho pode ser vazio, para evitar o erro de enviar um result sem nenhuma informação, pode
            ser utilizado o count */
            if(count($results) > 0){
                /* O resultado na posição zero é adicionado ao objeto */
                $this->setData($results[0]);
            }
        }
        /* Método para salvar os itens selecionados ao carrinho */
        public function save(){
            $sql = new Sql();
            $results = $sql->select("CALL sp_carts_save (:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
                ':idcart'=>$this->getidcart(),
                ':dessessionid'=>$this->getdessessionid(),
                ':iduser'=>$this->getiduser(),
                ':deszipcode'=>$this->getdeszipcode(),
                ':vlfreight'=>$this->getvlfreight(),
                ':nrdays'=>$this->getnrdays()
            ]);
            /* Após chamada da procedure, o results passa a ter os dados do carrinho. Com essas informações, passamos os dados para
            o objeto com o setData conforme abaixo */
            $this->setData($results[0]);
        }
    }   

?>