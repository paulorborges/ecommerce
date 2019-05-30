<?php if(!class_exists('Rain\Tpl')){exit;}?><div class="product-big-title-area">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="product-bit-title text-center">
                    <h2>Carrinho de Compras</h2>
                </div>
            </div>
        </div>
    </div>
</div> <!-- End Page title area -->

<div class="single-product-area">
    <div class="zigzag-bottom"></div>
    <div class="container">
        <div class="row">
            
            <div class="col-md-12">
                <div class="product-content-right">
                    <div class="woocommerce">

                        <form action="/checkout">
                            <!--Para verificar se existe uma mensagem de erro, podemos verificar se a variavel error e diferente de vazio -->
                            <?php if( $error != '' ){ ?>

                                <div class="alert alert-danger" role="alert">
                                    <!-- Caso tenha vindo alguma mensagem de erro, mostro na tela. Para isso, utilizo a mesma variável enviada pelo
                                        setTpl da rota get, presente no arquivo site.php
                                    Error!
                                    -->
                                    <?php echo htmlspecialchars( $error, ENT_COMPAT, 'UTF-8', FALSE ); ?>

                                </div>
                            <?php } ?>


                            <table cellspacing="0" class="shop_table cart">
                                <thead>
                                    <tr>
                                        <th class="product-remove">&nbsp;</th>
                                        <th class="product-thumbnail">&nbsp;</th>
                                        <th class="product-name">Produto</th>
                                        <th class="product-price">Preço</th>
                                        <th class="product-quantity">Quantidade</th>
                                        <th class="product-subtotal">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Loop dos produtos no carrinho -->
                                    <?php $counter1=-1;  if( isset($products) && ( is_array($products) || $products instanceof Traversable ) && sizeof($products) ) foreach( $products as $key1 => $value1 ){ $counter1++; ?>

                                        <tr class="cart_item">
                                            <td class="product-remove">
                                                <!-- Para remover o item do carrinho, adicionamos a rota correta
                                                <a title="Remove this item" class="remove" href="#">×</a> 
                                                -->
                                                <a title="Remove this item" class="remove" href="/cart/<?php echo htmlspecialchars( $value1["idproduct"], ENT_COMPAT, 'UTF-8', FALSE ); ?>/remove">×</a>
                                            </td>

                                            <td class="product-thumbnail">
                                                <!-- Para carregar corretamente a imagem salva no administrativo 
                                                <a href="#"><img width="145" height="145" alt="poster_1_up" class="shop_thumbnail" src="/resource/site/img/product-thumb-2.jpg"></a>
                                                -->
                                                <a href="/products/<?php echo htmlspecialchars( $value1["desurl"], ENT_COMPAT, 'UTF-8', FALSE ); ?>"><img width="145" height="145" alt="poster_1_up" class="shop_thumbnail" src="<?php echo htmlspecialchars( $value1["desphoto"], ENT_COMPAT, 'UTF-8', FALSE ); ?>"></a>
                                            </td>

                                            <td class="product-name">
                                                <!-- Nome do produto 
                                                <a href="#">Ship Your Idea</a> 
                                                -->
                                                <a href="/products/<?php echo htmlspecialchars( $value1["desurl"], ENT_COMPAT, 'UTF-8', FALSE ); ?>"><?php echo htmlspecialchars( $value1["desproduct"], ENT_COMPAT, 'UTF-8', FALSE ); ?></a> 
                                            </td>

                                            <td class="product-price">
                                                <!-- Para exibir o preço unitario do produto 
                                                <span class="amount">$700.00</span>
                                                -->
                                                <span class="amount">R$<?php echo formatPrice($value1["vlprice"]); ?></span>
                                            </td>

                                            <td class="product-quantity">
                                                <div class="quantity buttons_added">
                                                    <!-- Ajuste para que o método utilize corretamente as ferramentas integradas com o banco de dados
                                                    <input type="button" class="minus" value="-" onclick="window.location.href = '#'">
                                                    <input type="number" size="4" class="input-text qty text" title="Qty" value="1" min="0" step="1">
                                                    <input type="button" class="plus" value="+" onclick="window.location.href = '#'">
                                                    -->
                                                    <input type="button" class="minus" value="-" onclick="window.location.href = '/cart/<?php echo htmlspecialchars( $value1["idproduct"], ENT_COMPAT, 'UTF-8', FALSE ); ?>/minus'">
                                                    <input type="number" size="4" class="input-text qty text" title="Qty" value="<?php echo htmlspecialchars( $value1["nrqtd"], ENT_COMPAT, 'UTF-8', FALSE ); ?>" min="0" step="1">
                                                    <input type="button" class="plus" value="+" onclick="window.location.href = '/cart/<?php echo htmlspecialchars( $value1["idproduct"], ENT_COMPAT, 'UTF-8', FALSE ); ?>/add'">
                                                </div>
                                            </td>

                                            <td class="product-subtotal">
                                                <!-- Para exibir o preço total do produto 
                                                <span class="amount">$700.00</span>
                                                -->
                                                <span class="amount">R$<?php echo formatPrice($value1["vltotal"]); ?></span> 
                                            </td>
                                        </tr>
                                    <?php } ?>

                                    <!-- Fim do loop dos produtos no carrinho -->
                                </tbody>
                            </table>

                            <div class="cart-collaterals">

                                <div class="cross-sells">

                                    <h2>Cálculo de Frete</h2>
                                    
                                    <div class="coupon">
                                        <label for="cep">CEP:</label>
                                        <input type="text" placeholder="00000-000" value="<?php echo htmlspecialchars( $cart["deszipcode"], ENT_COMPAT, 'UTF-8', FALSE ); ?>" id="cep" class="input-text" name="zipcode">
                                        <input type="submit" formmethod="post" formaction="/cart/freight" value="CÁLCULAR" class="button">
                                    </div>

                                </div>

                                <div class="cart_totals ">

                                    <h2>Resumo da Compra</h2>

                                    <table cellspacing="0">
                                        <tbody>
                                            <tr class="cart-subtotal">
                                                <th>Subtotal</th>
                                                <td><span class="amount">R$<?php echo formatPrice($cart["vlsubtotal"]); ?></span></td>
                                            </tr>

                                            <tr class="shipping">
                                                <th>Frete</th>
                                                <td>R$<?php echo formatPrice($cart["vlfreight"]); ?><?php if( $cart["nrdays"] > 0 ){ ?> <small>prazo de <?php echo htmlspecialchars( $cart["nrdays"], ENT_COMPAT, 'UTF-8', FALSE ); ?> dia(s)</small><?php } ?></td>
                                            </tr>

                                            <tr class="order-total">
                                                <th>Total</th>
                                                <td><strong><span class="amount">R$<?php echo formatPrice($cart["vltotal"]); ?></span></strong> </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>

                            <div class="pull-right">
                                <input type="submit" value="Finalizar Compra" name="proceed" class="checkout-button button alt wc-forward">
                            </div>

                        </form>

                    </div>                        
                </div>                    
            </div>
        </div>
    </div>
</div>