<?php 
/* para definir que a aplicação utilizará sessões, necessário utilizar o comanado session_start. Pode ser utilizado 
também o if isset, ou seja, caso já exista o session id, não precisa rodar o session start. */
session_start();
/* busca as dependencias do projeto */
require_once("vendor/autoload.php");
/* classes necessarias */
use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
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
	/* Caso o admin seja chamado diretamente, o sistema deve verificar se o usuário está logado (se existe uma sessão válida).
	Apenas nessa condição deve ser exibido os dados.*/
	User::verifyLogin();
	//chama o construct e adiciona o header
    $page = new PageAdmin();
    //adiciona arquivo com conteúdo da página index!
    $page->setTpl("index");
	/* quando chega na última linha do app, o php limpa a memória chamando o destruct e adiciona o footer na página */
});
/* metodo para trabalhar com as rota do login */
$app->get('/admin/login', function() {
	/*chama o construct e adiciona o header. Como a página de login não tem um header, footer ou index e vai 
	aproveitar a estrutura da página atual, nesse caso é necessário passar alguns parametros para desabilitar 
	a montagem da página por arquivos distribuídos. */
    $page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
    //adiciona arquivo com conteúdo da página index!
    $page->setTpl("login");
	/* quando chega na última linha do app, o php limpa a memória chamando o destruct e adiciona o footer na página */
});
/* */
$app->post('/admin/login', function() {
	/* Nesse método, através da classe User, verifica se o login e senha são verdadeiros e caso seja, redireciona para o
	location adequado */
	User::login($_POST["login"], $_POST["password"]);
	header("Location: /admin");
	exit;
});
/* Rota para o método logout */
$app->get('/admin/logout', function() {
	/* Nesse método, basta chamar o método logout da classe User */
	User::logout();
	/* Após o logout, a página pode ser redirecionada para o login ou realizado outra chamada contra a loja virtual */
	header("Location: /admin/login");
	exit;
});
/* inicia a montagem da pagina */
$app->run();
?>