<?php
require_once('map.php');

class Map extends MapBase {
	private $_properties=array();
	function __construct() {
		if(method_exists(get_parent_class($this), '__construct')) {
			parent::__construct();
		}
		$this->_properties[]=new properties();
	}
	public function __call($name, $args) {
		//print('You called the method '.$name.' with '.count($args).' arguments.<br/>'."\r\n");
		foreach($this->_properties as $prop) {
			if(method_exists($prop, $name)) {
				return call_user_func_array(array($prop, $name), $args);
			}
		}
	}
};

class Tileset extends TilesetBase {
	private $_properties=array();
	function __construct() {
		if(method_exists(get_parent_class($this), '__construct')) {
			parent::__construct();
		}
		$this->_properties[]=new properties();
		$this->_properties[]=new idproperties();
	}
	public function __call($name, $args) {
		//print('You called the method '.$name.' with '.count($args).' arguments.<br/>'."\r\n");
		foreach($this->_properties as $prop) {
			if(method_exists($prop, $name)) {
				return call_user_func_array(array($prop, $name), $args);
			}
		}
	}
};

class TileLayer extends TileLayerBase {
	private $_properties=array();
	function __construct() {
		if(method_exists(get_parent_class($this), '__construct')) {
			parent::__construct();
		}
		$this->_properties[]=new properties();
	}
	public function __call($name, $args) {
		//print('You called the method '.$name.' with '.count($args).' arguments.<br/>'."\r\n");
		foreach($this->_properties as $prop) {
			if(method_exists($prop, $name)) {
				return call_user_func_array(array($prop, $name), $args);
			}
		}
	}
};

class ObjectLayer extends ObjectLayerBase {
	private $_properties=array();
	function __construct() {
		if(method_exists(get_parent_class($this), '__construct')) {
			parent::__construct();
		}
		$this->_properties[]=new properties();
	}
	public function __call($name, $args) {
		//print('You called the method '.$name.' with '.count($args).' arguments.<br/>'."\r\n");
		foreach($this->_properties as $prop) {
			if(method_exists($prop, $name)) {
				return call_user_func_array(array($prop, $name), $args);
			}
		}
	}
};

class ImageLayer extends ImageLayerBase {
	private $_properties=array();
	function __construct() {
		if(method_exists(get_parent_class($this), '__construct')) {
			parent::__construct();
		}
		$this->_properties[]=new properties();
	}
	public function __call($name, $args) {
		//print('You called the method '.$name.' with '.count($args).' arguments.<br/>'."\r\n");
		foreach($this->_properties as $prop) {
			if(method_exists($prop, $name)) {
				return call_user_func_array(array($prop, $name), $args);
			}
		}
	}
};

class Object extends ObjectBase {
	private $_properties=array();
	function __construct() {
		if(method_exists(get_parent_class($this), '__construct')) {
			parent::__construct();
		}
		$this->_properties[]=new properties();
	}
	public function __call($name, $args) {
		//print('You called the method '.$name.' with '.count($args).' arguments.<br/>'."\r\n");
		foreach($this->_properties as $prop) {
			if(method_exists($prop, $name)) {
				return call_user_func_array(array($prop, $name), $args);
			}
		}
	}
};
?>