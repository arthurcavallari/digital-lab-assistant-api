<?php
if(!isset($pathCheck))
{	
	if($_SERVER['HTTP_HOST'] == "127.0.0.1")
	{
		$GLOBALS['base_url'] = "/mydla/";
	}
	else
	{
		$GLOBALS['base_url'] = "/~sentinus/";
	}
	require_once ('Enum.php');
	header('Location: ' . $GLOBALS['base_url'] . 'index.php?src=' . $_SERVER['REQUEST_URI']);
}


/**
 * All possible statuses of a Request
 * @author Arthur Cavallari
 * @version 1.0
 * @created 26-May-2013 7:36:23 PM
 */
class REQUEST_STATUS extends Enum
{
	const __default = self::_INVALID;
	
	const _INVALID = 255;
	const UNHANDLED = 1;
	const PROCESSING = 2;
	const HANDLED = 3;
	const FAILED = 4;

}
?>