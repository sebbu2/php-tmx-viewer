<?php
ob_start();
$files=array();
$files=array_merge($files, glob('../../tmw/*.tmx'));
$files=array_merge($files, glob('../../tmx/*.tmx'));
$files=array_merge($files, glob('../../maps/*.tmx'));
$files=array_merge($files, glob('../tmw/*.tmx'));
$files=array_merge($files, glob('../tmx/*.tmx'));
$files=array_merge($files, glob('../maps/*.tmx'));
$files=array_merge($files, glob('../*.tmx'));
$files=array_merge($files, glob('../maps/Simple-Tiled-Implementation/tests/*.tmx'));
$files=array_merge($files, glob('tmw/*.tmx'));
$files=array_merge($files, glob('tmx/*.tmx'));
$files=array_merge($files, glob('maps/*.tmx'));
//$files=array_merge($files, glob('*/*.tmx'));
$files=array_merge($files, glob('*.tmx'));
$files=array_values(array_unique($files));
//echo '<input type="hidden" name="choice" value="list"/>'."\r\n";
//echo '<select name="list" id="map">'."\r\n";
//echo '<option value=""></option>'."\r\n";
foreach($files as $file) {
	echo '<option value="'.$file.'"';
	if(array_key_exists('list',$_REQUEST)&&$_REQUEST['list']==$file) echo ' selected="selected"';
	echo '>'.$file.'</option>'."\r\n";
}
$data=ob_get_flush();
file_put_contents('LOCAL_var.php', '<?php'."\n".'$files='.var_export($files, true)."\n".'?>');
file_put_contents('LOCAL.htm',$data);
?>
