<?php

require_once('properties.php');

class ObjectBase {
	//attributes
	public $name='';
	public $type='';
	public $x=0;
	public $y=0;
	public $width=0;
	public $height=0;
	public $gid=NULL;
	public $visible=1;
	public $ellipse=false;
	public $polygon=false;
	public $polyline=false;
	public $rotation=0;//deg clockwise
	public $points=array();
	
	//methods
	
	public function load_from_element(SimpleXMLElement $xml, $ref='') {
		$this->name=(string)$xml['name'];
		$this->type=(string)$xml['type'];
		$this->x=(int)$xml['x'];
		$this->y=(int)$xml['y'];
		$this->width=(int)$xml['width'];
		$this->height=(int)$xml['height'];
		if((bool)$xml['gid']!==false) {
			$this->gid=(int)$xml['gid'];
		}
		$this->visible=(int)$xml['visible'];
		$this->rotation=(int)$xml['rotation'];
		if((bool)$xml->ellipse!==false) {
			$this->ellipse=true;
		}
		if((bool)$xml->polygon!==false) {
			$this->polygon=true;
		}
		if((bool)$xml->polyline!==false) {
			$this->polyline=true;
		}
		if($this->ellipse + $this->polygon + $this->polyline > 1) die('ERROR : ellipse & polygon & polyline.');
		if($this->polygon||$this->polyline) {
			$p='';
			if((bool)$xml->polygon['points']!==false) {
				$p=strtok($xml->polygon['points'],', ');
			}
			if((bool)$xml->polyline['points']!==false) {
				$p=strtok($xml->polyline['points'],', ');
			}
			assert($p!='');
			do {
				$this->points[]=$p;
				$p=strtok(', ');
			}
			while($p!==false);
			assert(count($this->points)%2==0);
		}
		if((bool)$xml->properties!==false) {
			$this->loadProperties_from_element($xml->properties, $ref);
		}
	}
	
	public function getWidthL() {
		$w=0;
		for($i=0;$i<count($this->points);$i+=2) {
			if($this->points[$i]<$w) {
				$w=$this->points[$i];
			}
		}
		return abs($w);
	}
	
	public function getWidthR() {
		$w=0;
		for($i=0;$i<count($this->points);$i+=2) {
			if($this->points[$i]>$w) {
				$w=$this->points[$i];
			}
		}
		return $w;
	}
	
	public function getHeightT() {
		$h=0;
		for($i=1;$i<count($this->points);$i+=2) {
			if($this->points[$i]<$h) {
				$h=$this->points[$i];
			}
		}
		return abs($h);
	}
	
	public function getHeightB() {
		$h=0;
		for($i=1;$i<count($this->points);$i+=2) {
			if($this->points[$i]>$h) {
				$h=$this->points[$i];
			}
		}
		return $h;
	}
	
	public function isValid() {
		if(!is_string($this->name)) {
			throw new Exception('Incorrect object name value.');
			return false;
		}
		if(!is_string($this->type)) {
			throw new Exception('Incorrect object type value.');
			return false;
		}
		if(!is_int($this->x)) {
			throw new Exception('Incorrect object x value.');
			return false;
		}
		if(!is_int($this->y)) {
			throw new Exception('Incorrect object y value.');
			return false;
		}
		if(!is_int($this->width)) {
			throw new Exception('Incorrect object width value.');
			return false;
		}
		if(!is_int($this->height)) {
			throw new Exception('Incorrect object height value.');
			return false;
		}
		if(!is_null($this->gid) && !is_int($this->gid)) {
			throw new Exception('Incorrect object gid value.');
			return false;
		}
		if(!is_int($this->rotation)) {
			throw new Exception('Incorrect object rotation value.');
			return false;
		}
		if(!is_int($this->visible) || ($this->visible!=0 && $this->visible!=1)) {
			throw new Exception('Incorrect object visible value.');
			return false;
		}
		if(!is_bool($this->ellipse)) {
			throw new Exception('Incorrect object ellipse node.');
			return false;
		}
		if(!is_bool($this->polygon)) {
			throw new Exception('Incorrect object polygon node.');
			return false;
		}
		if(!is_bool($this->polyline)) {
			throw new Exception('Incorrect object polyline node.');
			return false;
		}
		if(!is_array($this->points) || count($this->points)%2!=0) {
			throw new Exception('Incorrect object points list.');
			return false;
		}
		return true;
	}
};

?>