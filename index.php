<?php 
/* busca as dependencias do projeto */
require_once("vendor/autoload.php");
/* classes necessarias */
use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
/* criacao do objeto para utilização de rotas */
$app = new Slim();
$app->config('debug', true);
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
/* metodo para trabalhar com as rota do admin */
$app->get('/admin/', function() {
	//chama o construct e adiciona o header
    $page = new PageAdmin();
    //adiciona arquivo com conteúdo da página index!
    $page->setTpl("index");
	/* quando chega na última linha do app, o php limpa a memória chamando o destruct e adiciona o footer na página */
});
/* inicia a montagem da pagina */
$app->run();
?>