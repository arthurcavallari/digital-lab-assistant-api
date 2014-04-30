	<div class="login" style="width:550px; height:340px">
      <h1>Reset Password</h1>
     	<p>
			<span style="font-weight:bold;">Check your email!</span>
            <p>We have sent you an email with instructions on how to reset your password.</p>
            <p>There are 2 ways for you to reset your password:</p>
            <p>
                <ol>
                    <li>Click the link given on the email and follow the prompts.</li>
                    <li>Enter the activation code found in the email in the space below and continue.</li>
                </ol>
            </p>
<?php
	/**
	 * Show the activation code form for a given email, displays an error message if $errMessage != "&nbsp;" 
	 * @param string $email
	 * @param string $errMessage
	 */
	function showActivationCodeForm($email, $errMessage = "&nbsp;")
	{
		include('inc/activation_code_form.inc');
	}
	
	$p_email = @$_REQUEST['email'];
	$p_activation_code = @$_REQUEST['activation_code'];	
	
	if(isset($p_email, $p_activation_code))
	{
		if(strlen(trim($p_email)) == 0 || strlen(trim($p_activation_code)) == 0)
		{
			showActivationCodeForm($email, "Activation code is invalid!");
		}
		else
		{
			$db = new DatabaseHandler();
			$db->initialize();
			
			$result = $db->query('users', '*', "email = '{$p_email}'");
			if($row = mysql_fetch_array ($result))
			{
				//echo "user_id = '{$row['id']}'";
				$result = $db->query('user_reset_code', '*', "user_id = '{$row['id']}'");
				if($row = mysql_fetch_array ($result))
				{
	
					// yay
					$reset_code = $row['resetcode'];
					$reset_date = new DateTime($row['expiry_date']);
					$uid = $row['user_id'];
					$now   = new DateTime;
					if($reset_code == $p_activation_code && $reset_code != "" && $reset_code != NULL)
					{					
						if($reset_date > $now)
						{
							header ('Location: resetpassword.php?action=do_reset_password_form&email=' . $p_email . '&activation_code=' . $p_activation_code);
						}
						else
						{
							header ('Location: resetpassword.php?action=expired_code');
						}
					}
					else
					{
						header ('Location: resetpassword.php?action=invalid_code');
					}							
				}
				else
				{
					header ('Location: resetpassword.php?action=no_request');
				}
			}
			else
			{
				header ('Location: resetpassword.php?action=email_not_found');
			}
		}
	}
	else
	{
		showActivationCodeForm($email);
	}
?>

        </p>
    </div>
