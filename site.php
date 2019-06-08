<?php

    use \Hcode\Page;
    use \Hcode\Model\Products;
    use \Hcode\Model\Category;
    use \Hcode\Model\Cart;
    use \Hcode\Model\Address;
    use \Hcode\Model\User;

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
        /* Pega o carrinho que está na sessão */
        $cart = Cart::getFromSession();
        /* Captura o endereço */
        $address = new Address();
        $page = new Page();
        $page -> setTpl("checkout", [
            'cart'=>$cart->getValues(),
            'address'=>$address->getValues()
        ]);
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
?>