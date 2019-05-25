<?php

    use \Hcode\Page;
    use \Hcode\Model\Products;
    use \Hcode\Model\Category;

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
?>