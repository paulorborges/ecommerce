<?php

    use \Hcode\PageAdmin;
    use \Hcode\Model\User;
    use \Hcode\Model\Order;
    use \Hcode\Model\OrderStatus;
   
    /* Quando definir regras, é importante tomar cuidado para sempre manter as rotas com maiores caminhos antes. Dessa forma o PHP
    vai buscar corretamente a informação sem que ele verifique uma rota com menor descrição e entenda ser a rota correta. Essa boa 
    prática evita que o PHP acesse a rota errada durante as pesquisas. */

    /* Metodo para alterar status do pedidos através do admin */
    $app->get('/admin/orders/:idorder/status', function($idorder) {
        /* Caso o admin seja chamado diretamente, o sistema deve verificar se o usuário está logado (se existe uma sessão válida).
        Apenas nessa condição deve ser exibido os dados.*/
        User::verifyLogin();
        /* Carregamos o pedido */
        $order = new Order();
        /* Verifico se o pedido ainda existe no banco de dados */
        $order -> get((int)$idorder);
        /* Como essa rota possui um template específico, criamos a nova página */
        $page = new PageAdmin();
        //adiciona arquivo com conteúdo da página orders!
        $page->setTpl("order-status",[
            'order'=>$order->getValues(),
            'status'=>OrderStatus::listAll(),
            'msgSuccess'=>Order::getSuccess(),
            'msgError'=>Order::getError()
        ]);
    });
    /* Metodo para alterar status do pedidos através do admin */
    $app->post('/admin/orders/:idorder/status', function($idorder) {
        /* Caso o admin seja chamado diretamente, o sistema deve verificar se o usuário está logado (se existe uma sessão válida).
        Apenas nessa condição deve ser exibido os dados.*/
        User::verifyLogin();
        /* Verificamos se o idstatus foi definido pelo usuário ou se foi carregado */
        if(!isset($_POST['idstatus']) || !(int)$_POST['idstatus'] > 0){
            Order::setError("Informe a situação atual do pedido.");
            header("Location: /admin/orders/" . $idorder . "/status");
            exit;
        }
        /* Carregamos o pedido */
        $order = new Order();
        /* Verifico se o pedido ainda existe no banco de dados */
        $order -> get((int)$idorder);
        /* Agora alteramos a situação da order conforme seleção do usuário */
        $order -> setidstatus((int)$_POST['idstatus']);
        /* Após a edição, necessário salvar as informações no banco de dados */
        $order -> save();
        /* Após atualização, envia mensagem de sucesso para o usuário */
        $order -> setSuccess("Situação atualizada com sucesso!");
        header("Location: /admin/orders/" . $idorder . "/status");
        exit;
    });    
    /* Metodo para excluir pedidos através do admin */
    $app->get('/admin/orders/:idorder/delete', function($idorder) {
        /* Caso o admin seja chamado diretamente, o sistema deve verificar se o usuário está logado (se existe uma sessão válida).
        Apenas nessa condição deve ser exibido os dados.*/
        User::verifyLogin();
        /* Carregamos o pedido */
        $order = new Order();
        /* Verifico se o pedido ainda existe no banco de dados */
        $order -> get((int)$idorder);
        /* Caso carregue a informação, consigo deletar. */
        $order -> delete();
        /* Após deletar, chamo novamente a tela de pedidos */
        header("Location: /admin/orders");
        exit;
    });    
    /* Metodo para detalhar pedidos através do admin */
    $app->get('/admin/orders/:idorder', function($idorder) {
        /* Caso o admin seja chamado diretamente, o sistema deve verificar se o usuário está logado (se existe uma sessão válida).
        Apenas nessa condição deve ser exibido os dados.*/
        User::verifyLogin();
        /* Carregamos o pedido */
        $order = new Order();
        /* Verifico se o pedido ainda existe no banco de dados */
        $order -> get((int)$idorder);
        /* Como a consulta do pedido já possui o idcart, podemos utilizar o metodo getCart dentro da classe order */
        $cart = $order->getCart();
        /* Como essa rota possui um template específico, criamos a nova página */
        $page = new PageAdmin();
        /* Para testar os produtos do carrinho */
        /*
        //var_dump ($cart);
        var_dump ($cart->getProducts());
        exit;
        */
        //adiciona arquivo com conteúdo da página orders!
        $page->setTpl("order",[
            'order'=>$order->getValues(),
            'cart'=>$cart->getValues(),
            /* Para capturar todos os produtos que estão dentro de um pedido já existe no carrinho de compras, método
            getProducts */
            'products'=>$cart->getProducts()
        ]);
    });
    /* Metodo para trabalhar com as rota do admin */
    $app->get('/admin/orders', function() {
        /* Caso o admin seja chamado diretamente, o sistema deve verificar se o usuário está logado (se existe uma sessão válida).
        Apenas nessa condição deve ser exibido os dados.*/
        User::verifyLogin();
        //chama o construct e adiciona o header
        $page = new PageAdmin();
        //adiciona arquivo com conteúdo da página orders!
        $page->setTpl("orders",[
            'orders'=>Order::listAll()
        ]);
        /* quando chega na última linha do app, o php limpa a memória chamando o destruct e adiciona o footer na página */
    });
?>