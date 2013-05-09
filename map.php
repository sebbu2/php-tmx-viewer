<?php

require_once('properties.php');
require_once('tileset.php');
require_once('layer.php');
require_once('objectlayer.php');

class MapBase {
	//attributes
	public $version='';
	public $orientation='';
	public $width=0;
	public $height=0;
	public $tilewidth=0;
	public $tileheight=0;
	public $tilesets=array();
	public $layers=array();
	public $objectlayers=array();
	public $filename='';
	private $xml=NULL;
	private static $tmx_url=array(
		'tmw'=>'https://github.com/themanaworld/tmwa-client-data/raw/master/',
		'evol'=>'https://github.com/EvolOnline/clientdata-beta/raw/master/',
		'tales'=>'https://github.com/tales/sourceoftales/raw/master/',
		);

	//constructors

	//static methods
	public static function load_xml_from_file($filename, $ref='') {
		if(!file_exists($filename)) {
			throw new Exception('File not found.');
		}
		return simplexml_load_file($filename);
	}

	public static function load_xml_from_url($url, $ref='') {
		return simplexml_load_file($url);
	}

	public static function load_xml($filename, $ref='') {
		if($ref=='') {
			return MapBase::load_xml_from_file($filename);
			//return self::load_xml_from_file($filename);
		}
		//else if(array_key_exists($ref, self::$tmx_url)) {
		else if(array_key_exists($ref, MapBase::$tmx_url)) {
			//var_dump(self::$tmx_url[$ref].$filename);
			//return self::load_xml_from_url(self::$tmx_url[$ref].$filename);
			return MapBase::load_xml_from_url(MapBase::$tmx_url[$ref].$filename);
		}
		else {
			throw new Exception('Incorrect Map ref.');
		}
	}

	//methods
	private function load_map($ref='') {
		$this->version = (string)$this->xml['version'];
		$this->orientation = (string)$this->xml['orientation'];
		$this->width =(int)$this->xml['width' ];
		$this->height=(int)$this->xml['height'];
		$this->tilewidth =(int)$this->xml['tilewidth' ];
		$this->tileheight=(int)$this->xml['tileheight'];
	}

	private function load_tilesets($ref='') {
		$i=0;
		foreach($this->xml->tileset as $ts) {
			$this->tilesets[$i]=new Tileset();
			$this->tilesets[$i]->setMap($this);
			$this->tilesets[$i]->load_from_element($ts, $ref);
			++$i;
		}
		return $i;
	}

	private function load_layers($ref='') {
		$i=0;
		foreach($this->xml->layer as $ly) {
			$this->layers[$i]=new Layer();
			$this->layers[$i]->setMap($this);
			$this->layers[$i]->load_from_element($ly, $ref);
			++$i;
		}
		return $i;
	}
	
	private function load_objectlayers($ref='') {
		$i=0;
		foreach($this->xml->objectgroup as $ol) {
			$this->objectlayers[$i]=new ObjectLayer();
			$this->objectlayers[$i]->setMap($this);
			$this->objectlayers[$i]->load_from_element($ol, $ref);
			++$i;
		}
		return $i;
	}

	public function load($filename, $ref='') {
		$this->filename=$filename;
		$this->xml=Map::load_xml($filename, $ref);
		$this->load_map($ref);
		if((bool)$this->xml->properties!==false) {
			$this->loadProperties_from_element($this->xml->properties, $ref);
		}
		$this->load_tilesets($ref);
		$this->load_layers($ref);
		$this->load_objectlayers($ref);
		return $this->xml;
	}

	public function get_tileset_index($gid) {
		$index=-1;
		foreach($this->tilesets as $i=>$ts) {
			if($gid>=$ts->firstgid) $index=$i;
		}
		//unset($i,$ts);
		return $index;
	}

	public function isValid() {
		if($this->version!=='1.0') {
			throw new Exception('Incorrect map version.');
			return false;
		}
		if(!in_array($this->orientation, array('orthogonal', 'isometric', 'stagerred'))) {
			throw new Exception('Incorrect map orientation.');
			return false;
		}
		if(!is_int($this->width ) || $this->width <0) {
			throw new Exception('Incorrect map width .');
			return false;
		}
		if(!is_int($this->height) || $this->height<0) {
			throw new Exception('Incorrect map height.');
			return false;
		}
		if(!is_int($this->tilewidth ) || $this->tilewidth <0) {
			throw new Exception('Incorrect map tilewidth .');
			return false;
		}
		if(!is_int($this->tileheight) || $this->tileheight<0) {
			throw new Exception('Incorrect map tileheight.');
			return false;
		}
		return true;
	}

	public function isValidR() {
		$this->isValid();
		foreach($this->tilesets as $i=>$ts) {
			if(!($ts instanceof Tileset)) {
				throw new Exception('Incorrect map tileset.');
				return false;
			}
			try {
				$ts->isValid();
			}
			catch(Exception $ex) {
				print('Tileset n°'.$i."\n");
				throw $ex;
			}
		}
		foreach($this->layers as $i=>$ly) {
			if(!($ly instanceof Layer)) {
				throw new Exception('Incorrect map layer.');
				return false;
			}
			try {
				$ly->isValid();
			}
			catch(Exception $ex) {
				print('Layer n°'.$i."\n");
				throw $ex;
			}
		}
		foreach($this->objectlayers as $i=>$ol) {
			if(!($ol instanceof ObjectLayer)) {
				throw new Exception('Incorrect map objectlayer.');
				return false;
			}
			try {
				$ol->isValid();
			}
			catch(Exception $ex) {
				print('ObjectLayer n°'.$i."\n");
				throw $ex;
			}
		}
		return true;
	}
};

?>