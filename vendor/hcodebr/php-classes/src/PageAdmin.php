<?php
	namespace Hcode;
	//use Rain\Tpl;
	class PageAdmin extends Page{
		public function __construct($opts = array(), $tpl_dir = "/views/admin/"){
			/* para evitar refazer todos os métodos da classe pai, utiliza-se o parent 
			para utilizar o método propriamente dito (da classe pai)*/
			parent:: __construct($opts, $tpl_dir);
		}
	}
?>