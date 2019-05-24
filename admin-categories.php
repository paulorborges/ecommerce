<?php

    use \Hcode\Page;
    use \Hcode\PageAdmin;
    use \Hcode\Model\User;
    use \Hcode\Model\Category;

    /* Rota para acessar template de categorias */
    $app->get("/admin/categories", function(){
        /* Como a tela de manutenção de usuários só deve estar disponível para usuários logado e o inadmin é true por padrão, 
        não é necessário passar nenhum parametro e o sistema vai avaliar, se além do acesso o usuário possui permissão de
        administrador.*/
        User::verifyLogin();
        /*como o template em questão recebe uma lista de categorias, necessário passar o parametro via array por exemplo*/
        $categories = Category::listAll();
        $page = new PageAdmin();
        $page->setTpl("categories", array(
            'categories'=>$categories
        ));
    });
    /* Rota para acessar ambiente de cadastro das categorias */
    $app->get("/admin/categories/create", function(){
        /* Como a tela de manutenção de usuários só deve estar disponível para usuários logado e o inadmin é true por padrão, 
        não é necessário passar nenhum parametro e o sistema vai avaliar, se além do acesso o usuário possui permissão de
        administrador.*/
        User::verifyLogin();
        $page = new PageAdmin();
        $page->setTpl("categories-create");
    });
    /* Rota para acessar função de cadastrar categorias */
    $app->post("/admin/categories/create", function(){
        /* Como a tela de manutenção de usuários só deve estar disponível para usuários logado e o inadmin é true por padrão, 
        não é necessário passar nenhum parametro e o sistema vai avaliar, se além do acesso o usuário possui permissão de
        administrador.*/
        User::verifyLogin();
        $category = new Category();
        $category->setData($_POST);
        $category->save();
        header("Location: /admin/categories");
        exit;
    });
    /* Rota para acessar as funções de deleção de categorias */
    $app->get("/admin/categories/:idcategory/delete", function($idcategory){
        /* Como a tela de manutenção de usuários só deve estar disponível para usuários logado e o inadmin é true por padrão, 
        não é necessário passar nenhum parametro e o sistema vai avaliar, se além do acesso o usuário possui permissão de
        administrador.*/
        User::verifyLogin();

        $category = new Category();
        /* Verifica se o objeto ainda existe no banco */
        $category->get((int)$idcategory);
        /* Caso carregue corretamente, o próximo passo seria excluir */
        $category->delete();
        /* Por fim, redirecoina para a tela principal onde serão listadas todas as categorias */
        header("Location: /admin/categories");
        exit;
    });
    /* Rota para edição de categorias. */
    $app->get("/admin/categories/:idcategory", function($idcategory){
        /* Como a tela de manutenção de usuários só deve estar disponível para usuários logado e o inadmin é true por padrão, 
        não é necessário passar nenhum parametro e o sistema vai avaliar, se além do acesso o usuário possui permissão de
        administrador.*/
        User::verifyLogin();
        /* como o template dessa rotina espera a passagenm de variáveis, necessário carregar o objeto da classe para passagem
        dos parametros */
        $category = new Category();
        $category->get((int)$idcategory);
        $page = new PageAdmin();
        /* Como essa rota possui uma tela específica, carrega-se no TPL em questão e passa-se o indice em questão via parametro
        para variavel category que está esperando pelo dado dentro do template. Para conversão do objeto em um array, deve ser
        utilizado o metodo getvalues conforme abaixo. */
        $page->setTpl("categories-update",[
            'category'=>$category->getValues()
        ]);
    });
    /* Rota para salvar edição de categorias. */
    $app->post("/admin/categories/:idcategory", function($idcategory){
        /* Como a tela de manutenção de usuários só deve estar disponível para usuários logado e o inadmin é true por padrão, 
        não é necessário passar nenhum parametro e o sistema vai avaliar, se além do acesso o usuário possui permissão de
        administrador.*/
        User::verifyLogin();
        /* como o template dessa rotina espera a passagenm de variáveis, necessário carregar o objeto da classe para passagem
        dos parametros */
        $category = new Category();
        $category->get((int)$idcategory);
        /* carrega os novos dados enviados via formulário */
        $category->setData($_POST);
        /* Salva os dados no banco */
        $category->save();
        /* Por fim, redirecoina para a tela principal onde serão listadas todas as categorias */
        header("Location: /admin/categories");
        exit;
    });
    /* Rota para template das categorias */
    $app->get("/categories/:idcategory", function($idcategory){
        $category = new Category();
        /* recupera o id que foi passado pelo get */
        $category->get((int)$idcategory);
        //chama o construct e adiciona o header
        $page = new Page();
        //adiciona arquivo com conteúdo da página index!
        $page->setTpl("category",[
            'category'=>$category->getValues(),
            'products'=>[]
        ]);
    });
?>