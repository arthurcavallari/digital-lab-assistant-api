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
	
	require_once ('../data/UserData.php');
	require_once ('../data/LabMetaData.php');
	require_once ('../data/SubmissionMetaData.php');
	header('Location: ' . $GLOBALS['base_url'] . 'index.php?src=' . $_SERVER['REQUEST_URI']);
	
	
	
}

/**
 * DEPRECATED
 * @author Arthur Cavallari
 * @version 1.0
 * @created 26-May-2013 7:34:56 PM
 */
class HandshakeData
{
	public $labFields;
	public $submissionFields;
	public $userFields;
	public $validators;
	
	/**
	 * 
	 */
	function __construct()
	{
		$this->labFields = array();
		$this->submissionFields = array();
		$this->userFields = array();
		$this->validators = array();
		
		$r = new ReflectionClass('LabMetaData');
		$props = $r->getProperties(ReflectionProperty::IS_PUBLIC);
		foreach ($props as $prop)
		{
			$this->labFields[] = $prop->getName();
		}		
		unset($r);
		
		$r = new ReflectionClass('SubmissionMetaData');
		$props = $r->getProperties(ReflectionProperty::IS_PUBLIC);
		foreach ($props as $prop)
		{
			$this->submissionFields[] = $prop->getName();
		}	
		unset($r);
		
		$r = new ReflectionClass('UserData');
		$props = $r->getProperties(ReflectionProperty::IS_PUBLIC);
		foreach ($props as $prop) 
		{
			$this->userFields[] = $prop->getName();
		}
		unset($r);
		
		$this->getValidationRules('./data/');
		$this->getValidationRules('./requests/');
	}
	
	/**
	 * 
	 * @param unknown $dirName
	 */
	private function getValidationRules($dirName)
	{
		$dir = new RegexIterator( new DirectoryIterator($dirName), "/\\.php\$/i");
		foreach ($dir as $fileinfo) 
		{
			if (!$fileinfo->isDot()) 
			{				
				$arr = explode(".php", $fileinfo->getFilename());
				//echo $arr[0] . PHP_EOL;
				$obj = new ReflectionClass($arr[0]);
				if($obj->hasProperty("validate"))
				{
					$obj = new $arr[0];
					$this->validators[$arr[0]] = $obj->validate;
				}
			}
		}	
	}
	
}
?>