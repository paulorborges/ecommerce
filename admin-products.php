<?php
    
    use \Hcode\PageAdmin;
    use \Hcode\Model\User;
    use \Hcode\Model\Products;
        
    /* Rota para acessar template de produtos */
    $app->get("/admin/products", function(){
        /* Como a tela de manutenção de usuários só deve estar disponível para usuários logado e o inadmin é true por padrão, 
        não é necessário passar nenhum parametro e o sistema vai avaliar, se além do acesso o usuário possui permissão de
        administrador.*/
        User::verifyLogin();
        /*como o template em questão recebe uma lista de categorias, necessário passar a lista de produtos via array*/
        $products = Products::listAll();
        $page = new PageAdmin();
        $page->setTpl("products", array(
            'products'=>$products
        ));
    });
    /* Rota para acessar template de criacao dos produtos */
    $app->get("/admin/products/create", function(){
        /* Como a tela de manutenção de usuários só deve estar disponível para usuários logado e o inadmin é true por padrão, 
        não é necessário passar nenhum parametro e o sistema vai avaliar, se além do acesso o usuário possui permissão de
        administrador.*/
        User::verifyLogin();
        $page = new PageAdmin();
        $page->setTpl("products-create");
    });
    /* Rota para cadastrar as informações do novo produto */
    $app->post("/admin/products/create", function(){
        /* Como a tela de manutenção de usuários só deve estar disponível para usuários logado e o inadmin é true por padrão, 
        não é necessário passar nenhum parametro e o sistema vai avaliar, se além do acesso o usuário possui permissão de
        administrador.*/
        User::verifyLogin();
        $products = new Products();
        $products->setData($_POST);
        $products->save();
        header("Location: /admin/products");
        exit;
    });
    /* Rota para cadastrar as informações do novo produto */
    $app->get("/admin/products/:idproducts", function($idproduct){
        /* Como a tela de manutenção de usuários só deve estar disponível para usuários logado e o inadmin é true por padrão, 
        não é necessário passar nenhum parametro e o sistema vai avaliar, se além do acesso o usuário possui permissão de
        administrador.*/
        User::verifyLogin();

        $products = new Products();
        $products->get((int)$idproduct);    

        $page = new PageAdmin();
        $page->setTpl("products-update",[
            'product'=>$products->getValues()
        ]);
    });
    /* Rota para editar as informações do produto */
    $app->post("/admin/products/:idproducts", function($idproduct){
        /* Como a tela de manutenção de usuários só deve estar disponível para usuários logado e o inadmin é true por padrão, 
        não é necessário passar nenhum parametro e o sistema vai avaliar, se além do acesso o usuário possui permissão de
        administrador.*/
        User::verifyLogin();

        $products = new Products();
        /* Carrega o ID do produto a ser editado */
        $products->get((int)$idproduct);    
        /* Passa as variáveis alteradas via formulario (POST) */
        $products->setData($_POST);
        /* Salva os dados */
        $products->save();
        /* Realiza o upload do arquivo e nome do campo do imput*/
        $products->setPhoto($_FILES["file"]);
        header("Location: /admin/products");
        exit;
    });
    /* Rota para exclusão de produtos */
    $app->get("/admin/products/:idproducts/delete", function($idproduct){
        /* Como a tela de manutenção de usuários só deve estar disponível para usuários logado e o inadmin é true por padrão, 
        não é necessário passar nenhum parametro e o sistema vai avaliar, se além do acesso o usuário possui permissão de
        administrador.*/
        User::verifyLogin();

        $products = new Products();
        $products->get((int)$idproduct);    
        $products->delete();

        header("Location: /admin/products");
        exit;
    });
    
?>