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
	$_REQUEST['list']=$file;
}
if(array_key_exists('ref', $_REQUEST) && $_REQUEST[$var]!='') {
	$ref=$_REQUEST['ref'];
}
else {
	$ref='';
}
if($ref=='LOCAL') $ref='';

$recur=false;

$res=$map->load($file, $ref, $recur);

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
	assert($zoom>=0.1 && $zoom<=10) or trigger_error('bad zoom range', E_USER_ERROR);
	$zoom=floatval($_REQUEST['zoom']);
	//$viewer->zoom=$zoom;
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


$dt=true;
if(array_key_exists('dt',$_REQUEST)) {
	$dt=$_REQUEST['dt'];
	if(is_null($dt)) $dt=true;
	else if( strcasecmp($dt,'true' )==0 || strcasecmp($dt,'yes')==0 || $dt==='1' || $dt===1 ) $dt=true;
	else if( strcasecmp($dt,'false')==0 || strcasecmp($dt,'no' )==0 || $dt==='0' || $dt===0 ) $dt=false;
	else $dt=false;
	//$viewer->draw_tiles=$dt;
}
$do=true;
if(array_key_exists('do',$_REQUEST)) {
	$do=$_REQUEST['do'];
	if(is_null($do)) $do=true;
	else if( strcasecmp($do,'true' )==0 || strcasecmp($do,'yes')==0 || $do==='1' || $do===1 ) $do=true;
	else if( strcasecmp($do,'false')==0 || strcasecmp($do,'no' )==0 || $do==='0' || $do===0 ) $do=false;
	else $do=false;
	//$viewer->draw_objects=$do;
}
$di=true;
if(array_key_exists('di',$_REQUEST)) {
	$di=$_REQUEST['di'];
	if(is_null($di)) $di=true;
	else if( strcasecmp($di,'true' )==0 || strcasecmp($di,'yes')==0 || $di==='1' || $di===1 ) $di=true;
	else if( strcasecmp($di,'false')==0 || strcasecmp($di,'no' )==0 || $di==='0' || $di===0 ) $di=false;
	else $di=false;
	//$viewer->draw_images=$di;
}
if(array_key_exists('rot',$_REQUEST)) {
	assert(in_array($_REQUEST['rot'],array('cw','ccw','180'))) or trigger_error('bad rot value', E_USER_ERROR);
	$rot=$_REQUEST['rot'];
}

$act_count=0;
if(array_key_exists('act_u',$_REQUEST)) {
	$vals=array(
		'-',//minus
		'+',//plus
		'&#8593;',//haut 1
		'&#8595;',//bas 1
		'&#8657;',//haut 2
		'&#8659;',//bas 2
		'&uarr;',//haut 1
		'&darr;',//bas 1
		'&uArr;',//haut 2
		'&dArr;',//bas 2
	);
	$act_u=htmlentities($_REQUEST['act_u'], ENT_COMPAT | ENT_HTML401, 'UTF-8');
	if(!in_array($act_u, $vals)) die('incorrect up action');
	switch($act_u) {
		case '-'://minus
			$h--;
			$y++;
			break;
		case '+'://plus
			$h++;
			$y--;
			break;
		case '&#8593;'://haut 1
		case '&uarr;'://haut 1
			$y--;
			break;
		case '&#8595;'://bas 1
		case '&darr;'://bas 1
			$y++;
			break;
		case '&#8657;'://haut 2
		case '&uArr;'://haut 2
			$y-=$h;
			break;
		case '&#8659;'://bas 2
		case '&dArr;'://bas 2
			$y+=$h;
			break;
		default:
			die('incorrect up action');
			break;
	}
	$act_count++;
}
if(array_key_exists('act_l',$_REQUEST)) {
	$vals=array(
		'-',//minus
		'+',//plus
		'&#8592;',//left 1
		'&#8596;',//right 1
		'&#8656;',//left 2
		'&#8658;',//right 2
		'&larr;',//left 1
		'&rarr;',//right 1
		'&lArr;',//left 2
		'&rArr;',//right 2
	);
	$act_l=htmlentities($_REQUEST['act_l'], ENT_COMPAT | ENT_HTML401, 'UTF-8');
	if(!in_array($act_l, $vals)) die('incorrect left action');
	switch($act_l) {
		case '-'://minus
			$w--;
			$x++;
			break;
		case '+'://plus
			$w++;
			$x--;
			break;
		case '&#8592;'://left 1
		case '&larr;'://left 1
			$x--;
			break;
		case '&#8596;'://right 1
		case '&rarr;'://right 1
			$x++;
			break;
		case '&#8656;'://left 2
		case '&lArr;'://left 2
			$x-=$w;
			break;
		case '&#8658;'://right 2
		case '&rArr;'://right 2
			$x+=$w;
			break;
		default:
			die('incorrect left action');
			break;
	}
	$act_count++;
}
if(array_key_exists('act_r',$_REQUEST)) {
	$vals=array(
		'-',//minus
		'+',//plus
		'&#8592;',//left 1
		'&#8596;',//right 1
		'&#8656;',//left 2
		'&#8658;',//right 2
		'&larr;',//left 1
		'&rarr;',//right 1
		'&lArr;',//left 2
		'&rArr;',//right 2
	);
	$act_r=htmlentities($_REQUEST['act_r'], ENT_COMPAT | ENT_HTML401, 'UTF-8');
	if(!in_array($act_r, $vals)) die('incorrect right action');
	$act_count++;
}
if(array_key_exists('act_d',$_REQUEST)) {
	$vals=array(
		'-',//minus
		'+',//plus
		'&#8593;',//haut 1
		'&#8595;',//bas 1
		'&#8657;',//haut 2
		'&#8659;',//bas 2
		'&uarr;',//haut 1
		'&darr;',//bas 1
		'&uArr;',//haut 2
		'&dArr;',//bas 2
	);
	$act_d=htmlentities($_REQUEST['act_d'], ENT_COMPAT | ENT_HTML401, 'UTF-8');
	if(!in_array($act_d, $vals)) die('incorrect down action');
	switch($act_d) {
		case '-'://minus
			$h--;
			break;
		case '+'://plus
			$h++;
			break;
		case '&#8593;'://haut 1
		case '&uarr;'://haut 1
			$y--;
			break;
		case '&#8595;'://bas 1
		case '&darr;'://bas 1
			$y++;
			break;
		case '&#8657;'://haut 2
		case '&uArr;'://haut 2
			$y-=$h;
			break;
		case '&#8659;'://bas 2
		case '&dArr;'://bas 2
			$y+=$h;
			break;
		default:
			die('incorrect down action');
			break;
	}
	$act_count++;
}

if($act_count>1) die('incorrect action');

if($x<0) $x=0;
if($y<0) $y=0;
if($h<1) $h=1;
if($w<1) $w=1;

if($w>$map->width ) $w=$map->width ;
if($h>$map->height) $h=$map->height;

if($x>=$map->width) {
	$x=$map->width-$w;
}
if($y>=$map->height) {
	$y=$map->height-$h;
}

if($x>$map->width -$w) $x=$map->width -$w;
if($y>$map->height-$h) $y=$map->height-$h;

if($w==$map->width &&$x>0) $x=0;
if($h==$map->height&&$y>0) $y=0;

function show_select($value) {
	$vals=array(0=>'Non',1=>'Oui');
	foreach($vals as $k=>$v) {
		echo '<option value="'.$k.'"';
		if($value==$k) {
			echo ' selected="selected"';
		}
		echo '>'.$v.'</option>'."\r\n";
	}
}

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
.content_tab {
	border-collapse: collapse;
}
.content_tab td {
	border: 1px solid green;
}
.b {
	width: 30px;
	height: 30px;
}
.content_t, .content_b {
	height: 30px;
	text-align: center;
}
.content_l, .content_r {
	width: 60px;
	vertical-align: center;
}
</style>
<script>
var xhr_object = null;
if(window.XMLHttpRequest) // Firefox
	xhr_object = new XMLHttpRequest();
else if(window.ActiveXObject) // Internet Explorer
	xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
else { // XMLHttpRequest not supported on browser
	alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
	//return;
}
function get_select(obj) {
	var type = (obj.value || obj.options[obj.selectedIndex].value);
	if(type=='') type='LOCAL';
	/* asynchrone */
	/*xhr_object.open("GET", type+'.htm', true);

	xhr_object.onreadystatechange = function() {
		if(xhr_object.readyState == 4) {
			document.getElementById('map').innerHTML="<option value=\"\"></option>\r\n"+xhr_object.responseText;
		}
	}
	xhr_object.send(null);//*/
	/* synchrone */
	xhr_object.open("GET", type+'.htm', false);
	xhr_object.send(null);
	if(xhr_object.readyState == 4) document.getElementById('map').innerHTML="<option value=\"\"></option>\r\n"+xhr_object.responseText;//*/
}
</script>
</head>
<body>

<form action="" method="get">
<div class="choice">
<select name="ref" id="ref" onchange="get_select(this);">
<?php
$ar=array(
	'LOCAL'=>'LOCAL',
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
if(!array_key_exists('ref',$_REQUEST) || $_REQUEST['ref']=='' || $_REQUEST['ref']=='LOCAL') {
	echo '<input type="hidden" name="choice" value="list"/>'."\r\n";
	echo '<select name="list" id="map">'."\r\n";
	echo '<option value=""></option>'."\r\n";
	//require('local.php');
	$data=file('LOCAL.htm');
	foreach($data as $line) {
		$file=substr($line, strpos($line,'"')+1, strpos($line,'"',strpos($line,'"')+1)-strpos($line,'"')-1);
		//var_dump($file);die();
		assert(strpos($file,'"')===false) or die('quote found in file.');
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
?><label for="x">X: </label><input type="text" id="x" name="x" value="<?php echo $x; ?>"/><br/>
<label for="y">Y: </label><input type="text" id="y" name="y" value="<?php echo $y; ?>"/><br/>
<label for="w">W: </label><input type="text" id="w" name="w" value="<?php echo (($w!=PHP_INT_MAX)?$w:12); ?>"/><br/>
<label for="h">H: </label><input type="text" id="h" name="h" value="<?php echo (($h!=PHP_INT_MAX)?$h:12); ?>"/><br/>
<label for="zoom">Zoom: </label><input type="text" id="zoom" name="zoom" value="<?php echo $zoom; ?>"/><br/>
<label for="dt">Draw tiles: </label><select id="dt" name="dt"><?php show_select($dt); ?></select><br/>
<label for="dt">Draw images: </label><select id="di" name="di"><?php show_select($di); ?></select><br/>
<label for="dt">Draw objects: </label><select id="do" name="do"><?php show_select($do); ?></select><br/>
</div>
<input type="submit" value="Valider"/>
</div>
<div class="content"><table class="content_tab">
	<tr class="content_t">
		<td colspan="3"><input class="b" type="submit" name="act_u" value="-"/><input class="b" type="submit" name="act_u" value="+"/>&nbsp; &nbsp;<input class="b" type="submit" name="act_u" value="&#8593;"/><input class="b" type="submit" name="act_u" value="&#8595;"/>&nbsp; &nbsp;<input class="b" type="submit" name="act_u" value="&#8657;"/><input class="b" type="submit" name="act_u" value="&#8659;"/></td>
	</tr>
	<tr class="content_mv">
		<td class="content_l"><input class="b" type="submit" name="act_l" value="-"/><input class="b" type="submit" name="act_l" value="+"/><br/><br/><input class="b" type="submit" name="act_l" value="&#8592;"/><input class="b" type="submit" name="act_l" value="&#8594;"/><br/><br/><input class="b" type="submit" name="act_l" value="&#8656;"/><input class="b" type="submit" name="act_l" value="&#8658;"/></td>
		<td class="content_c"><?php
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
if(array_key_exists('choice',$_REQUEST) && array_key_exists('list',$_REQUEST)) echo '&list='.$_REQUEST['list'];
if(array_key_exists('ref',$_REQUEST) && $_REQUEST['ref']!='LOCAL') echo '&ref='.$_REQUEST['ref'];
if(array_key_exists('zoom',$_REQUEST)) echo '&zoom='.$zoom;
if(array_key_exists('dt',$_REQUEST)) echo '&dt='.$_REQUEST['dt'];
if(array_key_exists('do',$_REQUEST)) echo '&do='.$_REQUEST['do'];
if(array_key_exists('di',$_REQUEST)) echo '&di='.$_REQUEST['di'];
if(array_key_exists('x',$_REQUEST)) echo '&x='.$x;
if(array_key_exists('y',$_REQUEST)) echo '&y='.$y;
if(array_key_exists('w',$_REQUEST)) echo '&w='.$w;
if(array_key_exists('h',$_REQUEST)) echo '&h='.$h;
if(array_key_exists('rot',$_REQUEST)) echo '&rot='.$rot;
?>"<?php
if($file!='') {
	//var_dump($_REQUEST['w'],$_REQUEST['h'],$map->tilewidth,$map->tileheight,$zoom);
	if($w!=PHP_INT_MAX && $h!=PHP_INT_MAX) {
		$_w=$w*$map->tilewidth *$zoom;
		$_h=$h*$map->tileheight*$zoom;
		echo ' width="' .$_w.'"';
		echo ' height="'.$_h.'"';
	}
	else {
		echo ' width="' .($map->width *$map->tilewidth *$zoom).'"';
		echo ' height="'.($map->height*$map->tileheight*$zoom).'"';
	}
}
?>/><?php
}
?></td>
		<td class="content_r"><input class="b" type="submit" name="act_r" value="-"/><input class="b" type="submit" name="act_r" value="+"/><br/><br/><input class="b" type="submit" name="act_r" value="&#8592;"/><input class="b" type="submit" name="act_r" value="&#8594;"/><br/><br/><input class="b" type="submit" name="act_r" value="&#8656;"/><input class="b" type="submit" name="act_r" value="&#8658;"/></td>
	</tr>
	<tr class="content_b">
		<td colspan="3"><input class="b" type="submit" name="act_d" value="-"/><input class="b" type="submit" name="act_d" value="+"/>&nbsp; &nbsp;<input class="b" type="submit" name="act_d" value="&#8593;"/><input class="b" type="submit" name="act_d" value="&#8595;"/>&nbsp; &nbsp;<input class="b" type="submit" name="act_d" value="&#8657;"/><input class="b" type="submit" name="act_d" value="&#8659;"/></td>
	</tr>
</table></form></div>

</body>
</html><?php /*ob_end_flush();*/ ?>