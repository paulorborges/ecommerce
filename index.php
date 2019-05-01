<?php 
/* busca as dependencias do projeto */
require_once("vendor/autoload.php");
/* classes necessarias */
use \Slim\Slim;
use \Hcode\Page;
/* criacao do objeto para utilização de rotas */
$app = new Slim();

$app->config('debug', true);
/* metodo para trabalhar com as rotas */
$app->get('/', function() {
    
    $page = new Page();
    $page->setTpl("index");
	//echo "OK";
	//$sql = new Hcode\DB\Sql();
	//$results = $sql->select("SELECT * FROM tb_users");
	//echo json_encode($results);
});
/* inicia a montagem da pagina */
$app->run();

?>