<?php
	if(!isset($pathCheck))
	{	
		require_once ('../globals.php');
		require_once ('ValidationUtils.php');
		require_once ('../db/DatabaseHandler.php');
	}

	/**
	 * Login Util used for the admin control panel
	 * @author Arthur Cavallari
	 *
	 */
	class LoginUtil
	{		
		/**
		 * Shows the login form - If $errMessage != "&nbsp;", it displays the message
		 * @param string $errMessage
		 */
		public static function showLoginForm($errMessage = "&nbsp;")
		{
			if(!self::do_check_session())
			{
				include ('inc/login_form.inc');
			}
			else
			{
				include ('inc/login_complete.inc');	
			}
			
		}
		
		/**
		 * Deletes the cookies, destroys the current session then redirects to the login screen
		 */
		public static function do_logout()
		{
			setcookie('email', "", time() - 3600);
			setcookie('val', "", time() - 3600);
			setcookie('uid', "", time() - 3600);
			setcookie('show_reviewed', false, time() - 3600);
			session_destroy();
			header('Location: login.php');
		}
		
		/**
		 * Gets the currently logged in user from the session/cookies 
		 * @return Ambigous <>|boolean
		 */
		public static function getCurrentUser()
		{
			$uid = self::do_check_session();

			if($uid !== FALSE)
			{
				$db = new DatabaseHandler();
				$db->initialize();
				
				$userData = $db->QueryArray('users', '*', "id = '{$uid}'");
				if($userData != false && count($userData) > 0)
				{
					$userData[0]['login_type'] = self::do_check_session_type();
					return $userData[0];
				}
				else
				{					
					return false;	
				}
				
			}
			else
			{
				return false;	
			}
		}
		
		/**
		 * Checks if the user is logged in and the details are valid
		 * @return unknown|boolean - Returns user ID if successful, or FALSE otherwise
		 */
		public static function do_check_session()
		{			
			if(session_id() == '')
			{
				die('Session not started!');	
			}
			$c_email 		= @$_COOKIE['email'];
			$c_uid 			= @$_COOKIE['uid'];
			$c_saltedHash 	= @$_COOKIE['dla_hash'];
			
			$s_email 		= @$_SESSION['email'];
			$s_saltedHash 	= @$_SESSION['dla_hash'];
			$s_uid 			= @$_SESSION['uid'];
			
			$db = new DatabaseHandler();
			$db->initialize();
			
			
			// ip, email, id, salt
			
			if(isset($c_email, $c_saltedHash, $c_uid))
			{
				$saltedHash = md5($_SERVER['REMOTE_ADDR'].$c_email.$c_uid.$GLOBALS['salt']);
				if($c_saltedHash == $saltedHash && strlen($c_saltedHash) > 0)
				{
					$userData = $db->QueryArray('users', '*', "id = '{$c_uid}' and email = '{$c_email}' and isAdmin = '1'");
					if($userData != false && count($userData) > 0)
					{
						return $c_uid;
					}
					else
					{
						return false;
					}
				}
				else
				{
					return false;	
				}
			}
			elseif(isset($s_email, $s_saltedHash, $s_uid))
			{
				$saltedHash = md5($_SERVER['REMOTE_ADDR'].$s_email.$s_uid.$GLOBALS['salt']);
				if($s_saltedHash == $saltedHash && strlen($s_saltedHash) > 0)
				{
					$userData = $db->QueryArray('users', '*', "id = '{$s_uid}' and email = '{$s_email}' and isAdmin = '1'");
					if($userData != false && count($userData) > 0)
					{
						return $s_uid;
					}
					else
					{
						echo "id = '{$c_uid}' and email = '{$s_email}' and isAdmin = '1'";
						return false;
					}
				}
				else
				{
					return false;	
				}
			}
			else
			{
				return false;	
			}
		}
		
		/**
		 * Figures out whether the user is logged in through cookies or a php session
		 * @return string|boolean Returns "session" or "cookie" if a user is logged in, FALSE otherwise.
		 */
		public static function do_check_session_type()
		{			
			if(session_id() == '')
			{
				die('Session not started!');	
			}
			$c_email 		= @$_COOKIE['email'];
			$c_uid 			= @$_COOKIE['uid'];
			$c_saltedHash 	= @$_COOKIE['dla_hash'];
			
			$s_email 		= @$_SESSION['email'];
			$s_saltedHash 	= @$_SESSION['dla_hash'];
			$s_uid 			= @$_SESSION['uid'];
			
			
			// ip, email, id, salt
			
			if(isset($c_email, $c_saltedHash, $c_uid))
			{
				$saltedHash = md5($_SERVER['REMOTE_ADDR'].$c_email.$c_uid.$GLOBALS['salt']);
				if($c_saltedHash == $saltedHash && strlen($c_saltedHash) > 0)
				{
					return "cookie";
				}
				else
				{
					return false;	
				}
			}
			elseif(isset($s_email, $s_saltedHash, $s_uid))
			{
				$saltedHash = md5($_SERVER['REMOTE_ADDR'].$s_email.$s_uid.$GLOBALS['salt']);
				if($s_saltedHash == $saltedHash && strlen($s_saltedHash) > 0)
				{
					return "session";
				}
				else
				{
					return false;	
				}
			}
			else
			{
				return false;	
			}
		}
		
		/**
		 * Attempts to login a user with the given details
		 * @param string $email
		 * @param string $password
		 * @param int $remember_me
		 */
		public static function do_login($email, $password, $remember_me)
		{
			$validator = new ValidationUtils();
			if(strlen(trim($email)) == 0 || strlen(trim($password)) == 0)
			{
				self::showLoginForm("Please enter your login details!");
			}
			else
			{			
				if($validator->valid_email($email) && strlen(trim($password)) > 0)
				{
					$passwordHash = md5($password);
					
					$db = new DatabaseHandler();
					$db->initialize();
					
					$userData = $db->QueryArray('users', '*', "email = '{$email}'");
	
					if($userData != false && count($userData) > 0)
					{
						if($userData[0]['password'] == $passwordHash)
						{
							if($userData[0]['isAdmin'] == 1 || $userData[0]['isAdmin'] == '1')
							{
								$saltedHash = md5($_SERVER['REMOTE_ADDR'].$email.$userData[0]['id'].$GLOBALS['salt']);
								if(isset($remember_me))
								{								
									setcookie('email', ($email), time() + 3600 * 24 * 365 * 2);
									setcookie('dla_hash', ($saltedHash), time() + 3600 * 24 * 365 * 2);
									setcookie('uid', ($userData[0]['id']), time() + 3600 * 24 * 365 * 2);
								}
								else
								{
									$_SESSION['email'] = $email;
									$_SESSION['dla_hash'] = $saltedHash;
									$_SESSION['uid'] = $userData[0]['id'];	
								}
								//var_dump($userData);
								header('Location: reviewflags.php');
								//include ('inc/login_complete.inc');
							}
							else
							{
								self::showLoginForm("Admin only page!");
							}
						}
						else
						{
							self::showLoginForm("Password is invalid!");		
						}
					}
					else
					{
						self::showLoginForm("Email address was not found in database!");	
					}
				}
				else
				{
					self::showLoginForm("Email address given is invalid!");
				}
			}
		}		
	}
?>