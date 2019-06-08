<?php

    use \Hcode\Model\User;
    use \Hcode\PageAdmin;
   
    /* Metodo para trabalhar com as rota do admin */
    $app->get('/admin', function() {
        /* Caso o admin seja chamado diretamente, o sistema deve verificar se o usuário está logado (se existe uma sessão válida).
        Apenas nessa condição deve ser exibido os dados.*/
        User::verifyLogin();
        //chama o construct e adiciona o header
        $page = new PageAdmin();
        //adiciona arquivo com conteúdo da página index!
        $page->setTpl("index");
        /* quando chega na última linha do app, o php limpa a memória chamando o destruct e adiciona o footer na página */
    });
    /* Metodo para trabalhar com as rota do login */
    $app->get('/admin/login', function() {
        /*chama o construct e adiciona o header. Como a página de login não tem um header, footer ou index e vai 
        aproveitar a estrutura da página atual, nesse caso é necessário passar alguns parametros para desabilitar 
        a montagem da página por arquivos distribuídos. */
        $page = new PageAdmin([
            "header"=>false,
            "footer"=>false
        ]);
        //adiciona arquivo com conteúdo da página index!
        $page->setTpl("login");
        /* quando chega na última linha do app, o php limpa a memória chamando o destruct e adiciona o footer na página */
    });
    /* */
    $app->post('/admin/login', function() {
        /* Nesse método, através da classe User, verifica se o login e senha são verdadeiros e caso seja, redireciona para o
        location adequado */
        User::login($_POST["login"], $_POST["password"]);
        header("Location: /admin");
        exit;
    });
    /* Rota para o método logout */
    $app->get('/admin/logout', function() {
        /* Nesse método, basta chamar o método logout da classe User */
        User::logout();
        /* Após o logout, a página pode ser redirecionada para o login ou realizado outra chamada contra a loja virtual */
        header("Location: /admin/login");
        exit;
    });
?>