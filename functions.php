<?php

    use \Hcode\Model\User;
    use \Hcode\Model\Cart;

    /* Método para formatar o preço em reais. Sem o método, os preços seriam exibidos com ponto para separação dos
    centavos e não com a vírgrula */
    /* Como na primeira vez que você entra no carrinho o valor pode ser zero, essa função acaba mostrando um erro. Para evitar, vamos remover
    o casting do valor e verificar se o mesmo contem um valor maior que zero. Caso não tenha, definimos como zero. */
    //function formatPrice(float $vlprice){
    function formatPrice($vlprice){
        if (!$vlprice > 0) $vlprice = 0;
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
    /* Método utilizado para atualizar quantidade de produtos na imagem do carrinho resumido. Canto superior direito de todas as páginas */
    function getCartNrQtd(){
        $cart = Cart::getFromSession();
        $totals = $cart->getProductsTotals();
        return $totals['nrqtd'];
    }
    /* Método utilizado para atualizar o valor de produtos na imagem do carrinho resumido. Canto superior direito de todas as páginas */
    function getCartVlSubTotal(){
        $cart = Cart::getFromSession();
        $totals = $cart->getProductsTotals();
        return formatPrice($totals['vlprice']);
    }
?>