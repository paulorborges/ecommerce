<?php

    namespace Hcode\Model;

    use \Hcode\Model;
    use \Hcode\DB\Sql;
    
    class Address extends Model{

        const SESSION_ERROR = "AddressError";

        /* Método para retornar verificar o endereço conforme o CEP */
        public static function getCEP($nrcep){
            /* metodo strreplace utilizado para retirar o hifen do CEP caso o usuário digite essa informação */
            $nrcep = str_replace("-","", $nrcep);
            /* Curlinit é utilizado para informar ao PHP que vamos rastrear uma URL*/
            $ch = curl_init();
            /* Informamos então quais opções o curl irá utilizar. */
            /* Primeiro parametro é a URL que iremos utilizar */
            curl_setopt($ch, CURLOPT_URL, "http://viacep.com.br/ws/" . $nrcep . "/json/");
            /* Segundo parametro informa para a url que estamos esperando um retorno sobre a mesma */
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            /* Terceiro parametro informa se vamos exigir algum tipo de autenticação, nesse caso, não = false */
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            /* Como o retorno foi atribuído a variável ch. Para capturar o retorno, utilizarmos o curlexec e nesse caso, serializamos o resultado
            em jasondecode passando true para que a variavel data seja um array e não um objeto */
            $data = json_decode(curl_exec($ch), true);
            /* Como o curl é um ponteiro, precisa ser encerrado com a função close. Se não fechar o ponteiro, a cada F5 da página, abrirá uma
            nova referencia de memoria e vai deixar o servidor pesado. */
            curl_close($ch);
            /* teste do objeto */
            /*
            var_dump ($data);
            exit;
            */
            /* retorno do objeto array data */
            return $data;
        }
        /* Método para carregar os dados do CEP */
        public function loadFromCEP ($nrcep){
            $data = Address::getCEP($nrcep);
            /* Verificamos se o data logradouro foi definido e se possui algum retorno - diferente de vazio */
            if (isset($data['uf']) && $data['uf'] !== ''){
                $this->setdesaddress($data['logradouro']);
                $this->setdescomplement($data['complemento']);
                $this->setdesdistrict($data['bairro']);
                $this->setdescity($data['localidade']);
                $this->setdesstate($data['uf']);
                $this->setdescountry('Brasil');
                $this->setdeszipcode($nrcep);
            }
            /* Caso não seja verificado um endereço válido, haverá um erro no carrinho. Seria importante tratar esse erro. No carrinho
            é importante verificar todos os dados antes de permitir a geração do boleto. */
        }
        /* Método para salvar o objeto endereço com as alterações realizados pelo usuário após preenchimento no momento de salvar a compra */
        public function save(){
            $sql = new Sql();
            $results = $sql->select("CALL sp_addresses_save(
                :idaddress, 
                :idperson, 
                :desaddress, 
                :descomplement, 
                :descity, 
                :desstate, 
                :descountry, 
                :deszipcode, 
                :desdistrict)", [
                    ':idaddress'=>$this->getidaddress(),
                    ':idperson'=>$this->getidperson(),
                    ':desaddress'=>utf8_decode($this->getdesaddress()),
                    ':descomplement'=>utf8_decode($this->getdescomplement()),
                    ':descity'=>utf8_decode($this->getdescity()),
                    ':desstate'=>utf8_decode($this->getdesstate()),
                    ':descountry'=>utf8_decode($this->getdescountry()),
                    ':deszipcode'=>$this->getdeszipcode(),
                    ':desdistrict'=>$this->getdesdistrict()
                ]
            );
            /* Verificamos se o result for maior que 0 para evitar erro de index */
            if (count($results)>0){
                $this->setData($results[0]);
            }
        }
        /* Método para criar sessão de mensagem de erro */
        public static function setMsgError($msg){
            /* Criamos uma constante dentro do carrinho */
            $_SESSION[Address::SESSION_ERROR] = $msg;
        }
        /* Método para recuperar a mensagem de erro */
        public static function getMsgError(){
            /* Verificamos se existe alguma mensagem de erro definida. Se existe, devolvemos a mensagem, se não, enviamos vazio */
            $msg = (isset($_SESSION[Address::SESSION_ERROR])) ? $_SESSION[Address::SESSION_ERROR] : "";
            /* Após carregar a mensagem de erro na variável, podemos limpar a mesma */
            Address::clearMsgError();
            /* Após limpar a mensagem na sessão, retornamos a mesma para o método que fez a chamada */
            return $msg;
        }
        /* Método para limpar a sessão da mensagem de erro */
        public static function clearMsgError(){
            /* Para limpar a sessão, igualamos a mesma a nulo */
            $_SESSION[Address::SESSION_ERROR] = NULL;
        }
    }
?>