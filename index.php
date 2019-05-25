<?php 
/* para definir que a aplicação utilizará sessões, necessário utilizar o comanado session_start. Pode ser utilizado 
também o if isset, ou seja, caso já exista o session id, não precisa rodar o session start. */
session_start();
/* busca as dependencias do projeto */
require_once("vendor/autoload.php");
/* classes necessarias */
use \Slim\Slim;
/* criacao do objeto para utilização de rotas */
$app = new Slim();
$app->config('debug', true);
/* Como o arquivo index está ficando muito grande por abrigar todas as rotas. Podem ser criados arquivos menores por
categoria ou menu por exemplo e os mesmos serem incluídos nesse por meio do require once conforme exemplo abaixo */
require_once("site.php");
require_once("functions.php");
require_once("admin.php");
require_once("admin-users.php");
require_once("admin-categories.php");
require_once("admin-products.php");

/* inicia a montagem da pagina */
$app->run();
?>