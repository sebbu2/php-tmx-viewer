<?php
session_start();
if(array_key_exists('act', $_REQUEST)) {
	@assert(array_key_exists('l', $_REQUEST)) or die('Hacking attempt.');
	switch($_REQUEST['act']) {
		case 'del':
			$_SESSION['layers_nodraw']=array_diff($_SESSION['layers_nodraw'], array($_REQUEST['l']));
			break;
		case 'add':
			if(!is_array($_SESSION['layers_nodraw'])) $_SESSION['layers_nodraw']=array();
			$_SESSION['layers_nodraw'][]=$_REQUEST['l'];
			break;
		default:
			break;
	}
}
if(array_key_exists('layers_nodraw',$_SESSION)) {
	if(is_array($_SESSION['layers_nodraw']) && count($_SESSION['layers_nodraw'])>0) {
		echo '<table border="1">'."\r\n";
		foreach($_SESSION['layers_nodraw'] as $layer) {
			echo '	<tr>'."\r\n";
			echo '		<td>'.$layer.'</td>'."\r\n";
			echo '		<td><a href="?act=del&l='.$layer.'">Delete</a></td>'."\r\n";
			echo '	</tr>'."\r\n";
		}
		echo '</table>'."\r\n";
	}
}
echo '<form method="get">'."\r\n";
echo '<input type="hidden" name="act" value="add"/>'."\r\n";
echo '<input type="text" name="l"/><br/>'."\r\n";
echo '<input type="reset" value="Reset"/>'."\r\n";
echo '<input type="submit" value="Submit"/>'."\r\n";
echo '</form>'."\r\n";
//var_dump($_SESSION['layers_nodraw']);
?>