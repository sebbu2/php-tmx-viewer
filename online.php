<?php
$map_url=array(
	'tmw'=>'https://api.github.com/repos/themanaworld/tmwa-client-data/contents/maps',
	'evol'=>'https://api.github.com/repos/EvolOnline/clientdata-beta/contents/maps',
	'tales'=>'https://api.github.com/repos/tales/sourceoftales/contents/maps',
);
if(!array_key_exists('ref',$_REQUEST)) {
?><form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
<select name="ref">
<option value="tmw">The Mana World</option>
<option value="evol">Evol Online</option>
<option value="tales">Source of Tales</option>
</select>
<input type="submit" value="Valider"/>
</form><?php
}
elseif(!array_key_exists('map',$_REQUEST)) {
?><form action="viewer_view.php" method="GET">
<input type="hidden" name="called_from" value="script"/>
<input type="hidden" name="choice" value="url"/>
<input type="hidden" name="ref" value="<?php echo $_REQUEST['ref']; ?>"/>
<select name="url">
<?php
$data=file_get_contents($map_url[$_REQUEST['ref']]);
$ar=json_decode($data, true);
foreach($ar as $entry) {
	//var_dump($entry);die();
	if(substr($entry['name'],-4)=='.tmx') {
		echo '<option value="'.$entry['name'].'">'.$entry['name'].'</option>'."\r\n";
	}
}
?></select>
<input type="submit" value="Valider"/>
</form><?php
}
?>