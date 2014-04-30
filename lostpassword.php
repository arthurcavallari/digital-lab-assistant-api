<?php
ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);
	date_default_timezone_set('Australia/Melbourne');
ob_start();
require_once ('globals.php');
require_once ('db/DatabaseHandler.php');

$db = new DatabaseHandler();
$db->initialize();

$r_action = @$_REQUEST['action'];
$uid = @$_REQUEST['uid'];
$code = @$_REQUEST['code'];
$p_email = @$_REQUEST['email'];
$p_password = @$_REQUEST['password'];
$p_password_check = @$_REQUEST['password_check'];
echo '
<!DOCTYPE HTML PUBLIC >
<html>
<head>
<title>Digital Lab Assistant - Password Recovery</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>

<body>

<h1>Lost Password</h1>
<hr><br>
';

	if(isset($r_action,$code) && $r_action == "resetpassword")
	{
		if($p_password == $p_password_check && isset($p_password,$p_password_check) && $p_password != "")
		{
			if((isset($uid) && is_numeric($uid)))
			{
				
				//$result = mysql_query ("SELECT p.* FROM exhibitors p WHERE p.id='$uid' limit 1",$dbh) or die(mysql_error());
				//$result = $db->query('users', '*', "id = {$uid}");	
				$result = $db->query('user_reset_code', '*', "user_id = '{$uid}'");	
				if($row = mysql_fetch_array ($result))
				{
					// yay
					$reset_code = $row['resetcode'];
					$reset_date = new DateTime($row['expiry_date']);
					$now = new DateTime;
					if($reset_code == $code && $reset_code != "" && $reset_code != NULL)
					{					
						if($reset_date > $now)
						{
							//$updatecode = mysql_query ("UPDATE `exhibitors`	SET `password`='".md5($p_password) . "',`reset_code`='',`reset_date`='0000-00-00 00:00:00' WHERE `id`='$uid';",$dbh) or die(mysql_error());
							$db->update('users', array ('password' => md5($p_password)), "id = '{$uid}'");
							$db->delete('user_reset_code', "user_id = '{$uid}'");
							echo "Password changed successfully!";
						}
						else
						{
							echo "Your reset code has expired, please re-submit your lost password request!";	
						}
					}
					else
					{
						echo "The reset code provided is invalid, please re-submit your lost password request!";
					}					
				}
				else
				{
					echo "This user has not requested a password recovery!";	
				}				
			}
			else
			{
				echo "Invalid user id!";	
			}
		}
		elseif((isset($uid) && is_numeric($uid)) || (isset($p_email) && $p_email != ""))
		{
			if(isset($p_email) && !isset($uid))
			{
				//$result = mysql_query ("SELECT p.* FROM exhibitors p WHERE p.email='$p_email' limit 1",$dbh) or die(mysql_error());	
				$result = $db->query('users', '*', "email = '{$p_email}'");
				if($row = mysql_fetch_array ($result))
				{
					//echo "user_id = '{$row['id']}'";
					$result = $db->query('user_reset_code', '*', "user_id = '{$row['id']}'");
				}
				else
				{
					die ("Invalid email!");
				}
				
			}
			elseif(!isset($p_email) && isset($uid))
			{
				//$result = mysql_query ("SELECT p.* FROM exhibitors p WHERE p.id='$uid' limit 1",$dbh) or die(mysql_error());
				//$result = $db->query('users', '*', "id = '{$uid}'");
				$result = $db->query('user_reset_code', '*', "user_id = '{$uid}'");		
			}
			
			if($row = mysql_fetch_array ($result))
			{

				// yay
				$reset_code = $row['resetcode'];
				$reset_date = new DateTime($row['expiry_date']);
				$uid = $row['user_id'];
				$now   = new DateTime;
				if($reset_code == $code && $reset_code != "" && $reset_code != NULL)
				{					
					if($reset_date > $now)
					{
							
echo '
        <form method="post" action="lostpassword.php?action=resetpassword">
        <input type="hidden" name="code" value="<? echo $code; ?>">
        <input type="hidden" name="uid" value="<? echo $uid; ?>">
        <table>
        <tr>    
            <th style="text-align:right;" valign="top">New Password</th>
            <td><input type="password" name="password" id="password" /></td>
        </tr>
        <tr>    
            <th style="text-align:right;" valign="top">Repeat New Password</th>
            <td><input type="password" name="password_check" id="password_check" onChange="javascript:if(password.value != password_check.value){pw_validator.innerHTML=\'Passwords do not match!\';}else{pw_validator.innerHTML=\'\';} " /> <span id="pw_validator" style="color:#F00;"></span> </td>
        </tr>
        <tr>
        <td colspan="2" style="text-align:center;"><input type="submit" name="submit" id="submit" value="Submit" style="width:100px;" /></td>
        </tr>
        </table>
        </form>  
';
					}
					else
					{
						echo "Your reset code has expired, please re-submit your lost password request!";	
					}
				}
				else
				{
					echo "The reset code provided is invalid, please re-submit your lost password request!";
				}							
			}
			else
			{
				echo "This user has not requested a password recovery!";
			}
		}
		else
		{
			echo "Invalid user id!";	
		}   
		
	}
	else
	{
echo '
        <form method="post" action="lostpassword.php?action=resetpassword">
        <table class="hovertable">
        <tr>    
            <th style="text-align:right;" valign="top">Username/Email</th>
            <td><input type="text" name="email" id="email" /></td>
        </tr>
        <tr>    
            <th style="text-align:right;" valign="top">Activation Code</th>
            <td><input type="text" name="code" id="code" /></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:center;">
                <input type="submit" name="submit" id="submit" value="Submit" style="width:100px;" />
            </td>
        </tr>
        </table>
        </form>  
';	
		
	}
	
	echo '

</div>
</body>
</html>'; ?>