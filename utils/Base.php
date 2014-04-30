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
	require_once ('../db/DatabaseHandler.php');
	require_once ('ValidationUtils.php');
	header('Location: ' . $GLOBALS['base_url'] . 'index.php?src=' . $_SERVER['REQUEST_URI']);
}

/**
 * Base class for most objects used in the MyDLA, contains a few useful functions for validation and custom deserialization
 * @author Arthur Cavallari
 *
 */
abstract class Base extends ValidationUtils
{
	public $errors = array();
	private $db;
	
	function __construct()
	{
		//Constructor is only called implicitly if the child class has NOT implemented a constructor
		// else: parent::__construct();
		
		$this->db = new DatabaseHandler();
		$this->db->initialize();
		//var_dump($this->db);
	}
	
	function __destruct()
	{
		//echo "destruct";
		unset($this->db);
	}
	
	
	/**
	 * Validate(): Returns true if all fields are valid, false if anything has been set on $errors
	 * (non-PHPdoc)
	 * @see Base::validate()
	 */
	public abstract function validate();	
	
	public function set($data) 
	{
		unset($this->errors['fields']);
        foreach ($data as $key => $value) 
		{			
			if (is_array($value)) 
			{
				$className = get_class($this);
				$sub = new $className;
				$sub->set($value);
				$value = $sub;
			}
			if(strpos($key, "dateTime") !== false)
			{
				if($value != "0000-00-00 00:00:00")
				{
					$this->{$key} = $value;
				}
				else
				{
					$this->{$key} = NULL;
				}
			}
			elseif(property_exists(get_class($this),$key))
			{
				$this->{$key} = $value;
			}
			else
			{
				//$msg = 'Field [' . $key . '] = ' . $value . ' is not part of this class.';
				//$val = var_export($value, true);
				$msg = 'Field [' . $key . '] is not part of the ' . get_class($this) . ' class.';
				$this->errors['fields'][] = $msg;
			}
        }
		
		return $this->validate();
    }
	
	/* 
	   Regarding Errors:
	 	Should I return a JSON request of errors, 
		wrapped in a "error" class which will contain 
		the source (UserData) and errors[] ?
	*/
	
	// Returns array of errors
	public function getErrors() 
	{
    	return $this->errors;
  	}
	
	public function getCountErrors() 
	{
		if(is_array($this->errors))
	    {
			return count($this->errors);
		}
		else
		{
			return 0;
		}
  	}
	
	// Prints errors to user
	public function printErrors() 
	{
		echo get_class($this) . " validation failed:" . PHP_EOL;
		foreach ($this->errors as $key => $value) 
		{
			echo "- $value" . PHP_EOL;
		}
  	}
		
	public function __toString() 
	{
		ob_start();
		print_r($this);
		$res = ob_get_clean();
		ob_flush();
		$res = str_replace("\n", PHP_EOL, $res);
		return (string)$res;
    }
	
	
	protected function &getDB()
	{
		return $this->db;	
	}
	
	
	/**
	 * @return
	 *		1 - Session id matches user id
	 *      2 - Session id does not match user id (Also sets error array)
	 *	 	3 - Session id not found (Also sets error array)
	 */
	public function valid_session_id($session_id, $user_id)
	{
		if(count($this->errors) == 0)
		{
			$db = &$this->getDB();
			
			$sessionCheck = $db->QueryArray('user_sessions', array('user_id'), "session_id = '{$session_id}'");
						
			if($sessionCheck != false)
			{
				if($sessionCheck[0]['user_id'] == $user_id)
				{
					return 1;					
				}
				else
				{
					$this->errors['sessionuserid_mismatch'] = "Session id does not match with given user id!" ;
					return 2;
				}
			}
			else
			{
				$this->errors['sessionid_invalid'] = "Session id is invalid!" ;
				return 3;
			}			
		}
	}
	
	
	public function getUserIDFromSession($session_id = NULL)
	{
		$userID = -1;
		if($session_id == NULL)
		{
			return $userID;
		}
		
		$db = &$this->getDB();		
		$arrList = $db->QueryArray('user_sessions', array('user_id'), "session_id = '{$session_id}'");
		if($arrList != false)
		{
			$userID = $arrList[0]['user_id'];
		}
		return $userID;
	}
		
	/*
	protected function testValidators()
	{
		echo $this->valid_date('2013-08-11 05:34:29') . PHP_EOL;
		echo '2013-08-11 05:34:29' . PHP_EOL;
		echo $this->valid_boolean(true) . PHP_EOL;
		echo $this->valid_boolean(false) . PHP_EOL;
		echo $this->valid_int(0) . PHP_EOL;
		echo $this->valid_int(1) . PHP_EOL;
		echo $this->valid_int(-1) . PHP_EOL;
		echo $this->valid_int(100, 0) . PHP_EOL;
		echo $this->valid_int(100, NULL, 99) . PHP_EOL;
		echo $this->valid_int(10, 11) . PHP_EOL;
		echo $this->valid_int(50, 0, 100) . PHP_EOL;
		echo $this->valid_int("50", 0, 100) . PHP_EOL;
		echo $this->valid_int("integer", 0, 100) . PHP_EOL;
		echo $this->valid_float("50.1", 0, 100) . PHP_EOL;
		echo $this->valid_float("50", 0, 100) . PHP_EOL;
		echo $this->valid_float("101.1", 0, 101.1) . PHP_EOL;	
	}
	*/
}
?>