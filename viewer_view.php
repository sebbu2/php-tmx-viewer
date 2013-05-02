<?php

ini_set('error_reporting', E_ALL | E_NOTICE | E_STRICT | E_RECOVERABLE_ERROR | E_DEPRECATED | E_USER_DEPRECATED | E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE);

ob_start();

libxml_use_internal_errors(true);



require_once('viewer.php');

require_once('map.php');

require_once('tileset.php');

require_once('layer.php');

require_once('compat.php');



$map=new Map();



$file='../../tmw/room.tmx';

if(array_key_exists('called_from',$_REQUEST) && array_key_exists('choice',$_REQUEST)) {

	$msg='';

	$var='';

	switch(strtolower($_REQUEST['choice'])) {

		case 'list':

			$var='list';

		break;

		case 'url':

			$var='url';

		break;

		case 'file':

			//$var='file';

			$msg='not yet implemented.';

		break;

	}

	if($msg!='') {

		die($msg);

	}

	if(array_key_exists($var, $_REQUEST)) {

		$file=$_REQUEST[$var];

	}

}
if(array_key_exists('ref', $_REQUEST)) {
	$ref=$_REQUEST['ref'];
}
else {
	$ref='';
}


/*var_dump($_REQUEST);

var_dump($file);//*/



//echo '<pre>'."\r\n";

$res=$map->load($file, $ref);





/*var_dump($map->getProperties());echo '<br/>'."\r\n";

var_dump($map->tilesets[0]->getProperties());echo '<br/>'."\r\n";

var_dump($map->tilesets[0]->getAllProperties());echo '<br/>'."\r\n";

//var_dump($map->tilesets[0]->getIdProperties(0));echo '<br/>'."\r\n";

var_dump($map->layers[0]->getProperties());echo '<br/>'."\r\n";

var_dump($map->objectlayers[0]->getProperties());echo '<br/>'."\r\n";

var_dump($map->objectlayers[0]->getObjectCount());echo '<br/>'."\r\n";

echo '<br/>'."\r\n";

var_dump($map->objectlayers[0]->getObject(0));echo '<br/>'."\r\n";

echo '</pre>'."\r\n";

die();//*/



$viewer=new Viewer();

$viewer->setMap($map);



ini_set('output_buffering','off');



$data=ob_get_clean();

if(!empty($data)) {

	header('Content-Type: text/plain'."\r\n");

	echo $data;

	die();

}



$header='';

ob_start();

$viewer->draw();

$data=ob_get_clean();

if($header!='') {

	header('Content-Type: '.$header."\r\n");

	echo $data;

}

else {

	if(!defined('DEBUG')||DEBUG!==true) {

		header('Content-Type: image/jpeg'."\r\n");

	}

	echo $data;

}

?>