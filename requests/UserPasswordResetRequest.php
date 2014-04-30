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
class UserPasswordResetRequest extends Base
{
	public $email;
	public $validate = array(
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
		
		$db = &$this->getDB();

		$arrList = $db->QueryArray('users', array('email'), "email = '{$this->email}'");
		if($arrList == false || count($arrList) == 0)
		{
			$this->errors['email_not_found'] = "Email address was not found in database!";
		}
		
		return (count($this->errors) == 0);
	}
	
	
	
	
	
}
?>