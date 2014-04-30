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
	require_once ('../main/CommunicationHandler.php');
	require_once ('../utils/Request.php');
	require_once ('../requests/FailedRequest.php');
	require_once ('../utils/REQUEST_STATUS.php');
	require_once ('../utils/REQUEST_TYPE.php');
	require_once ('../controllers/LaboratoriesController.php');
	require_once ('../controllers/UsersController.php');
	header('Location: ' . $GLOBALS['base_url'] . 'index.php?src=' . $_SERVER['REQUEST_URI']);
}


/**
 * @author Arthur Cavallari
 * @version 1.0
 * @created 26-May-2013 7:34:56 PM
 */
class RequestHandler
{
	
	public $_SubmissionsController;
	public $_LaboratoriesController;
	public $_UsersController;
	public $_Utils;

	/**
	 * This is where the received json requests are processed and directed to their
	 * respective controllers.
	 * 
	 */
	public static function HandleRequest() //$requestType)
	{
		/*
		Steps:
			1. Decode json request
			2. switch(REQUEST_TYPE)
			3. If there is any innerjson_data, processJson()
			4. Do stuff with processed inner data
		Extension:
			1a. If JSON is invalid, or not supplied we print an error -> plain string
			2a. If invalid REQUEST_TYPE, respond with a FailedRequest with relevant error messages
			3a. If processJson() fails, respond with a FailedRequest with relevant error messages
			4a. If stuff fails, respond with a FailedRequest with relevant error messages
		*/
		
		$request = new Request();
		$decodedJson = json_decode(file_get_contents('php://input'), false);
		if(isset($decodedJson))
		{
			if($request->set($decodedJson) == false)
			{
				$request->printErrors();
				$request->request_status = REQUEST_STATUS::FAILED;   
				$failedRequest = new FailedRequest($request->data->getErrors());
				$request->setData($failedRequest);
				CommunicationHandler::TransmitResponseToClient($request);
			}
					
			$requestType = new REQUEST_TYPE($request->request_type);
			
			if($requestType->__toInteger() != REQUEST_TYPE::_INVALID)
			{
				switch($requestType)
				{
					case "HANDSHAKE":
						$handshakeData = new HandshakeData();
						$request->request_status = REQUEST_STATUS::HANDLED;
						$request->setData($handshakeData);
						$encodedJson = json_encode($request);
						echo json_format($encodedJson);
					break;
					case "REGISTRATION":
						CommunicationHandler::TransmitResponseToClient(UsersController::register($request));
					break;					
					case "UPDATEUSERINFO":
						CommunicationHandler::TransmitResponseToClient(UsersController::updateProfile($request));
					break;
					case "RESETPASSWORD":
						CommunicationHandler::TransmitResponseToClient(UsersController::resetPassword($request));
					break;
					case "CREATELAB":
						CommunicationHandler::TransmitResponseToClient(LaboratoriesController::create($request));
					break;
					case "UPDATELAB":
						CommunicationHandler::TransmitResponseToClient(LaboratoriesController::update($request));
					break;
					case "UPDATELABMETA":
						CommunicationHandler::TransmitResponseToClient(LaboratoriesController::updateLab($request));
					break;
					case "DELETELAB":
						CommunicationHandler::TransmitResponseToClient(LaboratoriesController::delete($request));
					break;
					case "CREATESUBMISSION":
						CommunicationHandler::TransmitResponseToClient(SubmissionsController::create($request));
					break;
					case "UPDATESUBMISSIONMETA":
						CommunicationHandler::TransmitResponseToClient(SubmissionsController::updateSubmission($request));
					break;
					case "UPDATESUBMISSION":
						CommunicationHandler::TransmitResponseToClient(SubmissionsController::update($request));
					break;
					case "DELETESUBMISSION":
						CommunicationHandler::TransmitResponseToClient(SubmissionsController::delete($request));
					break;
					case "FLAGLAB":
						CommunicationHandler::TransmitResponseToClient(LaboratoriesController::flag($request));
					break;
					case "FAVOURITEUSER":
						CommunicationHandler::TransmitResponseToClient(UsersController::favourite($request));	
					break;
					case "SUBMITSUBMISSION":
						CommunicationHandler::TransmitResponseToClient(SubmissionsController::submit($request));
					break;
					case "SUBMITLAB":
						CommunicationHandler::TransmitResponseToClient(LaboratoriesController::submit($request));
					break;
					case "CLONELAB":
						CommunicationHandler::TransmitResponseToClient(LaboratoriesController::cloneLab($request));
					break;
					case "AUTHENTICATION":	
						CommunicationHandler::TransmitResponseToClient(UsersController::login($request));
					break;
					case "SEARCH":
						CommunicationHandler::TransmitResponseToClient(LaboratoriesController::search($request));
					break;
					case "SEARCHCREATED":
						CommunicationHandler::TransmitResponseToClient(LaboratoriesController::search_created($request));
					break;
					case "SEARCHFINISHED":
						CommunicationHandler::TransmitResponseToClient(LaboratoriesController::search_finished($request));
					break;
					case "SEARCHUNFINISHED":
						CommunicationHandler::TransmitResponseToClient(LaboratoriesController::search_unfinished($request));
					break;
					case "SEARCHFAVOURITES":
						CommunicationHandler::TransmitResponseToClient(LaboratoriesController::search_favourites($request));
					break;
					case "RETRIEVELAB":
						CommunicationHandler::TransmitResponseToClient(LaboratoriesController::retrieve($request));
					break;
					case "RETRIEVELABWIDGETS":
						CommunicationHandler::TransmitResponseToClient(LaboratoriesController::retrieveWidgets($request));
					break;
					case "RETRIEVESUBMISSION":
						CommunicationHandler::TransmitResponseToClient(SubmissionsController::retrieve($request));
					break;
					case "RETRIEVEIMAGE":
						// Deprecated
						// Notes: Now done through index.php?getImageId={WidgetID}
					break;
					case "UPLOADIMAGE":
						CommunicationHandler::TransmitResponseToClient(LaboratoriesController::uploadImage($request));
					break;
					case "CHECKIMAGE":
						CommunicationHandler::TransmitResponseToClient(LaboratoriesController::checkImage($request));
					break;	
					case "RETRIEVELABWIDGETS":
						/* TODO: To be implemented */
					break;					
					default:
						$request->request_status = REQUEST_STATUS::FAILED;   
						$failedRequest = new FailedRequest();
						$failedRequest->addReasonKey("invalid_request_type", "Invalid request type: [" . $requestType . "]");
						$request->setData($failedRequest);
						// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
						
						CommunicationHandler::TransmitResponseToClient($request);
				}
			}
			else
			{
				$request->request_status = REQUEST_STATUS::FAILED;   
				$failedRequest = new FailedRequest();
				$failedRequest->addReasonKey("invalid_request_type", "Invalid request type: [" . $requestType . "]");
				$request->setData($failedRequest);
				// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
				
				CommunicationHandler::TransmitResponseToClient($request);
			}
		}
		else
		{
			echo "JSON supplied is invalid or nonexistent\n" . file_get_contents('php://input');
		}
	}

}


?>