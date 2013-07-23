<?php

require_once('properties.php');
require_once('map.php');
require_once('layer.php');
require_once('tileset.php');

class TileLayerBase extends Layer {
	//attributes
	public $name='';
	public $x=0;
	public $y=0;
	public $width=0;
	public $height=0;
	public $visible=1;
	public $encoding='';
	public $compression='';
	private $map=NULL;
	private $data='';
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
		$this->x=(int)$xml['x'];
		$this->y=(int)$xml['y'];
		$this->width =(int)$xml['width' ];
		$this->height=(int)$xml['height'];
		$this->encoding=(string)$xml->data['encoding'];
		$this->compression=(string)$xml->data['compression'];
		if((bool)$xml->properties!==false) {
			$this->loadProperties_from_element($xml->properties, $ref);
		}
		//$this->parse_data((string)trim($xml->data[0]));
		$this->data=parse_data((string)trim($xml->data[0]), $this->encoding, $this->compression);
	}

	public function get_tile($index) {
		$cgid=substr($this->data, $index*4, 4);
		//var_dump($cgid);//die();
		$cgid=unpack('V',$cgid);
		//var_dump($cgid[1]);//die();
		return $cgid[1];
	}

	public function set_tile($index, $value) {
		//var_dump($value);//die();
		$cgid=pack('V',$value);
		//var_dump($cgid);//die();
		assert(strlen($cgid)==4) or die('bad cgid value.');
		$this->data=substr($this->data, 0, $index*4).$cgid.substr($this->data, $index*4+4);
	}

	public function isValid() {
		if(!is_string($this->name)) {
			throw new Exception('Incorrect tilelayer name.');
			return false;
		}
		if(!is_int($this->x)) {
			throw new Exception('Incorrect tilelayer x value.');
			return false;
		}
		if(!is_int($this->y)) {
			throw new Exception('Incorrect tilelayer y value.');
			return false;
		}
		if(!is_int($this->width ) || $this->width <0) {
			throw new Exception('Incorrect tilelayer width .');
			return false;
		}
		if(!is_int($this->height) || $this->height<0) {
			throw new Exception('Incorrect tilelayer height.');
			return false;
		}
		if(!is_int($this->visible) || ($this->visible!=0 && $this->visible!=1)) {
			throw new Exception('Incorrect tilelayer visible.');
			return false;
		}
		if(!in_array($this->encoding, array('base64', 'csv', 'xml', 'none'))) {
			throw new Exception('Incorrect tilelayer encoding.');
			return false;
		}
		if(!in_array($this->compression, array('zlib', 'gzip', 'bz2', 'bzip2', 'none'))) {
			throw new Exception('Incorrect tilelayer compression.');
			return false;
		}
		if($this->map!=NULL) {
			//assert($this->map instanceof Map);
			//if( strlen($this->data) != (4*$this->map->width*$this->map->height) ) {
			if( strlen($this->data) != (4*$this->width*$this->height) ) {
				var_dump(strlen($this->data),4*$this->width*$this->height);
				throw new Exception('Incorrect tilelayer data.');
				return false;
			}
		}
		return true;
	}
};

?>