<?php
require_once('map.php');

class Map extends MapBase {
	use properties;
};

class Tileset extends TilesetBase {
	use properties, idproperties;
};

class Layer extends LayerBase {
	use properties;
};

class ObjectLayer extends ObjectLayerBase {
	use properties;
};

class Object extends ObjectBase {
	use properties;
};
?>