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
class UploadImageRequest extends Base
{
	public $lab_id;
	public $sub_id;
	public $widget_id;
	public $image_data;
	public $image_name;
	public $imageChecksum;
	
	/**
	 * Validate(): Returns true if all fields are valid, false if anything has been set on $errors
	 * (non-PHPdoc)
	 * @see Base::validate()
	 */
	public function validate()
	{		
		if($this->IsNullOrEmptyString($this->lab_id)) 
		{
			$this->errors['lab_id_empty'] = "Lab id is null or empty!";
		}
		if($this->IsNullOrEmptyString($this->widget_id)) 
		{
			$this->errors['widget_id_empty'] = "Widget id is empty!";
		}
		if($this->IsNullOrEmptyString($this->image_data)) 
		{
			$this->errors['image_data_empty'] = "Image data is empty!";
		}
		if($this->IsNullOrEmptyString($this->image_name)) 
		{
			$this->errors['image_name_empty'] = "Image name is empty!";
		}
		
		
		
		$db = &$this->getDB();

		$arrList = $db->QueryArray('laboratories', array('id'), "id = '{$this->lab_id}'");
		if($arrList == false || count($arrList) == 0)
		{
			$this->errors['lab_id_invalid'] = "Lab id not found in database!";
		}
		
		$arrList = $db->QueryArray('laboratory_fields', array('id'), "w_id = '{$this->widget_id}'");
		if($arrList == false || count($arrList) == 0)
		{
			$this->errors['widget_id_invalid'] = "Widget id not found in database!";
		}
		

		if(is_numeric($this->sub_id) && $this->sub_id > -1)
		{
			$arrList = $db->QueryArray('submissions', array('id'), "id = '{$this->sub_id}'");
			if($arrList == false || count($arrList) == 0)
			{
				$this->errors['submission_id_invalid'] = "Submission id not found in database!";
			}			
		}
		
		return (count($this->errors) == 0);
	}
	
	
	
	
	
}
?>