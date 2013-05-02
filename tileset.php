<?php

require_once('properties.php');
require_once('idproperties.php');
require_once('map.php');

class TilesetBase {
	//attributes
	public $firstgid=0;
	public $name='';
	public $tilewidth=0;
	public $tileheight=0;
	public $margin=0;
	public $spacing=0;
	public $source='';
	public $sourceTSX='';
	public $trans='';
	public $width=0;
	public $height=0;
	public $terrains=array();
	public $tiles=array();
	private $filename='';
	//private $xml=NULL;
	private static $tsx_urls=array(
		'tmw'=>'https://github.com/themanaworld/tmwa-client-data/raw/master/tilesets/',
		'evol'=>'https://github.com/EvolOnline/clientdata-beta/raw/master/graphics/',
		'tales'=>'https://github.com/tales/sourceoftales/raw/master/tiles/',
		);

	//constructors

	//static methods
	public static function load_xml_from_file($filename) {
		if(!file_exists($filename)) {
			throw new Exception('File not found.');
		}
		return simplexml_load_file($filename);
	}

	public static function load_xml_from_url($url) {
		return simplexml_load_file($url);
	}

	public static function load_xml($filename, $ref='') {
		if($ref=='') {
			return TilesetBase::load_xml_from_file($filename);
			//return self::load_xml_from_file($filename);
		}
		else if(array_key_exists($ref, TilesetBase::$tsx_urls)) {
		//else if(array_key_exists($ref, self::$tsx_urls)) {
			return TilesetBase::load_xml_from_url(TilesetBase::$tsx_urls[$ref].$filename);
			//var_dump(self::$tsx_urls[$ref].$filename);
			//return self::load_xml_from_url(self::$tsx_urls[$ref].$filename);
		}
		else {
			throw new Exception('Incorrect Tileset ref.');
		}
	}

	//methods
	public function setMap(Map $map) {
		$this->map=$map;
	}
	public function getMap() {
		return $this->map;
	}
	
	public function load_from_tsx($filename, $ref='') {
		$this->filename=$filename;
		//$xml=Tileset::load_xml($filename, $ref);
		$xml=self::load_xml($filename, $ref);
		return $this->load_from_element($xml);
	}

	public function load_from_element(SimpleXMLElement $xml, $ref='') {
		if((bool)$xml['source']!=FALSE) {
			$this->sourceTSX=(string)$xml['source'];
			$this->firstgid=(int)$xml['firstgid'];
			$this->load_from_tsx(dirname($this->map->filename).'/'.$this->sourceTSX, $ref);
			return;
		}
		if(isset($xml['firstgid'])) $this->firstgid=(int)$xml['firstgid'];
		if((bool)$xml['firstgid']!=FALSE) $this->firstgid=(int)$xml['firstgid'];
		$this->name=(string)$xml['name'];
		$this->tilewidth =(int)$xml['tilewidth' ];
		$this->tileheight=(int)$xml['tileheight'];
		$this->margin =(int)$xml['margin'];
		$this->spacing=(int)$xml['spacing'];
		$this->source=(string)$xml->image['source'];//
		$this->trans=(string)$xml->image['trans'];
		$this->width =(int)$xml->image['width' ];
		$this->height=(int)$xml->image['height'];
		if( $this->width ==0 || $this->height==0 ) {
			if(file_exists(dirname($this->filename).'/'.$this->source)) {
				$ar=getimagesize(dirname($this->filename).'/'.$this->source);
				$this->width =$ar[0];
				$this->height=$ar[1];
				unset($ar);
			}
			else {
				trigger_error('Image not found', E_USER_NOTICE);
			}
		}
		$j=0;
		if((bool)$xml->terraintypes!=FALSE) {
			foreach($xml->terraintypes->terrain as $terrain) {
				$this->terrains[$j]=array();
				$this->terrains[$j]['name']=(string)$terrain['name'];
				$this->terrains[$j]['tile']=(int)$terrain['tile'];
				$this->terrains[$j]['distances']=(string)$terrain['distances'];
				++$j;
			}
		}
		unset($j);
		if((bool)$xml->tile!=FALSE) {
			$this->tiles=array();
			foreach($xml->tile as $tile) {
				assert(isset($tile['id']));
				$this->tiles[(string)$tile['id']]=array();
				//var_dump((string)$tile['id']);//die();
				//var_dump((string)$tile['terrain']);die();
				if(isset($tile['terrain'])) {
					$this->tiles[(string)$tile['id']]['terrain']=(string)$tile['terrain'];
				}
				if(isset($tile['probability'])) {
					$this->tiles[(string)$tile['id']]['probability']=(double)$tile['probability'];
				}
				/*if(count($this->tiles[(string)$tile['id']])==0) {
					unset($this->tiles[(string)$tile['id']]);
				}//*/
			}
		}
		if((bool)$xml->properties!==false) {
			$this->loadProperties_from_element($xml->properties, $ref);
		}
		if((bool)$xml->tile!==false) {
			$this->loadIdProperties_from_element($xml->xpath('tile'), $ref);
		}
		return $xml;
	}

	public function isValid() {
		if(!is_string($this->sourceTSX)) {
			throw new Exception('Incorrect tileset source.');
			return false;
		}
		if(!is_int($this->firstgid ) || $this->firstgid<1) {
			throw new Exception('Incorrect tileset firstgid.');
			return false;
		}
		if(!is_string($this->name)) {
			throw new Exception('Incorrect tileset name.');
			return false;
		}
		if(!is_int($this->tilewidth ) || $this->tilewidth <0) {
			throw new Exception('Incorrect tileset width .');
			return false;
		}
		if(!is_int($this->tileheight) || $this->tileheight<0) {
			throw new Exception('Incorrect tileset height.');
			return false;
		}
		if(!is_int($this->margin) || $this->margin<0) {
			throw new Exception('Incorrect tileset margin.');
			return false;
		}
		if(!is_int($this->spacing) || $this->spacing<0) {
			throw new Exception('Incorrect tileset spacing.');
			return false;
		}
		if(!is_string($this->source)) {
			throw new Exception('Incorrect tileset source.');
			return false;
		}
		if(!is_string($this->trans) || strlen($this->trans)!=6 || strcspn($this->trans, '0123456789abcdefABCDEF')!=0 ) {
			throw new Exception('Incorrect tileset trans.');
			return false;
		}
		if(!is_int($this->width ) || $this->width <0) {
			throw new Exception('Incorrect tileset width .');
			return false;
		}
		if(!is_int($this->height) || $this->height<0) {
			throw new Exception('Incorrect tileset height.');
			return false;
		}
		if(!is_array($this->terrains)) {
			throw new Exception('Incorrect tileset terrains.');
			return false;
		}
		if(!is_array($this->tiles)) {
			throw new Exception('Incorrect tileset tiles.');
			return false;
		}
		return true;
	}
};

?>