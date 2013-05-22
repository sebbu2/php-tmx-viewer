<?php
function exception_handler($exception) {
	global $header;
	$header='text/plain';

	//echo "Uncaught exception: " , $exception->getMessage(), "\n";
	printf('Uncaught exception: %s on %s:%d\n',
		$exception->getMessage(),
		$exception->getFile(),
		$exception->getLine()
	);
}

set_exception_handler('exception_handler');

function myErrorHandler($errno, $errstr, $errfile, $errline)
{
	if (!(error_reporting() & $errno)) {
		// This error code is not included in error_reporting
		return;
	}

	global $header;
	$header='text/plain';

	switch ($errno) {
	case E_USER_ERROR:
		echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
		echo "  Fatal error on line $errline in file $errfile";
		echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
		echo "Aborting...<br />\n";
		exit(1);
		break;

	case E_USER_WARNING:
		echo "<b>My WARNING</b> [$errno] $errstr in $errfile:$errline<br />\n";
		break;

	case E_USER_NOTICE:
		echo "<b>My NOTICE</b> [$errno] $errstr in $errfile:$errline<br />\n";
		break;

	default:
		echo "Unknown error type: [$errno] $errstr in $errfile:$errline<br />\n";
		break;
	}

	/* Don't execute PHP internal error handler */
	return true;
}

$old_error_handler = set_error_handler("myErrorHandler");

?>