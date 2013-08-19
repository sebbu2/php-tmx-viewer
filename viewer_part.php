<?php

ini_set('error_reporting', E_ALL | E_NOTICE | E_STRICT | E_RECOVERABLE_ERROR | E_DEPRECATED | E_USER_DEPRECATED | E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE);

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

if(array_key_exists('choice',$_REQUEST)) {
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
		default:
			//$var='file';
			$msg='not yet implemented.';
		break;
	}
	if($msg!='') {
		die($msg);
	}
	if($var=='') {
		die('unknown choice');
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

if($file=='../maps/isometric.tmx' && array_key_exists('rot', $_REQUEST)) {
	assert(in_array($_REQUEST['rot'],array('cw','ccw','180'))) or trigger_error('bad rot value', E_USER_ERROR);
	$rot=$_REQUEST['rot'];
	require('rot.php');
	foreach($map->layers as $a=>$layer) {
		for($i=0;$i<$map->height;++$i) {
			for($j=0;$j<$map->width;++$j) {
				$tile=$map->layers[$a]->get_tile($i*$map->width+$j);
				$lid=$tile-$map->tilesets[$map->get_tileset_index($tile)]->firstgid;
				if($rot=='cw') {
					$lid=rotate90cw_lid($lid);
				}
				elseif($rot=='ccw') {
					$lid=rotate90ccw_lid($lid);
				}
				elseif($rot=='180') {
					$lid=rotate180_lid($lid);
				}
				if($lid==-1) $tile=0;
				else $tile=$lid+$map->tilesets[$map->get_tileset_index($tile)]->firstgid;
				$map->layers[$a]->set_tile($i*$map->width+$j, $tile);
			}
		}
		if($rot=='cw') {
			$map->layers[$a]->rot90cw();
		}
		elseif($rot=='ccw') {
			$map->layers[$a]->rot90ccw();
		}
		elseif($rot=='180') {
			$map->layers[$a]->rot180();
		}
	}
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

$zoom=1;
if(array_key_exists('zoom',$_REQUEST)) {
	assert(is_numeric($_REQUEST['zoom'])) or trigger_error('bad zoom value', E_USER_ERROR);
	$zoom=floatval($_REQUEST['zoom']);
	$viewer->zoom=$zoom;
	assert($viewer->zoom>=0.1 && $viewer->zoom<=10) or trigger_error('bad zoom range', E_USER_ERROR);
}

$x=0;
if(array_key_exists('x',$_REQUEST)) {
	assert(is_numeric($_REQUEST['x'])) or trigger_error('bad x value', E_USER_ERROR);
	$x=intval($_REQUEST['x']);
}
$y=0;
if(array_key_exists('y',$_REQUEST)) {
	assert(is_numeric($_REQUEST['y'])) or trigger_error('bad y value', E_USER_ERROR);
	$y=intval($_REQUEST['y']);
}
$w=PHP_INT_MAX;
if(array_key_exists('w',$_REQUEST)) {
	assert(is_numeric($_REQUEST['w'])) or trigger_error('bad w value', E_USER_ERROR);
	$w=intval($_REQUEST['w']);
}
$h=PHP_INT_MAX;
if(array_key_exists('h',$_REQUEST)) {
	assert(is_numeric($_REQUEST['h'])) or trigger_error('bad h value', E_USER_ERROR);
	$h=intval($_REQUEST['h']);
}

$ox=-$x*$map->tilewidth *$zoom;
/*if(array_key_exists('ox',$_REQUEST)) {
	assert(is_numeric($_REQUEST['ox'])) or trigger_error('bad ox value', E_USER_ERROR);
	$ox=intval($_REQUEST['ox']);
}//*/
$oy=-$y*$map->tileheight*$zoom;
/*if(array_key_exists('oy',$_REQUEST)) {
	assert(is_numeric($_REQUEST['oy'])) or trigger_error('bad oy value', E_USER_ERROR);
	$oy=intval($_REQUEST['oy']);
}//*/

$dt=false;
$viewer->draw_tiles=$dt;
if(array_key_exists('dt',$_REQUEST)) {
	$dt=$_REQUEST['dt'];
	if(is_null($dt)||empty($dt)) $dt=false;
	else if( strcasecmp($dt,'true' )==0 || strcasecmp($dt,'yes')==0 || strcasecmp($dt,'on' )==0 || $dt===1 ) $dt=true;
	else if( strcasecmp($dt,'false')==0 || strcasecmp($dt,'no' )==0 || strcasecmp($dt,'off')==0 || $dt===0 ) $dt=false;
	else $dt=false;
	$viewer->draw_tiles=$dt;
}
$do=false;
$viewer->draw_objects=$do;
if(array_key_exists('do',$_REQUEST)) {
	$do=$_REQUEST['do'];
	if(is_null($do)||empty($do)) $do=false;
	else if( strcasecmp($do,'true' )==0 || strcasecmp($do,'yes')==0 || strcasecmp($do,'on' )==0 || $do===1 ) $do=true;
	else if( strcasecmp($do,'false')==0 || strcasecmp($do,'no' )==0 || strcasecmp($do,'off')==0 || $do===0 ) $do=false;
	else $do=false;
	$viewer->draw_objects=$do;
}
$di=false;
$viewer->draw_images=$di;
if(array_key_exists('di',$_REQUEST)) {
	$di=$_REQUEST['di'];
	if(is_null($di)||empty($di)) $di=false;
	else if( strcasecmp($di,'true' )==0 || strcasecmp($di,'yes')==0 || strcasecmp($di,'on' )==0 || $di===1 ) $di=true;
	else if( strcasecmp($di,'false')==0 || strcasecmp($di,'no' )==0 || strcasecmp($di,'off')==0 || $di===0 ) $di=false;
	else $di=false;
	$viewer->draw_images=$di;
}

$viewer->ox=$ox;
$viewer->oy=$oy;

//var_dump($x, $y, $w, $h);die();

$viewer->init_draw($x, $y, $w, $h);
$viewer->draw($x, $y, $w, $h);

$data=ob_get_contents();
if(strlen($data)!=0) {
	header('Content-Type: text/plain'."\r\n");
	echo $data;
	die();
}

$viewer->render();

$data=ob_get_clean();
unset($viewer);$viewer=NULL;
if(!defined('DEBUG')||DEBUG!==true) {
	//header('Content-Type: image/jpeg'."\r\n");
	header('Content-Type: image/png'."\r\n");
}
echo $data;

?>