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
 * DEPRECATED
 * @author Arthur Cavallari
 * @version 1.0
 * @created 26-May-2013 7:34:55 PM
 */
class WidgetList extends Base
{
	public $Widgets = array();
	
	private $session_id;

	/**
	 * DEPRECATED
	 * @param unknown $session_id
	 */
	function __construct($session_id)
	{
		parent::__construct();
		$this->session_id = $session_id;			
	}
	
	/**
	 * Updates all the fields of this object from a given associative array
	 * (non-PHPdoc)
	 * @see Base::set()
	 */
	public function set($data) 
	{
		unset($this->errors['fields']);
        foreach ($data as $key => $value) 
		{			
			$sub = new WidgetData(/*$this->session_id*/);
			$sub->set($value);
			$value = $sub;
        }
		
		return $this->validate();
    }
	
	/**
	 * Validate(): Returns true if all fields are valid, false if anything has been set on $errors
	 * (non-PHPdoc)
	 * @see Base::validate()
	 */
	public function validate()
	{		/*
		$db = &$this->getDB();
		
		$arrList = $db->QueryArray('users', 'id', "id = '{$this->user_id}'");

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
		}	*/
		
		return (count($this->errors) == 0);
	}


}
?>