<?php
	// Start buffering output
	ob_start();
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);
	date_default_timezone_set('Australia/Melbourne');
	
	require_once ('globals.php');
	require_once ('utils/ValidationUtils.php');
	require_once ('utils/LoginUtil.php');
	require_once ('db/DatabaseHandler.php');
	
	$r_action = @$_REQUEST['action'];
	$r_subaction = @$_REQUEST['subaction'];

	$p_email = @$_REQUEST['email'];
	$p_password = @$_REQUEST['password'];
	$p_remember_me = @$_REQUEST['remember_me'];
	
	//session_save_path(sys_get_temp_dir());
	session_start();
	
	
?><!DOCTYPE html>
<!--[if lt IE 7]> <html class="lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]> <html class="lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]> <html class="lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="en"> <!--<![endif]-->
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title>Digital Lab Assistant | Login</title>
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
		case "do_logout":
			LoginUtil::do_logout();
		break;
		case "do_login":
			if(isset($p_email, $p_password))
			{
				LoginUtil::do_login($p_email, $p_password, $p_remember_me);
			}
			else
			{
				LoginUtil::showLoginForm(); //"Please enter your email and the verification code!");
			}
			//ResetPasswordUtil::showResetPasswordForm();
		break;
		case "expired_code":
			LoginUtil::showLoginForm("Your reset code has expired, please re-submit your lost password request!");
		break;
		case "invalid_code":
			LoginUtil::showLoginForm("The reset code provided is invalid, please re-submit your lost password request!");
		break;
		case "no_request":
			LoginUtil::showLoginForm("This user has not requested a password recovery!");
		break;
		case "email_not_found":
			LoginUtil::showLoginForm("Email address was not found in database!");
		break;
		default:
			if(!LoginUtil::do_check_session())
			{
				LoginUtil::showLoginForm();	
			}
			else
			{
				header('Location: reviewflags.php');
				//include ('inc/login_complete.inc');	
			}
		break;				
	}
}
else
{
	if(!LoginUtil::do_check_session())
	{
		LoginUtil::showLoginForm();	
	}
	else
	{
		header('Location: reviewflags.php');
		//include ('inc/login_complete.inc');	
	}
}
		
?>
  </section>

</body>
</html><?php
	ob_end_flush();
	// flush output buffer
?>