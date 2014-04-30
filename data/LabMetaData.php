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
	require_once ('../utils/Base.php');
	require_once ('../db/DatabaseHandler.php');
	header('Location: ' . $GLOBALS['base_url'] . 'index.php?src=' . $_SERVER['REQUEST_URI']);
}


/**
 * @author Arthur Cavallari
 * @version 1.0
 * @created 26-May-2013 7:34:55 PM
 */
class LabMetaData extends Base
{

	public $id;
	public $owner_user_id;
	public $title;
	public $description;
	public $authorFirstName;
	public $authorLastName;
	public $topic;
	public $area;
	public $organisation;
	public $pages;
	public $lastWidgetCounter;
	public $isPublished;
	public $dateTimeCreated;
	public $dateTimeLastUpdated;
	public $dateTimePublished;
	
	// Not on the laboratories table	
	public $LabAuthorFavourited;
	public $UserFlaggedLab;
	public $UserFlaggedNotes;
	public $UserHasSubmission;
	public $UserSubmissionSubmitted;
	
	private $session_id;
	private $creatingLab;

	/**
	 * 
	 * @param string $session_id
	 * @param bool $creatingLab
	 */
	function __construct($session_id, $creatingLab)
	{
		parent::__construct();
		$this->session_id = $session_id;	
		$this->creatingLab = $creatingLab;			
	}
	
	
	/**
	 * Validate(): Returns true if all fields are valid, false if anything has been set on $errors
	 * (non-PHPdoc)
	 * @see Base::validate()
	 */
	public function validate()
	{		
		$db = &$this->getDB();
		$userID = $this->getUserIDFromSession($this->session_id);
		if($userID == -1)
		{
			$this->errors['sessionid_invalid'] = "Session id is invalid!";
		}
		elseif($userID != $this->owner_user_id)
		{
			// if the user requesting this lab is not the author
			// we check if the lab is published
			// if the lab is NOT published, we return an error as it's not viewable yet			
			if($this->creatingLab == TRUE)
			{
				$this->errors['owner_user_id_invalid'] = "You're trying to create a lab as someone else, naughty naughty!";
			}
			else
			{
				$arrList = $db->QueryArray('laboratories', '*', "id = '{$this->id}'");
			
				if($arrList != false)
				{
					$lab = $arrList[0]; 
					if($lab['isPublished'] != 1 && $lab['isPublished'] != '1')
					{
						// Means it's not published yet
						$this->errors['lab_not_published_yet'] = "This laboratory has not been published yet!";
					}
				}
			}
		}
				
		return (count($this->errors) == 0);
	}


}
?>