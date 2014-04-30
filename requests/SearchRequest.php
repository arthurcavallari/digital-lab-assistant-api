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
class SearchRequest extends Base
{
	public $Query;
	public $Page;
	
	/**
	 * Validate(): Returns true if all fields are valid, false if anything has been set on $errors
	 * (non-PHPdoc)
	 * @see Base::validate()
	 */
	public function validate()
	{		
		if($this->IsNullOrEmptyString($this->Query))
		{
			$this->errors['empty_query'] = "Query data is null or empty!";
		}
		
		if(!$this->valid_int($this->Page, 1, 99))
		{
			$this->errors['page_invalid'] = "Invalid page number supplied!" . $this->Page;
		}
		
		return (count($this->errors) == 0);
	}	
}

/**
 * 
 * @author Arthur Cavallari
 *
 */
class SearchResponse extends Base
{
	public $ResultsCount; // count($ResultsCount)
	public $SearchResults; // array of LabMetaData
	
	/**
	 * 
	 * @param array $results Array of LabMetaData
	 */
	public function setResults($results)
	{
		if(is_array($results))
		{
			foreach($results as &$labs)
			{
				foreach($labs as $key => &$value)
				{
					if(strpos($key, "dateTime") !== false)
					{						
						if($value == "0000-00-00 00:00:00")
						{
							$labs[$key] = NULL;
						}
					}
				}
				$this->SearchResults = $results;
			}
		}
			
	}
	/**
	 * Validate(): Returns true if all fields are valid, false if anything has been set on $errors
	 * (non-PHPdoc)
	 * @see Base::validate()
	 */
	public function validate()
	{				
		return (count($this->errors) == 0);
	}	
}

/**
 * DEPRECATED
 * @author Arthur Cavallari
 *
 */
class LabWidgets extends Base
{
	public $ResultsCount; // count($ResultsCount)
	public $Widgets; // array of WidgetaData
	
	
	/**
	 * Validate(): Returns true if all fields are valid, false if anything has been set on $errors
	 * (non-PHPdoc)
	 * @see Base::validate()
	 */
	public function validate()
	{				
		return (count($this->errors) == 0);
	}	
}
?>