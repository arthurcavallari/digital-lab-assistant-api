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
	// These require_once are here for Dreamweaver code hinting to work.. 
	header('Location: ' . $GLOBALS['base_url'] . 'index.php?src=' . $_SERVER['REQUEST_URI']);
}

/**
 * @author Arthur Cavallari
 * @version 1.0
 * @created 26-May-2013 7:34:56 PM
 */
class FailedRequest
{
	public $errors = array();
	
	/**
	 * Initializes the FailedRequest object, if a parameter is given, 
	 * it's expected to be an associative array of errors, which will then be merged to the local array of errors
	 */
	function __construct()
	{
		$var_c = func_num_args();
		if($var_c == 1)
		{
			if(is_array(func_get_arg(0)))
			{
				$this->errors = array_merge($this->errors, func_get_arg(0));
			}
		}
	}
	
	/**
	 * Displays all the errors to screen - prints the errors array
	 * @return string
	 */
	public function __toString() 
	{
		ob_start();
		print_r($this);
		$res = ob_get_clean();
		ob_flush();
		$res = str_replace("\n", PHP_EOL, $res);
		return (string)$res;
    }
	
    /**
     * Adds a string to the error list
     * @param string $err
     */
	public function addReason($err)
	{
		$this->errors['err' . count($this->errors)] = $err;
	}
	
	/**
	 * Adds an error to the error array
	 * @param string $key Error code
	 * @param string $value Description
	 */
	public function addReasonKey($key, $value)
	{
		$this->errors[$key] = $value;
	}
	
	/**
	 * Merges an already existing error array with the local error array
	 * @param array $err Associative array of errors
	 */
	public function addReasonArray($err)
	{
		if(is_array($err))
		{
			$this->errors = array_merge($this->errors, $err);
		}
		elseif(strlen(trim($err)) > 1)
		{
			$this->errors[] = $err;
		}
	}
	
	
	
}
?>