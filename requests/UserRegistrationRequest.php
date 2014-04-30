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
	require_once('../db/DatabaseHandler.php');
	header('Location: ' . $GLOBALS['base_url'] . 'index.php?src=' . $_SERVER['REQUEST_URI']);
}

/**
 * @author Arthur Cavallari
 * @version 1.0
 * @created 26-May-2013 7:34:56 PM
 */
class UserRegistrationRequest extends Base
{
	public $email;
	public $password;
	public $validate = array(
		'password' => array(
			'minLength' => array(
				'rule' => array('minLength', 6),
				'message' => 'Password must be at least 6 characters long.'
			),
			'maxLength' => array(
				'rule' => array('maxLength', 30),
				'message' => 'Password must be at least 30 characters long.'
			)
		),
		'email' => array(
			'validEmail' => array(
				'rule'    => array('email', true),
				'message' => 'Please supply a valid email address.'
			)
   		 )
	);
	
	/**
	 * Validate(): Returns true if all fields are valid, false if anything has been set on $errors
	 * (non-PHPdoc)
	 * @see Base::validate()
	 */
	public function validate()
	{		
		if(!$this->valid_email($this->email)) 
		{
			$this->errors['email_invalid'] = "Email address is invalid!";
		}
		
		// Password validation is now done on the client as per Quantum's suggestion of having the password never leave the client in plain text
		/*if(strlen($this->password) < 6)
		{
			$this->errors['password_too_short'] = "Password must have at least 6 characters, current password is too short at " . strlen($this->password) . " characters.";
		}
		if(strlen($this->password) > 30)
		{
			$this->errors['password_too_long'] = "Password must have at most 30 characters, current password is too long at " . strlen($this->password) . " characters.";
		}*/
		
		$db = &$this->getDB();

		$arrList = $db->QueryArray('users', array('email'), "email = '{$this->email}'");
		if($arrList != false && count($arrList) > 0)
		{
			$this->errors['email_exists'] = "Email address already exists in database!";
		}
		
		return (count($this->errors) == 0);
	}
	
	
	
	
	
}
?>