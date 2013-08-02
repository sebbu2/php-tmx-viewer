<?php
session_start();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>PHP TMX Map Viewer<?php if(array_key_exists('ref',$_REQUEST)) print(' - '.$_REQUEST['ref']); ?></title>
</head>
<body>

<?php
$map_url=array(
	'tmw'=>'https://api.github.com/repos/themanaworld/tmwa-client-data/contents/maps',
	'evol'=>'https://api.github.com/repos/EvolOnline/clientdata-beta/contents/maps',
	'tales'=>'https://api.github.com/repos/tales/sourceoftales/contents/maps',
	'stendhal'=>'http://arianne.cvs.sourceforge.net/viewvc/arianne/stendhal/tiled/',
);
$github=array('tmw','evol','tales');
$viewvc=array('stendhal');
if(!array_key_exists('ref',$_REQUEST)) {
?><form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
<select name="ref">
<option value="tmw">The Mana World</option>
<option value="evol">Evol Online</option>
<option value="tales">Source of Tales</option>
<option value="stendhal">Stendhal</option>
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
//$data=file_get_contents($map_url[$_REQUEST['ref']]);
$opts=array(
	'http'=>array(
		'user_agent'=>'Mozilla/5.0 (Windows NT 5.1; rv:26.0) Gecko/20100101 Firefox/26.0',
	),
	'ssl'=>array(
		'allow_self_signed'=>true,
	),
);
$ctx = stream_context_create($opts);
$data=file_get_contents($map_url[$_REQUEST['ref']],false,$ctx);
if(in_array($_REQUEST['ref'], $github)) {
	$ar=json_decode($data, true);
	$dirs=array();
	foreach($ar as $entry) {
		//var_dump($entry);die();
		if(substr($entry['name'],-4)=='.tmx') {
			echo '<option value="'.$entry['path'].'">'.$entry['path'].'</option>'."\r\n";
		}
		else if($entry['type']=='dir') {
			$dirs[]=$entry['url'];
		}
	}
	foreach($dirs as $dir) {
		$data=file_get_contents($dir,false,$ctx);
		$ar=json_decode($data, true);
		foreach($ar as $entry) {
			//var_dump($entry);die();
			if(substr($entry['name'],-4)=='.tmx') {
				echo '<option value="'.$entry['path'].'">'.$entry['path'].'</option>'."\r\n";
			}
		}
	}
}
elseif(in_array($_REQUEST['ref'], $viewvc)) {
	if(!file_exists($_REQUEST['ref'].'.htm') || (array_key_exists('force',$_REQUEST)&&$_REQUEST['force']==='1')) {
	ob_start();
	function filter_dir_1($dir) {
		return ($dir==='world');
	}
	function filter_dir_2($dir) {
		return ($dir==='interiors' || substr($dir,0,6)=='Level ');
	}
	$dirs=array();
	$files=array();
	$res=preg_match_all('#<a name="([^"]+)" href="([^"]+)" title="View directory contents">#', $data, $subdirs, PREG_SET_ORDER);
	foreach($subdirs as $dir) {
		if(filter_dir_1($dir[1])) {
			$data2=file_get_contents($map_url[$_REQUEST['ref']].$dir[1].'/');
			$res2=preg_match_all('#<a name="([^"]+)" href="([^"]+)" title="View file revision log">#', $data2, $files2, PREG_SET_ORDER);
			foreach($files2 as $file) {
				if(substr($file[1], -4)==='.tmx') {
					$files[]=$dir[1].'/'.$file[1];
				}
			}
		}
		elseif(filter_dir_2($dir[1])) {
			$data2=file_get_contents($map_url[$_REQUEST['ref']].$dir[1].'/');
			$res=preg_match_all('#<a name="([^"]+)" href="([^"]+)" title="View directory contents">#', $data2, $subdirs2, PREG_SET_ORDER);
			foreach($subdirs2 as $dir2) {
				$data3=file_get_contents($map_url[$_REQUEST['ref']].$dir[1].'/'.$dir2[1].'/');
				$res=preg_match_all('#<a name="([^"]+)" href="([^"]+)" title="View file revision log">#', $data3, $subdirs3, PREG_SET_ORDER);
				foreach($subdirs3 as $file) {
					if(substr($file[1], -4)==='.tmx') {
						$files[]=$dir[1].'/'.$dir2[1].'/'.$file[1];
					}
				}
			}
		}
	}
	$res=preg_match_all('#<a name="([^"]+)" href="([^"]+)" title="View file revision log">#', $data, $dfiles, PREG_SET_ORDER);
	foreach($dfiles as $file) {
		if(substr($file[1], -4)==='.tmx') {
			$files[]=$file[1];
		}
	}
	foreach($files as $file) {
		echo '<option value="'.$file.'">'.$file.'</option>'."\r\n";
	}
	$data=ob_get_contents();
	ob_end_clean();
	file_put_content($_REQUEST['ref'].'.htm', $data);
	}
	else {
		require($_REQUEST['ref'].'.htm');
	}
}
else {
	echo '<option value="">Work in Progress</option>'."\r\n";
}
?></select>
<input type="submit" value="Valider"/>
</form><?php
}
?>

</body>
</html>