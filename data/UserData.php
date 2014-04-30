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
	require_once ('../utils/Base.php');
	require_once ('../db/DatabaseHandler.php');
	header('Location: ' . $GLOBALS['base_url'] . 'index.php?src=' . $_SERVER['REQUEST_URI']);
}

/**
 * @author Arthur Cavallari
 * @version 1.0
 * @created 26-May-2013 7:34:56 PM
 */
class UserData extends Base
{
	public $id;
	public $username;
	public $password;
	public $firstName;
	public $lastName;
	public $organisation;
	public $email;
	public $country;
	public $isAdmin;
	public $dateTimeRegistered;
	public $dateTimeLastUpdated;
	
	private $session_id;
	
	/**
	 * Initializes the object - if 1 argument is provided, it is expected to be a session id
	 */
	function __construct()
	{
		parent::__construct();
		$this->session_id == NULL;
		$var_c = func_num_args();
		if($var_c == 1)
		{
			if(func_get_arg(0))
			{
				$this->session_id = func_get_arg(0);
			}
		}		
	}	

	/**
	 * Validate(): Returns true if all fields are valid, false if anything has been set on $errors
	 * (non-PHPdoc)
	 * @see Base::validate()
	 */
	public function validate()
	{
		if(!$this->valid_email($this->email)) 
		{
			$this->errors['email'] = "Email address is invalid!";
		}
		
		$db = &$this->getDB();

		$arrList = $db->QueryArray('users', '*', "email = '{$this->email}'");
		if($arrList != false && count($arrList) > 0)
		{
			if($arrList[0]['id'] != $this->id)
			{
				$this->errors['email_exists'] = "Email address already exists in database!";
			}
		}
		
		// Disabled password validating as it's done elsewhere, and by now it's md5 hashed
		/*if(strlen($this->password) < 6)
		{
			$this->errors['password_too_short'] = "Password must have at least 6 characters, current password is too short at " . strlen($this->password) . " characters.";
		}
		if(strlen($this->password) > 30)
		{
			$this->errors['password_too_long'] = "Password must have at most 30 characters, current password is too long at " . strlen($this->password) . " characters.";
		}*/
		if(!$this->valid_int($this->id)) 
		{
			$this->errors['id'] = "User ID is invalid!";
		}
		
		if($this->session_id !== NULL)
		{
			$requestUserID = parent::getUserIDFromSession($this->session_id);
			if($requestUserID != $this->id)
			{
				$this->errors['sessionid_invalid'] = "Session id is invalid!";
			}
		}
		return (count($this->errors) == 0);
	}
	
}


?>