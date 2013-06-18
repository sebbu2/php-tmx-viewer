<?php
require_once('map.php');
require_once('layer.php');

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

class Object extends ObjectBase {
	use properties;
};
?>