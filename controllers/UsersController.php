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
	require_once ('BaseController.php');
	require_once ('../utils/Request.php');
	require_once ('../data/UserData.php');
	require_once ('../db/DatabaseHandler.php');
	header('Location: ' . $GLOBALS['base_url'] . 'index.php?src=' . $_SERVER['REQUEST_URI']);
}




/**
 * Users Controller - handles all the user-related actions
 * @author Arthur Cavallari
 * @version 1.0
 * @created 26-May-2013 7:34:56 PM
 */
class UsersController extends BaseController
{
	/**
	 * Favourites a user
	 * @param Request $request
	 * @return Request
	 */
	public static function &favourite(Request &$request)
	{
		if($request->processJson())
		{
			$result = self::_favourite($request->session_id, $request->data->user_id, $request->data->favourited_user_id, $request->data->favourited);
			if($result['error'] == NULL)
			{				
				$request->request_status = REQUEST_STATUS::HANDLED;
				if($request->data->favourited == 1)
				{
					$request->setData((object) array('user_favourite' => 'success'));
				}
				else
				{
					$request->setData((object) array('user_unfavourite' => 'success'));
				}
			}
			else
			{
				$request->request_status = REQUEST_STATUS::FAILED;   
				$failedRequest = new FailedRequest($result['error']);
				$request->setData($failedRequest);
				// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
			}
		}
		else
		{
			$request->request_status = REQUEST_STATUS::FAILED;   
			$failedRequest = new FailedRequest($request->data->getErrors());
			$failedRequest->addReasonKey("inner_json_failed", "Failed to process request's JSON inner data.");
			$request->setData($failedRequest);
			// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
		}
		
		return $request;
	}
	
	/**
	 * Internal function - processes the favouriting of a user
	 * @param string $session_id
	 * @param string $user_id
	 * @param string $favourited_user_id
	 * @param int $favourited
	 * @return multitype:NULL
	 */
	private static function _favourite($session_id, $user_id, $favourited_user_id, $favourited)
	{
		// To have reached this point, we have already established the following:
		// user_id != favourited_user_id
		// Both user_id and favourited_user_id exist
		// And favourited is either 0 or 1
		// And session is valid
		
		self::initDB();
		$result = array();
		$result['error'] = NULL; //array( "failed_authentication" => "Password is invalid!" );
		$result['data'] = NULL;

		$arrList = self::$db->QueryArray('user_favourites', array('user_id'), "user_id = '{$user_id}' and favouritedUser_Id = '{$favourited_user_id}'");
		if($favourited == 0 && $arrList != false)
		{
			$result['data'] = self::$db->delete('user_favourites', "user_id = '{$user_id}' and favouritedUser_Id = '{$favourited_user_id}'");
		}
		elseif($favourited == 1 && $arrList == false)
		{
			$result['data'] = self::$db->insert('user_favourites', array('user_id'=>$user_id, 'favouritedUser_Id'=>$favourited_user_id));  
		}
		
		return $result;
	}

	/**
	 * Logins in a user
	 * @param Request $request
	 * @return Request
	 */
	public static function &login(Request &$request)
	{
		if($request->processJson())
		{			
			$result = self::_login($request->data->email, $request->data->password);
			if($result['error'] == NULL)
			{
				$user = new UserData();
				$user->set($result['data']);

				$request->setData($user);
				$request->request_status = REQUEST_STATUS::HANDLED;
				$request->generateSession_id($user->id);
			}
			else
			{
				$request->request_status = REQUEST_STATUS::FAILED;   
				$failedRequest = new FailedRequest($result['error']);
				$request->setData($failedRequest);
				// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
			}
		}
		else
		{
			$request->request_status = REQUEST_STATUS::FAILED;   
			$failedRequest = new FailedRequest($request->data->getErrors());
			$failedRequest->addReasonKey("inner_json_failed", "Failed to process request's JSON inner data.");
			$request->setData($failedRequest);
			// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
		}
		
		return $request;
	}
	
	/**
	 * Internal function - processes the loginning in of a user
	 * @param string $email
	 * @param string $password
	 * @return multitype:NULL multitype:string  |multitype:NULL multitype:string  unknown
	 */
	private static function _login($email, $password)
	{
		$result = array();
		$result['error'] = NULL;
		$result['data'] = NULL;
		if(isset($email, $password) && IsNullOrEmptyString($password) == false && IsNullOrEmptyString($email) == false)
		{
			// Only check db if no errors occured.. 
			self::initDB();
	
			$arrList = self::$db->QueryArray('users', '*', "email = '{$email}'");
			
			if(is_array( $arrList ))
			{
				$arrList = $arrList[0]; // choosing the first result.. there should only be one anyway

				if($arrList['password'] !== $password)
				{
					$result['error'] = array( "failed_authentication" => "Password is invalid!" );
					return $result;
				}
				else
				{	
					$result['data']	= $arrList;	
					return $result;
				}
			}
			else
			{
				$result['error'] = array( "failed_authentication" => "Email address is invalid!" );
				return $result;
			}
		}		
	}
	
	/**
	 * Resets a user password - sends an email to the user with password recovery information
	 * @param Request $request
	 * @return Request
	 */
	public static function &resetPassword(Request &$request)
	{
		if($request->processJson())
		{			
			$result = self::_resetPasswordCode($request->data->email);
			if($result['error'] == NULL)
			{
				$request->request_status = REQUEST_STATUS::HANDLED;				
				$request->setData( (object) array('code_expiry_date' => $result['data']) );
			}
			else
			{
				$request->request_status = REQUEST_STATUS::FAILED;   
				$failedRequest = new FailedRequest($result['error']);
				$request->setData($failedRequest);
				// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
			}
		}
		else
		{
			$request->request_status = REQUEST_STATUS::FAILED;   
			$failedRequest = new FailedRequest($request->data->getErrors());
			$failedRequest->addReasonKey("inner_json_failed", "Failed to process request's JSON inner data.");
			$request->setData($failedRequest);
			// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
		}
		
		return $request;
	}
	
	/**
	 * Internal function - processes the resetting of an user's password
	 * @param unknown $email
	 * @return multitype:NULL string
	 */
	private static function _resetPasswordCode($email)
	{
		self::initDB();
		
		$result = array();
		$result['error'] = NULL;
		$result['data'] = NULL;
		/*
		//Generate a RANDOM MD5 Hash for a password
		$random_password=md5(uniqid(rand()));
	 
		//Take the first 8 digits and use them as the password we intend to email the user
		$emailpassword=substr($random_password, 0, 8);
	 
		//Encrypt $emailpassword in MD5 format for the database
		$newpassword = md5($emailpassword);
		*/

		/** Update user's password */ 
		// 'users', array('password'=>'newpassword'), 'username = "kabindra"'
		//$id = self::$db->update('users', array('password'=>$newpassword), "email = '{$email}'");
		$userData = self::$db->QueryArray('users', '*', "email = '{$email}'");
		
		if($userData != false && count($userData) > 0)
		{
			$userId = $userData[0]['id'];
			$firstName = $userData[0]['firstName'];
			$lastName = $userData[0]['lastName'];
			$reset_code = uniqid(rand());
			$expiry_date = date('Y-m-d H:i:s', strtotime('+1 day', time()));
			
			
			$existingResetId = self::$db->QueryArray('user_reset_code', array('id'), "user_id = '{$userId}'");
			if($existingResetId != false && count($existingResetId) > 0)
			{
				$id = self::$db->update('user_reset_code', array('resetcode'=>$reset_code, 'expiry_date'=>$expiry_date), "user_id = '{$userId}'");
			}
			else
			{
				$id = self::$db->insert('user_reset_code', array('user_id'=>$userId, 'resetcode'=>$reset_code, 'expiry_date'=>$expiry_date));
			}
			$greeting = "$firstName $lastName";
			if($greeting == " ")
			{
				$greeting = $email;	
			}
			$message =  "$greeting," . "<br>" . PHP_EOL;
			$message .= "<br>" . PHP_EOL;
			$message .= "To complete the phase of resetting your account password at the Digital Lab Assistant, you will need to go to the URL below in your web browser." . "<br>" . PHP_EOL;
			$message .= "<br>" . PHP_EOL;
			$message .= "{$GLOBALS['server_url']}resetpassword.php?action=do_reset_password_form&uid={$userId}&code={$reset_code}" . "<br>" . PHP_EOL;
			$message .= "<br>" . PHP_EOL;
			$message .= "If the above link does not work correctly, go to {$GLOBALS['server_url']}resetpassword.php?action=do_reset_password_form" . "<br>" . PHP_EOL;
			$message .= "You will need to enter the following:" . "<br>" . PHP_EOL;
			$message .= "<strong>Username:</strong> {$email}" . "<br>" . PHP_EOL;
			$message .= "<strong>Activation Code:</strong> {$reset_code}" . "<br>" . PHP_EOL;
			$message .= "<br>" . PHP_EOL;
			$message .= "<em><strong>Please note that this code will expire in 24 hours.</strong></em><br>" . PHP_EOL;
			$message .= "<br>" . PHP_EOL;
			$message .= "Thank you," . "<br>" . PHP_EOL;
			$message .= "Team Sentinus - Digital Lab Assistant Staff";

			
			$subject = "Password Recovery Information";
			//($from, $to, $cc, $bcc, $subject, $body, $headers)
			$m = new EmailHandler($GLOBALS['email_address'], $email, NULL, NULL, $subject, $message, "");
			$m->send();

			
			$result['data'] = $expiry_date;
		}
		else
		{			
			$result['error'] = $db->getErrors();
		}	
		
		return $result;	
	}

	/**
	 * Registers a user
	 * @param Request $request
	 * @return Request
	 */
	public static function &register(Request &$request)
	{
		if($request->processJson())
		{			
			$user = new UserData();
			$result = self::_register($request->data->email, $request->data->password);
			if($result['error'] == NULL)
			{
				$user->set($result['data']);
				$request->setData($user);
				$request->request_status = REQUEST_STATUS::HANDLED;
				$request->generateSession_id($user->id);
			}
			else
			{
				$request->request_status = REQUEST_STATUS::FAILED;   
				$failedRequest = new FailedRequest($user->errors);
				$failedRequest->addReasonArray($result['error']);
				$request->setData($failedRequest);
				// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
			}
		}
		else
		{
			$request->request_status = REQUEST_STATUS::FAILED;   
			$failedRequest = new FailedRequest($request->data->getErrors());
			$failedRequest->addReasonKey("inner_json_failed", "Failed to process request's JSON inner data.");
			$request->setData($failedRequest);
			// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
		}
		
		return $request;
	}
	
	/**
	 * Internal function - processes the registering of a user, also logs a user in on a successful registration
	 * @param string $email
	 * @param string $password
	 * @return multitype:NULL unknown
	 */
	private static function _register($email, $password)
	{
		self::initDB();
		$result = array();
		$result['error'] = NULL;
		$result['data'] = NULL;
		$dateTimeRegistered = date("Y-m-d H:i:s");
		
		// Password now comes hashed from the client
		// $password = md5($password);
		/** Insert Records of users table */ 
		
		$id = self::$db->insert('users', array('username'=>$email, 'password'=>$password, 'email'=>$email, 'dateTimeRegistered'=>$dateTimeRegistered));
		
		if(isset($id) && $id !== NULL)
		{
			$arrList = self::$db->QueryArray('users', '*', "id = '{$id}'");
			$result['data'] = $arrList[0];
		}
		else
		{			
			$result['error'] = $db->getErrors();
		}	
		
		return $result;	
	}

	/**
	 * Updates user details
	 * @param Request $request
	 * @return Request
	 */
	public static function &updateProfile(Request $request)
	{
		if($request->processJson())
		{
			$result = self::_updateProfile($request->data);
			if($result['error'] == NULL)
			{
				$request->setData($result['data']);
				$request->request_status = REQUEST_STATUS::HANDLED;
			}
			else
			{
				$request->request_status = REQUEST_STATUS::FAILED;   
				$failedRequest = new FailedRequest($result['error']);
				$request->setData($failedRequest);
				// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
			}
		}
		else
		{
			
			$request->request_status = REQUEST_STATUS::FAILED;   
			$failedRequest = new FailedRequest($request->data->getErrors());
			$failedRequest->addReasonKey("inner_json_failed", "Failed to process request's JSON inner data.");
			$request->setData($failedRequest);
			// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
		}
		
		return $request;
	}
	
	/**
	 * Internal function - processes the updating of user details
	 * @param UserData $userData
	 * @return multitype:NULL unknown
	 */
	private static function _updateProfile($userData)
	{
		self::initDB();
		$result = array();
		$result['error'] = NULL;
		$result['data'] = NULL;
		
		// Password now comes hashed from the client
		// $password = md5($password);
		/** Insert Records of users table */ 

		$fields = array('username'		=>$userData->email, 
						'password'	    =>$userData->password, 
						'email'			=>$userData->email, 
						'firstName'		=>$userData->firstName, 
						'lastName'		=>$userData->lastName, 
						'organisation'	=>$userData->organisation, 
						'country'		=>$userData->country
						);
		$where = "id = '{$userData->id}'";
		
		$id = self::$db->update('users', $fields, $where);
		
		
		if(isset($id) && $id !== NULL)
		{
			$arrList = self::$db->QueryArray('users', '*', "id = '{$userData->id}'");
			
			$result['data'] = $arrList[0];
		}
		else
		{			
			$result['error'] = $db->getErrors();
		}	
		
		return $result;	
	}


}


?>