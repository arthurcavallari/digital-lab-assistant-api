    <div class="login">
      <h1>Login to Digital Lab Assistant</h1>
      <form method="post" action="login.php">
      <input type="hidden" name="action" value="do_login" />
        <p><input type="text" name="email" value="" placeholder="Email Address" /></p>
        <p><input type="password" name="password" value="" placeholder="Password" /></p>
        <p class="remember_me">
          <label>
            <input type="checkbox" name="remember_me" id="remember_me">
            Remember me on this computer
          </label>
        </p>
		<p><label style="color:#F00;"><?php if(strlen($errMessage) > 0 && $errMessage != "&nbsp;") echo "<br><br>{$errMessage}"; ?></label></p>
        <p class="submit"><input type="submit" name="commit" value="Login" /></p>
      </form>
    </div>

    <div class="login-help">
      <p>Forgot your password? <a href="resetpassword.php">Click here to reset it</a>.</p>
    </div>