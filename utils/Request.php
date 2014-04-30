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
	
	require_once ('Base.php');
	require_once ('REQUEST_STATUS.php');
	require_once ('REQUEST_TYPE.php');
	require_once ('../data/UserData.php');
	header('Location: ' . $GLOBALS['base_url'] . 'index.php?src=' . $_SERVER['REQUEST_URI']);
}



/**
 * Request object, all data sent to and from the server will be based on this object
 * @author Arthur Cavallari
 * @version 1.0
 * @created 26-May-2013 7:34:56 PM
 */
class Request extends Base
{

	public $dateTimeCreated;
	public $request_id;
	public $session_id;
	public $request_status;
	public $request_type;
	public $json_data;
	public $data;
		
	/**
	 * 
	 * @param string $dateTimeCreated
	 * @param string $request_id
	 * @param int $request_status
	 * @param int $request_type
	 * @param string $json_data
	 */
	public function create($dateTimeCreated, $request_id, $request_status, $request_type, $json_data)
	{
		$this->dateTimeCreated = $dateTimeCreated;
		$this->request_id = $request_id;
		$this->request_status = $request_status;
		$this->request_type = $request_type;
		$this->json_data = $json_data;
	}
	
	/**
	 * Sets the json_data attribute
	 * @param string $json_data
	 */
	public function setData($json_data)
	{
		$this->json_data = $json_data;
		$this->data = NULL;
	}
	
		/**
	 * Validate(): Returns true if all fields are valid, false if anything has been set on $errors
	 * (non-PHPdoc)
	 * @see Base::validate()
	 */
	public function validate()
	{
		if(!$this->valid_date($this->dateTimeCreated))
		{
			$this->errors['date'] = "Date format is invalid, expecting YYYY-MM-DD HH:MM:SS." . $this->dateTimeCreated;
		}
		if($this->request_id === NULL) 
		{
			$this->errors['request_id'] = "Request ID is invalid!";
		}		
		if(!REQUEST_TYPE::isValid($this->request_type))			
		{
			$this->errors['request_type'] = "Request Type is invalid!";
		}
		if(!REQUEST_STATUS::isValid($this->request_status))			
		{
			$this->errors['request_status'] = "Request Status is invalid!" ;
		}
		return (count($this->errors) == 0);
	}
	
	/**
	 * Processes the given request type, and data accordingly
	 * @return boolean TRUE on success, FALSE otherwise. If FALSE, it sets the $errors attribute with the errors, as well as the $data attribute's errors if necessary
	 */
	public function processJson()
	{
		if(REQUEST_TYPE::isValid($this->request_type))
		{
			$type = new REQUEST_TYPE($this->request_type);
			/*
			// This section isn' needed anymore because before we were sending the json_data as a string instead of a json object
			
			var_dump($this->json_data);
			var_dump(json_encode($this->json_data));
			$decodedJson = json_decode(json_encode($this->json_data));
			var_dump($decodedJson);*/
			
			$decodedJson = $this->json_data;
			switch($type->__toInteger())
			{
				case (REQUEST_TYPE::REGISTRATION):
					if($decodedJson == NULL) { $this->errors['innerjson_invalid'] = "Request data is NULL or invalid!"; return false; }
					$this->data = new UserRegistrationRequest();					
					return $this->data->set($decodedJson);					
				break;	
				case (REQUEST_TYPE::AUTHENTICATION):
					if($decodedJson == NULL) { $this->errors['innerjson_invalid'] = "Request data is NULL or invalid!"; return false; }
					$this->data = new UserAuthenticationRequest();					
					return $this->data->set($decodedJson);					
				break;	
				case (REQUEST_TYPE::FAVOURITEUSER):
					if($decodedJson == NULL) { $this->errors['innerjson_invalid'] = "Request data is NULL or invalid!"; return false; }
					$this->data = new UserFavouriteRequest($this->session_id);					
					return $this->data->set($decodedJson);	
				break;
				case (REQUEST_TYPE::SEARCH):
					if($decodedJson == NULL) { $this->errors['innerjson_invalid'] = "Request data is NULL or invalid!"; return false; }
					$this->data = new SearchRequest();					
					return $this->data->set($decodedJson);	
				break;	
				case (REQUEST_TYPE::RETRIEVELAB):
					if($decodedJson == NULL) { $this->errors['innerjson_invalid'] = "Request data is NULL or invalid!"; return false; }
					$this->data = new RetrieveLabRequest($this->session_id);					
					return $this->data->set($decodedJson);	
				break;
				case (REQUEST_TYPE::SUBMITLAB):
					if($decodedJson == NULL) { $this->errors['innerjson_invalid'] = "Request data is NULL or invalid!"; return false; }
					$this->data = new SubmitLabRequest($this->session_id);					
					return $this->data->set($decodedJson);	
				break;
				case (REQUEST_TYPE::DELETELAB):
					if($decodedJson == NULL) { $this->errors['innerjson_invalid'] = "Request data is NULL or invalid!"; return false; }
					$this->data = new DeleteLabRequest($this->session_id);					
					return $this->data->set($decodedJson);	
				break;
				case (REQUEST_TYPE::FLAGLAB):
					if($decodedJson == NULL) { $this->errors['innerjson_invalid'] = "Request data is NULL or invalid!"; return false; }
					$this->data = new FlagLabRequest($this->session_id);					
					return $this->data->set($decodedJson);	
				break;
				case (REQUEST_TYPE::CLONELAB):
					if($decodedJson == NULL) { $this->errors['innerjson_invalid'] = "Request data is NULL or invalid!"; return false; }
					$this->data = new CloneLabRequest($this->session_id);					
					return $this->data->set($decodedJson);	
				break;
				case (REQUEST_TYPE::SUBMITSUBMISSION):
					if($decodedJson == NULL) { $this->errors['innerjson_invalid'] = "Request data is NULL or invalid!"; return false; }
					$this->data = new SubmitSubmissionRequest($this->session_id);					
					return $this->data->set($decodedJson);	
				break;
				case (REQUEST_TYPE::RETRIEVESUBMISSION):
					if($decodedJson == NULL) { $this->errors['innerjson_invalid'] = "Request data is NULL or invalid!"; return false; }
					$this->data = new RetrieveSubmissionRequest($this->session_id);					
					return $this->data->set($decodedJson);	
				break;
				case (REQUEST_TYPE::DELETESUBMISSION):
					if($decodedJson == NULL) { $this->errors['innerjson_invalid'] = "Request data is NULL or invalid!"; return false; }
					$this->data = new DeleteSubmissionRequest($this->session_id);					
					return $this->data->set($decodedJson);	
				break;
				case (REQUEST_TYPE::RETRIEVELABWIDGETS):
					if($decodedJson == NULL) { $this->errors['innerjson_invalid'] = "Request data is NULL or invalid!"; return false; }
					$this->data = new RetrieveLabRequest($this->session_id);					
					return $this->data->set($decodedJson);	
				break;
				case (REQUEST_TYPE::RESETPASSWORD):
					if($decodedJson == NULL) { $this->errors['innerjson_invalid'] = "Request data is NULL or invalid!"; return false; }
					$this->data = new UserPasswordResetRequest();					
					return $this->data->set($decodedJson);	
				break;	
				case (REQUEST_TYPE::UPLOADIMAGE):
					if($decodedJson == NULL) { $this->errors['innerjson_invalid'] = "Request data is NULL or invalid!"; return false; }
					$this->data = new UploadImageRequest();					
					return $this->data->set($decodedJson);	
				break;	
				case (REQUEST_TYPE::CHECKIMAGE):
					if($decodedJson == NULL) { $this->errors['innerjson_invalid'] = "Request data is NULL or invalid!"; return false; }
					$this->data = new UploadImageChecksumRequest();					
					return $this->data->set($decodedJson);	
				break;
				case (REQUEST_TYPE::CREATELAB):
					if($decodedJson == NULL) { $this->errors['innerjson_invalid'] = "Request data is NULL or invalid!"; return false; }
					$this->data = new LabMetaData($this->session_id, TRUE);					
					return $this->data->set($decodedJson);
				case (REQUEST_TYPE::UPDATELABMETA):
					if($decodedJson == NULL) { $this->errors['innerjson_invalid'] = "Request data is NULL or invalid!"; return false; }
					$this->data = new LabMetaData($this->session_id, TRUE);					
					return $this->data->set($decodedJson);	
				case (REQUEST_TYPE::UPDATESUBMISSIONMETA):
					if($decodedJson == NULL) { $this->errors['innerjson_invalid'] = "Request data is NULL or invalid!"; return false; }
					$this->data = new SubmissionMetaData($this->session_id, TRUE);					
					return $this->data->set($decodedJson);	
				case (REQUEST_TYPE::UPDATELAB):
					if($decodedJson == NULL) { $this->errors['innerjson_invalid'] = "Request data is NULL or invalid!"; return false; }
					$this->data = new LabDocument($this->session_id);								
					return $this->data->set($decodedJson);
				case (REQUEST_TYPE::UPDATESUBMISSION):
					if($decodedJson == NULL) { $this->errors['innerjson_invalid'] = "Request data is NULL or invalid!"; return false; }
					$this->data = new SubmissionDocument($this->session_id);								
					return $this->data->set($decodedJson);
				break;	
				case (REQUEST_TYPE::UPDATEUSERINFO):
					if($decodedJson == NULL) { $this->errors['innerjson_invalid'] = "Request data is NULL or invalid!"; return false; }
					$this->data = new UserData($this->session_id);								
					return $this->data->set($decodedJson);
				break;
				default:
					$this->errors['requesttype_invalid'] = "Request::processJson(): Invalid request type: [" . $this->request_type . "]";
					return false;
				break;
			}
		}
	}
	
	/**
	 * Returns the user IP
	 * @return string
	 */
	private function getUserIP()
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) //if from shared
		{
			return $_SERVER['HTTP_CLIENT_IP'];
		}
		else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //if from a proxy
		{
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
			return $_SERVER['REMOTE_ADDR'];
		}
	}
	
	/**
	 * Gets the user ID from a given session id
	 * (non-PHPdoc)
	 * @see Base::getUserIDFromSession()
	 */
	public function getUserIDFromSession($session_id = NULL)
	{
		$userID = -1;
		if($session_id == NULL)
		{
			$session_id = $this->session_id;
		}
		
		return parent::getUserIDFromSession($session_id);
	}

	/**
	 * Generates a session id for a given user id - used on registration / login
	 * @param unknown $userID
	 */
	public function generateSession_id($userID)
	{
		if(!isset($userID) || $userID === NULL) die('generateSession_id called with invalid user id');
		
		if(($this->session_id === NULL || $this->session_id == "") && $this->validate())
		{
			$db = &$this->getDB();
			$session_id = uniqid(rand(), true);			
			$arrList = $db->QueryArray('user_sessions', array('session_id'), "session_id = '{$session_id}'");
			$count = 1;

			while($count < 10 && $arrList != false)
			{
				$count++;
				$session_id = uniqid(rand(), true);			
				$arrList = $db->QueryArray('user_sessions', array('session_id'), "session_id = '{$session_id}'");
			}
			
			if($count == 10)
			{
				die('Could not generate id after 10 attempts :(');
			}
			else
			{
				$arrList = $db->QueryArray('user_sessions', array('user_id'), "user_id = '{$userID}'");
				
				if($userID == 43)
				{
					$session_id = "1550547771521d88535027b2.29325962"; // TODO: Remove this later.. used for testing only	
				}
				
				if($arrList == false)
				{
					$db->insert('user_sessions', array('user_id'=>$userID, 'session_id'=>$session_id, 'ip'=>$this->getUserIP()));
				}
				else
				{
					$db->update('user_sessions', array('session_id'=>$session_id, 'ip'=>$this->getUserIP()), "user_id = '{$userID}'");
				}
				$this->session_id = $session_id; // Generates session ID on success/login
			}
			
		}
	}

}
?>