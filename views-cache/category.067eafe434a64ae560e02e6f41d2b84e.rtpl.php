<?php if(!class_exists('Rain\Tpl')){exit;}?><div class="product-big-title-area">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="product-bit-title text-center">
                    <h2><?php echo htmlspecialchars( $category["descategory"], ENT_COMPAT, 'UTF-8', FALSE ); ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="single-product-area">
    <div class="zigzag-bottom"></div>
    <div class="container">
        <div class="row">
            <!-- Para que os produtos sejam inseridos automaticamente no site, deve-se criar um loop conforme abaixo -->
            <?php $counter1=-1;  if( isset($products) && ( is_array($products) || $products instanceof Traversable ) && sizeof($products) ) foreach( $products as $key1 => $value1 ){ $counter1++; ?>

            <div class="col-md-3 col-sm-6">
                <div class="single-shop-product">
                    <div class="product-upper">
                        <!--Como os dados abaixo eram estáticos, ajuste para buscar o link conforme tabela productscategories
                        <img src="/resource/site/img/product-2.jpg" alt="">
                         -->
                         <img src="<?php echo htmlspecialchars( $value1["desphoto"], ENT_COMPAT, 'UTF-8', FALSE ); ?>" alt="">
                    </div>
                    <!--Como os dados abaixo eram estáticos, ajuste para buscar o link conforme tabela productscategories
                    <h2><a href="">Apple new mac book 2015 March :P</a></h2>
                    -->
                    <h2><a href="/products/<?php echo htmlspecialchars( $value1["desurl"], ENT_COMPAT, 'UTF-8', FALSE ); ?>"><?php echo htmlspecialchars( $value1["desproduct"], ENT_COMPAT, 'UTF-8', FALSE ); ?></a></h2>
                    <div class="product-carousel-price">
                        <!--Como os dados abaixo eram estáticos, ajuste para buscar o link conforme tabela productscategories.
                        Considerado também que o site ainda não possui a função de promoção
                        <ins>$899.00</ins> <del>$999.00</del>
                        Utilizado função formatPrice do arquivo functions.php
                        -->
                        <ins>R$<?php echo formatPrice($value1["vlprice"]); ?></ins>
                    </div>  
                    
                    <div class="product-option-shop">
                        <a class="add_to_cart_button" data-quantity="1" data-product_sku="" data-product_id="70" rel="nofollow" href="/cart/<?php echo htmlspecialchars( $value1["idproduct"], ENT_COMPAT, 'UTF-8', FALSE ); ?>/add">Comprar</a>
                    </div>                       
                </div>
            </div>
            <?php } ?>

            <!--
            <div class="col-md-3 col-sm-6">
                <div class="single-shop-product">
                    <div class="product-upper">
                        <img src="/resource/site/img/product-1.jpg" alt="">
                    </div>
                    <h2><a href="">Apple new mac book 2015 March :P</a></h2>
                    <div class="product-carousel-price">
                        <ins>$899.00</ins> <del>$999.00</del>
                    </div>  
                    
                    <div class="product-option-shop">
                        <a class="add_to_cart_button" data-quantity="1" data-product_sku="" data-product_id="70" rel="nofollow" href="/canvas/shop/?add-to-cart=70">Add to cart</a>
                    </div>                       
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="single-shop-product">
                    <div class="product-upper">
                        <img src="/resource/site/img/product-3.jpg" alt="">
                    </div>
                    <h2><a href="">Apple new mac book 2015 March :P</a></h2>
                    <div class="product-carousel-price">
                        <ins>$899.00</ins> <del>$999.00</del>
                    </div>  
                    
                    <div class="product-option-shop">
                        <a class="add_to_cart_button" data-quantity="1" data-product_sku="" data-product_id="70" rel="nofollow" href="/canvas/shop/?add-to-cart=70">Add to cart</a>
                    </div>                       
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="single-shop-product">
                    <div class="product-upper">
                        <img src="/resource/site/img/product-4.jpg" alt="">
                    </div>
                    <h2><a href="">Apple new mac book 2015 March :P</a></h2>
                    <div class="product-carousel-price">
                        <ins>$899.00</ins> <del>$999.00</del>
                    </div>  
                    
                    <div class="product-option-shop">
                        <a class="add_to_cart_button" data-quantity="1" data-product_sku="" data-product_id="70" rel="nofollow" href="/canvas/shop/?add-to-cart=70">Add to cart</a>
                    </div>                       
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="single-shop-product">
                    <div class="product-upper">
                        <img src="/resource/site/img/product-2.jpg" alt="">
                    </div>
                    <h2><a href="">Apple new mac book 2015 March :P</a></h2>
                    <div class="product-carousel-price">
                        <ins>$899.00</ins> <del>$999.00</del>
                    </div>  
                    
                    <div class="product-option-shop">
                        <a class="add_to_cart_button" data-quantity="1" data-product_sku="" data-product_id="70" rel="nofollow" href="/canvas/shop/?add-to-cart=70">Add to cart</a>
                    </div>                       
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="single-shop-product">
                    <div class="product-upper">
                        <img src="/resource/site/img/product-1.jpg" alt="">
                    </div>
                    <h2><a href="">Apple new mac book 2015 March :P</a></h2>
                    <div class="product-carousel-price">
                        <ins>$899.00</ins> <del>$999.00</del>
                    </div>  
                    
                    <div class="product-option-shop">
                        <a class="add_to_cart_button" data-quantity="1" data-product_sku="" data-product_id="70" rel="nofollow" href="/canvas/shop/?add-to-cart=70">Add to cart</a>
                    </div>                       
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="single-shop-product">
                    <div class="product-upper">
                        <img src="/resource/site/img/product-3.jpg" alt="">
                    </div>
                    <h2><a href="">Apple new mac book 2015 March :P</a></h2>
                    <div class="product-carousel-price">
                        <ins>$899.00</ins> <del>$999.00</del>
                    </div>  
                    
                    <div class="product-option-shop">
                        <a class="add_to_cart_button" data-quantity="1" data-product_sku="" data-product_id="70" rel="nofollow" href="/canvas/shop/?add-to-cart=70">Add to cart</a>
                    </div>                       
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="single-shop-product">
                    <div class="product-upper">
                        <img src="/resource/site/img/product-4.jpg" alt="">
                    </div>
                    <h2><a href="">Apple new mac book 2015 March :P</a></h2>
                    <div class="product-carousel-price">
                        <ins>$899.00</ins> <del>$999.00</del>
                    </div>  
                    
                    <div class="product-option-shop">
                        <a class="add_to_cart_button" data-quantity="1" data-product_sku="" data-product_id="70" rel="nofollow" href="/canvas/shop/?add-to-cart=70">Add to cart</a>
                    </div>                       
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="single-shop-product">
                    <div class="product-upper">
                        <img src="/resource/site/img/product-2.jpg" alt="">
                    </div>
                    <h2><a href="">Apple new mac book 2015 March :P</a></h2>
                    <div class="product-carousel-price">
                        <ins>$899.00</ins> <del>$999.00</del>
                    </div>  
                    
                    <div class="product-option-shop">
                        <a class="add_to_cart_button" data-quantity="1" data-product_sku="" data-product_id="70" rel="nofollow" href="/canvas/shop/?add-to-cart=70">Add to cart</a>
                    </div>                       
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="single-shop-product">
                    <div class="product-upper">
                        <img src="/resource/site/img/product-1.jpg" alt="">
                    </div>
                    <h2><a href="">Apple new mac book 2015 March :P</a></h2>
                    <div class="product-carousel-price">
                        <ins>$899.00</ins> <del>$999.00</del>
                    </div>  
                    
                    <div class="product-option-shop">
                        <a class="add_to_cart_button" data-quantity="1" data-product_sku="" data-product_id="70" rel="nofollow" href="/canvas/shop/?add-to-cart=70">Add to cart</a>
                    </div>                       
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="single-shop-product">
                    <div class="product-upper">
                        <img src="/resource/site/img/product-3.jpg" alt="">
                    </div>
                    <h2><a href="">Apple new mac book 2015 March :P</a></h2>
                    <div class="product-carousel-price">
                        <ins>$899.00</ins> <del>$999.00</del>
                    </div>  
                    
                    <div class="product-option-shop">
                        <a class="add_to_cart_button" data-quantity="1" data-product_sku="" data-product_id="70" rel="nofollow" href="/canvas/shop/?add-to-cart=70">Add to cart</a>
                    </div>                       
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="single-shop-product">
                    <div class="product-upper">
                        <img src="/resource/site/img/product-4.jpg" alt="">
                    </div>
                    <h2><a href="">Apple new mac book 2015 March :P</a></h2>
                    <div class="product-carousel-price">
                        <ins>$899.00</ins> <del>$999.00</del>
                    </div>  
                    
                    <div class="product-option-shop">
                        <a class="add_to_cart_button" data-quantity="1" data-product_sku="" data-product_id="70" rel="nofollow" href="/canvas/shop/?add-to-cart=70">Add to cart</a>
                    </div>                       
                </div>
            </div>
            -->
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="product-pagination text-center">
                    <nav>
                        <ul class="pagination">
                        <!-- Para montar o link da página, o li abaixo será substituído por um loop
                        <li>
                            <a href="#" aria-label="Previous">
                            <span aria-hidden="true">«</span>
                            </a>
                        </li>
                        e ficará da seguinte forma -->
                        <?php $counter1=-1;  if( isset($pages) && ( is_array($pages) || $pages instanceof Traversable ) && sizeof($pages) ) foreach( $pages as $key1 => $value1 ){ $counter1++; ?>

                        <!-- Os itens abaixo, deixa de ser estáticos e serão substituídos pelas função dinamica 
                        <li><a href="#">1</a></li>
                        <li><a href="#">2</a></li>
                        <li><a href="#">3</a></li>
                        <li><a href="#">4</a></li>
                        <li><a href="#">5</a></li>
                        -->
                        <li><a href="<?php echo htmlspecialchars( $value1["link"], ENT_COMPAT, 'UTF-8', FALSE ); ?>"><?php echo htmlspecialchars( $value1["page"], ENT_COMPAT, 'UTF-8', FALSE ); ?></a></li>
                        <?php } ?>

                        <li>
                            <a href="#" aria-label="Next">
                            <span aria-hidden="true">»</span>
                            </a>
                        </li>
                        </ul>
                    </nav>                        
                </div>
            </div>
        </div>
    </div>
</div>