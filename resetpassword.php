<?php
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);
	date_default_timezone_set('Australia/Melbourne');
	
	require_once ('globals.php');
	require_once ('utils/AntiSpam.php');
	require_once ('utils/ValidationUtils.php');
	require_once ('utils/EmailHandler.php');
	require_once ('utils/ResetPasswordUtil.php');
	require_once ('db/DatabaseHandler.php');
	
	$r_action = @$_REQUEST['action'];
	$r_subaction = @$_REQUEST['subaction'];

	$p_email = @$_REQUEST['email'];
	$p_verification = @$_REQUEST['verification'];
		
?><!DOCTYPE html>
<!--[if lt IE 7]> <html class="lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]> <html class="lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]> <html class="lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="en"> <!--<![endif]-->
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title>Digital Lab Assistant | Reset Password</title>
  <link rel="stylesheet" href="css/style.css">
  <!--[if lt IE 9]><script src="js/html5.js"></script><![endif]-->
</head>
<body>
  <section class="container">
<?php
if(isset($r_action))
{
	switch($r_action)
	{
		case "do_resetpassword_request":
			if(isset($p_email, $p_verification))
			{
				ResetPasswordUtil::do_resetpassword_request($p_email, $p_verification);
			}
			else
			{
				ResetPasswordUtil::showRequestResetPasswordForm("Please enter your email and the verification code!");
			}
		break;
		case "do_processcode":
			if(isset($r_subaction))
			{
				ResetPasswordUtil::showResetPasswordForm();
			}
			else
			{
				$email = @$_REQUEST['email'];
				include ('inc/reset_password_complete.inc');
			}
		break;
		case "do_reset_password_form":
			$g_uid = @$_GET['uid'];
			$g_activation_code = @$_GET['code'];
			
			$p_password = @$_REQUEST['password'];
			$p_password_confirmation = @$_REQUEST['password_confirmation'];
			$p_activation_code = @$_REQUEST['activation_code'];
				
			if(isset($g_uid, $g_activation_code))
			{
				$result = ResetPasswordUtil::checkURLParams($g_uid, $g_activation_code);
				if($result != FALSE)
				{
					$_REQUEST['email'] = $result;
					$_REQUEST['activation_code'] = $g_activation_code;
					ResetPasswordUtil::showResetPasswordForm();
				}
				else
				{
					ResetPasswordUtil::showRequestResetPasswordForm("This user has not requested a password recovery!");	
				}
			}
			else
			{
				if(isset($p_email, $p_activation_code, $p_password, $p_password_confirmation))
				{
					ResetPasswordUtil::do_resetpassword($p_email, $p_activation_code, $p_password, $p_password_confirmation);
				}
				else
				{
					ResetPasswordUtil::showResetPasswordForm(); //"Please enter your email and the verification code!");
				}
			}
			//ResetPasswordUtil::showResetPasswordForm();
		break;
		case "expired_code":
			ResetPasswordUtil::showRequestResetPasswordForm("Your reset code has expired, please re-submit your lost password request!");
		break;
		case "invalid_code":
			ResetPasswordUtil::showRequestResetPasswordForm("The reset code provided is invalid, please re-submit your lost password request!");
		break;
		case "no_request":
			ResetPasswordUtil::showRequestResetPasswordForm("This user has not requested a password recovery!");
		break;
		case "email_not_found":
			ResetPasswordUtil::showRequestResetPasswordForm("Email address was not found in database!");
		break;
		default:
			ResetPasswordUtil::showRequestResetPasswordForm();
		break;				
	}
}
else
{
	ResetPasswordUtil::showRequestResetPasswordForm();	
}
		
	  ?>
  </section>

</body>
</html>