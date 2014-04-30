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
	header('Location: ' . $GLOBALS['base_url'] . 'index.php?src=' . $_SERVER['REQUEST_URI']);
}


/**
 * DEPRECATED
 * @author Arthur Cavallari
 * @version 1.0
 * @created 26-May-2013 7:34:56 PM
 */
class Utils
{

	/**
	 * 
	 * @param request
	 */
	public function dispatchEmail(Request $request)
	{
	}

	/**
	 * 
	 * @param request
	 */
	public function generateReport(Request $request)
	{
	}

}
?>