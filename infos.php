<?php
//header('Content-type: text/plain');
//header('Content-type: text/plain'."\n");
//header('Content-type: text/plain'."\r\n");

define('DEBUG', true);

ob_start();

ini_set('error_reporting', E_ALL | E_STRICT | E_RECOVERABLE_ERROR | E_DEPRECATED | E_USER_DEPRECATED);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$files=array();
$files[]='map.php';
$files[]='tileset.php';
$files[]='tilelayer.php';
$files[]='layer.php';
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
$files[]='functions.inc.php';
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
	//echo '<pre>';var_dump($matches[1]);echo '</pre>';die();
	//var_dump($res);die();
	if($res>0) {
		//var_dump(0,$file);echo '<br/>'."\r\n";
		foreach($matches[1] as $class) {
			$classes[]=$class;
		}
		unset($class);
	}
	else {
		//var_dump(1,$file);echo '<br/>'."\r\n";
		$res=preg_match_all('/function\s+(\S+)\s*\(/', $data, $matches);
		//echo '<pre>';var_dump($matches[1]);echo '</pre>';die();
		//var_dump($res);die();
		if($res>0) {
			foreach($matches[1] as $function) {
				$functions[]=$function;
				//var_dump($function);
			}
			unset($function);
		}
	}
	unset($data);
}
unset($file,$res,$matches);

$data=ob_get_clean();
if(!empty($data)) {
	//header('Content-type: text/plain'."\r\n");
	echo '<pre>'."\r\n";
	echo $data;
	echo '</pre>'."\r\n";
	die();
}

$action='';
if(array_key_exists('action',$_GET)) $action=$_GET['action'];
$class='';
if(array_key_exists('class',$_GET)) $class=$_GET['class'];
if($action==''||$action=='list') {
	echo '<h1>Classes</h1>'."\r\n";
	echo "\r\n";
	
	natcasesort($classes);
	//var_dump($exts);
	foreach($classes as $class) {
		echo '<a href="?action=desc_class&class='.$class.'">'.$class.'</a><br/>'."\r\n";
	}

	echo '<h1>Functions</h1>'."\r\n";
	foreach($functions as $function) {
		//var_dump($function);
		$fct=new ReflectionFunction($function);
		echo $fct->name.'(';//.'<br/>'."\r\n";
		//echo $fct->export($fct->name,true).'<br/>'."\r\n";
		//var_dump($fct->getNumberOfRequiredParameters(),$fct->getNumberOfParameters());
		$prms=$fct->getParameters();
		$prms_c=count($prms);
		//foreach($prms as $prm) {
		for($i=0;$i<$prms_c;++$i) {
			$prm=&$prms[$i];
			if($prm->isOptional()) echo '[';
			if($prm->allowsNull()) echo 'NULL ';
			//if($prm->canBePassedByValue()) echo 'BYVALUE ';
			if($prm->isArray()) echo 'array ';// else echo 'string ';
			if($prm->isPassedByReference()) echo '&';
			echo '$'.$prm->name;//.' ';
			if($prm->isOptional()) {
				if($prm->isDefaultValueAvailable()) echo '='.$prm->getDefaultValue();
				echo ']';
			}
			if($i+1<$prms_c) echo ', ';
		}
		echo ')<br/>'."\r\n";
	}
	echo "\r\n";
}
elseif($action=='desc_class'&&$class!='') {
	$class='';
	if(array_key_exists('class',$_GET)) $class=$_GET['class'];
	echo '<h1>'.$class.'</h1>';
	echo "\r\n";
	echo "\r\n";
	
	$cl=new ReflectionClass($class);
	echo '<h2>'.$cl->getName().'</h2>'."\r\n";
	
	echo '<h3>Methods</h3>'."\r\n";
	//$ct=$cl->getConstructor();//NOTE : useless
	$mths=$cl->getMethods();
	$mths_c=count($mths);
	for($i=0;$i<$mths_c;++$i) {
		$mth=&$mths[$i];
		if($mth->isAbstract()) echo 'abstract ';
		//if($mth->isConstructor()) echo 'constr ';
		//if($mth->isDestructor()) echo 'destr ';
		if($mth->isFinal()) echo 'final ';
		if($mth->isPrivate()) echo 'private ';
		if($mth->isProtected()) echo 'protected ';
		if($mth->isPublic()) echo 'public ';
		if($mth->isStatic()) echo 'static ';
		echo $mth->name.'(';
		$prms=$mth->getParameters();
		$prms_c=count($prms);
		//foreach($prms as $prm) {
		for($j=0;$j<$prms_c;++$j) {
			$prm=&$prms[$j];
			if($prm->isOptional()) echo '[';
			if($prm->allowsNull()) echo 'NULL ';
			//if($prm->canBePassedByValue()) echo 'BYVALUE ';
			if($prm->isArray()) echo 'array ';// else echo 'string ';
			if($prm->isPassedByReference()) echo '&';
			echo '$'.$prm->name;//.' ';
			if($prm->isOptional()) {
				if($prm->isDefaultValueAvailable()) {
					if(is_array($prm->getDefaultValue())) {
						//var_dump($prm->getDefaultValue());die();
						echo '=array(';
						foreach($prm->getDefaultValue() as $key=>$value) {
							echo $key.' => '.$value.', ';
						}
						echo ')';
					}
					else {
						echo '='.$prm->getDefaultValue();
					}
				}
				echo ']';
			}
			if($j+1<$prms_c) echo ', ';
		}
		echo ')<br/>'."\r\n";
		//var_dump($mth);
	}
	
	echo '<h3>Properties</h3>'."\r\n";
	$props=$cl->getProperties();
	$props_c=count($props);
	for($i=0;$i<$props_c;++$i) {
		$prop=&$props[$i];
		if($prop->isPrivate()) echo 'private ';
		if($prop->isProtected()) echo 'protected ';
		if($prop->isPublic()) echo 'public ';
		if($prop->isStatic()) echo 'static ';
		echo $prop->name;
		if($prop->isPublic()&&$prop->isStatic()) echo ' = '.($prop->isDefault()?'<span style="text-color: green">':'<span style="text-color: ref">').$prop->getValue().'</span>';
		echo '<br/>'."\r\n";
	}
}
elseif($action=='desc_ext') {
	$ext='';
	if(array_key_exists('ext',$_GET)) $ext=$_GET['ext'];
	$e=new ReflectionExtension($ext);
	//var_dump($e);
	//$e->info();
	echo '<h1>'.$e->getName().'</h1>';
	if($e->getVersion()!=NULL) echo 'Version = '.var_export($e->getVersion(), true);
	echo "\r\n";
	echo "\r\n";
	
	$cls=$e->getClassNames();
	echo '<h2>Classes</h2>'."\r\n";
	foreach($cls as $cl) {
		echo '<a href="?action=desc_ext&ext='.$e->getName().'&class='.$cl.'">'.$cl.'</a><br/>'."\r\n";
	}
	echo "\r\n";
	
	$dps=$e->getDependencies();
	echo '<h2>Dependancies</h2>'."\r\n";
	echo '<pre>'.var_export($dps,true).'</pre>'."\r\n";
	
	$fcts=$e->getFunctions();
	echo '<h2>Functions</h2>'."\r\n";
	foreach($fcts as $fct) {
		echo $fct->name.'(';//.'<br/>'."\r\n";
		//echo $fct->export($fct->name,true).'<br/>'."\r\n";
		//var_dump($fct->getNumberOfRequiredParameters(),$fct->getNumberOfParameters());
		$prms=$fct->getParameters();
		$prms_c=count($prms);
		//foreach($prms as $prm) {
		for($i=0;$i<$prms_c;++$i) {
			$prm=&$prms[$i];
			if($prm->isOptional()) echo '[';
			if($prm->allowsNull()) echo 'NULL ';
			//if($prm->canBePassedByValue()) echo 'BYVALUE ';
			if($prm->isArray()) echo 'array ';// else echo 'string ';
			if($prm->isPassedByReference()) echo '&';
			echo '$'.$prm->name;//.' ';
			if($prm->isOptional()) {
				if($prm->isDefaultValueAvailable()) echo '='.$prm->getDefaultValue();
				echo ']';
			}
			if($i+1<$prms_c) echo ', ';
		}
		echo ')<br/>'."\r\n";
	}
	echo "\r\n";
	
	$cts=$e->getConstants();//NOTE : Don't sort (constants are grouped)
	echo '<h2>Constants</h2>'."\r\n";
	foreach($cts as $ct=>$vl) {
		echo $ct.' = '.var_export($vl,true).'<br/>'."\r\n";
	}
	echo "\r\n";
	
	$inis=$e->getINIEntries();
	echo '<h2>INI entries</h2>'."\r\n";
	foreach($inis as $ini=>$vl) {
		echo $ini.' = '.var_export($vl,true).'<br/>'."\r\n";
	}
	echo "\r\n";
}
?>