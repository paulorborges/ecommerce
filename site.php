<?php

    use \Hcode\Page;
    use \Hcode\Model\Products;
    use \Hcode\Model\Category;
    use \Hcode\Model\Cart;
    use \Hcode\Model\Address;
    use \Hcode\Model\User;
    use \Hcode\Model\Order;
    use \Hcode\Model\OrderStatus;

    /* metodo para trabalhar com as rotas */
    $app->get('/', function() {
        // Lista os produtos que estão no banco
        $products = Products::listAll();
        // chama o construct e adiciona o header
        $page = new Page();
        /* Adiciona arquivo com conteúdo da página index e passa a lista de produtos como parâmetro! Utiliza-se também
        o metodo checkList para evitar o problema ao carregar a imagem. Esse método tem o retorno do objeto já formatado,
        incluindo a imagem. */
        $page->setTpl("index", [
            //'products'=>$products
            'products'=>Products::checkList($products)
        ]);
        //echo "OK";
        //$sql = new Hcode\DB\Sql();
        //$results = $sql->select("SELECT * FROM tb_users");
        //echo json_encode($results);
        /* quando chega na última linha do app, o php limpa a memória chamando o destruct e adiciona o footer na página */
    });
    /* Rota para template das categorias */
    $app->get("/categories/:idcategory", function($idcategory){
        $category = new Category();
        /* recupera o id que foi passado pelo get */
        $category->get((int)$idcategory);
        /*adiciona arquivo com conteúdo da página index! Utiliza-se também o metodo checkList para evitar o problema ao 
        carregar a imagem. Esse método tem o retorno do objeto já formatado, incluindo a imagem. */
        /*
        $page = new Page();
        $page->setTpl("category",[
            'category'=>$category->getValues(),
            'products'=>Products::checkList($category->getProducts())
        ]);
        */
        /* Com a criação do método getProductsPages na classe Category para atender a visão de categorias do site, 
        a função anterior é substituída pela função abaixo uma vez que o próprio método já faz a checkList para 
        carregar corretamente o objeto e incluir a imagem */
        /* Que página eu estou para chamar a página no getproductspage? Para verificar, posso utilizar um isset conforme
        abaixo */
        $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
        /* Para o caso de deixar o usuário escolher quantas páginas ele quer ver por página, bastaria passar um segundo
        parametro na função abaixo, como por exemplo getProductsPages($page, parametro 2)*/
        $pagination = $category->getProductsPages($page);
        $page = new Page();
        $pages = [];
        for ($i=1; $i <= $pagination['pages'] ; $i++) { 
            array_push($pages,[
                'link'=>'/categories/' . $category->getidcategory() . '?page=' . $i,
                'page'=>$i
            ]);
        }
        $page->setTpl("category",[
            'category'=>$category->getValues(),
            'products'=>$pagination["data"],
            'pages'=>$pages
        ]);
    });
    /* Rota para template de detalhes dos produtos */
    $app->get("/products/:desurl", function($desurl){
        $product = new Products();
        /* O metodo getFromUrl utilizado abaixo carrega os dados do produto para o próprio objeto com o setData */
        $product->getFromUrl($desurl);
        $page = new Page();
        $page -> setTpl("product-detail",[
            /* Como os dados foram carregados no objeto produto, o getValues busca as informações e envia, através 
            do elemento product criado nessa função para o template */
            'product'=>$product->getValues(),
            /* Como os dados foram carregados no objeto produto, o getCategories busca as informações e envia, através 
            do elemento categories criado nessa função para o template */
            'categories'=>$product->getCategories()
        ]);
    });
    /* Rota para template do carrinho de compras */
    $app->get("/cart", function(){
        /* Verifica se já existe um carrinho de compras setado, quais os produtos e caso não exista, cria um novo 
        carrinho */
        $cart = Cart::getFromSession();
        /* Verifica se o carrinho de compras possui itens */
        $cart->checkZipCode();

        $page = new Page();
        /* Para verificar as informações presentes no carrinho, pode ser utilizado o var-dump conforme abaixo */
        /*
        var_dump($cart->getValues());
        exit;
        */
        /* Além de indicar a rota, passa-se os parametros do carrinho, produtos, usuários, etc */
        $page -> setTpl("cart", [
            'cart'=>$cart->getValues(),
            'products'=>$cart->getProducts(),
            'error'=>Cart::getMsgError()
        ]);
    });
    /* Rota para template do método de adicionar produtos ao carrinho de compras */
    $app->get("/cart/:idproduct/add", function($idproduct){
        $product = new Products();
        $product->get((int)$idproduct);
        /* Recuperar a sessão do carrinho */
        $cart = Cart::getFromSession();
        /* Na aba detalhes do produto, existe a possibilidade de comprar e nessa tela digitar/selecionar a quantidade de 2 ou mais unidades do 
        produto em questão. Para que essa informação seja passada para o carrinho de compras, no template do product-detail existe um campo
        com o nome de qtd, na ferramenta de compras, e nessa rota, podemos pegar essa informação do método get e passar para o carrinho afim
        de adicionar o produto e já passar a quantidade selecionada. A função abaixo verifica se a variável foi setada, se foi, pega a quantidade
        do campo qtd, se não, passa apenas 1 unidade mesmo. */
        $qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;
        /* com o for abaixo, chamamos o método quantas vezes o usuário digitar na quantidade */
        for ($i = 0 ; $i < $qtd ; $i++){
            /* Para adicionar o produto ao carrinho, segue função */
            $cart -> addProduct ($product);
        }
        header("Location: /cart");
        exit;
    });
    /* Rota para template do método de remover produtos ao carrinho de compras. Minus para remoção de 1 produto */
    $app->get("/cart/:idproduct/minus", function($idproduct){
        $product = new Products();
        $product->get((int)$idproduct);
        /* Recuperar a sessão do carrinho */
        $cart = Cart::getFromSession();
        /* Para remover apenas 1 unidade do produto, como o parametro all por default é false, não precisa passar o 
        segundo parametro */
        $cart -> removeProduct ($product);
        header("Location: /cart");
        exit;
    });
    /* Rota para template do método de remover produtos ao carrinho de compras. */
    $app->get("/cart/:idproduct/remove", function($idproduct){
        $product = new Products();
        $product->get((int)$idproduct);
        /* Recuperar a sessão do carrinho */
        $cart = Cart::getFromSession();
        /* Para remover o produto inteiro do carrinho, como o parametro all por default é false, passamos o segundo
        parametro como true */
        $cart -> removeProduct ($product, true);
        header("Location: /cart");
        exit;
    });
    /* Rota para template do cálculo do frete no carrinho de compras. */
    $app->post("/cart/freight", function(){
        $cart = Cart::getFromSession();
        /* Para passar o CEP para sessão, utilizamos o $_POST do objeto digitado no campo ZIPCODE do template cart.html */
        $cart -> setFreight($_POST['zipcode']);
        /* Redireciona para a tela do carrinho */
        header("Location: /cart");
        exit;
    });
    /* Rota para template do checkout. Essa rota é acionada após acesso ao botão finalizar compra, dentro do carrinho de compras.*/
    $app->get("/checkout", function(){
        /* Verifica se o usuário está logado no site. Nesse caso, como é uma rota de compra, deve ser passado o parametro como false
        para que o metódo redirecione corretamente para o ambiente de usuário e senha do site e não da administração */
        User::verifyLogin(false);
        /* Cria o objeto address do tipo Address */
        $address = new Address();
        /* Pega o carrinho que está na sessão */
        $cart = Cart::getFromSession();
        /* Como algumas validações, de algumas das páginas chamam a rota get do checkout, em alguns casos o zipcode não é enviado. Para
        evitar problemas na próxima validação onde iremos carregar o endereço, podemos aproveitar o cep que está no carrinho. */
        if (isset($_GET['zipcode'])){
            $_GET['zicode'] = $cart->getdeszipcode();
        }
        /* Para os casos onde o CEP não foi enviado e não foi digitado no carrinho, existe um tratamento diretamente no template para 
        validar essa digitação. */
        /* Verifica se o CEP foi enviado */
        if (isset($_GET['zipcode'])){
            /* caso o cep seja enviado, o método loadfrom verifica se existe e carrega os dados para o objeto address */
            $address -> loadFromCEP($_GET['zipcode']);
            /* Para o caso de haver um novo CEP no carrinho, atualiza os dados no carrinho e recalcula o frete para garantir 
            o frete correto no ato de fechar a compra. */
            $cart -> setdeszipcode($_GET['zipcode']);
            $cart -> save();
            $cart -> getCalculateTotal();
        }
        /* Para os casos onde o CEP não foi enviado e não foi digitado no carrinho, os parâmetros são definidos como vazio. */        
        if(!$address->getdesaddress()) $address->setdesaddress('');
        if(!$address->getdescomplement()) $address->setdescomplement('');
        if(!$address->getdesdistrict()) $address->setdesdistrict('');
        if(!$address->getdescity()) $address->setdescity('');
        if(!$address->getdesstate()) $address->setdesstate('');
        if(!$address->getdescountry()) $address->setdescountry('');
        if(!$address->getdeszipcode()) $address->setdeszipcode('');

        $page = new Page();
        $page -> setTpl("checkout", [
            'cart'=>$cart->getValues(),
            'address'=>$address->getValues(),
            'products'=>$cart->getProducts(),
            'error'=>$address->getMsgError()
        ]);
    });
    /* Rota para template do checkout. Essa rota é acionada após preenchimento dos dados, dentro do carrinho de compras.*/
    $app->post("/checkout", function(){
        /* Verifica se o usuário está logado no site. Nesse caso, como é uma rota de compra, deve ser passado o parametro como false
        para que o metódo redirecione corretamente para o ambiente de usuário e senha do site e não da administração */
        User::verifyLogin(false);
        /* Após o preenchimento dos dados, recalculo do frete, o usuário pode ter apagado de forma involuntária algum dos campos do endereco
        ou o endereço pode realmente estar em branco. Nessas condições precisamos validar antes de dar sequencia no código */
        if (!isset($_POST['zipcode']) || $_POST['zipcode'] === ''){
            Address::setMsgError("Informe o CEP.");
            /* Quando chamamos novamente a rota checkout, é possível observar que não passamos o zipcode, portanto, dependendo da situação
            vai apresentar erro de endereço uma vez que o mesmo não estará definido */
            header("Location: /checkout");
            exit;
        }
        if (!isset($_POST['desaddress']) || $_POST['desaddress'] === ''){
            Address::setMsgError("Informe o logradouro.");
            /* Quando chamamos novamente a rota checkout, é possível observar que não passamos o zipcode, portanto, dependendo da situação
            vai apresentar erro de endereço uma vez que o mesmo não estará definido */
            header("Location: /checkout");
            exit;
        }
        if (!isset($_POST['desdistrict']) || $_POST['desdistrict'] === ''){
            Address::setMsgError("Informe a bairro.");
            /* Quando chamamos novamente a rota checkout, é possível observar que não passamos o zipcode, portanto, dependendo da situação
            vai apresentar erro de endereço uma vez que o mesmo não estará definido */
            header("Location: /checkout");
            exit;
        }
        if (!isset($_POST['descity']) || $_POST['descity'] === ''){
            Address::setMsgError("Informe a cidade.");
            /* Quando chamamos novamente a rota checkout, é possível observar que não passamos o zipcode, portanto, dependendo da situação
            vai apresentar erro de endereço uma vez que o mesmo não estará definido */
            header("Location: /checkout");
            exit;
        }
        if (!isset($_POST['desstate']) || $_POST['desstate'] === ''){
            Address::setMsgError("Informe a UF.");
            /* Quando chamamos novamente a rota checkout, é possível observar que não passamos o zipcode, portanto, dependendo da situação
            vai apresentar erro de endereço uma vez que o mesmo não estará definido */
            header("Location: /checkout");
            exit;
        }
        if (!isset($_POST['descountry']) || $_POST['descountry'] === ''){
            Address::setMsgError("Informe o país.");
            /* Quando chamamos novamente a rota checkout, é possível observar que não passamos o zipcode, portanto, dependendo da situação
            vai apresentar erro de endereço uma vez que o mesmo não estará definido */
            header("Location: /checkout");
            exit;
        }
        /* Carregamos o usuário da sessão */
        $user = User::getFromSession();
        /* Para criar um novo endereço */
        $address = new Address();
        /* Como o nome do campo referente ao CEP está diferente do nome desse campo na base de dados, vamos sobreescrevê-lo e assim
        conseguimos passar o objeto post diretamente com o setData */
        $_POST['deszipcode'] = $_POST['zipcode'];
        /* Precisamos também do id da pessoa, o idperson */
        $_POST['idperson'] = $user->getidperson();

        $address -> setData($_POST);
        
        /* chamamos o metodo save */
        $address -> save();

        /* Neste ponto recuperamos o carrinho da sessão e forçamos o cálculo total de produtos + valor do frete */
        $cart = Cart::getFromSession();
        /* como o metodo getcalculatetotal não possui um retorno explícito, o array abaixo nao seria retornado no objeto 
        totals. Para correção, apenas forcamos o método e buscamos essa informação diretamente do carrinho conforme última
        linha do setData abaixo */
        //$totals = $cart->getCalculateTotal();
        $cart->getCalculateTotal();

        $order = new Order();
        $order->setData([
            'idcart'=>$cart->getidcart(),
            'idaddress'=>$address->getidaddress(),
            'iduser'=>$user->getiduser(),
            'idstatus'=>OrderStatus::EM_ABERTO,
            /* Ao inves das funções abaixo, podemos forçar o método gtcalculatetotal que já seta no carrinho 
            tanto o valor total dos produtos como também a soma do valor total e do frete e buscar esse dado a partir
            desse resultado */
            /* 'vltotal'=>$totals['vlprice'] + $cart->getvlfreight() */
            'vltotal'=>$cart->getvltotal()
        ]);
        $order->save();
        header("Location: /order/".$order->getidorder());
        exit;
    });
    /* Rota para template da página de login. Essa rota é acionada após acesso ao botão finalizar compra, dentro do carrinho de compras.*/
    $app->get("/login", function(){
        $page = new Page();
        $page -> setTpl("login", [
            /* Em caso de erro, passa erro para o template e as informações já digitadas na sessão afim de evitar que o usuário perca 
            esses dados, se esses existirem, se não, passo um erray com cada index vazio */
            'error'=>User::getError(),
            'errorRegister'=>User::getErrorRegister(),
            'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : [
                'name'=>'',
                'email'=>'',
                'phone'=>''
                /* por segurança, o password sempre é zerado então não preciso passar o index nesse caso */
            ]
        ]);
    });
    /* Rota para template da página de login. Essa rota é acionada após preenchimento dos campos email e senha. */
    $app->post("/login", function(){
        /* O try catch abaixo verifica se o login vai retornar algum erro, caso não, segue com o código. Caso sim, gera a exception. */
        try {
            /* Método login verifica a autencidade do usuário e já cria a sessão do mesmo. */
            User::login($_POST['login'], $_POST['password']);
        } catch(Exception $e){
            /* Para enviar o erro, podemos utilizar os métodos estáticos da classe user conforme abaixo */
            User::setError($e->getMessage());
        }
        
        /* Se o usuário for verificado na operação anterior, basta redirecionar a navegação para a página de checkout */
        header("Location: /checkout");
        exit;
    });
    /* Rota para realizar logout */
    $app->get("/logout", function(){
        User::logout();
        header("Location: /login");
        exit;
    });
    /* Rota para criação de novo usuário do site */
    $app->post("/register", function(){
        /* Como haverão validações abaixo onde, em caso de problemas, o usuário deverá corrigir os dados, se não capturarmos o que foi 
        digitado, quando ocorrer o erro o usuário perderá todas as informações. Nesse caso, podemos pegar os dados e coloca-los em uma 
        sessão antes de realizar as validações: */
        $_SESSION['registerValues'] = $_POST;
        /* Antes de criar o usuário no banco de dados, verificamos se o nome NÃO foi definido OU se É vazio */
        if(!isset($_POST['name']) || $_POST['name'] == ''){
            User::setErrorRegister("Preencha o seu nome.");
            header("Location: /login");
            exit;
        }
        /* Antes de criar o usuário no banco de dados, verificamos se o email NÃO foi definido OU se É vazio*/
        if(!isset($_POST['email']) || $_POST['email'] == ''){
            User::setErrorRegister("Preencha o seu email.");
            header("Location: /login");
            exit;
        }
        /* Antes de criar o usuário no banco de dados, verificamos se o password NÃO foi definido OU se É vazio */
        if(!isset($_POST['password']) || $_POST['password'] == ''){
            User::setErrorRegister("Preencha a senha.");
            header("Location: /login");
            exit;
        }
        /* Antes de criar o usuário no banco de dados, verificamos se ele já existe. */
        if(User::checkLoginExist($_POST['email']) === true){
            User::setErrorRegister("Este endereço de e-mail já está sendo utilizado por outro usuário.");
            header("Location: /login");
            exit;
        }
        $user = new User();
        /* Conforme a segunda coluna do arquivo login.html, dentro do diretorio views, os campos são diferentes dos campos presentes em usuários
        administradores. Para esse caso, o inadmin será sempre zero. */
        $user->setData([
            'inadmin'=>0,
            /* Como tratamos um usuário do site, utilizamos o e-mail como login */
            'deslogin'=>$_POST['email'],
            'desperson'=>$_POST['name'],
            'desemail'=>$_POST['email'],
            /* como o método save já faz a criptografia, não é necessário utilizar o método aqui */
            'despassword'=>$_POST['password'],
            'nrphone'=>$_POST['phone']
        ]);
        $user->save();
        /* Após salvar o usuário, autenticamos o mesmo e posteriormente redirecionamentos para a tela de checkout. Essa estratégia evita que 
        o usuário ao ser redirecionado para o checkout seja enviado novamente para a tela de login uma vez que ainda não estará autenticado. */
        User::login($_POST['email'], $_POST['password']);
        header('Location: /checkout');
        exit;
    });
    /* Rota para página de recuperação de senhas */
    $app->get("/forgot", function(){
        $page = new Page();
        //adiciona arquivo com conteúdo da página forgot!
        $page->setTpl("forgot");
    });
    /* Rota para página de recuperação de senhas */
    $app->post("/forgot", function(){
        /* após digitação do endereço, vamos capturar o email digitado, verificar se o mesmo existe no banco de dados e 
        enviar um link para que o usuário, dentro de um período pré-determinado, consiga realizar os processos de recuperação
        da senha. O link, além de possuir um tempo de validada, poderá ser utilizado uma única vez com a chave em questão. 
        Caso seja necessário alterar novamente a senha, será gerado um novo link com uma nova chave. Na página forgot.html pode
        ser verificado que o forgot, além de ser enviado via post, possui um campo com nome email. Vamos utilizar o método 
        getForgot da classe User e o retorno do método é guardado na variável user.*/
        /* Como esse método deve recuperar a senha de um  usuário normal e não de um usuário da administração, devemos forçar 
        o inadmin como false */
        $user = User::getForgot($_POST["email"], false);
        /* Redirect para confirmar para o usuário que o email foi enviado com sucesso */
        header("Location: /forgot/sent");
        exit;
    });
    /* Rota para página de confirmçaõ do envio/recuperação de senha */
    $app->get("/forgot/sent", function(){
        $page = new Page();
        //adiciona arquivo com conteúdo da página forgot!
        $page->setTpl("forgot-sent");
    });
    /* Rota referente ao botão redefinir senha, presente no e-mail que é enviado ao usuário */
    $app->get("/forgot/reset", function(){
        /* Método validForgotDecrypt retorna os dados do usuário em questão para a variável $user*/
        $user = User::validForgotDecrypt($_GET["code"]);

        $page = new Page();
        /*adiciona arquivo com conteúdo da página forgot! Passando duas variáveis conforme necessário para o
        template em questão*/
        $page->setTpl("forgot-reset", array(
            "name"=>$user["desperson"],
            "code"=>$_GET["code"]
        ));
    });
    /* Rota para página de esqueceu a senha */
    $app->post("/forgot/reset", function(){
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

        $page = new Page();
        /*adiciona arquivo com conteúdo da página forgot! Passando duas variáveis conforme necessário para o
        template em questão*/
        $page->setTpl("forgot-reset-success");
    });
    /* Rota para exibir a página de edição/alteração do usuário */
    $app->get("/profile", function(){
        /* Verifica se o usuário está logado no site. Nesse caso, como é uma rota de compra, deve ser passado o parametro como false
        para que o metódo redirecione corretamente para o ambiente de usuário e senha do site e não da administração */
        User::verifyLogin(false);

        /* Recuperar usuário que está na sessão */
        $user = User::getFromSession();

        $page = new Page();
        $page -> setTpl("profile",[
            'user'=>$user->getValues(),
            'profileMsg'=>User::getSuccess(),
            'profileError'=>User::getError()
        ]);
    });
    /* Rota para permitir que as alterações sejam salvas no banco de dados */
    $app->post("/profile", function(){
        /* Verifica se o usuário está logado no site. Nesse caso, como é uma rota de compra, deve ser passado o parametro como false
        para que o metódo redirecione corretamente para o ambiente de usuário e senha do site e não da administração */
        User::verifyLogin(false);
        /* Verifica se os dados foram corretamente preenchidos antes de iniciar com as rotinas de recuperação e alteração dos dados */
        if (!isset($_POST['desperson']) || $_POST['desperson'] === ''){
            User::setError("Preencha o seu nome.");
            /* Se houver o erro, mandamos redirecionar para o profile */
            header("Location: /profile");
            exit;
        }
        if (!isset($_POST['desemail']) || $_POST['desemail'] === ''){
            User::setError("Preencha o seu email.");
            /* Se houver o erro, mandamos redirecionar para o profile */
            header("Location: /profile");
            exit;
        }
        /* Recuperar usuário que está na sessão */
        $user = User::getFromSession();
        /* Como o objetivo seria alterar os dados, inclusive o e-mail se for de interesse do usuário, nesse caso é necessário
        verificar se existe outro usuário com o mesmo e-mail cadastrado, uma vez que o e-mail também é o nome de usuário.
        Para evitar problemas com essa rotina, antes de validar, verificarmos se o usuário fez alguma alteração no e-mail, caso não,
        não precisamos fazer a validação, uma vez que existe o cadastro dele próprio com esse mesmo e-mail ou por consequência, usuário */
        if ($_POST['desemail'] !== $user->getdesemail()){
            if (User::checkLoginExist($_POST['desemail']) === true){
                User::setError("Este endereço de e-mail já está cadastrado.");
                /* Se houver o erro, mandamos redirecionar para o profile */
                header("Location: /profile");
                exit;
            }
        }
        /* Para evitar que o usuário, através de sqlinjection por exemplo force uma alteração em usuário administradores, passando via post
        por exemplo o inadmin como 1, podemos carregar essa variável e a senha oficiais do usuário, direto do banco, conforme abaixo */
        $_POST['iduser'] = $user->getiduser();
        $_POST['inadmin'] = $user->getinadmin();
        $_POST['despassword'] = $user->getdespassword();
        //$_POST['deslogin'] = $_POST['desemail']; - nesta linha, se o usuário for admin vai gerar problemas
        /* Como o usuário já está instanciado na sessão, basta passar o post para atualização dos dados */
        $user -> setData($_POST);
        $user->update();
        $_SESSION[User::SESSION] = $user->getValues();
        /* Como a rotina conseguiu salvar corretamente os dados, geramos uma mensagem de sucesso para visualização do usuario */
        User::setSuccess("Dados alterados com sucesso!");
        /* Após atualização dos dados com o update, redirecionamos para a mesma página com os novos dados */
        header("Location: /profile");
        exit;
    });
    /* Rota para finalizar pedidos */
    $app->get("/order/:idorder", function($idorder){
        /* Verifica se o usuário está logado no site. Nesse caso, como é uma rota de compra, deve ser passado o parametro como false
        para que o metódo redirecione corretamente para o ambiente de usuário e senha do site e não da administração */
        User::verifyLogin(false);
        /* Carregamos a ordem utilizando o seu ID */
        $order = new Order();
        $order->get((int)$idorder);
        /* Para fazer o teste da order, pode utilizar o var-dump */
        /*
        var_dump ($order);
        exit;
        */
        $page = new Page();
        $page -> setTpl("payment",[
            'order'=>$order->getValues()
        ]);
    });
    /* Rota para boletos relativos a pedidos */
    $app->get("/boleto/:idorder", function($idorder){
        /* Verifica se o usuário está logado no site. Nesse caso, como é uma rota de compra, deve ser passado o parametro como false
        para que o metódo redirecione corretamente para o ambiente de usuário e senha do site e não da administração */
        User::verifyLogin(false);
        /* Carregamos a ordem utilizando o seu ID */
        $order = new Order();
        $order->get((int)$idorder);
        /* Verificação de dados do boleto */
        /*
        var_dump($order);
        exit;
        */
        // DADOS DO BOLETO PARA O SEU CLIENTE
        $dias_de_prazo_para_pagamento = 10;
        $taxa_boleto = 5.00;
        $data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
        /* Apesar do formatPrice fazer exatamente o que é soicitado pela regra em questão, nesse ponto utilizamos outro método para calcular
        corretamente o valor total das compras, e no retorno, o valor já está formatado. */
        /*
        $valor_cobrado = formatPrice($order->getvltotal()); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
        */
        //$valor_cobrado = $order->getvltotal(); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
        /* Após as correções anteriores, na aula 123, o problema foi corrigido da seguinte forma */
        $valor_cobrado = formatPrice($order->getvltotal()); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
        $valor_cobrado = str_replace(".", "",$valor_cobrado);
        $valor_cobrado = str_replace(",", ".",$valor_cobrado);
        $valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

        $dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
        $dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
        $dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
        $dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
        $dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
        $dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

        // DADOS DO SEU CLIENTE
        $dadosboleto["sacado"] = $order->getdesperson();
        $dadosboleto["endereco1"] = $order->getdesaddress()
                            . " " . $order->getdesdistrict();
                            
        $dadosboleto["endereco2"] = $order->getdescity()
                          . " - " . $order->getdesstate()
                          . " - " . $order->getdescountry()
                         ." CEP: ". $order->getdeszipcode();
        
        // INFORMACOES PARA O CLIENTE
        $dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Hcode E-commerce";
        $dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
        $dadosboleto["demonstrativo3"] = "";
        $dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
        $dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
        $dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@hcode.com.br";
        $dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hcode E-commerce - www.hcode.com.br";

        // DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
        $dadosboleto["quantidade"] = "";
        $dadosboleto["valor_unitario"] = "";
        $dadosboleto["aceite"] = "";		
        $dadosboleto["especie"] = "R$";
        $dadosboleto["especie_doc"] = "";


        // ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


        // DADOS DA SUA CONTA - ITAÚ
        $dadosboleto["agencia"] = "3227"; // Num da agencia, sem digito
        $dadosboleto["conta"] = "01002152";	// Num da conta, sem digito
        $dadosboleto["conta_dv"] = "7"; 	// Digito do Num da conta

        // DADOS PERSONALIZADOS - ITAÚ
        $dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

        // SEUS DADOS
        $dadosboleto["identificacao"] = "Frisa Comunicacao";
        $dadosboleto["cpf_cnpj"] = "24.700.731/0001-08";
        $dadosboleto["endereco"] = "Rua Ademar Saraiva Leão, 234 - Alvarenga, 09853-120";
        $dadosboleto["cidade_uf"] = "São Bernardo do Campo - SP";
        $dadosboleto["cedente"] = "HCODE TREINAMENTOS LTDA - ME";

        // NÃO ALTERAR!
        $path = $_SERVER['DOCUMENT_ROOT'] . 
            DIRECTORY_SEPARATOR . "resource" . 
            DIRECTORY_SEPARATOR . "boletophp" . 
            DIRECTORY_SEPARATOR . "include" .
            DIRECTORY_SEPARATOR;
        require_once($path . "funcoes_itau.php");
        require_once($path . "layout_itau.php");
        /* 
        include("include/funcoes_itau.php"); 
        include("include/layout_itau.php");
        */
    });
    /* Rota para mostrar os pedidos vinculados a conta do usuário */
    $app->get("/profile/orders", function(){
        /* Verifica se o usuário está logado no site. Nesse caso, como é uma rota de compra, deve ser passado o parametro como false
        para que o metódo redirecione corretamente para o ambiente de usuário e senha do site e não da administração */
        User::verifyLogin(false);
        
        $user = User::getFromSession();

        $page = new Page();
        $page->setTpl("profile-orders",[
            'orders'=>$user->getOrders()
        ]);
    });
    /* Rota para mostrar os pedidos vinculados a conta do usuário */
    $app->get("/profile/orders/:idorder", function($idorder){
        /* Verifica se o usuário está logado no site. Nesse caso, como é uma rota de compra, deve ser passado o parametro como false
        para que o metódo redirecione corretamente para o ambiente de usuário e senha do site e não da administração */
        User::verifyLogin(false);
        
        $order = new Order();
        $order->get((int)$idorder);

        $cart = new Cart();
        $cart->get((int)$order->getidcart());
        $cart->getCalculateTotal();
        
        $page = new Page();
        $page->setTpl("profile-orders-detail",[
            'order'=>$order->getValues(),
            'cart'=>$cart->getValues(),
            'products'=>$cart->getProducts()
        ]);
    });
?>