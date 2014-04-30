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
class FlagLabRequest extends Base
{	
	public $user_id;
	public $lab_id;
	public $notes;
	private $session_id;
	
	/**
	 * 
	 * @param string $session_id
	 */
	function __construct($session_id)
	{
		parent::__construct();
		$this->session_id = $session_id;			
	}
	
	/**
	 * Validate(): Returns true if all fields are valid, false if anything has been set on $errors
	 * (non-PHPdoc)
	 * @see Base::validate()
	 */
	public function validate()
	{
		//			$result['error'] = array( "sessionuserid_mismatch" => "Session id does not match with given user id!" );
		if(!isset($this->session_id) || $this->IsNullOrEmptyString($this->session_id))
		{
			$this->errors['sessionid_empty'] = "Session id is null or empty!";
		}
		if(!$this->valid_int($this->lab_id, 0, NULL))
		{
			$this->errors['labid_invalid'] = "Invalid lab id supplied!";
		}
		if($this->valid_session_id($this->session_id, $this->user_id) == 1)
		{
			$arrList = $this->getDB()->QueryArray('laboratories', '*', "id = '{$this->lab_id}'");
	
			if($arrList == false)
			{
				$this->errors['labid_notfound'] = "A lab with that id could not be found!";
			}
			elseif(count($arrList) == 1)
			{

				$arrList = $arrList[0];
				if($arrList['isPublished'] == 0 && $arrList['owner_user_id'] != $this->user_id)
				{
					$this->errors['lab_unpublished'] = "Unpublished labs are only available to the owner!";
				}
				
				/*else
				{
					// isPublished == 0 && owner_id == user_id
					// isPublished == 1
					// return lab
				}*/
			}
		}		
		return (count($this->errors) == 0);
	}
	
	
	
	
	
}
?>