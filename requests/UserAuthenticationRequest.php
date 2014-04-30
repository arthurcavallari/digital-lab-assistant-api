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
class UserAuthenticationRequest extends Base
{
	public $email;
	public $password; // md5
	public $validate = array(
		'email' => array(
			'notEmpty' => array(
				'rule'    => 'notEmpty',
				'message' => 'Email address cannot be blank.'
			)
   		 ),
		 'password' => array(
			'notEmpty' => array(
				'rule'    => 'notEmpty',
				'message' => 'Password cannot be blank.'
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
		if($this->IsNullOrEmptyString($this->email) == true || !$this->valid_email($this->email)) 
		{
			$this->errors['email_invalid'] = "Email address is invalid!";
		}
		if($this->IsNullOrEmptyString($this->password) == true)
		{
			$this->errors['password_invalid'] = "Password is invalid";
		}
		
			
		return (count($this->errors) == 0);
	}
	
	
	
	
	
}
?>