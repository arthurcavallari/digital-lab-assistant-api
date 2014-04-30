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
class WidgetData extends Base
{

	public $id;
	public $w_id;
	public $laboratory_id;
	public $fieldType;
	public $posZ;
	public $posX;
	public $posY;
	public $width;
	public $height;
	public $frameWidth;
	public $frameHeight;
	public $pageNumber;
	public $label;
	public $value;
	public $readOnly;
	public $table_id;
	public $tableCellRow;
	public $tableCellColumn;
	public $tableRowCount;
	public $tableColumnCount;
	public $timerType;
	public $timerTime;
	public $isStoppable;
	public $isPausable;
	public $imagePath;
	
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
		//$this->laboratory_owner_id = $laboratory_owner_id;			
	}
	
	/**
	 * Validate(): Returns true if all fields are valid, false if anything has been set on $errors
	 * (non-PHPdoc)
	 * @see Base::validate()
	 */
	public function validate()
	{
		$db = &$this->getDB();
		$labIDFromWidgetID = explode('-', $this->w_id);
		$labIDFromWidgetID = $labIDFromWidgetID[0];
		$laboratory_owner_id = -1;
		
		$userID = $this->getUserIDFromSession($this->session_id);
		if($userID == -1)
		{
			$this->errors['sessionid_invalid'] = "Session id is invalid!";
		}
		else
		{
			// Session id is valid		
			if($labIDFromWidgetID != $this->laboratory_id)
			{
				$this->errors['widgetid_invalid'] = "Widget id is invalid!";
			}
			else
			{
				// If widget id is valid (w_id = xx-yy AND xx == $this->laboratory_id)
				
				if(is_numeric($labIDFromWidgetID))
				{
					$arrList = $db->QueryArray('laboratories', '*', "id = '{$labIDFromWidgetID}'");
					if($arrList != false)
					{
						$lab = $arrList[0]; 
						$laboratory_owner_id = $lab['owner_user_id'];
						if($lab['isPublished'] != 1 && $lab['isPublished'] != '1' && $laboratory_owner_id != $userID)
						{
							// if the user requesting this lab is not the author
							// and the lab is not published
							// we return an error as it's not viewable yet
							$this->errors['lab_not_published_yet'] = "This laboratory has not been published yet!";
						}
					}
					else
					{
						$this->errors['labid_invalid'] = "Laboratory id is invalid!"; // a lab with the requested id does not exist
					}
				}
				else
				{
					$this->errors['labid_format_invalid'] = "Laboratory id format is invalid!"; // lab id is invalid - not numeric
				}
			}
		}
		//$this->valid_session_id($this->session_id, $this->laboratory_owner_id);
				
		return (count($this->errors) == 0);
	}


}
?>