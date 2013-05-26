<?php
class IdProperties {
	private $idproperties=array();
	
	public function getAllProperties() {
		return $this->idproperties;
	}
	
	public function setAllProperties($idproperties=array()) {
		$this->idproperties=array_values(array_merge($this->idproperties, $idproperties));
	}
	
	public function getIdProperties($id=NULL) {
		if(array_key_exists($id, $this->idproperties)) {
			return $this->idproperties[$id];
		}
		else {
			return NULL;
		}
	}
	
	public function setIdProperties($id=NULL, $idproperties=array()) {
		$this->idproperties[$id]=array_values(array_merge($this->idproperties[$id], $idproperties));
	}
	
	public function getIdProperty($id=NULL, $key='') {
		if(array_key_exists($id, $this->idproperties)) {
			if(array_key_exists($key, $this->idproperties)) {
				return $this->idproperties[$id][$key];
			}
			else {
				return NULL;
			}
		}
		else {
			return NULL;
		}
	}
	
	public function setIdProperty($id=NULL, $key='', $value='') {
		$this->idproperties[$id][$key]=$value;
	}
	
	public function loadIdProperties_from_element(array $xml=NULL, $ref='') {
		if($xml===NULL) return;
		foreach($xml as $elem) {
			$id=(string)$elem['id'];
			if((bool)$elem->properties===false) continue;
			foreach($elem->properties->property as $prop) {
				$this->setIdProperty($id, (string)$prop['name'], (string)$prop['value']);
			}
		}
	}
};
?>