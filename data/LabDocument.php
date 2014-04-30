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
class LabDocument extends Base
{
	/**
	 * LabMetaData object
	 * @var LabMetaData
	 */
	public $MetaData;
	/**
	 * Array of WidgetData objects
	 * @var array
	 */
	public $WidgetData;
	/**
	 * Array of widgets ids to be deleted
	 * @var array
	 */
	public $DeletedWidgets;

	
	private $session_id;
	private $laboratory_owner_id; 

	/**
	 * 
	 * @param string $session_id
	 */
	function __construct($session_id)
	{
		parent::__construct();
		$this->session_id = $session_id;	
		$this->DeletedWidgets = array();
		
		/*$this->SubmissionMetaData = NULL;
		$this->SubmissionWidgetData = array();
		*/
		//$this->laboratory_owner_id = $laboratory_owner_id;			
	}
	
	/**
	 * Updates all the fields of this object from a given associative array
	 * (non-PHPdoc)
	 * @see Base::set()
	 */
	public function set($data) 
	{
		$db = &$this->getDB();
		unset($this->errors['fields']);
        foreach ($data as $key => $value) 
		{			
			
			if ($key == "MetaData") 
			{
				$sub = new LabMetaData($this->session_id, FALSE);
				$sub->set($value);
				$this->MetaData = $sub;		
				if(count($this->DeletedWidgets) != 0)
				{
					$widgetids = array();
					foreach($this->DeletedWidgets as &$val)
					{
						// Validate widget ids given
						$labIDFromWidgetID = explode('-', $val);
						$labIDFromWidgetID = $labIDFromWidgetID[0];
						if($labIDFromWidgetID != $this->MetaData->id)
						{
							$this->errors['deleted_widgetid_invalid'] = 'Some of the widgets id given to be deleted were invalid, they did not match the lab id.';
						}
						else
						{
							$widgetids[] = $val;
						}
					}
					$this->DeletedWidgets = $widgetids;	
				}
			}
			elseif($key == "WidgetData")
			{
				$arr = $value;
				$count = 0;
				if(is_array($arr))
				{
					foreach($arr as &$val)
					{
						$this->WidgetData[$count] = new WidgetData($this->session_id);
						$this->WidgetData[$count]->set($val);
						++$count;
						
					}
				}
				//$this->WidgetData[] = new WidgetData($this->session_id);
				
			}
			elseif($key == "DeletedWidgets")
			{			
				if($this->MetaData == NULL)	
				{
					$this->DeletedWidgets = $value;
					// we check this later if the lab hasn't been assigned yet
				}
				else
				{
					$widgetids = array();
					foreach($value as &$val)
					{
						// Validate widget ids given
						$labIDFromWidgetID = explode('-', $val);
						$labIDFromWidgetID = $labIDFromWidgetID[0];
						if($labIDFromWidgetID != $this->MetaData->id)
						{
							$this->errors['deleted_widgetid_invalid'] = 'Some of the widgets id given to be deleted were invalid, they did not match the lab id.';
						}
						else
						{
							$widgetids[] = $val;
						}
					}
					$this->DeletedWidgets = $widgetids;	
				}			
			}
			
			/*elseif ($key == "SubmissionMetaData") 
			{
				$sub = new SubmissionMetaData();
				$sub->set($value);
				$this->SubmissionMetaData = $sub;		
			}
			elseif($key == "SubmissionWidgetData")
			{
				$arr = $value;
				$count = 0;
				if(is_array($arr))
				{
					foreach($arr as &$val)
					{
						$this->SubmissionWidgetData[$count] = new SubmissionWidgetData();
						$this->SubmissionWidgetData[$count]->set($val);
						++$count;
						
					}
				}
			}*/
			else
			{
				//$msg = 'Field [' . $key . '] = ' . $value . ' is not part of this class.';
				//$val = var_export($value, true);
				$msg = 'Field [' . $key . '] is not part of the ' . get_class($this) . ' class.';
				$this->errors['fields'][] = $msg;
			}
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