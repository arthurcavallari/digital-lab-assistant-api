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
	header('Location: ' . $GLOBALS['base_url'] . 'index.php?src=' . $_SERVER['REQUEST_URI']);
}


/**
 * @author Arthur Cavallari
 * @version 1.0
 * @created 26-May-2013 7:34:56 PM
 */
class SubmissionMetaData extends Base
{
	public $id;
	public $user_id;
	public $laboratory_id;
	public $authorFirstName;
	public $authorLastName;
	public $dateTimeCreated;
	public $dateTimeLastUpdated;
	public $isSubmitted;
	public $dateTimeSubmitted;
	public $dateTimeAssessed;
	/**
	 * Validate(): Returns true if all fields are valid, false if anything has been set on $errors
	 * (non-PHPdoc)
	 * @see Base::validate()
	 */
	public function validate()
	{		
		return (count($this->errors) == 0);
	}
}
?>