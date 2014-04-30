<?php
	// Start buffering output
	ob_start();
	
	
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);
	date_default_timezone_set('Australia/Melbourne');
	
	require_once ('globals.php');
	require_once ('utils/AntiSpam.php');
	require_once ('utils/ValidationUtils.php');
	require_once ('utils/EmailHandler.php');
	require_once ('utils/LoginUtil.php');
	require_once ('db/DatabaseHandler.php');
	
	$r_action = @$_REQUEST['action'];
	$r_subaction = @$_REQUEST['subaction'];

	$r_id = @$_REQUEST['id'];
	$r_flag_id = @$_REQUEST['flagid'];
		
	//session_save_path(sys_get_temp_dir());
	session_start();
	
	$showReviewed = false;
	
	$currentUser = LoginUtil::getCurrentUser();
	
	if($currentUser['login_type'] == "cookie")
	{
		$showReviewed = @$_COOKIE['show_reviewed'];
	}
	elseif($currentUser['login_type'] == "session")
	{
		$showReviewed = @$_SESSION['show_reviewed'];		
	}
		
	$currentUserFullName = trim("{$currentUser['firstName']} {$currentUser['lastName']}");
	if(strlen($currentUserFullName) == 0)
	{
		$currentUserFullName = $currentUser['email'];
	}
		
?><!DOCTYPE html>
<!--[if lt IE 7]> <html class="lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]> <html class="lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]> <html class="lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="en"> <!--<![endif]-->
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title>Digital Lab Assistant | Review Flagged Labs</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/table.css">
  <link rel="stylesheet" href="css/prettyBorders.css">
  <!--[if lt IE 9]><script src="js/html5.js"></script><![endif]-->
</head>
<body>
  <section class="container">
<?php
if(isset($r_action))
{
	if(!LoginUtil::do_check_session())
	{
		LoginUtil::showLoginForm();	
	}
	else
	{
		switch($r_action)
		{
			case "do_logout":
				LoginUtil::do_logout();
			break;
			case "review_lab":				
				if(isset($r_id) && is_numeric($r_id))
				{
					include ('inc/reviewflags_reviewlab.inc');	
				}
				else
				{
					include ('inc/reviewflags_index.inc');	
				}			
			break;
			case "review_flag":				
				if(isset($r_id, $r_flag_id) && is_numeric($r_id) && is_numeric($r_flag_id))
				{
					if(isset($r_subaction) && $r_subaction == "post")
					{
						$r_reviewer_action = @$_REQUEST['actionList'];
						$r_reviewer_notes = @$_REQUEST['reviewer_notes'];
						if(isset($r_reviewer_action, $r_reviewer_notes))
						{
							$validActions = array("dismiss", "delete", "ban");
							$errList = "";
							$errCount = 0;
							if(!in_array($r_reviewer_action, $validActions))
							{
								$errList .= "<br>- Invalid action!";
								++$errCount;
							}
							/*
							// Never gonna be reached
							elseif(strlen(trim($r_reviewer_action)) == 0)
							{
								$errList .= "<br>- Please select an action!";
								++$errCount;
							}
							*/
							
							if(strlen(trim($r_reviewer_notes)) == 0)
							{
								$errList .= "<br>- Reviewer notes cannot be blank!";
								++$errCount;
							}
							
							if($errCount > 0)
							{
								$errMessage = "There " . ($errCount == 1 ? "is" : "are") . " $errCount " . ($errCount == 1 ? "error" : "errors") . ":" . $errList;
								include ('inc/reviewflags_reviewflag.inc');	
							}
							else
							{
								include ('inc/reviewflags_processflag.inc');
							}
						}
						else
						{
							$errMessage = "There are 2 errors:<br>- Please select an action!<br>- Reviewer notes cannot be blank!";
							include ('inc/reviewflags_reviewflag.inc');	
						}
					}
					else
					{
						// If parameters are valid but there is no subaction
						include ('inc/reviewflags_reviewflag.inc');	
					}
				}
				else
				{
					// if(isset($r_id, $r_flag_id) && is_numeric($r_id) && is_numeric($r_flag_id)) == false
					// If any of the parameters is invalid
					include ('inc/reviewflags_index.inc');	
				}			
			break;
			case "index":				
				if(isset($r_subaction) && $r_subaction == "show_reviewed")
				{
					if($currentUser['login_type'] == "cookie")
					{
						setcookie('show_reviewed', true, time() + 3600 * 24 * 365 * 2);
					}
					elseif($currentUser['login_type'] == "session")
					{
						@$_SESSION['show_reviewed'] = true;		
					}
					$showReviewed = true;	
				}
				elseif(isset($r_subaction) && $r_subaction == "show_unreviewed")
				{
					if($currentUser['login_type'] == "cookie")
					{
						setcookie('show_reviewed', false, time() + 3600 * 24 * 365 * 2);
					}
					elseif($currentUser['login_type'] == "session")
					{
						@$_SESSION['show_reviewed'] = false;		
					}
					$showReviewed = false;	
				}
			default:
				include ('inc/reviewflags_index.inc');	
			break;				
		}
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
		include ('inc/reviewflags_index.inc');	
	}
}		
	  ?>

  </section>

</body>
</html><?php
	ob_end_flush();
	// flush output buffer
?>