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
class UserFavouriteRequest extends Base
{
	public $user_id;
	public $favourited_user_id;
	public $favourited;
	
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
		$this->valid_session_id($this->session_id, $this->user_id);
		
		if($this->favourited != 1 && $this->favourited != 0)
		{
			$this->errors['favourited_invalid'] = "Favourited value can only be 1 or 0!";
		}
		
		if($this->user_id === $this->favourited_user_id)
		{
			$this->errors['favouriteduser_cannot_favourite_self'] = "Favourited user id cannot be the same as user id!";
		}
		else
		{
			$db = &$this->getDB();
			
			$arrList = $db->QueryArray('users', 'id', "id = '{$this->user_id}' or id = '{$this->favourited_user_id}'");
	
			if($arrList == false)
			{
				$this->errors['userid_invalid'] = "Invalid user ids supplied!";
			}
			elseif(count($arrList) == 1)
			{
				if($arrList[0]['id'] == $this->user_id)
				{
					$this->errors['favouriteduserid_invalid'] = "Invalid user id supplied to be favourited!";
				}
				else
				{
					$this->errors['userid_invalid'] = "Invalid user id supplied as owner!";
				}
			}	
		}
		
		return (count($this->errors) == 0);
	}
	
	
	
	
	
}
?>