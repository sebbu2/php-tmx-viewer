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
			//$var='file';
			$msg='not yet implemented.';
		break;
	}
	if($msg!='') {
		die($msg);
	}
	if(array_key_exists($var, $_REQUEST) && $_REQUEST[$var]!='') {
		$file=$_REQUEST[$var];
	}
}
else {
	//
}
if(array_key_exists('ref', $_REQUEST) && $_REQUEST[$var]!='') {
	$ref=$_REQUEST['ref'];
}
else {
	$ref='';
}

$res=$map->load($file, $ref);

//$viewer=new Viewer();
//$viewer->setMap($map);

ini_set('output_buffering','off');

$data=ob_get_clean();
if(!empty($data)) {
	header('Content-Type: text/plain'."\r\n");
	echo $data;
	//var_dump($data);
	die();
}

//ob_start();

if(!array_key_exists('layers_nodraw', $_SESSION)) {
	$_SESSION['layers_nodraw']=array('collision');
}
if(!array_key_exists('tilesets_nodraw', $_SESSION)) {
	$_SESSION['tilesets_nodraw']=array('collision');
}

//$viewer->load_ts();

$zoom=1;
if(array_key_exists('zoom',$_REQUEST)) {
	assert(is_numeric($_REQUEST['zoom'])) or trigger_error('bad zoom value', E_USER_ERROR);
	$zoom=floatval($_REQUEST['zoom']);
	//$viewer->zoom=$zoom;
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

//$ox=-$x*$map->tilewidth *$zoom;
//$oy=-$y*$map->tileheight*$zoom;

$dt=true;
if(array_key_exists('dt',$_REQUEST)) {
	$dt=$_REQUEST['dt'];
	if(is_null($dt)||empty($dt)) $dt=true;
	else if( strcasecmp($dt,'true' )==0 || strcasecmp($dt,'yes')==0 || $dt===1 ) $dt=true;
	else if( strcasecmp($dt,'false')==0 || strcasecmp($dt,'no' )==0 || $dt===0 ) $dt=false;
	else $dt=false;
	//$viewer->draw_tiles=$dt;
}
$do=true;
if(array_key_exists('do',$_REQUEST)) {
	$do=$_REQUEST['do'];
	if(is_null($do)||empty($do)) $do=true;
	else if( strcasecmp($do,'true' )==0 || strcasecmp($do,'yes')==0 || $do===1 ) $do=true;
	else if( strcasecmp($do,'false')==0 || strcasecmp($do,'no' )==0 || $do===0 ) $do=false;
	else $do=false;
	//$viewer->draw_objects=$do;
}
$di=true;
if(array_key_exists('di',$_REQUEST)) {
	$di=$_REQUEST['di'];
	if(is_null($di)||empty($di)) $di=true;
	else if( strcasecmp($di,'true' )==0 || strcasecmp($di,'yes')==0 || $di===1 ) $di=true;
	else if( strcasecmp($di,'false')==0 || strcasecmp($di,'no' )==0 || $di===0 ) $di=false;
	else $di=false;
	//$viewer->draw_images=$di;
}
if(array_key_exists('rot',$_REQUEST)) {
	assert(in_array($_REQUEST['rot'],array('cw','ccw','180'))) or trigger_error('bad rot value', E_USER_ERROR);
	$rot=$_REQUEST['rot'];
}
//$viewer->ox=$ox;
//$viewer->oy=$oy;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>PHP TMX Map Viewer<?php if(array_key_exists('ref',$_REQUEST)&&$_REQUEST['ref']!='') print(' - '.$_REQUEST['ref']); ?><?php if(array_key_exists('url',$_REQUEST)&&$_REQUEST['url']!='') print(' - '.$_REQUEST['url']); else if(array_key_exists('list',$_REQUEST)&&$_REQUEST['list']!='') print(' - '.$_REQUEST['list']);?></title>
<style>
.tr {
	position:fixed;
	top:10px;
	right:10px;
	width: 200px;
	height: 200px;
	border: 1px solid black;
	padding: 5px;
}
.tr input {
	position: fixed;
	right: 15px;
}
img {
	border: 1px dashed gray;
}
</style>
</head>
<body>

<div class="choice">
<form action="" method="get">
<select name="ref" onchange="document.getElementById('map').selectedIndex=0;">
<?php
$ar=array(
	''=>'LOCAL',
	'tmw'=>'The Mana World',
	'evol'=>'Evol Online',
	'tales'=>'Source of Tales',
	'stendhal'=>'Stendhal',
);
foreach($ar as $k=>$v) {
	echo '<option value="'.$k.'"';
	if(array_key_exists('ref',$_REQUEST) && $_REQUEST['ref']==$k) echo ' selected="selected"';
	echo '>'.$v.'</option>'."\r\n";
}
?></select>
<?php
if(!array_key_exists('ref',$_REQUEST) || $_REQUEST['ref']=='') {
	$files=array();
	$files=array_merge($files, glob('../../tmw/*.tmx'));
	$files=array_merge($files, glob('../../tmx/*.tmx'));
	$files=array_merge($files, glob('../../maps/*.tmx'));
	$files=array_merge($files, glob('../tmw/*.tmx'));
	$files=array_merge($files, glob('../tmx/*.tmx'));
	$files=array_merge($files, glob('../maps/*.tmx'));
	$files=array_merge($files, glob('../*.tmx'));
	$files=array_merge($files, glob('tmw/*.tmx'));
	$files=array_merge($files, glob('tmx/*.tmx'));
	$files=array_merge($files, glob('maps/*.tmx'));
	//$files=array_merge($files, glob('*/*.tmx'));
	$files=array_merge($files, glob('*.tmx'));
	$files=array_values(array_unique($files));
	echo '<input type="hidden" name="choice" value="list"/>'."\r\n";
	echo '<select name="list" id="map">'."\r\n";
	echo '<option value=""></option>'."\r\n";
	foreach($files as $file) {
		echo '<option value="'.$file.'"';
		if(array_key_exists('list',$_REQUEST)&&$_REQUEST['list']==$file) echo ' selected="selected"';
		echo '>'.$file.'</option>'."\r\n";
	}
	echo '</select>'."\r\n";
}
else {
	if(file_exists($_REQUEST['ref'].'.htm') && (!array_key_exists('force',$_REQUEST) || $_REQUEST['force']!=='1') ) {
		echo '<input type="hidden" name="choice" value="url"/>'."\r\n";
		echo '<select name="url" id="map">'."\r\n";
		echo '<option value=""></option>'."\r\n";
		//require($_REQUEST['ref'].'.htm');
		$data=file($_REQUEST['ref'].'.htm');
		foreach($data as $line) {
			$file=substr($line, strpos($line,'"')+1, strpos($line,'"',strpos($line,'"')+1)-strpos($line,'"')-1);
			//var_dump($file);die();
			assert(strpos($file,'"')===false) or die('quote found in file.');
			if(array_key_exists('url',$_REQUEST)&&$_REQUEST['url']==$file) {
				echo substr($line, 0, strpos($line,'"',strpos($line,'"')+1)+1);
				echo ' selected="selected"';
				echo substr($line, strpos($line,'"',strpos($line,'"')+1));
			}
			else {
				echo $line;
			}
		}
		echo '</select>'."\r\n";
	}
	else {
		//echo '<option value=""></option>'."\r\n";
	}
}
?></select>
<div class="tr">
<?php
if(!array_key_exists('x',$_REQUEST))
$_REQUEST['x']=0;
if(!array_key_exists('y',$_REQUEST))
$_REQUEST['y']=0;
if(!array_key_exists('w',$_REQUEST))
$_REQUEST['w']=12;
if(!array_key_exists('h',$_REQUEST))
$_REQUEST['h']=12;
if(!array_key_exists('dt',$_REQUEST))
$dt_='';
else
$dt_=' checked="checked"';
if(!array_key_exists('do',$_REQUEST))
$do_='';
else
$do_=' checked="checked"';
if(!array_key_exists('di',$_REQUEST))
$di_='';
else
$di_=' checked="checked"';
?><label for="x">X: </label><input type="text" id="x" name="x" value="<?php echo $_REQUEST['x']; ?>"/><br/>
<label for="y">Y: </label><input type="text" id="y" name="y" value="<?php echo $_REQUEST['y']; ?>"/><br/>
<label for="w">W: </label><input type="text" id="w" name="w" value="<?php echo $_REQUEST['w']; ?>"/><br/>
<label for="h">H: </label><input type="text" id="h" name="h" value="<?php echo $_REQUEST['h']; ?>"/><br/>
<label for="dt">Draw tiles: </label><input type="checkbox" id="dt" name="dt"<?php echo $dt_; ?>/><br/>
<label for="dt">Draw images: </label><input type="checkbox" id="di" name="di"<?php echo $di_; ?>/><br/>
<label for="dt">Draw objects: </label><input type="checkbox" id="do" name="do"<?php echo $do_; ?>/><br/>
</div>
<input type="submit" value="Valider"/>
</form>
</div>
<div class="content"><?php
if(array_key_exists('choice',$_REQUEST) && $_REQUEST['choice']=='url' && $_REQUEST['url']=='') {
	echo 'Please choose a map.';
}
else if(array_key_exists('choice',$_REQUEST) && $_REQUEST['choice']=='list' && $_REQUEST['list']=='') {
	echo 'Please choose a map.';
}
else {
?><img src="viewer_part.php?<?php
if(array_key_exists('choice',$_REQUEST)) echo '&choice='.$_REQUEST['choice'];
if(array_key_exists('url',$_REQUEST)) echo '&url='.$_REQUEST['url'];
if(array_key_exists('file',$_REQUEST)) echo '&file='.$_REQUEST['file'];
if(array_key_exists('list',$_REQUEST)) echo '&list='.$_REQUEST['list'];
if(array_key_exists('ref',$_REQUEST)) echo '&ref='.$_REQUEST['ref'];
if(array_key_exists('zoom',$_REQUEST)) echo '&zoom='.$_REQUEST['zoom'];
if(array_key_exists('dt',$_REQUEST)) echo '&dt='.$_REQUEST['dt'];
if(array_key_exists('do',$_REQUEST)) echo '&do='.$_REQUEST['do'];
if(array_key_exists('di',$_REQUEST)) echo '&di='.$_REQUEST['di'];
if(array_key_exists('x',$_REQUEST)) echo '&x='.$_REQUEST['x'];
if(array_key_exists('y',$_REQUEST)) echo '&y='.$_REQUEST['y'];
if(array_key_exists('w',$_REQUEST)) echo '&w='.$_REQUEST['w'];
if(array_key_exists('h',$_REQUEST)) echo '&h='.$_REQUEST['h'];
if(array_key_exists('rot',$_REQUEST)) echo '&rot='.$_REQUEST['rot'];
?>"<?php
if($file!='') {
	echo ' width="' .($_REQUEST['w']*$map->tilewidth) .'"';
	echo ' height="'.($_REQUEST['h']*$map->tileheight).'"';
}
?>/><?php
}
?></div>

</body>
</html><?php /*ob_end_flush();*/ ?>