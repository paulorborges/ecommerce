<?php

    use \Hcode\Model\User;

    /* Método para formatar o preço em reais. Sem o método, os preços seriam exibidos com ponto para separação dos
    centavos e não com a vírgrula */
    function formatPrice(float $vlprice){
        return number_format($vlprice, 2, ",", ".");
    }
    /* Método utilizado nesse local para permitir a utilização da mesma em escopo global. Ela recebe o inadmin como true e passa essa
    informação para o método checkLogin da classe User. */
    function checkLogin($inadmin = true){
        return User::checkLogin($inadmin);
    }
    /* Método utilizado para pegar a sessão do usuário logado e retornar o nome do mesmo, também em escopo global */
    function getUserName(){
        $user = User::getFromSession();
        /* para verificar informações do objeto user
        var_dump ($user);
        exit;
        */
        return $user->getdesperson();
    }
?>