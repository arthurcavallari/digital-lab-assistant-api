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
class UploadImageChecksumRequest extends Base
{
	public $lab_id;
	public $sub_id;
	public $submission_id;
	public $widget_id;
	public $image_checksum;
	public $image_name;
	
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
		if($this->IsNullOrEmptyString($this->image_checksum)) 
		{
			$this->errors['image_checksum_empty'] = "Image checksum is empty!";
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
		
		if(file_exists($GLOBALS['absolute_path'] . 'uploadedfiles/' . $this->image_name))
		{
			//$hash = md5(sha1_file($GLOBALS['absolute_path'] . 'uploadedfiles/' . $this->image_name));
			$submissionCheck = "";
			if($this->sub_id > -1)
			{
				$submissionCheck = " AND submission_id = '{$this->sub_id}'";
			}
			
			$imageCheck = $db->QueryArray('image_uploads', array('*'), "w_id = '{$this->widget_id}' $submissionCheck");
			if($imageCheck != false && count($imageCheck) > 0)
			{
				$hash = $imageCheck[0]['checksum'];
				if($hash != $this->image_checksum)
				{
					$this->errors['hash_mismatch'] = "Local hash and remote hash do not match!" . PHP_EOL . "Server: $hash" . PHP_EOL . "Client: {$this->image_checksum}";
					//$this->errors['server_hash'] = $hash;
					//$this->errors['client_hash'] = $this->image_checksum;
					//$this->errors['server_shahash'] = md5_file($GLOBALS['absolute_path'] . 'uploadedfiles/' . $this->image_name);
				}
			}
			else
			{
				$this->errors['file_not_found'] = "File not found in server storage!";	
			}
			
		}
		else
		{
			$this->errors['file_not_found'] = "File not found in server storage!";
		}
		
		return (count($this->errors) == 0);
	}
	
	
	
	
	
}
?>