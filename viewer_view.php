<?php

ini_set('error_reporting', E_ALL | E_NOTICE | E_STRICT | E_RECOVERABLE_ERROR | E_DEPRECATED | E_USER_DEPRECATED | E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('ignore_repeated_errors', 0);
ini_set('track_errors', 1);

ob_start();

libxml_use_internal_errors(true);

session_start();

require_once('viewer.php');
require_once('map.php');
require_once('layer.php');
require_once('tileset.php');
require_once('tilelayer.php');
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

//print_r($map->layers[0]);die();

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

if($file=='../maps/isometric.tmx' && array_key_exists('rot', $_REQUEST)) {
	if(!in_array($_REQUEST['rot'],array('','0','ccw','90','180','cw','270','360'))) {
		trigger_error('unknown rotation', E_USER_ERROR);
	}
	require('rot.php');
	foreach($map->layers as $a=>$layer) {
		for($i=0;$i<$map->height;++$i) {
			for($j=0;$j<$map->width;++$j) {
				$tile=$map->layers[$a]->get_tile($i*$map->width+$j);
				$lid=$tile-$map->tilesets[$map->get_tileset_index($tile)]->firstgid;
				//var_dump($lid);
				if($_REQUEST['rot']=='cw' || $_REQUEST['rot']=='270') {
					$lid=rotate90cw_lid($lid);
				}
				elseif($_REQUEST['rot']=='ccw' || $_REQUEST['rot']=='90') {
					$lid=rotate90ccw_lid($lid);
				}
				elseif($_REQUEST['rot']=='180') {
					$lid=rotate180_lid($lid);
				}
				//var_dump($lid);
				//echo '<br/>';
				if($lid==-1) $tile=0;
				else $tile=$lid+$map->tilesets[$map->get_tileset_index($tile)]->firstgid;
				//var_dump($tile);
				$map->layers[$a]->set_tile($i*$map->width+$j, $tile);
			}
		}
		//var_dump($map->layers[$a]);die();
		if($_REQUEST['rot']=='cw' || $_REQUEST['rot']=='270') {
			$map->layers[$a]->rot90cw();
		}
		elseif($_REQUEST['rot']=='ccw' || $_REQUEST['rot']=='90') {
			$map->layers[$a]->rot90ccw();
		}
		elseif($_REQUEST['rot']=='180') {
			$map->layers[$a]->rot180();
		}
	}
	if(!in_array($_REQUEST['rot'],array('cw','ccw'))) {
		$tmp=$map->width;
		$map->width=$map->height;
		$map->height=$tmp;
	}
	//var_dump($map);
}

$viewer->setMap($map);

ini_set('output_buffering','off');

$data=ob_get_clean();
if(!empty($data)) {
	header('Content-Type: text/plain'."\r\n");
	echo $data;
	die();
}

ob_start();

if(!array_key_exists('layers_nodraw', $_SESSION)) {
	$_SESSION['layers_nodraw']=array('collision');
}
if(!array_key_exists('tilesets_nodraw', $_SESSION)) {
	$_SESSION['tilesets_nodraw']=array('collision');
}

$viewer->load_ts();

if(array_key_exists('zoom',$_REQUEST)) {
	assert(is_numeric($_REQUEST['zoom'])) or die('bad zoom value');
	$viewer->zoom=floatval($_REQUEST['zoom']);
	assert($viewer->zoom>=0.1 && $viewer->zoom<=10) or die('bad zoom range');
}

$viewer->init_draw();
$viewer->draw();

$data=ob_get_contents();
if(strlen($data)!=0) {
	header('Content-Type: text/plain'."\r\n");
	echo $data;
	die();
}

$viewer->render();

$data=ob_get_clean();
if(!defined('DEBUG')||DEBUG!==true) {
	//header('Content-Type: image/jpeg'."\r\n");
	header('Content-Type: image/png'."\r\n");
}
echo $data;

?>