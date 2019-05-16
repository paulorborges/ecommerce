<?php

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\DB\Sql;

class User extends Model{
    const SESSION = "User";
    public static function login($login,$password){
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
            ":LOGIN"=>$login
        ));
        /* para verificar se o login acima foi identificado no banco de dados, pode ser verificar 
        se o contador = a 1 por exemplo, o que seria um retorno positivo para o método de verificação acima*/
        if (count($results)===0){
            /* a contra-barra no exception é necessária uma vez que a exception estaria no escopo principal do PHP e não no namespace
            HCode Model. Como não foi criado a própria exceção, necessário a barra para buscar corretamente. */
            /* para confundir o usuário, sempre mostrar a mensagem genérica: Usuário inexistente ou senha inválida. Nesse 
            caso utilizado individualmente para verificação de onde o código está validando cada elemento. */
            throw new \Exception ("Usuário inexistente.");
        }
        $data = $results[0];
        /* a função password_verify verifica se o hash da senha bate com o que foi digitado pelo usuário e retorno true ou false */
        if (password_verify($password, $data["despassword"]) === true){
            $user = new User();
            /* para realizar um teste se a classe model está pegando corretamente os valores do método */
            //$user->setiduser($data["iduser"]);
            /* o setiduser acima, no formato que foi construído, pega campo a campo e seria necessário muitos códigos para
            capturar todas as infomrações. Com o formato abaixo, utilizando o setdata da classe model, podemos pegar todos 
            os dados e salvar os elementos uma vez que no select realizado foram filtradas todas as informações. */
            $user->setData($data);
            /* estrutura abaixo utilizada para verificar os testes e informações carregadas */
            //var_dump($user);
            //exit;
            /* para que o login funcione, precisa haver uma sessão. Dessa forma você pode verificar em cada página que o
            usuário navega se ele foi autenticado. Caso não, o usuário deve ser redirecionado para página de login 
            conforme abaixo. Além de criar a sessão, para ficar mais organizado, o nome da sessão pode ser definido em uma 
            constante para facilitar a utilização em outros locais do programa conforme abaixo.*/
            $_SESSION[User::SESSION] = $user -> getValues();
            /* Retorno do método, caso o programa que fizer a invocação precise. */
            return $user;
        } else {
            /* para confundir o usuário, sempre mostrar a mensagem genérica: Usuário inexistente ou senha inválida. Nesse 
            caso utilizado individualmente para verificação de onde o código está validando cada elemento. */
            throw new \Exception ("Senha inválida.");
        }
    }
    /* Método para verificar se o usuário está logado. */
    public static function verifyLogin($inadmin = true){
        /* Necessário verificar se a sessão NÃO foi definida com a constante session OU, tenha sido definida mas esteja
        VAZIA (nesse caso, seja falsa) OU, se fizer o castin de uma sessão vazia o valor atribuído será zero e portanto
        não será uma sessão válida. Nesse último, se o valor for maior que zero será uma sessão verdadeira, portanto, se NÃO
        for, provavelmente, possui um id de usuário inválido. Como o acesso em questão é feito contra a página de administração
        o parámetro inadmin = true verifica se o usuário em questão, mesmo sendo válido, pertence realmente à administração do site.
        Para todos os demais, redirecionar para página de login. */
        if(!isset ($_SESSION[User::SESSION])
           ||
           !$_SESSION[User::SESSION]
           ||
           !(int)$_SESSION[User::SESSION]["iduser"]>0
           ||
           (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
           /* caso o usuário passar por todas as validações anteriores, necessário verificar se a sessão está rodando 
           no servidor web. Para isso, no arquivo de configuração é necessário iniciar o uso de sessões, antes do 
           require, função session start. */
        ){
            header("Location: /admin/login");
            exit;
        }
    }
    /* Método para encerrar a sessão */
    public static function logout(){
        /* Em caso de haver mais sessions rodando, pode ser chamado o destroy apenas para a session de usuario */
        $_SESSION[User::SESSION] = NULL;
    }

    /* Método para listar os usuários */
    public static function listAll(){
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
    }
    /* Método para salvar as variáveis no banco */
    public function save(){
        $sql = new Sql();
        /* chamada da procedure. os campos devem ser enviados na mesma ordem da procedure */
        $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
        ));
        /* apesar de ser um array, é necessário apenas a primeira posição, ou seja, zero. Sendo assim, é enviado para o 
        setData a posição zero do results. */
        $this->setData($results[0]);
    }
    /* Método para capturar todos os elementos de um usuário conforme seu ID. */
    public function get($iduser){
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING (idperson) WHERE a.iduser = :iduser", array(
            ":iduser" => $iduser
        ));
        /* Como o objeto results recebe um array em seu retorno, necessário enviar par ao setData apenas a posição zero. */
        $this->setData($results[0]);
    }
    /* Método para alteração dos dados */
    public function update(){
        $sql = new Sql();
        /* chamada da procedure. os campos devem ser enviados na mesma ordem da procedure */
        $results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":iduser"=>$this->getiduser(),
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
        ));
        /* apesar de ser um array, é necessário apenas a primeira posição, ou seja, zero. Sendo assim, é enviado para o 
        setData a posição zero do results. */
        $this->setData($results[0]);
    }
    /* Método para apagar os dados */
    public function delete(){
        $sql = new Sql();
        $sql -> query("CALL sp_users_delete(:iduser)", array(
            ":iduser"=>$this->getiduser()
        ));

    }
}

?>