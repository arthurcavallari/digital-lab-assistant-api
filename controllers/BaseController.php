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
	require_once('../db/DatabaseHandler.php');
	header('Location: ' . $GLOBALS['base_url'] . 'index.php?src=' . $_SERVER['REQUEST_URI']);
}

/**
 * Base Controller class for all the controllers
 * @author Arthur Cavallari
 *
 */
abstract class BaseController
{
	/**
	 * Instance of a DatabaseHandler object
	 * @var unknown
	 */
	protected static $db;
	
	/**
	 * If the DatabaseHandler object hasn't been created yet..
	 * Creates a new DatabaseHandler object and calls the initializes function
	 */
	protected static function initDB()
	{
		if(self::$db == NULL)
		{
			self::$db = new DatabaseHandler();
			self::$db->initialize();
		}
	}
		
}
?>