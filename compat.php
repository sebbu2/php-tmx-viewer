<?php
$version=array();
// PHP_VERSION_ID est disponible depuis PHP 5.2.7,
// si votre version est antérieure, émulez-le.
if (!defined('PHP_VERSION_ID')) {
   $version = explode('.',PHP_VERSION);

   define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

// PHP_VERSION_ID est défini comme un nombre : plus il est grand, plus
// la version de PHP est récente. Il est défini comme illustré dans
// le code ci-dessous :
//
// $version_id = $major_version * 10000 + $minor_version * 100 + $release_version;
//
// Maintenant, avec PHP_VERSION_ID, il est possible de vérifier la disponibilité
// de fonctionnalités de PHP, sans passer par version_compare().
//
// Par exemple, on peut définir les constantes PHP_VERSION_* qui n'étaient pas
// disponibles avant 5.2.7

if (PHP_VERSION_ID < 50207) {
	define('PHP_MAJOR_VERSION',   $version[0]);
	define('PHP_MINOR_VERSION',   $version[1]);
	define('PHP_RELEASE_VERSION', $version[2]);

	// etc.

}

//ajouts
if(PHP_VERSION_ID >= 50400) {
	require_once('compat_54.php');
}
else {
	require_once('compat_53.php');
}
if(!function_exists('gzdecode')) {
	function gzdecode($data)
	{
	return gzinflate(substr($data,10,-8));
	}
}
?>