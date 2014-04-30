<?php
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);
	date_default_timezone_set('Australia/Melbourne');
	$imageID = @$_REQUEST['getImageId'];
	@include('install/installed.php');
	if(!isset($installed) || $installed != 1)
	{
		require_once ('install/install.php');
	}
	elseif(!isset($imageID))
	{
		require_once ('globals.php');
		require_once ('db/DatabaseHandler.php');
		require_once ('utils/ValidationUtils.php');
		require_once ('utils/Base.php');
		require_once ('utils/Enum.php');
		require_once ('utils/REQUEST_TYPE.php');
		require_once ('utils/REQUEST_STATUS.php');
		require_once ('utils/Request.php');
		
		require_once ('main/RequestHandler.php');
		require_once ('main/CommunicationHandler.php');
		
		require_once ('utils/json.php');
		require_once ('utils/Utils.php');
		require_once ('utils/EmailHandler.php');
		
		require_once ('controllers/BaseController.php');
		require_once ('controllers/SubmissionsController.php');
		require_once ('controllers/LaboratoriesController.php');
		require_once ('controllers/UsersController.php');
		require_once ('data/UserData.php');
		require_once ('requests/UserRegistrationRequest.php');
		require_once ('requests/UserAuthenticationRequest.php');
		require_once ('requests/UserFavouriteRequest.php');	
		require_once ('requests/SearchRequest.php');	
		require_once ('requests/RetrieveLabRequest.php');
		require_once ('requests/SubmitLabRequest.php');
		require_once ('requests/DeleteLabRequest.php');
		require_once ('requests/FlagLabRequest.php');
		require_once ('requests/CloneLabRequest.php');
		require_once ('requests/RetrieveSubmissionRequest.php');
		require_once ('requests/DeleteSubmissionRequest.php');
		require_once ('requests/SubmitSubmissionRequest.php');
		require_once ('data/LabMetaData.php');
		require_once ('data/LabDocument.php');
		require_once ('data/SubmissionDocument.php');
		require_once ('data/SubmissionWidgetData.php');
		require_once ('data/WidgetData.php');
		require_once ('data/WidgetList.php');
		require_once ('data/SubmissionMetaData.php');
		require_once ('requests/FailedRequest.php');
		require_once ('requests/HandshakeData.php');
		require_once ('requests/UserPasswordResetRequest.php');
		require_once ('requests/UploadImageRequest.php');
		require_once ('requests/UploadImageChecksumRequest.php');
	
		//$requestType = (int)@$_REQUEST['request'];
		//$requestType = @$_REQUEST['request'];
	
		//var_dump(get_object_vars($lab));
		
		//$handshakeData = new HandshakeData();
		//$encodedJson = json_encode($handshakeData);
		//echo json_format($encodedJson);
		
		//array_walk($arr, create_function('&$i,$k','$i=" $k=\"$i\"";'));
		//$p_string = implode($arr," ");
	
		RequestHandler::handleRequest();
	}
	else
	{
		require_once ('globals.php');
		require_once ('db/DatabaseHandler.php');
		
		$path = "uploadedfiles/";
		
		
		$db = new DatabaseHandler();
		$db->initialize();
		
		$parsedID = explode('-', $imageID);
		$arrList = NULL;
		$submissionImage = NULL;
		$imageName = NULL;
		
		if(count($parsedID) == 3)
		{
			$submissionImage = $db->QueryArray('submission_fields', array('value'), "field_id = '{$parsedID[1]}-{$parsedID[2]}'");
			$arrList = $db->QueryArray('laboratory_fields', array('fieldType', 'value'), "w_id = '{$parsedID[1]}-{$parsedID[2]}'");
		}
		elseif(count($parsedID) == 2)
		{
			$arrList = $db->QueryArray('laboratory_fields', array('fieldType', 'value'), "w_id = '{$imageID}'");
		}
		else
		{
			// error :/
			// do something here to tell the user
			$arrList == false;	
		}
		
		if($arrList == false || count($arrList) == 0)
		{
			$path = "404.png";
		}
		else
		{
			if($arrList[0]['fieldType'] == 5 || $arrList[0]['fieldType'] == "5")
			{
				if(count($parsedID) == 2)
				{
					$imageName	 = $arrList[0]['value'];					
				}
				else
				{
					$imageName	 = $submissionImage[0]['value'];
				}
				$path .= $imageName;	

				if(!file_exists($path) || $path == "uploadedfiles/")
				{
					$path = "403.png";
				}
			}
			else
			{
				$path = "500.png";
			}
			
		}
		
		
		/*if($imageID == 1)
		{
			$path = "1cabbagephindicator.png";
		}
		else if($imageID == 2)
		{
			$path = "volcanoerupt.jpg";
		}
		else if($imageID == 3)
		{
			$path = "5472.gif";
		}	
		else
		{
			$path = "uploadedfiles/1-1.png";	
		}*/
		//$path = "5472.gif"; // findImagePathById($imageID);
		$filename = basename($path);
		$file_extension = strtolower(substr(strrchr($filename,"."),1));
		
		switch( $file_extension ) {
			case "gif": $ctype="image/gif"; break;
			case "png": $ctype="image/png"; break;
			case "jpeg":
			case "jpg": $ctype="image/jpeg"; break;
			default:
		}
		
		header('Content-Disposition: inline; filename="' . $filename . '"');
		header('Content-Type: ' . $ctype);
		header("Content-Length: " . filesize($path));	
		$fp = fopen($path, 'rb');
		fpassthru($fp);
	}
?>