<?php
	namespace Hcode;
	use Rain\Tpl;
	class Page{
		private $tpl;
		private $options = [];
		private $defaults = [
			"header"=>true,
			"footer"=>true,
			"data"=>[]
		];
		private function setData ($data = array()){
			foreach ($data as $key => $value){
				$this->tpl->assign($key,$value);
			}
		}
		//Método construtor
		public function __construct($opts = array(),$tpl_dir = "/views/"){
			/* no Merge é importante a ordem, no formato abaixo, os conflitos entre o opts e defaults, vale o opts*/
			$this->options=array_merge($this->defaults,$opts);
			// config
			$config = array(
				"tpl_dir" => $_SERVER["DOCUMENT_ROOT"].$tpl_dir,
				"cache_dir" => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
				"debug" => true // set to false to improve the speed
			 );

			Tpl::configure( $config );
			$this->tpl = new Tpl;
			$this->setData($this->options["data"]);
			/* como existem páginas que utilizam o header e outras não, necessário validar se os parâmetros enviados 
			pela chamada do método vieram como true ou false antes de realizar a operação */
			if ($this->options["header"] === true) $this->tpl->draw("header");
		}

		/* método para corpo da página */
		public function setTpl($name, $data = array(),$returnHTML = false){
			$this->setData($data);
			return $this->tpl->draw($name,$returnHTML);
		}
		//Método destrutor
		public function __destruct(){
			/* como existem páginas que utilizam o footer e outras não, necessário validar se os parâmetros enviados 
			pela chamada do método vieram como true ou false antes de realizar a operação */
			if ($this->options["footer"] === true) $this->tpl->draw("footer");
		}
	}
?>