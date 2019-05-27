<?php

    use \Hcode\Page;
    use \Hcode\Model\Products;
    use \Hcode\Model\Category;
    use \Hcode\Model\Cart;

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

        $page = new Page();
        /* Além de indicar a rota, passa-se os parametros do carrinho, produtos, usuários, etc */
        $page -> setTpl("cart", [
            'cart'=>$cart->getValues(),
            'products'=>$cart->getProducts()
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
?>