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
		require_once ('../utils/Request.php');
		header('Location: ' . $GLOBALS['base_url'] . 'index.php?src=' . substr($_SERVER['REQUEST_URI'], strlen($base_url) - 1));
	}

/**
 * Handles the encoding of request objects
 * @author Arthur Cavallari
 *
 */
class CommunicationHandler
{
	/**
	 * DEPRECATED.
	 * This is the first point of interaction between the DLA client and server, some
	 * basic validation happens here.
	 * 
	 * @param request    This is the JSON request sent from the DLA client
	 */
	public static function ReceiveRequestFromClient(Request &$request)
	{
		
	}
	/**
	 * This is the last point of interaction between the server and the DLA client,
	 * some basic validation happens here.
	 * 
	 * @param request    This is the reponse json object that will be sent to the
	 * client
	 */
	public static function TransmitResponseToClient(Request &$request)
	{
		$encodedJson = json_encode($request);
		echo json_format($encodedJson);
	}
}

?>