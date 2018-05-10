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

	public function load_from_element(SimpleXMLElement $xml, $ref='', $recur=true) {
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
		//var_dump((string)trim($xml->data[0]));die();
		$this->data=(string)trim($xml->data[0]);
		if($recur) {
			$this->load_data();
		}
		if($recur && strlen($this->data)<$this->map->width*$this->map->height*4) {
			var_dump($this->name,strlen($this->data),$this->map->width*$this->map->height*4,$xml->data,(string)trim($xml->data[0]));
			trigger_error('incorrect layer data', E_USER_ERROR);
		}
	}

	function load_data() {
		if( strlen($this->data) < $this->map->width*$this->map->height*4 ) {
			$this->data=parse_data($this->data, $this->encoding, $this->compression);
		}
	}

	public function get_tile($index) {
		if($index*4+4>strlen($this->data)) {
			var_dump(strlen($this->data),$index);
			debug_print_backtrace();
			trigger_error('incorrect index or layer data (1).', E_USER_ERROR);
		}
		$cgid=substr($this->data, $index*4, 4);
		if(strlen($cgid)<4) {
			var_dump($index);
			debug_print_backtrace();
			trigger_error('incorrect index or layer data (2).', E_USER_ERROR);
		}//*/
		//var_dump($cgid);//die();
		$cgid=unpack('V',$cgid);
		//var_dump($cgid[1]);//die();
		return $cgid[1];
	}

	public function get_tile_number() {
		return floor(strlen($this->data)/4);
	}

	public function set_tile($index, $value) {
		//var_dump($value);//die();
		$cgid=pack('V',$value);
		//var_dump($cgid);//die();
		assert(strlen($cgid)==4) or trigger_error('bad cgid value.', E_USER_ERROR);
		$this->data=substr($this->data, 0, $index*4).$cgid.substr($this->data, $index*4+4);
	}

	public function transpose() {
		$data2='';
		for($i=0;$i<$this->width;++$i) {
			for($j=0;$j<$this->height;++$j) {
				$data2.=substr($this->data, ($j*$this->width+$i)*4, 4);
			}
		}
		assert(strlen($data2)===strlen($this->data));
		$this->data=$data2;
		$tmp=$this->width;
		$this->width=$this->height;
		$this->height=$tmp;
	}

	public function reverse_row() {
		for($i=0;$i<$this->height;++$i) {
			for($j=0;$j<floor($this->width/2);++$j) {
				for($a=0;$a<4;++$a) swap_ar($this->data, ($i*$this->width+$j)*4+$a, (($i+1)*$this->width-1-$j)*4+$a);
			}
		}
	}

	public function reverse_col() {
		for($j=0;$j<$this->width;++$j) {
			for($i=0;$i<floor($this->height/2);++$i) {
				for($a=0;$a<4;++$a) swap_ar($this->data, ($i*$this->width+$j)*4+$a, (($this->height-1-$i)*$this->width+$j)*4+$a);
			}
		}
	}

	public function rot90cw() {
		$this->transpose();
		$this->reverse_row();
	}

	public function rot90ccw() {
		$this->transpose();
		$this->reverse_col();
	}

	public function rot180() {
		$this->reverse_row();
		$this->reverse_col();
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