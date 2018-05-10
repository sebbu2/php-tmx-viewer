<?php
class Properties {
	private $properties=array();

	public function getProperties() {
		return $this->properties;
	}

	public function setProperties($properties=array()) {
		$this->properties=array_values(array_merge($this->properties, $properties));
	}

	public function getProperty($key='') {
		if(array_key_exists($key, $this->properties)) {
			return $this->properties[$key];
		}
		else {
			return NULL;
		}
	}

	public function setProperty($key='', $value='') {
		$this->properties[$key]=$value;
	}

	public function loadProperties_from_element(SimpleXMLElement $xml, $ref='') {
		foreach($xml->property as $prop) {
			$this->setProperty((string)$prop['name'], (string)$prop['value']);
		}
	}
};
?>