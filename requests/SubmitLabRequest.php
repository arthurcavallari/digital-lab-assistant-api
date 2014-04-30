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
class SubmitLabRequest extends Base
{
	public $lab_id;
	public $user_id;
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
				
				// AND id NOT IN (select laboratory_id from deleted_laboratories) AND owner_user_id NOT IN (select user_id from banned_users)
				if($arrList['owner_user_id'] == $this->user_id)
				{
					$labCheck = $this->getDB()->QueryArray('deleted_laboratories', 'laboratory_id', "laboratory_id = '{$this->lab_id}'");
					if($labCheck != false && count($labCheck) > 0)
					{
						$this->errors['lab_deleted'] = "Cannot publish a lab that has already been deleted!"; //derp
					}
					if($arrList['isPublished'] == 1)
					{
						$this->errors['lab_already_published'] = "Cannot publish a lab that has already been published!";
					}
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