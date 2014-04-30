	<div class="login">
      <h1>Reset Password</h1>
      <form method="post" action="resetpassword.php">
      <input type="hidden" name="action" value="do_resetpassword_request" />
        <p>
        	<label>Email address:
            <input type="text" name="email" value="" placeholder="Email address" />
        	</label>
        </p>
     	<div style="position:relative;">
          <p style="position:absolute; top:-20%;">
            	<label>Verification image:<br>
            	<input type="text" name="verification" value="" placeholder="Enter the 5 characters" style=" width:150px;"/>
                </label>
            </p>
            <p style="text-align:right; right:0;"><?php echo '<img src="data:image/png;base64,' . AntiSpam::GetAntiSpamImageBase64() . '" alt="Verification Image" width="150" height="50"/>'; ?></p>
         </div>
        <p>        
          
        </p>
        <label style="color:#F00;"><?php echo $errMessage; ?></label>
        <p class="submit"><input type="submit" name="commit" value="Send Reset Password Instructions" /></p>      
      </form>
    </div>