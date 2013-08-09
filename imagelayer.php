<?php

require_once('properties.php');
require_once('map.php');
require_once('layer.php');

class ImageLayerBase extends Layer {
	//attributes
	public $name='';
	public $x=0;
	public $y=0;
	public $width=0;
	public $height=0;
	public $opacity=1;
	public $visible=1;
	public $source='';
	public $trans='';
	private $map=NULL;
	//constructors

	//methods
	public function setMap(Map $map) {
		$this->map=$map;
	}
	public function getMap() {
		return $this->map;
	}

	public function load_from_element(SimpleXMLElement $xml, $ref='') {
		$this->name=(string)$xml['name'];
		$this->x=(string)$xml['x'];
		$this->y=(string)$xml['y'];
		$this->width=(string)$xml['width'];
		$this->height=(string)$xml['height'];
		$this->opacity=(int)$xml['opacity'];
		$this->visible=(int)$xml['visible'];
		$this->source=(string)$xml->image['source'];
		$this->trans=(string)$xml->image['trans'];
		if((bool)$xml->properties!==false) {
			$this->loadProperties_from_element($xml->properties, $ref);
		}
	}

	public function isValid() {
		if(!is_string($this->name)) {
			throw new Exception('Incorrect imagelayer name value.');
			return false;
		}
		if(!is_int($this->x)) {
			throw new Exception('Incorrect imagelayer x value.');
			return false;
		}
		if(!is_int($this->y)) {
			throw new Exception('Incorrect imagelayer y value.');
			return false;
		}
		if(!is_int($this->width ) || $this->width <0) {
			throw new Exception('Incorrect imagelayer width .');
			return false;
		}
		if(!is_int($this->height) || $this->height<0) {
			throw new Exception('Incorrect imagelayer height.');
			return false;
		}
		if(!is_int($this->opacity) || ($this->opacity!=0 && $this->opacity!=1)) {
			throw new Exception('Incorrect imagelayer opacity value.');
			return false;
		}
		if(!is_int($his->visible) || ($this->visible!=0 && $this->visible!=1)) {
			throw new Exception('Incorrect imagelayer visible value.');
			return false;
		}
		if(!is_string($this->source)) {
			throw new Exception('Incorrent imagelayer image source value.');
			return false;
		}
		if(!is_string($this->trans)) {
			throw new Exception('Incorrent imagelayer image trans value.');
			return false;
		}
		return true;
	}
};

?>