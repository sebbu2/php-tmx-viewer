<?php

require_once('properties.php');
require_once('map.php');
require_once('layer.php');
require_once('tileset.php');
require_once('object.php');

class ObjectLayerBase extends Layer {
	//attributes
	public $name='';
	public $color='';
	public $x=0;
	public $y=0;
	public $width=0;
	public $height=0;
	public $opacity=1;
	public $visible=1;
	//internal
	public $ref=NULL;
	private $map=NULL;
	private $objects=array();
	//constructors

	//methods
	public function setMap(Map $map) {
		$this->map=$map;
	}
	public function getMap() {
		return $this->map;
	}

	private function load_objects(array $xml, $ref='') {
		foreach($xml as $obj) {
			$ob=new MapObject();
			$ob->load_from_element($obj, $ref);
			$this->addObject($ob);
		}
	}

	public function load_from_element(SimpleXMLElement $xml, $ref='', $recur=true) {
		$this->name=(string)$xml['name'];
		$this->color=(string)$xml['color'];
		$this->x=(string)$xml['x'];
		$this->y=(string)$xml['y'];
		$this->width=(string)$xml['width'];
		$this->height=(string)$xml['height'];
		$this->opacity=(int)$xml['opacity'];
		$this->visible=(int)$xml['visible'];
		if((bool)$xml->properties!==false) {
			$this->loadProperties_from_element($xml->properties, $ref);
		}
		if((bool)$xml->object!==false) {
			$this->load_objects($xml->xpath('object'), $ref);
		}
	}

	public function addObject(ObjectBase $obj) {
		$this->objects[]=$obj;
	}

	public function getObjectCount() {
		return count($this->objects);
	}

	public function getObject($id) {
		if(array_key_exists($id, $this->objects)) {
			return $this->objects[$id];
		}
		else {
			return NULL;
		}
	}

	public function getObjects($name='') {
		$arr=array();
		foreach($this->objects as $obj) {
			if($obj->name==$name) {
				$arr[]=$obj;
			}
		}
		if(count($arr)==0) return NULL;
		return $arr;
	}

	public function getAllObjects() {
		return $this->objects;
	}

	public function isValid() {
		if(!is_string($this->name)) {
			throw new Exception('Incorrect objectlayer name value.');
			return false;
		}
		if(!is_int($this->x)) {
			throw new Exception('Incorrect objectlayer x value.');
			return false;
		}
		if(!is_int($this->y)) {
			throw new Exception('Incorrect objectlayer y value.');
			return false;
		}
		if(!is_int($this->width ) || $this->width <0) {
			throw new Exception('Incorrect objectlayer width .');
			return false;
		}
		if(!is_int($this->height) || $this->height<0) {
			throw new Exception('Incorrect objectlayer height.');
			return false;
		}
		if(!is_string($this->color)) {
			throw new Exception('Incorrect objectlayer color value.');
			return false;
		}
		if(!is_int($this->opacity) || ($this->opacity!=0 && $this->opacity!=1)) {
			throw new Exception('Incorrect objectlayer opacity value.');
			return false;
		}
		if(!is_int($his->visible) || ($this->visible!=0 && $this->visible!=1)) {
			throw new Exception('Incorrect objectlayer visible value.');
			return false;
		}
		if(!is_array($this->objects)) {
			throw new Exception('Incorrect objectlayer objects array.');
			return false;
		}
		return true;
	}
};

?>