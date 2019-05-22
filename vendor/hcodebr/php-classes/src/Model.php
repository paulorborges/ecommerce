<?php

    namespace Hcode;

    class Model{
        /* o método values terá todos os valores dos objetos */
        private $values = [];
        /* para identificar que método foi chamado, utiliza-se o método mágico call */
        public function __call($name,$args){
            /* como os métodos get e set possuem funcionalidades diferentes, primeiro se identifica que 
            método será analisado e depois a sua funcionalidade. */
            $method = substr ($name, 0, 3); //a partir da posição zero, traga 3 posições
            $fieldName = substr ($name, 3, strlen($name)); // a partir da posição 3 (4 dígito = 0 1 2 3) traga o restante das posições
            //var_dump($method,$fieldName);
            /* para evitar que seja feito um redirect e não seja possível avaliar o resultado, necessário utilizar o exit */
            //exit;
            /* */
            switch($method){

                case "get":
                    /* Para os casos onde não houver o id no array passado, pode haver um problema por causa do valor 
                    que ainda não foi definido. Nesse caso, pode ser utilizado o isset e o if ternário para evitar o 
                    problema de indice indefinido. No modelo abaixo, se foi definido, retorna ele mesmo, se não, retorna
                    nulo. */
                    //return $this->values[$fieldName];
                    return (isset($this->values[$fieldName])) ? $this->values[$fieldName] : NULL;
                break;

                case "set":
                    $this->values[$fieldName] = $args[0];
                break;

            }
        }

        public function setData ($data = array()){
            foreach ($data as $key => $value) {
                /* como está sendo criado cada variável dinamicamente, uma vez que essa rotina será utilizada para várias
                funções do sistema, o PHP permite a junção deses elementos conforme abaixo. Qualquer criação dinamica no PHP
                precisa vir entre chaves */
                $this->{"set".$key}($value);
            }
        }

        public function getValues (){
            /* método que permite capturar os valores. */
            return $this->values;
        }
    }
?>