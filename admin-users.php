<?php

    use \Hcode\PageAdmin;
    use \Hcode\Model\User;

    /* Rota para chamada das telas de manipulação de usuários */
    $app->get("/admin/users", function(){
        /* Como a tela de manutenção de usuários só deve estar disponível para usuários logado e o inadmin é true por padrão, 
        não é necessário passar nenhum parametro e o sistema vai avaliar, se além do acesso o usuário possui permissão de
        administrador.*/
        User::verifyLogin();
        /* Para carregar os usuários, necessário utilizar rotina abaixo. O método listAll retorna um array com todos os usuários. */
        $users = User::listAll();
        $page = new PageAdmin();
        /* Para que o setTpl envie o array para o template utilizamos o setData em um array passando a chave do usuário
        contendo um array com todos os campos */
        $page->setTpl("users", array(
            "users"=>$users
        ));
    });
    /* Rota para chamada das telas de criação de usuários. Nesse caso, como o método é get, será respondido com HTML. */
    $app->get("/admin/users/create", function(){
        /* Como a tela de manutenção de usuários só deve estar disponível para usuários logado e o inadmin é true por padrão, 
        não é necessário passar nenhum parametro e o sistema vai avaliar, se além do acesso o usuário possui permissão de
        administrador.*/
        User::verifyLogin();
        $page = new PageAdmin();
        $page->setTpl("users-create");
    });
    /* Rota para deletar os dados. Nesse caso, como o método delete não pode ser utilizado diretamente porque no slin seria
    necessário passar via post e mais um campo chamado _method escrito delete.*/
    //$app->delete("/admin/users/:iduser", function($iduser){
    /* Rota para deletar os valores. Essa rota deve vir antes da rota apenas com o :iduser porque o slin tpl quando analisar
    os campos vai entender corretamente que a rota delete possui a barra. Se fosse invertido, o slin identificaria a rota 
    abaixo como válida antes de avaliar o complemento /delete. */
    $app->get("/admin/users/:iduser/delete", function($iduser){
        /* Como a tela de manutenção de usuários só deve estar disponível para usuários logado e o inadmin é true por padrão, 
        não é necessário passar nenhum parametro e o sistema vai avaliar, se além do acesso o usuário possui permissão de
        administrador.*/
        User::verifyLogin();
        /* Carregar usuário para ter certeza que ele ainda existe */
        $user = new User();
        $user->get((int)$iduser);
        $user->delete();
        header("Location: /admin/users");
        exit;
    });
    /* Rota para chamada das telas de atualização de usuários. Nesse caso, além da rota é necessário passar o id do usuário afim
    de evitar problemas com a edição. */
    $app->get("/admin/users/:iduser", function($iduser){
        /* Como a tela de manutenção de usuários só deve estar disponível para usuários logado e o inadmin é true por padrão, 
        não é necessário passar nenhum parametro e o sistema vai avaliar, se além do acesso o usuário possui permissão de
        administrador.*/
        User::verifyLogin();
        /* Para carregar o método de edição, necessário pegar o id do usuário e exibir os campos a partir desses campos. */
        $user = new User();
        $user -> get((int)$iduser);
        $page = new PageAdmin();
        $page->setTpl("users-update", array(
            "user"=>$user->getValues()
        ));
    });
    /* Rota para salvar as informações. Nesse caso, como o método é post, será diferenciado pelo php por causa da outra rota
    create com método get e nesse caso será feito o insert dos dados. */
    $app->post("/admin/users/create", function(){
        /* Como a tela de manutenção de usuários só deve estar disponível para usuários logado e o inadmin é true por padrão, 
        não é necessário passar nenhum parametro e o sistema vai avaliar, se além do acesso o usuário possui permissão de
        administrador.*/
        User::verifyLogin();
        /* para testar se os dados estão sendo recebidos, var-dump do POST */
        //var_dump($_POST);
        /* Para criar um novo usuário, segue código abaixo */
        $user = new User();
        /* Verifica se o inadmin foi definido. Se ele existe, atribui o valor como 1, se não existe, atribui como zero. */
        $_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
        /* Considerando que o setDAta cria automaticamente os campos para o DAO de acesso ao banco e que o $POST é um array
        com todos os elementos necessários, basta usar o código abaixo para inserção no banco. Como os nomes no HTML foram 
        adicionados com os mesmos nomes da tabela do banco de dados, a inserção é passada de forma transparente. */
        $user->setData($_POST);
        /* Para verificar o funcionamento dessa rotina, basta utilizar o vardump na variável user */
        //var_dump($user);
        /* Para inserir os dados no banco de dados, utiliza-se o save conforme abaixo. */
        $user->save();
        /* Após o cadastro, chama a rota novamente para que o usuário perceba que o registro já foi efetivado e faz 
        parte do banco */
        header("Location: /admin/users");
        exit;
    });
    /* Rota para salvar os dados. Nesse caso, como o método é post, será diferenciado pelo php por causa da outra rota
    :iduser com método get e nesse caso será salvo a edição dos dados. */
    $app->post("/admin/users/:iduser", function($iduser){
        /* Como a tela de manutenção de usuários só deve estar disponível para usuários logado e o inadmin é true por padrão, 
        não é necessário passar nenhum parametro e o sistema vai avaliar, se além do acesso o usuário possui permissão de
        administrador.*/
        User::verifyLogin();
        /* Verifica se o inadmin foi definido. Se ele existe, atribui o valor como 1, se não existe, atribui como zero. */
        $_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
        /* para alterar os dados, devemos primeiro carregar todos os dados do banco e depois realizar a alteração */
        $user = new User();
        $user-> get((int)$iduser);
        /* Após carregar os dados, necessário buscar as informações digitadas no post e preparar o objeto para gravação */
        $user->setData($_POST);
        /* Com os dados atualizados, necessário chamar a função de update para realizar a gravação no banco */
        $user->update();
        /* Após a gravação, mostrar novamente a tela para análise do usuário */
        header ("Location: /admin/users");
        exit;
    });
    
    /* Rota para página de recuperação de senhas */
    $app->get("/admin/forgot", function(){
        $page = new PageAdmin([
            "header"=>false,
            "footer"=>false
        ]);
        //adiciona arquivo com conteúdo da página forgot!
        $page->setTpl("forgot");
    });
    /* Rota para página de recuperação de senhas */
    $app->post("/admin/forgot", function(){
        /* após digitação do endereço, vamos capturar o email digitado, verificar se o mesmo existe no banco de dados e 
        enviar um link para que o usuário, dentro de um período pré-determinado, consiga realizar os processos de recuperação
        da senha. O link, além de possuir um tempo de validada, poderá ser utilizado uma única vez com a chave em questão. 
        Caso seja necessário alterar novamente a senha, será gerado um novo link com uma nova chave. Na página forgot.html pode
        ser verificado que o forgot, além de ser enviado via post, possui um campo com nome email. Vamos utilizar o método 
        getForgot da classe User e o retorno do método é guardado na variável user.*/
        $user = User::getForgot($_POST["email"]);
        /* Redirect para confirmar para o usuário que o email foi enviado com sucesso */
        header("Location: /admin/forgot/sent");
        exit;
    });

    /* Roda para página de confirmçaõ do envio/recuperação de senha */
    $app->get("/admin/forgot/sent", function(){
        $page = new PageAdmin([
            "header"=>false,
            "footer"=>false
        ]);
        //adiciona arquivo com conteúdo da página forgot!
        $page->setTpl("forgot-sent");
    });
    /* Rota referente ao botão redefinir senha, presente no e-mail que é enviado ao usuário */
    $app->get("/admin/forgot/reset", function(){
        /* Método validForgotDecrypt retorna os dados do usuário em questão para a variável $user*/
        $user = User::validForgotDecrypt($_GET["code"]);

        $page = new PageAdmin([
            "header"=>false,
            "footer"=>false
        ]);
        /*adiciona arquivo com conteúdo da página forgot! Passando duas variáveis conforme necessário para o
        template em questão*/
        $page->setTpl("forgot-reset", array(
            "name"=>$user["desperson"],
            "code"=>$_GET["code"]
        ));
    });

    $app->post("/admin/forgot/reset", function(){
        /* Método validForgotDecrypt retorna os dados do usuário em questão para a variável $user. Necessário validar
        pela segunda vez em função da possibilidade de um hacker tentar invadir a segunda página e não a primeira
        conforme método get */
        $forgot = User::validForgotDecrypt($_POST["code"]);
        /* Para evitar que o método de recuperação e alteração de senha seja utilizado mais de uma vez, necessário
        gravar no banco de dados que a chave em questão já foi utilizada. Isso ocorre pelo campo dtrecovery. Dessa forma,
        necessário criar o método setForgotUsed. */
        User::setForgotUsed($forgot["idrecovery"]);
        /* Para trocar a senha de fato, necessário carregar o usuário conforme abaixo */
        $user = new User();
        $user -> get((int)$forgot["iduser"]);
        /* Para criptografar o password, a função a seguir pode ser utilizada. Importante verificar outros métodos e
        funcionalidades na documentação do php */
        $password = password_hash($_POST["password"], PASSWORD_DEFAULT,[
            "cost"=>12
        ]);
        /* Existe o método despassword e o metodo save. Nesse caso porém é necessário criar um novo método uma vez que é 
        necessário utilizar o novo hash da senha escolhida antes de salvar no banco de dados. No tamplate forgot-reset o nome
        do camo em questão é password, portanto, via post, vamos pegar o conteúdo desse campo para enviar como parâmetro.*/ 
        $user -> setPassword($password);

        $page = new PageAdmin([
            "header"=>false,
            "footer"=>false
        ]);
        /*adiciona arquivo com conteúdo da página forgot! Passando duas variáveis conforme necessário para o
        template em questão*/
        $page->setTpl("forgot-reset-success");
    });
?>