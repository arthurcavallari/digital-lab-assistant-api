<?php
	if(!isset($pathCheck))
	{	
		require_once ('../globals.php');
		require_once ('AntiSpam.php');
		require_once ('ValidationUtils.php');
		require_once ('EmailHandler.php');
		require_once ('../db/DatabaseHandler.php');
	}

	/**
	 * Reset Password util class - used for users to reset their password
	 * @author Arthur Cavallari
	 *
	 */
	class ResetPasswordUtil
	{
		/**
		 * Attempts to reset the user's password if the given details are validated
		 * @param string $email
		 * @param string $activation_code
		 * @param string $password
		 * @param string $password_confirmation
		 */
		public static function do_resetpassword($email, $activation_code, $password, $password_confirmation)
		{
			if(strlen(trim($email)) == 0 || strlen(trim($activation_code)) == 0)
			{
				//self::showRequestResetPasswordForm("Session lost, please try again.");
				self::showResetPasswordForm("Email address and activation code cannot be blank!");
			}
			elseif(strlen(trim($password)) < 6 || strlen(trim($password_confirmation)) < 6)
			{
				self::showResetPasswordForm("Your password must be at least 6 characters long!");	
			}
			elseif(strlen(trim($password)) > 30 || strlen(trim($password_confirmation)) > 30)
			{
				self::showResetPasswordForm("Your password must be at most 30 characters long!");	
			}
			elseif($password != $password_confirmation)
			{
				self::showResetPasswordForm("You must enter the same password twice in order to confirm it!");	
			}
			else
			{
				$db = new DatabaseHandler();
				$db->initialize();
				
				$userData = $db->QueryArray('users', '*', "email = '{$email}'");
				if($userData != false && count($userData) > 0)
				{		
					$uid = $userData[0]['id'];		
					$result = $db->query('user_reset_code', '*', "user_id = '{$uid}'");	
					if($row = mysql_fetch_array ($result))
					{
						// yay
						$reset_code = $row['resetcode'];
						$reset_date = new DateTime($row['expiry_date']);
						$now = new DateTime;
						if($reset_code == $activation_code && $reset_code != "" && $reset_code != NULL)
						{					
							if($reset_date > $now)
							{
								//$updatecode = mysql_query ("UPDATE `exhibitors`	SET `password`='".md5($p_password) . "',`reset_code`='',`reset_date`='0000-00-00 00:00:00' WHERE `id`='$uid';",$dbh) or die(mysql_error());
								$db->update('users', array ('password' => md5($password)), "id = '{$uid}'");
								$db->delete('user_reset_code', "user_id = '{$uid}'");
								include ('inc/reset_password_success.inc');
							}
							else
							{
								self::showRequestResetPasswordForm("Your reset code has expired, please re-submit your lost password request!");	
							}
						}
						else
						{
							self::showRequestResetPasswordForm("The reset code provided is invalid, please re-submit your lost password request!");
						}					
					}
					else
					{
						self::showRequestResetPasswordForm("This user has not requested a password recovery!");	
					}
				}
				else
				{
					self::showRequestResetPasswordForm("Session lost, please try again. Could not find a user ith that email address.");
				}
			}
		}
		
		/**
		 * 
		 * @param unknown $uid
		 * @param unknown $code
		 * @return unknown|boolean
		 */
		public static function checkURLParams($uid, $code)
		{
			$validator = new ValidationUtils();
			if($validator->valid_int($uid))
			{
				$db = new DatabaseHandler();
				$db->initialize();
				$userData = $db->QueryArray('users', '*', "id = '{$uid}'");
				if($userData != false && count($userData) > 0)
				{
					$email = $userData[0]['email'];
					$result = $db->query('user_reset_code', '*', "user_id = '{$uid}'");	
					if($row = mysql_fetch_array ($result))
					{
						return $email;
					}
					else
					{
						return false;
					}
				}
			}
		}
		
		/**
		 * 
		 * @param string $errMessage
		 */
		public static function showRequestResetPasswordForm($errMessage = "&nbsp;")
		{
			include ('inc/reset_password_request_form.inc');
		}
		
		/**
		 * 
		 * @param string $errMessage
		 */
		public static function showResetPasswordForm($errMessage = "&nbsp;")
		{
			include ('inc/reset_password_form.inc');
		}
		
		/**
		 * 
		 * @param string $email
		 * @param string $verification
		 */
		public static function do_resetpassword_request($email, $verification)
		{
			session_save_path(sys_get_temp_dir());
			@session_start();
			$validator = new ValidationUtils();
			if(strlen(trim($email)) == 0 || strlen(trim($verification)) == 0)
			{
				self::showRequestResetPasswordForm("Please enter your email and the verification code!");
			}
			else
			{			
				if($validator->valid_email($email))
				{
					if(isset($_SESSION['image_value']) && md5(strtolower($verification)) == $_SESSION['image_value'])
					{
						unset($_SESSION['image_value']);
						$db = new DatabaseHandler();
						$db->initialize();
						
						$userData = $db->QueryArray('users', '*', "email = '{$email}'");
		
						if($userData != false && count($userData) > 0)
						{
							include ('inc/reset_password_complete.inc');
							$userId = $userData[0]['id'];
							$firstName = $userData[0]['firstName'];
							$lastName = $userData[0]['lastName'];
							$reset_code = uniqid(rand());
							$expiry_date = date('Y-m-d H:i:s', strtotime('+1 day', time()));							
							
							$existingResetId = $db->QueryArray('user_reset_code', array('id'), "user_id = '{$userId}'");
							if($existingResetId != false && count($existingResetId) > 0)
							{
								$id = $db->update('user_reset_code', array('resetcode'=>$reset_code, 'expiry_date'=>$expiry_date), "user_id = '{$userId}'");
							}
							else
							{
								$id = $db->insert('user_reset_code', array('user_id'=>$userId, 'resetcode'=>$reset_code, 'expiry_date'=>$expiry_date));
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
						}
						else
						{
							self::showRequestResetPasswordForm("Email address was not found in database!");	
						}
					}
					else
					{
						self::showRequestResetPasswordForm("Verification code is invalid!");
					}
				}
				else
				{
					self::showRequestResetPasswordForm("Email address given is invalid!");
				}
			}
		}		
	}

?>