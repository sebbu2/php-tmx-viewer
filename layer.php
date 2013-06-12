<?php

require_once('properties.php');
require_once('map.php');
require_once('tileset.php');

class LayerBase {
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
		$this->parse_data((string)trim($xml->data[0]));
	}
	
	public function parse_data($data) {
		if($this->encoding=='base64') {
			$this->data=base64_decode($data);
		}
		else if($this->encoding=='csv') {
			$data2=explode(chr(10),$data);
			//var_dump(count($data2),$height);
			assert(count($data2)==$height);
			$data3=array();
			$i=0;
			foreach($data2 as $line) {
				$data3[$i]=explode(',',$line);
				//var_dump(count($data3[$i]),$width);
				if(count($data3[$i])>$width) {
					assert($data3[$i][$width]=='') or die('error2');
					array_pop($data3[$i]);
				}
				assert(count($data3[$i])==$width) or die('error');
				++$i;
			}
			unset($line,$data2);
			foreach($data3 as $row) {
				foreach($row as $gid) {
					$this->data.=pack('V', $gid);
				}
			}
			unset($gid,$row,$data3);
		}
		else {
			$this->data=$data;
		}
		switch(strtolower($this->compression)) {
			case 'zlib':
				//$data=gzuncompress($data, $height*$width*4);
				$this->data=gzuncompress($this->data);
				break;
			case 'gzip':
				//$data=gzuncompress($data, $height*$width*4);
				//$this->data=gzuncompress($this->data);
				//$this->data=gzinflate($this->data);
				//$this->data=softcoded_gzdecode($this->data);
				$this->data=gzdecode($this->data);
				break;
			case 'bzip2':
			case 'bz2':
				$this->data=bzdecompress($this->data);
				break;
			case 'none':
			default:
				break;
		}
		//
	}

	public function get_tile($index) {
		$cgid=substr($this->data, $index*4, 4);
		//var_dump($cgid);//die();
		//var_dump(ord($cgid[3]),ord($cgid[2]),ord($cgid[1]),ord($cgid[0]));//die();
		$cgid=(ord($cgid[3])&0x1F)*16777216+ord($cgid[2])*65536+ord($cgid[1])*256+ord($cgid[0]);
		//var_dump($cgid);//die();
		return $cgid;
	}

	public function set_tile($index, $value) {
		$cgid=str_repeat(' ', 4);
		$cgid[3]=($value>>24);
		$cgid[2]=($value>>16)&0xFF;
		$cgid[1]=($value>>8)&0xFF;
		$cgid[0]=($value>>0)&0xFF;
		$data=substr($data, 0, $index*4).$cgid.substr($data, $index*4+4);
	}

	public function isValid() {
		if(!is_string($this->name)) {
			throw new Exception('Incorrect layer name.');
			return false;
		}
		if(!is_int($this->x)) {
			throw new Exception('Incorrect layer x value.');
			return false;
		}
		if(!is_int($this->y)) {
			throw new Exception('Incorrect layer y value.');
			return false;
		}
		if(!is_int($this->width ) || $this->width <0) {
			throw new Exception('Incorrect layer width .');
			return false;
		}
		if(!is_int($this->height) || $this->height<0) {
			throw new Exception('Incorrect layer height.');
			return false;
		}
		if(!is_int($this->visible) || ($this->visible!=0 && $this->visible!=1)) {
			throw new Exception('Incorrect layer visible.');
			return false;
		}
		if(!in_array($this->encoding, array('base64', 'csv', 'xml', 'none'))) {
			throw new Exception('Incorrect layer encoding.');
			return false;
		}
		if(!in_array($this->compression, array('zlib', 'gzip', 'bz2', 'bzip2', 'none'))) {
			throw new Exception('Incorrect layer compression.');
			return false;
		}
		if($this->map!=NULL) {
			//assert($this->map instanceof Map);
			//if( strlen($this->data) != (4*$this->map->width*$this->map->height) ) {
			if( strlen($this->data) != (4*$this->width*$this->height) ) {
				var_dump(strlen($this->data),4*$this->width*$this->height);
				throw new Exception('Incorrect layer data.');
				return false;
			}
		}
		return true;
	}
};

?>