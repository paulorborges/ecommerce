<?php
    /* Método para formatar o preço em reais. Sem o método, os preços seriam exibidos com ponto para separação dos
    centavos e não com a vírgrula */
    function formatPrice(float $vlprice){
        return number_format($vlprice, 2, ",", ".");
    }
?>