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
 * Validation utility class
 * @author Arthur Cavallari
 *
 */
class ValidationUtils
{	
	/**
	 * Attempts to validate a given date using a regular expression, must match: YYYY-MM-DD HH:MM:SS
	 * @param unknown $date
	 * @return boolean|number
	 */
	public function valid_date($date) 
	{
		// Example: 2013-08-11 05:34:29
		if(isset($date) == false) return false;
		if($date === NULL) return false;
		return (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/", $date));

	}
	
	/**
	 * Attempts to validate a given email address using the php validator as well as the domain address for a MX record
	 * @param string $email
	 * @return boolean|mixed
	 */
	public function valid_email($email)
	{
		if(isset($email) == false) return false;
		if($email === NULL) return false;
		$result = filter_var($email, FILTER_VALIDATE_EMAIL);
		$domain = explode("@",$email);
		if($result && !checkdnsrr(array_pop($domain),"MX"))
		{
			return false;
		}
		return $result;
	}
	
	/**
	 * Attempts to validate an integer from the given parameters using php validator
	 * @param unknown $value
	 * @param string $min_range
	 * @param string $max_range
	 * @return boolean|mixed
	 */
	public function valid_int($value, $min_range = NULL, $max_range = NULL)
	{
		if(isset($value) == false) return false;
		if($value === NULL) return false;
		
		if(filter_var($value, FILTER_VALIDATE_INT))
		{
			if($min_range !== NULL && $value < $min_range) return false;
			if($max_range !== NULL && $value > $max_range) return false;
		}
		
		// filter_var has a third argument called $options, but it doesn't work very well and it's poorly documented.. :(
		// It can be used to check for ranges, but it only seems to work with ints
		return filter_var($value, FILTER_VALIDATE_INT);
	}
	
	/**
	 * Attempts to validate a float from the given parameters using php validator
	 * @param unknown $value
	 * @param string $min_range
	 * @param string $max_range
	 * @return boolean|mixed
	 */
	public function valid_float($value, $min_range = NULL, $max_range = NULL)
	{
		if(isset($value) == false) return false;
		if($value === NULL) return false;

		
		if(filter_var($value, FILTER_VALIDATE_FLOAT))
		{
			if($min_range !== NULL && $value < $min_range) return false;
			if($max_range !== NULL && $value > $max_range) return false;
		}
		
		// filter_var has a third argument called $options, but it doesn't work very well and it's poorly documented.. :(
		return filter_var($value, FILTER_VALIDATE_FLOAT);
	}
	
	/**
	 * Attempts to validate a boolean with the given paramters
	 * @param unknown $value
	 * @return mixed
	 */
	public function valid_boolean($value)
	{
		//if(isset($value) == false) return false;
		//if($value === NULL) return false;
		return filter_var($value, FILTER_VALIDATE_BOOLEAN);
	}
	
	/**
	 * Checks if a given value is null or empty string
	 * @param unknown $str
	 * @return boolean
	 */
	public function IsNullOrEmptyString($str)
	{
    	return (!isset($str) || trim($str) === '');
	}
}
?>