<?php

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\DB\Sql;

class User extends Model{

    const SESSION = "User";
    /* constante para chave de criptografia */
    const SECRET    = "HcodePHP7_Secret"; //16 caracteres
    const SECRET_IV = "FrisaComunicacao"; //16 caracteres
    /* Constantes para tratamento das mensagens de erro */
    const ERROR = "UserError";
    const ERROR_REGISTER = "UserErrorRegister";

    /* Como o usuário já está inserido na sessão atraves do User::SESSION, podemos criar um método para 
    retornar o usuário logado sempre que outras classes precisarem */
    public static function getFromSession(){
        $user = new User();
        /* Verificar se a sessão existe e se o ID do usuario é maior que zero */
        if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0){
            /* Caso a sessão esteja definida e o id do usuário for maior que zero, podemos setar os dados da sessão
            ao objeto usuário */
            $user->setData($_SESSION[User::SESSION]);
        }
        /* Se o if tiver retornado true, o programa seta no objeto usuário as informações do mesmo e o retorno abaixo
        envia os dados do usuário logado para as funções que chamarem o método. Se o if retornou para uma das duas condições
        como false, o retorno abaixo será feito com um usuário/objeto vazio. */
        return $user;
    }
    /* Metodo para verificar se o usuário está logado */
    public static function checkLogin($inadmin = true){
        /* Necessário verificar se a sessão NÃO foi definida com a constante session OU, tenha sido definida mas esteja
        VAZIA (nesse caso, seja falsa) OU, se fizer o castin de uma sessão vazia o valor atribuído será zero e portanto
        não será uma sessão válida. Nesse último, se o valor for maior que zero será uma sessão verdadeira, portanto, se NÃO
        for, provavelmente, possui um id de usuário inválido. Como o acesso em questão é feito contra a página de administração
        o parámetro inadmin = true verifica se o usuário em questão, mesmo sendo válido, pertence realmente à administração do site.
        Para todos os demais, redirecionar para página de login. */
        if(
            !isset($_SESSION[User::SESSION])
            ||
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]["iduser"]>0
        ){
            // Não está logado
            return false;
        } else {
            /* A sessão existe, ela foi definida, ele está logado mas pode acessar a sessão de admin? Na tabela de usuário existe um 
            parâmetro que fala se o usuário é administrador ou não mas além disso precisamos entender, se ele tem a permissão e se 
            ele quer utilizar a sessão administrativa ou apenas a loja. O if abaixo só ocorre se a rota acessada for de administrador.
            Basicamente o primeiro if verifica se o inadmin do usuário é true e se a rota que ele está utilizando é de administração.
            Caso sim, ok, ele está logado e retorna true. O else if verifica se é um usuário da administração e para o caso de não ser, já
            retorna como verdadeiro sem nem precisar verificar a rota utilizada, dessa forma ele pode verificar a parte de cliente uma 
            vez que ele já está logado. O else final retorna false uma vez que o usuário não está logado. */
            if($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true){
                return true;
            } else if ($inadmin === false){
                /* Nesse caso, como caiu no else, ele está logado, mas não é administrativo */
                return true;
            } else {
                /* Nesse caso, como caiu no else, ele não está logado. */
                return false;
            }
        }
    }

    public static function login($login,$password){
        $sql = new Sql();
        /* objeto substituído por outro com a vinculação da tabela de usuário com a tabela de pessoas */
        /*
        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
            ":LOGIN"=>$login
        ));
        */
        $results = $sql->select("SELECT * 
            FROM tb_users a
            INNER JOIN tb_persons b 
            ON a.idperson = b.idperson 
            WHERE a.deslogin = :LOGIN", array(
                ":LOGIN"=>$login
            )
        );
        
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
            /* Função utilizada para correção de acentuação em caso dos dados estarem em formato utf8. Encode utilizado sempre que
            buscamos alguma informação do banco. decode sempre que levamos alguma informação para o banco. */
            $data['desperson'] = utf8_encode($data['desperson']);
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
        /* com a Criação do método checkLogin, as verificações abaixo foram transferidas para o mesmo e podem ser desabilitadas
        aqui fazendo com que esse método utilize as mesmas validações do checkLogin conforme abaixo */
        /*
        if(!isset ($_SESSION[User::SESSION])
           ||
           !$_SESSION[User::SESSION]
           ||
           !(int)$_SESSION[User::SESSION]["iduser"]>0
           ||
           (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
           // caso o usuário passar por todas as validações anteriores, necessário verificar se a sessão está rodando 
           // no servidor web. Para isso, no arquivo de configuração é necessário iniciar o uso de sessões, antes do 
           // require, função session start.
        ){
            header("Location: /admin/login");
            exit;
        }
        */
        if(!User::checkLogin($inadmin)){
            if($inadmin){
                header("Location: /admin/login");
            } else {
                header("Location: /login");
            }
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
            /* Função utilizada para correção de acentuação em caso dos dados estarem em formato utf8. Encode utilizado sempre que
            buscamos alguma informação do banco. decode sempre que levamos alguma informação para o banco. */
            ":desperson"=>utf8_decode($this->getdesperson()),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>User::getPasswordHash($this->getdespassword()),
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
        $results = $sql->select("SELECT * 
            FROM tb_users a 
            INNER JOIN tb_persons b 
            USING(idperson) 
            WHERE a.iduser = :iduser", array(
                ":iduser" => $iduser
            )
        );
        $data = $results[0];
        /* Função utilizada para correção de acentuação em caso dos dados estarem em formato utf8. Encode utilizado sempre que
        buscamos alguma informação do banco. decode sempre que levamos alguma informação para o banco. */
        $data['desperson'] = utf8_encode($data['desperson']);
        /* Como o objeto results recebe um array em seu retorno, necessário enviar par ao setData apenas a posição zero. */
        $this->setData($data);
    }
    /* Método para alteração dos dados */
    public function update(){
        $sql = new Sql();
        /* chamada da procedure. os campos devem ser enviados na mesma ordem da procedure */
        $results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":iduser"=>$this->getiduser(),
            /* Função utilizada para correção de acentuação em caso dos dados estarem em formato utf8. Encode utilizado sempre que
            buscamos alguma informação do banco. decode sempre que levamos alguma informação para o banco. */
            ":desperson"=>utf8_decode($this->getdesperson()),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>User::getPasswordHash($this->getdespassword()),
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
    /* Método para recuperação de senha */
    public static function getForgot($email, $inadmin = true){
        /* Verificar se o e-mail está cadastrado no banco de dados */
        $sql = new Sql();
        $results = $sql->select(
            "SELECT * 
            FROM tb_persons a 
            INNER JOIN tb_users b 
            USING (idperson) 
            WHERE a.desemail = :email;
            ", array(
                ":email"=>$email
            )
        );
        /* se não houver retorno no results, quer dizer que não encontrou o email, função abaixo. A mensagem abaixo
        não deve ser utilizada em ambiente oficial uma vez que permite que o usuário mal intencionado faça novos testes.
        Importante colocar mensagens com códigos ou informações que apenas você entenda o erro. */
        if(count($results) === 0){
            throw new \Exception("Não foi possível identificar o email!");
        } else {
            /* se não for igual a zero, quer dizer que encontrou o email em questão. A informação é guardada em uma variável
            presente nesse escopo para o correto tratamento. */
            $data = $results[0];
            //var_dump ($data);
            /* Nesse ponto é importante registrar no banco de dados quais usuários estão tentando recuperar a senha, 
            de que IP ele fez a tentativa, entre outras funções. Para isso, foi criado uma nova procedure conforme 
            utilização abaixo. A procedure em questão recebe alguns parametros para o seu funcionamento. */
            $resultsRecovery = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
                ":iduser"=>$data["iduser"],
                /* para pegar o ip do usuário $server addr */
                ":desip"=>$_SERVER["REMOTE_ADDR"]
            ));
            //var_dump ($resultsRecovery);
            /* Caso o resultsRecover não tenha sido criado, haverá um problema na estrutura. Importante portanto verificar
            se existe informações associadas conforme abaixo. Assim como na observação anterior, importante ajustar a mensagem
            de erro de modo a não permitir que o usuário identique facilmente o erro e promova novas tentativas. */
            if (count($resultsRecovery) === 0){
                throw new \Exception("Não foi possível recuperar a senha!");
            } else {
                $dataRecovery = $resultsRecovery[0];
                //var_dump ($dataRecovery);
                /* Como a procedure vai retornar o idrecovey que foi a chave primária gerada automaticamente, autoincrement
                que foi gerada no banco de dados. Vamos capturar esse número, criptografar e enviar como link no e-mail para
                evitar que usuário mal intencionado tente outros códigos. */
                /*
                $code = base64_encode(mcrypt_encrypt(
                    MCRYPT_RIJNDAEL_128, 
                    User::SECRET, 
                    $dataRecovery["idrecovery"], 
                    MCRYPT_MODE_ECB)
                );
                echo $code;
                */
                /* como o mcrypt foi descontinuada nas versões 7.0 do php em diante, segue outra opção de criptografia. */
                $code = base64_encode(openssl_encrypt(
                    /*conversão para string afim de ser encriptado */
                    //json_encode($dataRecovery),
                    //json_encode($dataRecovery["idrecovery"]),
                    /* Informação pura ser encriptada */
                    $dataRecovery["idrecovery"],
                    /* algoritimo de criptografia */
                    'AES-128-CBC', 
                    /* primeira chave a ser encriptada */
                    User::SECRET,
                    /* o zero encripta e não precisa retornar nada */
                    0,
                    /* segunda chave a ser encriptada */
                    User::SECRET_IV
                ));
                /* Teste de objetos e conteúdos */
                //echo $code;
                //$string = openssl_decrypt($openssl, 'AES-128-CBC', SECRET, 0, SECRET_IV);
                //echo "<br>";
                //var_dump(json_decode($string, true));

                /* Para enviar o link, deve-se analisar se o usuário é da administração ou se é um usuário normal da loja */
                if($inadmin === true ){
                    /* Após criptografado, necessário montar o link. Nesse caso, link de usuário admin. */
                    $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";
                    //echo $link;
                } else {
                    /* Após criptografado, necessário montar o link. Nesse caso, link de usuário comum. */
                    $link = "http://www.hcodecommerce.com.br/forgot/reset?code=$code";
                    //echo $link;
                }
                $subj = "Redefinir senha da Frisa Comunicacao";
                $mailer = new Mailer(
                    /* email para recuperação da senha. Endereco de destino. */
                    $data["desemail"],
                    /* nome da pessoa */
                    $data["desperson"], 
                    /* assunto da mensagem */
                    $subj, 
                    /* nome do template dentro da pasta views\email */
                    "forgot", array(
                        "name"=>$data["desperson"],
                        "link"=>$link
                    )
                );
                $mailer->send();
                return $data;

                /* segue abaixo duas chaves, em 16 bits */
                //define('SECRET_IV', pack ('a16', 'senha'));
                //define('SECRET', pack('a16','senha'));
                /* mesmo array para ser encriptado */
                //$data = [
                //    "idRecovery"=>"$dataRecovery"
                //];

                //$openssl = openssl_encrypt(
                    /*conversão para string afim de ser encriptado */
                //    json_encode($data), 
                    /* algoritimo de criptografia */
                //    'AES-128-CBC', 
                    /* primeira chave a ser encriptada */
                //    SECRET,
                    /* o zero encripta e não precisa retornar nada */
                //    0,
                    /* segunda chave a ser encriptada */
                //    SECRET_IV
                //);
                //echo $openssl;
                /* para fazer o processo inverso, decrypt, segue instruções abaixo */
                /*
                $string = openssl_decrypt($openssl, 'AES-128-CBC', SECRET, 0, SECRET_IV);
                echo "<br>";
                var_dump(json_decode($string, true));
                */


            }
        }
    }
    /* Método para descriptograr a senha*/
    public static function validForgotDecrypt($code){
        /* função para recuperar o id de recuperação da senha através do hash criado em base64 e depois decriptado 
        pela mesma chave de criptografia utilizada.*/
        //echo base64_decode($code);
        $idRecovery = openssl_decrypt(base64_decode($code), 'AES-128-CBC', User::SECRET, 0, User::SECRET_IV);
        //var_dump ($idRecovery);
        //$idRecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::SECRET, base64_decode($code), MCRYPT_MODE_ECB);
        $sql = new Sql();
        /* dtrecovery é null porque ainda não foi utilizado. dtregister mostra a informação que foi gerado e essa 
        data somada a 1 hora precisa ser menor ou igual 1 hora para permitir o funcionamento. */
        $results = $sql->select ("SELECT * 
            FROM tb_userspasswordsrecoveries a
            INNER JOIN tb_users b USING(iduser)
            INNER JOIN tb_persons c USING(idperson)
            WHERE
                a.idrecovery = :idrecovery
                AND
                a.dtrecovery IS NULL
                AND
                DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
        ", array(
            ":idrecovery" => $idRecovery
        ));
        //var_dump($results[0]);
        if(count($results)===0){
            throw new \Exception("Não foi possível recuperar a senha.");
        } else {
            return $results[0];
        }
    }
    /* Método para setar campo dtrecovery como utilizado afim de proteger a chave e não permitir novas utilizações */
    public static function setForgotUsed($idRecovery){
        $sql = new Sql();
        $sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() 
                    WHERE idrecovery = :idrecovery", 
                    array(
                        ":idrecovery" => $idRecovery 
                    )
                );
    }
    /* Método para criptografar a senha e salvar no banco de daodos */
    public function setPassword($password){
        $sql = new Sql();
        $sql->query("UPDATE tb_users 
                    SET despassword = :password 
                    WHERE iduser = :iduser", 
                    array(
                        ":password"=>$password,
                        ":iduser"=>$this->getiduser()
                    )
        );
    }
    /* Método para setar a mensagem de erro */
    public static function setError($msg){
        $_SESSION[User::ERROR] = $msg;
    }
    /* Método para recuperar a mensagem de erro */
    public static function getError(){
        /* Verifica se a sessão foi definida e se possui algum conteúdo */
        $msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';
        User::clearError();
        return $msg;
    }
    /* Método para limpar a mensagem de erro */
    public static function clearError(){
        $_SESSION[User::ERROR] = NULL;    
    }
    /* Método para atribuir a mensagem de erro */
    public static function setErrorRegister($msg){
        $_SESSION[User::ERROR_REGISTER] = $msg;    
    }
    /* Método para recuperar a mensagem de erro */
    public static function getErrorRegister(){
        /* Verifica se o erro foi definido na sessão e se não é vazio */
        $msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : ''; 
        User::clearErrorRegister();
        return $msg;
    }
    /* Método para limpar a mensagem de erro */
    public static function clearErrorRegister(){
        /* Para limpar o erro, atribuo o valor nulo a constante de erro da sessão. */
        $_SESSION[User::ERROR_REGISTER] = NULL;
    }
    /* Método para verificar se o login existe na base de dados */
    public static function checkLoginExist($login){
        $sql = new Sql();
        $results = $sql->select("SELECT *
            FROM tb_users 
            WHERE deslogin = :deslogin", [
                ':deslogin'=>$login
            ]
        );
        /* Se identificar que o login já existe, retorna alguma coisa. Caso não exista, retorna false por default */
        return (count($results) > 0);
    }
    /* Método para criptografar senha banco de dados */
    public static function getPasswordHash($password){
        /* assim como na função esqueci a senha, este método recebe a senha como parametro e utiliza a classe de criptografia
        do PHP para converter a senha em um código hash com processamento 12. */
        return password_hash($password, PASSWORD_DEFAULT, [
            'cost'=>12
        ]);
    }
}

?>