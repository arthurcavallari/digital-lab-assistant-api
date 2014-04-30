<?php
// REF: http://www.php.net/manual/en/jsonserializable.jsonserialize.php
// class name implements JsonSerializable 

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
	header('Location: ' . $GLOBALS['base_url'] . 'index.php?src=' . $_SERVER['REQUEST_URI']);
}


/**
 * DEPRECATED
 * @author Arthur Cavallari
 * @version 1.0
 * @created 26-May-2013 7:34:55 PM
 */
class JsonHandler 
{
 
	protected static $_messages = array(
		JSON_ERROR_NONE => 'No error has occurred',
		JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
		JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
		JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
		JSON_ERROR_SYNTAX => 'Syntax error',
		JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
	);
	 
	public static function encode($value, $options = 0) 
	{
		$result = json_encode($value, $options);
		if($result)
		{
			return $result;
		}
		throw new RuntimeException(static::$_messages[json_last_error()]);
	}
	 
	public static function decode($json, $assoc = false) 
	{
		$result = json_decode($json, $assoc);
		if($result)
		{
			return $result;
		}
		throw new RuntimeException(static::$_messages[json_last_error()]);
	}
 
} // end JsonHandler
    
?>