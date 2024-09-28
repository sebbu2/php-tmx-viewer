<?php
require_once('map.php');
require_once('layer.php');
require_once('objectlayer.php');
require_once('object.php');
require_once('imagelayer.php');

class Map extends MapBase {
	use properties;
};

class Tileset extends TilesetBase {
	use properties, idproperties;
};

class TileLayer extends TileLayerBase {
	use properties;
};

class ObjectLayer extends ObjectLayerBase {
	use properties;
};

class ImageLayer extends ImageLayerBase {
	use properties;
};

class MapObject extends ObjectBase {
	use properties;
};
?>