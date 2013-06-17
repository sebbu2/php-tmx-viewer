<?php

require_once('properties.php');
require_once('tileset.php');
require_once('tilelayer.php');
require_once('objectlayer.php');
require_once('imagelayer.php');

class MapBase {
	//attributes
	public $version='';
	public $orientation='';
	public $width=0;
	public $height=0;
	public $tilewidth=0;
	public $tileheight=0;
	public $backgroundcolor='';
	public $tilesets=array();
	public $tilelayers=array();
	public $objectlayers=array();
	public $imagelayers=array();
	public $filename='';
	private $xml=NULL;
	public $ref='';
	private static $urls=array(
		'tmw'=>'https://github.com/themanaworld/tmwa-client-data/raw/master/',
		'evol'=>'https://github.com/EvolOnline/clientdata-beta/raw/master/',
		'tales'=>'https://github.com/tales/sourceoftales/raw/master/',
		'stendhal'=>'http://arianne.cvs.sourceforge.net/viewvc/arianne/stendhal/tiled/',
		);

	//constructors

	//static methods
	public static function load_xml_from_file($filename, $ref='') {
		if(!file_exists($filename)) {
			throw new Exception('File \''.$filename.'\' not found with ref \''.$ref.'\'.');
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
		//else if(array_key_exists($ref, self::$urls)) {
		else if(array_key_exists($ref, MapBase::$urls)) {
			//var_dump(self::$urls[$ref].$filename);
			//return self::load_xml_from_url(self::$urls[$ref].$filename);
			return MapBase::load_xml_from_url(MapBase::$urls[$ref].$filename);
		}
		else {
			throw new Exception('Incorrect Map ref.');
		}
	}

	//methods
	private function load_map() {
		$this->version = (string)$this->xml['version'];
		$this->orientation = (string)$this->xml['orientation'];
		$this->width =(int)$this->xml['width' ];
		$this->height=(int)$this->xml['height'];
		$this->tilewidth =(int)$this->xml['tilewidth' ];
		$this->tileheight=(int)$this->xml['tileheight'];
		$this->backgroundcolor=(string)$this->xml['backgroundcolor'];
	}

	private function load_tilesets() {
		$i=0;
		foreach($this->xml->tileset as $ts) {
			$this->tilesets[$i]=new Tileset();
			$this->tilesets[$i]->ref=$this->ref;
			$this->tilesets[$i]->setMap($this);
			$this->tilesets[$i]->load_from_element($ts, $this->ref);
			++$i;
		}
		return $i;
	}

	private function load_tilelayers() {
		$i=0;
		foreach($this->xml->layer as $ly) {
			$this->tilelayers[$i]=new TileLayer();
			$this->tilelayers[$i]->setMap($this);
			$this->tilelayers[$i]->ref=$this->ref;
			$this->tilelayers[$i]->load_from_element($ly, $this->ref);
			++$i;
		}
		return $i;
	}
	
	private function load_imagelayers() {
		$i=0;
		foreach($this->xml->imagelayer as $il) {
			$this->imagelayers[$i]=new ImageLayer();
			$this->imagelayers[$i]->setMap($this);
			$this->imagelayers[$i]->ref=$this->ref;
			$this->imagelayers[$i]->load_from_element($il, $this->ref);
			++$i;
		}
		return $i;
	}
	
	private function load_objectlayers() {
		$i=0;
		foreach($this->xml->objectgroup as $ol) {
			$this->objectlayers[$i]=new ObjectLayer();
			$this->objectlayers[$i]->setMap($this);
			$this->objectlayers[$i]->ref=$this->ref;
			$this->objectlayers[$i]->load_from_element($ol, $this->ref);
			++$i;
		}
		return $i;
	}

	public function load($filename, $ref='') {
		$this->ref=$ref;
		$this->filename=$filename;
		$this->xml=self::load_xml($filename, $ref);
		if($this->xml===false) {
			if($ref==='') {
				throw new Exception('File \''.$filename.'\' not found.');
			}
			else {
				throw new Exception('File \''.$filename.'\' not found or inaccessible with ref \''.$ref.'\'.');
			}
		}
		$this->load_map();
		if((bool)$this->xml->properties!==false) {
			$this->loadProperties_from_element($this->xml->properties, $ref);
		}
		$this->load_tilesets();
		$this->load_tilelayers();
		$this->load_objectlayers();
		$this->load_imagelayers();
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
		if(!is_string($this->backgroundcolor)) {
			throw new Exception('Incorrect map backgroundcolor.');
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
		foreach($this->tilelayers as $i=>$ly) {
			if(!($ly instanceof TileLayer)) {
				throw new Exception('Incorrect map tilelayer.');
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
		foreach($this->imagelayers as $i=>$il) {
			if(!($il instanceof ImageLayer)) {
				throw new Exception('Incorrect map imagelayer.');
				return false;
			}
			try {
				$il->isValid();
			}
			catch(Exception $ex) {
				print('ImageLayer n°'.$i."\n");
				throw $ex;
			}
		}
		return true;
	}
};

?>