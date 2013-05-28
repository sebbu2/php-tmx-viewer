<?php
session_start();
if(!is_array($_SESSION['layers_nodraw'])) $_SESSION['layers_nodraw']=array();
if(!is_array($_SESSION['tilesets_nodraw'])) $_SESSION['tilesets_nodraw']=array();
if(array_key_exists('act', $_REQUEST)) {
	@assert(array_key_exists('l', $_REQUEST) || array_key_exists('t', $_REQUEST)) or die('Hacking attempt.');
	switch($_REQUEST['act']) {
		case 'ldel':
			$_SESSION['layers_nodraw']=array_diff($_SESSION['layers_nodraw'], array($_REQUEST['l']));
			break;
		case 'ladd':
			$_SESSION['layers_nodraw'][]=$_REQUEST['l'];
			break;
		case 'tdel':
			$_SESSION['tilesets_nodraw']=array_diff($_SESSION['tilesets_nodraw'], array($_REQUEST['t']));
			break;
		case 'tadd':
			$_SESSION['tilesets_nodraw'][]=$_REQUEST['t'];
			break;
		default:
			break;
	}
}
echo '<h1>Layers not to draw</h1>'."\r\n";
if(array_key_exists('layers_nodraw',$_SESSION)) {
	if(is_array($_SESSION['layers_nodraw']) && count($_SESSION['layers_nodraw'])>0) {
		echo '<table border="1">'."\r\n";
		foreach($_SESSION['layers_nodraw'] as $layer) {
			echo '	<tr>'."\r\n";
			echo '		<td>'.$layer.'</td>'."\r\n";
			echo '		<td><a href="?act=ldel&l='.$layer.'">Delete</a></td>'."\r\n";
			echo '	</tr>'."\r\n";
		}
		echo '</table>'."\r\n";
	}
}
echo '<form method="get">'."\r\n";
echo '<input type="hidden" name="act" value="ladd"/>'."\r\n";
echo '<input type="text" name="l"/><br/>'."\r\n";
echo '<input type="reset" value="Reset"/>'."\r\n";
echo '<input type="submit" value="Submit"/>'."\r\n";
echo '</form>'."\r\n";
//var_dump($_SESSION['layers_nodraw']);
echo '<h1>Tilesets not to draw</h1>'."\r\n";
if(array_key_exists('tilesets_nodraw',$_SESSION)) {
	if(is_array($_SESSION['tilesets_nodraw']) && count($_SESSION['tilesets_nodraw'])>0) {
		echo '<table border="1">'."\r\n";
		foreach($_SESSION['tilesets_nodraw'] as $tileset) {
			echo '	<tr>'."\r\n";
			echo '		<td>'.$tileset.'</td>'."\r\n";
			echo '		<td><a href="?act=tdel&t='.$tileset.'">Delete</a></td>'."\r\n";
			echo '	</tr>'."\r\n";
		}
		echo '</table>'."\r\n";
	}
}
echo '<form method="get">'."\r\n";
echo '<input type="hidden" name="act" value="tadd"/>'."\r\n";
echo '<input type="text" name="t"/><br/>'."\r\n";
echo '<input type="reset" value="Reset"/>'."\r\n";
echo '<input type="submit" value="Submit"/>'."\r\n";
echo '</form>'."\r\n";

?>