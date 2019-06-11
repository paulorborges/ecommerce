<?php

    namespace Hcode\Model;

    use \Hcode\Model;
    //use \Hcode\Mailer;
    use \Hcode\DB\Sql;
    use \Hcode\Model\User;

    class Cart extends Model{
        /* Quando o usuário insere o primeiro item no carrinho, ele basicamente está criando o carrinho. A partir do momento que
        ele exclui ou inclui novos, ao inves do insert, precisamos utilizar o update. Desta forma, como vamos saber que carrinho
        alterar? Para isso, necessário trabalhar com a sessão do usuário e dessa forma, enquanto a sessão estiver ativa, 
        utilizaremos sempre o mesmo carrinho que foi instanciado na sessão vigente. Para isso, vamos utilizar uma constante
        para permitir a seleção em vários lugares diferentes do projeto */
        const SESSION = "Cart";
        /* Declaração da constante que contem a session do error. Constante utilizada por exemplo no carrinho de compras para tratar
        possíveis mensagens de erro enviadas pelo sistema do correio. */
        const SESSION_ERROR = "CartError";
        /* Método para verificar se é necessáiro inserir um carrinho novo, se devemos apenas editar o carrinho existente, 
        se a sessão foi perdida por causa do tempo mas se eu ainda tenho o ID do carrinho em questão, etc. */
        public static function getFromSession(){
            $cart = new Cart();
            /* Primeiro, devemos verificar se o carrinho já está na sessão. Isset(session(cartSession)) = verifica se
            a sessão (nome da sessão) do carrinho já existe. Caso exista, verifica-se se o ID da sessão já foi definido,
            uma vez que apesar da sessão ter sido definida, pode ser que ela esteja vazia. */
            if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0){
                /* Caso a sessão exista e o id da sessão seja maior que zero, vamos carregar o carrinho */
                $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
            } else {
                /* Se a sessão ou o id não existirem, primeiro verificarmos se a partir do sessionId conseguimos recuperar
                o carrinho */
                $cart -> getFromSessionID();
                /* Caso o retorno do getFromSessionID esteja vazio, precisamos carregar um novo carrinho. Dessa forma, podemos
                testar se o retorno não for maior que zero, ou seja, ele não conseguiu recuperar nenhuma informação */
                if(!(int)$cart->getidcart() > 0){
                    /* Nesse ponto, não foi retornado nenhuma informação e criamos um novo carrinho */
                    $data = [
                        /* função session_id pertence ao proprio PHP */
                        'dessessionid'=>session_id()
                    ];
                    /* Para verificar se o usuário está logado, posso utilizar o método checklogin. Como o processo em 
                    questão é uma função do site e não da administração, o parâmetor a ser enviado é false conforme 
                    abaixo. Se o retorno da função for true quer dizer que está loado e entra no if para realização das
                    funções */
                    if(User::checkLogin(false)){
                        /* Nesse ponto, o usuário pode ser desconhecido e estar apenas manipulando o carrinho, portanto, 
                        por hora não seria obrigatório identificar o usuário logado mas seria muito interessante. Se 
                        eu souber que usuário está logado, posso por exemplo enviar um e-mail pra ele avisando que ele 
                        esqueceu de finalizar a compra e enviar os itens do carrinho. Para isso, posso tentar identificar 
                        que usuário está logado utilizado o seguinte código. */
                        $user = User::getFromSession();
                        /* Como o usuári está logado, posso nesse ponto enviar para o data o id do usuário */
                        $data['iduser'] = $user->getiduser();
                    }
                    /* Após as validações do usuário, fica pendente passar para o carrinho quais são os dados gravados
                    no array data e dessa forma, ou ida da sessão e um usuário válido ou o id da sessão */
                    $cart->setData($data);
                    /* Após carregar as informações no objeto cart, precisamos salvar as mesmas no banco */
                    $cart->save();
                    /* Como estamos falando de um carrinho novo, precisamos colocar na sessão porque da próxima vez que 
                    passar por esse método o mesmo vai pegar as informações da sessão e não criar outro carrinho */
                    $cart -> setToSession();
                }
            }
            /* Após as validações, retorna-se o carrinho existe ou um novo carrinho */
            return $cart;
        }
        /* Metodo para setar o objeto cart na sessão de atual. Como nesse caso vamos utilizar a variável this, o método
        não pode ser estático */
        public function setToSession(){
            $_SESSION[Cart::SESSION] = $this->getValues();
        }
        /* Metodo para remover o objeto cart na sessão de atual. Como nesse caso vamos utilizar a variável this, o método
        não pode ser estático */
        public function removeSession(){
            $_SESSION[Cart::SESSION] = NULL;
            session_regenerate_id();
        }
        /* Método estático para remover itens do carrinho quando houver o logoff do usuário por exemplo */
        public static function removeFromSession(){
            $_SESSION[Cart::SESSION] = NULL;
            /* A opção abaixo não foi sugerida na pergunta relativa ao problema do carrinho do primeiro usuário aparecer para o segundo
            usuário, aula 125, conforme implementação na rota get /logout do site.php*/
            //session_regenerate_id();
        }
        /* Método para carregar o id da sessão. Esse método não precisa de parametro uma vez que o PHP possui a função que
        realiza essa ação (sessionID) */
        public function getFromSessionID(){
            $sql = new Sql();
            $results = $sql->select("SELECT *
                FROM tb_carts
                WHERE dessessionid = :dessessionid",[
                    ':dessessionid'=>session_id()
                ]
            );
            /* Como o id do carrinho pode ser vazio, para evitar o erro de enviar um result sem nenhuma informação, pode
            ser utilizado o count */
            if(count($results) > 0){
                /* O resultado na posição zero é adicionado ao objeto */
                $this->setData($results[0]);
            }
        }
        /* Método para carregar a sessão e o id do carrinho */
        public function get(int $idcart){
            $sql = new Sql();
            $results = $sql->select("SELECT *
                FROM tb_carts
                WHERE idcart = :idcart",[
                    ':idcart'=>$idcart
                ]
            );
            /* Como o id do carrinho pode ser vazio, para evitar o erro de enviar um result sem nenhuma informação, pode
            ser utilizado o count */
            if(count($results) > 0){
                /* O resultado na posição zero é adicionado ao objeto */
                $this->setData($results[0]);
            }
        }
        /* Método para salvar os itens selecionados ao carrinho */
        public function save(){
            $sql = new Sql();
            $results = $sql->select("CALL sp_carts_save (:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
                ':idcart'=>$this->getidcart(),
                ':dessessionid'=>$this->getdessessionid(),
                ':iduser'=>$this->getiduser(),
                ':deszipcode'=>$this->getdeszipcode(),
                ':vlfreight'=>$this->getvlfreight(),
                ':nrdays'=>$this->getnrdays()
            ]);
            /* Após chamada da procedure, o results passa a ter os dados do carrinho. Com essas informações, passamos os dados para
            o objeto com o setData conforme abaixo */
            $this->setData($results[0]);
        }
        /* Método para adicionar produtos ao carrinho. Recebe uma instancia da classe Product */
        public function addProduct(Products $product){
            $sql = new Sql();
            /* Função para inserir produtos ao carrinho */
            $sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct)
                VALUES(:idcart, :idproduct)", [
                    ':idcart'=>$this->getidcart(),
                    ':idproduct'=>$product->getidproduct()
                ]
            );
            /* Para caso das quantidades serem alteradas, recalcula o valor do frete */
            $this->getCalculateTotal();
        }
        /* Método para remoção dos produtos do carrinho não deve excluir os mesmos, apenas marcar que eles foram removidos.
        Essa técnica permite que posteriormente seja feito uma análise do funil de vendas afim de avaliar quais produtos
        foram incluídos e removidos e em que momento. Além de receber a instancia da classe product, esse método recebe
        também uma variável all que por default ela é setada como false. Essa variável permite uma análise se a remoção é
        de uma quantidade do item do carrinho ou se seria do item inteiro (todas as unidades). Isso basicamente verifica
        se o usuário está aumentando ou diminuindo a quantidade de itens comprados ou clicando no x para remover o item por
        copmleto */
        public function removeProduct(Products $product, $all = false){
            $sql = new Sql();
            
            if ($all){
                /* Função para remover o item por completo, independente da quantidade de unidades deste item */
                $sql -> query("UPDATE tb_cartsproducts
                    SET dtremoved = NOW()
                    WHERE idcart = :idcart
                    AND idproduct = :idproduct
                    /* verifica se dtRemoved é nulo ou não. Senão podemos ficar setando a data de remoção de um item que 
                    já foi setado */
                    AND dtremoved IS NULL
                    ",[
                        ':idcart'=>$this->getidcart(),
                        ':idproduct'=>$product->getidproduct()
                    ]
                );
            } else {
                /* Função para remover uma unidade do item em questão, porém mantém o item no carrinho */
                $sql -> query("UPDATE tb_cartsproducts
                    SET dtremoved = NOW()
                    WHERE idcart = :idcart
                    AND idproduct = :idproduct
                    /* verifica se dtRemoved é nulo ou não. Senão podemos ficar setando a data de remoção de um item que 
                    já foi setado */
                    AND dtremoved IS NULL
                    /* para garantir que seja removido apenas 1, utilizamos o limit */
                    LIMIT 1
                    ",[
                        ':idcart'=>$this->getidcart(),
                        ':idproduct'=>$product->getidproduct()
                    ]
                );
            }
            /* Para caso das quantidades serem alteradas, recalcula o valor do frete */
            $this->getCalculateTotal();
        }
        /* Metodo para listar produtos do carrinho */
        public function getProducts(){
            $sql = new Sql();
            /* Para testar a query abaixo, podemos utilizar o var-dump */
            /*
            var_dump("SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, 
                b.vlheight, b.vllength, b.vlweight, b.desurl,
                    COUNT(*) AS nrqtd, 
                    SUM (b.vlprice) AS vltotal
                FROM tb_cartsproducts a
                INNER JOIN tb_products b
                ON a.idproduct = b.idproduct
                WHERE a.idcart = :idcart
                AND a.dtremoved IS NULL
                GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
                ORDER BY b.desproduct");
            exit;
            */
            /* Como iremos utilizar o group by, nesse caso não devemos utilizar o select * e sim apontar quais colunas
            devem ser agrupadas e como elas serão agrupadas (count, sum, etc) */
            $rows = $sql->select("SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, 
                b.vlheight, b.vllength, b.vlweight, b.desurl,
                    COUNT(*) AS nrqtd, 
                    SUM(b.vlprice) AS vltotal
                FROM tb_cartsproducts a
                INNER JOIN tb_products b
                ON a.idproduct = b.idproduct
                WHERE a.idcart = :idcart
                /* verifica também se o produto não foi removido */
                AND a.dtremoved IS NULL
                /* O agrupamento permite gerar totais por categoria ou conforme os parametros selecionados */
                GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
                /* Ordenado pelo nome do produto */
                ORDER BY b.desproduct
            ", [
                ':idcart'=>$this->getidcart()
            ]);
            /* Importante fazer tambem o tratamento dos objetos e das figuras. Para isso, temos o metodo checkList na 
            classe product */
            return Products::checkList($rows);
        }
        /* Metodo para totalizar produtos do carrinho */
        public function getProductsTotals(){
            $sql = new Sql();
            $results = $sql->select("SELECT 
                SUM(vlprice) AS vlprice, 
                SUM(vlwidth) AS vlwidth,
                SUM(vlheight) AS vlheight,
                SUM(vllength) AS vllength,
                SUM(vlweight) AS  vlweight,
                COUNT(*) AS nrqtd
                FROM tb_products a
                INNER JOIN tb_cartsproducts b
                ON a.idproduct = b.idproduct
                WHERE b.idcart = :idcart
                AND dtremoved IS NULL;
            ",[
                /* Como o metodo está na mesma classe, basta utilizar o this */
                ':idcart'=>$this->getidcart()
            ]);
            /* Verifica se houve algum erro na seleção do itens */
            if(count($results) > 0) {
                return $results[0];
            } else {
                /* Se não encontrou nada, retorna um array vazio */
                return [];
            }
        }
        /* Método para calcular o valor do frete */
        public function setFreight($nrzipcode){
            /* Caso o usuário digite o código adicionando o tracinho, devemos remove-lo para evitar problemas com o webservice */
            //$nrzipcode = "04180112";
            $nrzipcode = str_replace('-','',$nrzipcode);
            /* Verifica as informações totais do carrinho */
            /* verifica se houve a passagem do cep */
            /* 
            echo $nrzipcode;
            exit;
            */
            $totals = $this->getProductsTotals();
            /* verifica se foi possível pegar o total dos produtos */
            /*
            var_dump ($totals);
            exit;
            */
            /* Verifica se existe alguma informação no carrinho */
            if($totals['nrqtd'] > 0){
                /* Os cálculos abaixo foram realizados apenas para testes. Lembrar que com mais de um objeto, pode ser necessário somar
                por exemplo as alturas e utilizar o maior comprimento para identificar uma caixa que caiba todo o conteúdo. O fato é que,
                apenas somar as dimensões dos objetos provavelmente não vai espelhar o correto dimensionamento e frete */

                /* Existem várias regras de negócio que podem retornar erro. Uma delas é a comprimento do objeto (nVlComprimento) dependendo 
                do tipo de serviço (nCdServico). Para o exemplo abaixo, vamos travar a quantidade no tamanho mínimo permitido para o cálculo 
                conforme regra de negócio. Deve ser considerado no desenvolvimento oficial que determinado produto não pode utilizar tais
                serviços, afim de não alterar a característica e configurar corretamente os dados. Metodo abaixo apenas para teste */
                if ($totals['vllength'] < 16 || $totals['vllength'] > 105){
                    $totals['vllength'] = 16;
                }
                if ($totals['vllength'] > 105){
                    $totals['vllength'] = 105;
                }
                /* Existem várias regras de negócio que podem retornar erro. Uma delas é a altura do objeto (nVlAltura) dependendo 
                do tipo de serviço (nCdServico). Para o exemplo abaixo, vamos travar a quantidade no tamanho máximo permitido para o cálculo 
                conforme regra de negócio. Deve ser considerado no desenvolvimento oficial que determinado produto não pode utilizar tais
                serviços, afim de não alterar a característica e configurar corretamente os dados. Metodo abaixo apenas para teste */
                if ($totals['vlheight'] < 2){
                    $totals['vlheight'] = 2;
                }
                if ($totals['vlheight'] > 105){
                    $totals['vlheight'] = 105;
                }
                /* Função para ajustar o tamanho da largura */
                if ($totals['vlwidth'] < 11){
                    $totals['vlwidth'] = 11;
                }
                if ($totals['vlwidth'] > 105){
                    $totals['vlwidth'] = 105;
                }
                /* Função para ajustar o tamanho máximo de todos os elementos dimensionais  
                if ($totals['vllength'] + $totals['vlheight'] + $totals['vlwidth'] > 200){
                    $totals['vlwidth'] = 50;
                    $totals['vlheight'] = 105;
                    $totals['vllength'] = 36;
                }*/
                /* Para realizar a montagem da string com as variaveis separadas por & e de forma mais prática, é utilizando a ferramenta de
                montagem do php conforme abaixo */
                $qs = http_build_query([
                    'nCdEmpresa'=>'',
                    'sDsSenha'=>'',
                    'nCdServico'=>'40010', /*Nesse caso o serviço ficou fixo por causa do teste, se der opção de escolha para o usuário, 
                    necessário alterar formulário para permitir a seleção via combo por exemplo */
                    'sCepOrigem'=>'35661326', //cep para teste, necessário pegar essa informação do cadastro da loja
                    'sCepDestino'=>$nrzipcode,
                    'nVlPeso'=>$totals['vlweight'],
                    'nCdFormato'=>'1', /*formato de caixa ou pacote conforme manual. Necessário avaliar opção para ajuste 
                    do cadastro do produto */
                    'nVlComprimento'=>$totals['vllength'],
                    'nVlAltura'=>$totals['vlheight'],
                    'nVlLargura'=>$totals['vlwidth'],
                    'nVlDiametro'=>'0', //Verificar cadastro na tabela de produtos para carregar essa informação. Quando não houver, passar zero.
                    'sCdMaoPropria'=>'S', /* Opção de serviço do correio, S para Sim ou N para Não. Verificar parametro para deixar que o usuário
                    selecione essa opção. Esse serviço afeta o valor do frete. */
                    'nVlValorDeclarado'=>$totals['vlprice'],// valor total do carrinho
                    'sCdAvisoRecebimento'=>'S' /* Opção de serviço do correio, S para Sim ou N para Não. Verificar parametro para deixar que o 
                    usuário selecione essa opção. Esse serviço afeta o valor do frete. */
                ]);
                /* Realiza o cálculo do frete. Como o retorno do site ocorre no formato XML, a função abaixo precisa fazer essa leitura */
                $xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);
                /* para analisar o retorno, pode ser utilizado a função de echo e utilizar o json para análise. Como o resultado está 
                em formato XML, converte-se para um array */
                /*
                var_dump($totals);
                //var_dump($xml);
                echo json_encode((array)$xml);
                //exit;
                */
                /* Com o retorno dos dados no objeto xml, podemos setar as informações para que o usuário avalie o valor calculado do frete,
                prazo de entrega, etc. Importante reparar que o retorno ocorreu encadeado em vários níveis, no formato de árvore, portanto, 
                para acessar a informação é importante percorrer os nós da árvore conforme exemplos abaixo (Servico->cServico) */
                $result = $xml->Servicos->cServico;
                /* Para exibir alguma possível mensagem de erro que ainda não foi tratada no campo reservado para essa finalidade, podemos
                verificar se a mensagem de erro é diferente de vazio e exibí-la para melhor análise */
                /* Teste do objeto mensagem */
                /*               
                //var_dump($result->MsgErro);
                var_dump($result);
                exit;
                */
                if ($result->MsgErro != '') {
                    /* Se a mensagem de erro é diferente de vazio, passamos a mesma */
                    Cart::setMsgError((string)$result->MsgErro);
                    return false;
                } else {
                    /* Se não houve mensagem de erro, podemos limpar a sessão de erro */
                    Cart::clearMsgError();
                    /* Verifica as informações do objeto Cart */
                    /*
                    var_dump ($result->MsgErro);
                    var_dump (setnrdays($result->PrazoEntrega));
                    var_dump (Cart::formatValueToDecimal($result->Valor));
                    var_dump (setdeszipcode($nrzipcode));
                    exit;
                    */
                    /* Para inserir a informação do prazo de entrega no objeto, segue abaixo */
                    $this->setnrdays($result->PrazoEntrega);
                    /* O retorno do correio manda o valor no formato brasileiro mas no banco de dados salvamos no formato americado, portanto, 
                    necessário converter conforme abaixo */
                    $this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
                    /* Para inserir a informação do código de CEP no objeto, segue abaixo */
                    $this->setdeszipcode($nrzipcode);
                    /* Grava as informações no banco */
                    $this->save();
                    return $result;
                }
            } else {
                /* Caso não exista informações no carrinho, zera os dados e avisa ao usuário */
                /*
                $sql = new Sql();
                $sql->query("UPDATE tb_carts 
                    SET deszipcode = NULL, vlfreight = NULL, nrdays = NULL 
                    WHERE idcart = :idcart ", [
                        ':idcart'=>$this->getidcart()
                    ]
                );
                */
                /* Se não houve mensagem de erro, podemos limpar a sessão de erro */
                Cart::clearMsgError();
                $this->getCalculateTotal();             
                //return Cart::setMsgError("Carrinho de Compra não possui itens!");
            }
        }
        /* Método para converter valores brasileiros em americanos */
        public static function formatValueToDecimal($value):float{
            /* primeiro retiramos os pontos por nenhuma informação */
            $value = str_replace('.','',$value);
            /* agora retorno a informação trocando a vírgula por um ponto */
            return str_replace(',','.',$value);
        }
        /* Método para criar sessão de mensagem de erro */
        public static function setMsgError($msg){
            /* Criamos uma constante dentro do carrinho */
            $_SESSION[Cart::SESSION_ERROR] = $msg;
        }
        /* Método para recuperar a mensagem de erro */
        public static function getMsgError(){
            /* Verificamos se existe alguma mensagem de erro definida. Se existe, devolvemos a mensagem, se não, enviamos vazio */
            $msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";
            /* Após carregar a mensagem de erro na variável, podemos limpar a mesma */
            Cart::clearMsgError();
            /* Após limpar a mensagem na sessão, retornamos a mesma para o método que fez a chamada */
            return $msg;
        }
        /* Método para limpar a sessão da mensagem de erro */
        public static function clearMsgError(){
            /* Para limpar a sessão, igualamos a mesma a nulo */
            $_SESSION[Cart::SESSION_ERROR] = NULL;
        }
        public function updateFreight(){
            $products = $this->getProducts();
            /* Valores das condições do IF 
            var_dump($products);
            echo $this->getdeszipcode();
            */
            /* Se o CEP for diferente de vazio e houver produtos no carrinho, passo o CEP presente no objeto do carrinho */
            if ($this->getdeszipcode() != '' && count($products) > 0){
                $this->setFreight($this->getdeszipcode());
            } else {
                $this->setdeszipcode('');
                $this->setvlfreight(0);
            }
        }
        /* Método para sobreescrita do método getValues da classe extendida. O objetivo será preencher no carrinho de compra
        o subtotal referente ao valor dos produtos e total geral da compra incluindo o valor do frete */
        public function getValues(){
            $this->getCalculateTotal();
            return parent::getValues();
        }
        /* Método para verificar as informações totais do carrinho */
        public function getCalculateTotal(){
            /* atualiza o valor do frete após alterações de quantidade */
            $this->updateFreight();
            /* o getProductsTotals já possui os valores totais das quantidades, soma do preço, etc */
            $totals = $this->getProductsTotals();
            /* para verificar as informações do objeto */
            //var_dump($totals);
            if ((int)$totals['nrqtd'] > 0){
                /* agora adicionamos as informações que não existem dentro do cart */
                $this->setvlsubtotal($totals['vlprice']);
                $this->setvltotal($totals['vlprice'] + $this->getvlfreight());
            } else {
                /* Caso não tenha produtos no carrinho, alteramos as informações para exibir zerado e avisamos ao usuário. */
                $this->setvlsubtotal(0);
                $this->setvltotal(0);
                $this->setnrdays(0);
                return Cart::setMsgError("Carrinho de Compra não possui itens!");
            }
        }
        /* Método para zerar o valor do frete e dias de entrega caso o carrinho esteja vazio */
        public function checkZipCode(){
            $products = $this->getProducts();
            if (!count($products) > 0) {
                /* Caso o carrinho não possua nenhum produto e você queira remover os dados do frete, valor e número de dias do 
                banco de dados. Essa opção pressupoe que o usuário inseriu alguns produtos, digitou os dados para calcular o frete
                e depois removeu o produto. Utilizando a configuração abaixo o carrinho viria vazio em uma segunda inserção de dados */
                /*
                $sql = new Sql();
                $sql->query("UPDATE tb_carts 
                    SET deszipcode = NULL, vlfreight = NULL, nrdays = NULL 
                    WHERE idcart = :idcart ", [
                        ':idcart'=>$this->getidcart()
                    ]
                );
                */
                $this->setdeszipcode('');
                $this->setvlfreight(0);
            }
        }

    }   
?>