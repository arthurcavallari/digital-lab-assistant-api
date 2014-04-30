    <div class="login" style="width:450px;">
      <h1>Choose a new password</h1>
      <p>A strong password is a combination of letters and punctuation marks. It must be at least 6 characters long.</p>
      <form method="post" action="resetpassword.php">
        <input type="hidden" name="action" value="do_reset_password_form" />
<?php
    if(isset($_REQUEST['email'], $_REQUEST['activation_code']) && strlen(trim($_REQUEST['email'])) > 0 && strlen(trim($_REQUEST['activation_code'])) > 0)
    {
echo '      <input type="hidden" name="email" value="' . @$_REQUEST['email'] . '" />
      <input type="hidden" name="activation_code" value="' . @$_REQUEST['activation_code'] . '" />';
    }
    else
    {
echo '        <p>
        <label>
          Activation code:
          <input type="text" name="activation_code" value="" placeholder="Activation code" style="width:420px;" />
        </label>
        </p>
        <p>
        <label>
          Email ddress:
          <input type="text" name="email" value="" placeholder="Email address" style="width:420px;" />
        </label>
        </p>';
    }
    
    ?>
        
        <p>
        <label>
          New password:
          <input type="password" name="password" value="" placeholder="New password" style="width:420px;" />
        </label>
        </p>        
        <p>
        <label>
          Confirm new password:
          <input type="password" name="password_confirmation" value="" placeholder="Confirm new password" style="width:420px;" />
        </label>
        </p>
        <label style="color:#F00;"><?php echo $errMessage; ?></label>
        <p class="submit"><input type="submit" name="commit" value="Reset Password" /></p>      
      </form>
    </div>
