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
	require_once ('Enum.php');
	header('Location: ' . $GLOBALS['base_url'] . 'index.php?src=' . $_SERVER['REQUEST_URI']);
}





 
 /*

 
 */
/**
 * All available request types
 *  INSTRUCTIONS FOR ADDING A NEW REQUEST TYPE:
 *  
 *   1. Add new request type to this enum (REQUEST_TYPE)
 *   2. Create the relevant request file under /requests/
 *   3. /utils/request.php:			Add the relevant decode call on the processJson() function
 *   4. index.php: 					Add the new "require_once" for the newly created request file
 *   5. /main/RequestHandler.php:	Add the new request type to the SWITCH statement, along with it's relevant controller call - see other requests for examples
 *   6. Implement the request processor on the relevant controller - see other requests for examples
 *   7. Done!
 *    
 * @author Arthur Cavallari
 * @version 1.0
 * @created 26-May-2013 7:36:17 PM
 */
class REQUEST_TYPE extends Enum
{
	const __default = self::_INVALID;
	
	const _INVALID = 255;
	const HANDSHAKE = 254;
	const REGISTRATION = 1;
	const UPDATEUSERINFO = 2;
	const RESETPASSWORD = 3;
	const CREATELAB = 4;
	const UPDATELAB = 5;
	const UPDATELABMETA = 6;
	const DELETELAB = 7;
	const CREATESUBMISSION = 8;
	const UPDATESUBMISSION = 9;
	const DELETESUBMISSION = 10;
	const FLAGLAB = 11;
	const FAVOURITEUSER = 12;
	const SUBMITSUBMISSION = 13;
	const SUBMITLAB = 14;
	const CLONELAB = 15;
	const AUTHENTICATION = 16;
	const SEARCH = 17;
	const RETRIEVELAB = 18;
	const RETRIEVESUBMISSION = 19;
	const RETRIEVEIMAGE = 20;
	const SEARCHCREATED = 21;
	const SEARCHFINISHED = 22;
	const SEARCHUNFINISHED = 23;
	const SEARCHFAVOURITES = 24;
	const UPLOADIMAGE = 25;
	const CHECKIMAGE = 26;
	const RETRIEVELABWIDGETS = 27;
	const UPDATESUBMISSIONMETA = 28;
	
}
?>