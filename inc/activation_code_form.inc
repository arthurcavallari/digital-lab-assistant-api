            <form method="post" action="resetpassword.php">
            <input type="hidden" name="action" value="do_processcode" />
            <input type="hidden" name="email" value="<?php echo $email; ?>" />
                <p>
                <label>
                    <span style="float:right; "><span style="font-weight:bold;">We have sent the code to:</span><br>- <?php echo $email; ?></span>
                    <input type="text" name="activation_code" value="" placeholder="Activation code" class="largePlaceholder" style="width:260px; font-size:24px" />
                </label>
                </p>
                <br>
                <label style="color:#F00;"><?php echo $errMessage; ?></label>
               <p style="position:relative; bottom:10px; left:-20px; width:590px" class="submit footerButtons">
                   <input type="submit" name="commit" value="Continue" />
                   <input type="submit" name="commit" value="Cancel" />
               </p>
            </form>