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
	public $layers=array();
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
		//return simplexml_load_file($url);
		return simplexml_load_string(get_url($url));
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

	private function load_tilesets($recur=true) {
		$i=0;
		foreach($this->xml->tileset as $ts) {
			$this->tilesets[$i]=new Tileset();
			$this->tilesets[$i]->ref=$this->ref;
			$this->tilesets[$i]->setMap($this);
			$this->tilesets[$i]->load_from_element($ts, $this->ref, $recur);
			++$i;
		}
		return $i;
	}

	private function load_layers($recur=true) {
		$i=0;
		foreach($this->xml->children() as $ly) {
			if( $ly->getName() === 'properties' || $ly->getName() === 'tileset' ) {
				continue;
			}
			if($ly->getName() === 'layer') {
				$this->load_tilelayer($i, $ly, $recur);
			}
			elseif($ly->getName() === 'objectgroup') {
				$this->load_objectlayer($i, $ly, $recur);
			}
			elseif($ly->getName() === 'imagelayer') {
				$this->load_imagelayer($i, $ly, $recur);
			}
			else {
				throw new Exception('unknown element in xml file (from '.__FILE__.':'.__LINE__.')');
				return false;
			}
			++$i;
		}
		return $i;
	}

	private function load_tilelayer($i, $tl, $recur=true) {
		$this->layers[$i]=new TileLayer();
		$this->layers[$i]->setMap($this);
		$this->layers[$i]->ref=$this->ref;
		$this->layers[$i]->load_from_element($tl, $this->ref);
	}

	private function load_imagelayer($i, $il, $recur=true) {
		$this->layers[$i]=new ImageLayer();
		$this->layers[$i]->setMap($this);
		$this->layers[$i]->ref=$this->ref;
		$this->layers[$i]->load_from_element($il, $this->ref);
	}

	private function load_objectlayer($i, $ol, $recur=true) {
		$this->layers[$i]=new ObjectLayer();
		$this->layers[$i]->setMap($this);
		$this->layers[$i]->ref=$this->ref;
		$this->layers[$i]->load_from_element($ol, $this->ref);
	}

	public function load($filename, $ref='', $recur=true) {
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
		if($recur) {
			$this->load_tilesets($recur);
			$this->load_layers($recur);
		}
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
		foreach($this->layers as $i->$ly) {
			if(!($ly instanceof Layer)) {
				throw new Exception('Incorrect map element.');
				return false;
			}
			try {
				$ly->isValid();
			}
			catch(Exception $ex) {
				print('*Layer n°'.$i."\n");
				throw $ex;
			}
		}
		return true;
	}
};

?>