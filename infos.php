<?php
if(!defined('DEBUG')) {
define('DEBUG', true);

ini_set('error_reporting', E_ALL | E_STRICT | E_RECOVERABLE_ERROR | E_DEPRECATED | E_USER_DEPRECATED);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$files=array();
$files[]='functions.inc.php';
$files[]='map.php';
$files[]='tileset.php';
$files[]='layer.php';
$files[]='tilelayer.php';
$files[]='objectlayer.php';
$files[]='object.php';
$files[]='properties.php';
if(PHP_VERSION_ID < 50400) {
	$files[]='properties_53.php';
}
else {
	$files[]='properties_54.php';
}
$files[]='idproperties.php';
if(PHP_VERSION_ID < 50400) {
	$files[]='idproperties_53.php';
}
else {
	$files[]='idproperties_54.php';
}
$files[]='compat.php';
if(PHP_VERSION_ID < 50400) {
	$files[]='compat_53.php';
}
else {
	$files[]='compat_54.php';
}
$files[]='viewer.php';
$files[]='viewer_view.php';
$files[]='viewer_ui.php';

$classes=array();
$functions=array();

foreach($files as $file) {
	ob_start();
	require_once($file);
	ob_end_clean();
	$data=file_get_contents($file);
	//$res=preg_match_all('/class\s*(\S+)(?:\s*extends\s*(?:\S+,\s*)?\S+)?(?:\s*implements\s*(?:\S+,\s*)?\S+)?\s*{/', $data, $matches);
	$res=preg_match_all('/(?:class|trait)\s*(\S+)(?:\s*extends\s*(?:\S+,\s*)?\S+)?(?:\s*implements\s*(?:\S+,\s*)?\S+)?\s*{/', $data, $matches);
	if($res>0) {
		foreach($matches[1] as $class) {
			$classes[]=$class;
		}
		unset($class);
	}
	else {
		$res=preg_match_all('/function\s+(\S+)\s*\(/', $data, $matches);
		if($res>0) {
			foreach($matches[1] as $function) {
				$functions[]=$function;
			}
			unset($function);
		}
	}
	unset($data);
}
unset($file,$res,$matches);

$action='list_proj';
}
require(dirname(__FILE__).'/../infos.php');
?>