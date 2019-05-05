<?php
	namespace Hcode;
	use Rain\Tpl;
	class Page{
		private $tpl;
		private $options = [];
		private $defaults = [
			"data"=>[]
		];
		private function setData ($data = array()){
			foreach ($data as $key => $value){
				$this->tpl->assign($key,$value);
			}
		}
		//Método construtor
		public function __construct($opts = array()){
			/* no Merge é importante a ordem, no formato abaixo, os conflitos entre o opts e defaults, vale o opts*/
			$this->options=array_merge($this->defaults,$opts);
			/* variável dir para armazenamento do caminho padrão */
			$dir = $_SERVER["DOCUMENT_ROOT"]."/ecommerce/";
			// config
			$config = array(
				"tpl_dir" => $dir."views/",
				"cache_dir" => $dir."views-cache/",
				"debug" => true // set to false to improve the speed
			 );

			Tpl::configure( $config );
			$this->tpl = new Tpl;
			$this->setData($this->options["data"]);
			$this->tpl->draw("header");
		}

		/* método para corpo da página */
		public function setTpl($name, $data = array(),$returnHTML = false){
			$this->setData($data);
			return $this->tpl->draw($name,$returnHTML);
		}
		//Método destrutor
		public function __destruct(){
			$this->tpl->draw("footer");
		}
	}
?>