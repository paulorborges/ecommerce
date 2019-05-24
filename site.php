<?php

    use \Hcode\Page;

    /* metodo para trabalhar com as rotas */
    $app->get('/', function() {
        //chama o construct e adiciona o header
        $page = new Page();
        //adiciona arquivo com conteúdo da página index!
        $page->setTpl("index");
        //echo "OK";
        //$sql = new Hcode\DB\Sql();
        //$results = $sql->select("SELECT * FROM tb_users");
        //echo json_encode($results);
        /* quando chega na última linha do app, o php limpa a memória chamando o destruct e adiciona o footer na página */
    });
?>