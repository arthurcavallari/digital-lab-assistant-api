<?php 
	$db = new DatabaseHandler();
	$db->initialize();
	//($strTable, $strJoin, $arFields = '*', $strWhere = '', $strOrder = '',$intRecords=10, $intPage = 1 )
	
	$fields = NULL;
	$fields[] = 'l.*';
	$fields[] = 'u.firstName';
	$fields[] = 'u.lastName';
	$fields[] = 'u.email';
	$fields[] = 'concat_ws(" ", l.authorFirstName, l.authorLastName) as authorFullName';
	$fields[] = '(select count(laboratory_id) from user_flags where laboratory_id = l.id group by laboratory_id) as countReports';
	
	$where = "l.id IN (select distinct laboratory_id from user_flags) AND l.id = '{$r_id}'";
	
	$flaggedLab = $db->QueryJoinArray('laboratories l', 'left join users u on u.id=l.owner_user_id', $fields, $where, "l.owner_user_id, l.id");
	//var_dump($flaggedLab);
	$flaggedCount = count($flaggedLab);
	
	if($flaggedLab != false && $flaggedCount == 1)
	{
		$fields = NULL;
		
		$fields[] = 'uf.*';
		
		$fields[] = 'u.firstName as userFirstName';
		$fields[] = 'u.lastName as userLastName';
		$fields[] = 'u.email as userEmail';
		$fields[] = 'concat_ws(" ", u.firstName, u.lastName) as userFullName';
		
		$fields[] = 'u2.firstName as reviewerFirstName';
		$fields[] = 'u2.lastName as reviewerLastName';
		$fields[] = 'u2.email as reviewerEmail';
		$fields[] = 'concat_ws(" ", u2.firstName, u2.lastName) as reviewerFullName';
		
		$where = "uf.laboratory_id = '{$r_id}' AND uf.id = '{$r_flag_id}'";
		
		$userFlags = $db->QueryJoinArray('user_flags uf', 'left join users u on u.id=uf.user_id left join users u2 on u2.id=uf.reviewer_user_id', $fields, $where, "uf.id");
		$userFlagsCount = count($userFlags);
	
?>      <div class="prettyBorders">
      <h1>Control Panel</h1>
      <a href="reviewflags.php?action=review_lab&id=<?php echo $r_id; ?>">Return to laboratory review</a>
      <p>
      <table class="prettyTable">
          <thead>
            <tr>
              <th class="item" colspan="6">Laboratory details</th>
            </tr>
          </thead>
          <tbody>
<?php 

	
		$lab = $flaggedLab[0];
		$author = trim($lab['authorFullName']);
		if(strlen($author) == 0)
		{
			$author = trim($lab['firstName'] . " " . $lab['lastName']);
		}
		if(strlen($author) == 0)
		{
			$author = trim($lab['email']);
		}
		$organisation = $lab['organisation'];
		if(strlen($organisation) > 0)
		{
			$organisation = "<span>$organisation</span>"; 
		}
		
		$description = $lab['description'];
		if(strlen($description) > 0)
		{
			$description = "<span>$description</span>"; 
		}
		$topic = $lab['topic'];
		if(strlen($topic) > 0)
		{
			$topic = "<span>$topic</span>"; 
		}
		$area = $lab['area'];
		if(strlen($area) > 0)
		{
			$area = "<span>$area</span>"; 
		}
		
		$countReports = $lab['countReports'];
		
			  
		?>
            <tr>
              <td class="subHeader centerText">Title:</td>
              <td><?php echo $lab['title']; ?></td>
            </tr>
            <tr>
              <td class="subHeader centerText">Author:</td>
              <td><?php echo $author; ?></td>
            </tr>
            <tr>
              <td class="subHeader centerText">Description:</td>
              <td><?php echo $description; ?></td>
            </tr>
            <tr>
              <td class="subHeader centerText">Topic:</td>
              <td><?php echo $topic; ?></td>
            </tr>
            <tr>
              <td class="subHeader centerText">Area:</td>
              <td class="item"><?php echo $area; ?></td>
            </tr>
            <tr>
              <td class="subHeader centerText">Organisation:</td>
              <td class="item"><?php echo $organisation; ?></td>
            </tr>
          </tbody>
          <tfoot>

            <tr class="tableSubFooter">
              <td colspan="5"></td>
            </tr>
            <tr class="tableFooter">
              <td colspan="5"></td>
            </tr>
          </tfoot>
        </table>
        </p>
      <p> 
      <div>
      <div style="float:left; width:49%; margin-right:10px;">
      <table style="width:100%;" class="prettyTable">
          <thead>
            <tr>
              <th class="item" colspan="2">User Flag</th>
            </tr>
          </thead>
          <tbody>
<?php 
		$flag =  $userFlags[0];
		$userFullName = trim($flag['userFullName']);
		if(strlen($userFullName) == 0)
		{
			$userFullName = trim($flag['userEmail']);
		}
		
		$reviewerFullName = trim($flag['reviewerFullName']);
		if(strlen($reviewerFullName) == 0)
		{
			$reviewerFullName = trim($flag['reviewerEmail']);
		}
		
		$userNotes = $flag['notes'];
		if(strlen($organisation) > 0)
		{
			$userNotes = "<span>$userNotes</span>"; 
		}
		
		$reviewerNotes = $flag['reviewer_notes'];
		if(strlen($reviewerNotes) > 0)
		{
			$reviewerNotes = "<span>$reviewerNotes</span>"; 
		}
		
			  
		?>
            <tr>
              <td class="subHeader centerText" style="width:14%; white-space: nowrap;">User</td>
              <td><?php echo $userFullName; ?></td>
            </tr>
            <tr>
              <td class="subHeader centerText" style="width:14%; white-space: nowrap; height:188px;">Notes</td>
              <td><div style="overflow:auto; max-height:188px; min-height:188px; "><?php echo $userNotes; ?></div></td>
            </tr>
          </tbody>
          <tfoot>

            <tr class="tableSubFooter">
              <td colspan="5"></td>
            </tr>
            <tr class="tableFooter">
              <td colspan="5"></td>
            </tr>
          </tfoot>
        </table>
        </div>
        <div style="float:right; width:49%;">   
		<script type="text/javascript">
			function validateForm(e)
			{
				var errorList = "";
				var i = 0;
				if(review_form.actionList.value == -1) 
				{
					++i;
					errorList = '- Please select an action!';
				}
				if(review_form.reviewer_notes.value == "")
				{
					if(errorList != "") errorList += '\n';
					++i;
					errorList += '- Reviewer notes cannot be blank!';
				}
				
				if(errorList != "")
				{
					//alert("There " + (i > 1 ? "are" : "is") + " " + i + " error" + (i > 1 ? "s" : "") + ":\n" + errorList);
					findLabelForControl('errMessage').innerHTML = "There " + (i > 1 ? "are" : "is") + " " + i + " error" + (i > 1 ? "s" : "") + ":<br>" + errorList.replace('\n', '<br>');
					return false;	
				}
				else
				{		
					return true;
				}
			}
			
			function findLabelForControl(el) {
				   labels = document.getElementsByTagName('label');
			   for( var i = 0; i < labels.length; i++ ) {
				  if (labels[i].id == el)
					   return labels[i];
			   }
			}

		</script>
        <form method="post" name="review_form" id="review_form" onsubmit="return validateForm(this);">
        <input type="hidden" name="action" value="review_flag" />
        <input type="hidden" name="subaction" value="post" />
       <table style="width:100%;float:right;" class="prettyTable">
          <thead>
            <tr>
              <th class="item" colspan="2">Review</th>
            </tr>
          </thead>
          <tbody>
<?php 
		$flag =  $userFlags[0];
		$userFullName = trim($flag['userFullName']);
		if(strlen($userFullName) == 0)
		{
			$userFullName = trim($flag['userEmail']);
		}
		
		$reviewerFullName = trim($flag['reviewerFullName']);
		if(strlen($reviewerFullName) == 0)
		{
			$reviewerFullName = trim($flag['reviewerEmail']);
		}
		
		$userNotes = $flag['notes'];
		if(strlen($organisation) > 0)
		{
			$userNotes = "<span>$userNotes</span>"; 
		}
		
		$reviewerNotes = $flag['reviewer_notes'];
		if(strlen($reviewerNotes) > 0)
		{
			$reviewerNotes = "<span>$reviewerNotes</span>"; 
		}
		
		
		?>
            <tr>
            	<td class="subHeader centerText" style="width:10px;">Reviewer</td>
            	<td><?php echo $currentUserFullName; ?></td>
            </tr>
            <tr>
            	<td class="subHeader centerText" style="width:10px;">Action<label style="color:#F00;">*</label></td>
            	<td>
                <select name="actionList" id="actionList" style="min-width:206px; max-width:206px;" form="review_form">
                    <option value="-1" selected="selected" style="font-style:italic; color: #666; ">(Select an action)</option>
                    <option value="dismiss">Dismiss Report</option>
                    <option value="delete">Delete Lab</option>
                    <option value="ban">Ban Lab Author</option>
                </select>
                </td>
            </tr>
            <tr>
              <td class="subHeader centerText" style="width:10px;">Notes<label style="color:#F00;">*</label></td>
              <td colspan="1">              
                  <label>
                  	Reviewer Notes
                    <textarea name="reviewer_notes" id="reviewer_notes" cols="44" style="max-width:375px; height:80px; max-height:80px;"></textarea>
                  </label>                      
        		  <p class="submit" style="margin-top:5px; text-align:right;">
                  <input type="submit" name="commit" value="Submit Review" /> <input type="button" name="commit" value="Cancel" style="margin-left:20px;" onclick='window.location.href = "reviewflags.php?action=review_lab&id=<?php echo $r_id; ?>";' />
                  </p>        
              </td>
            </tr>
            
          </tbody>
          <tfoot>

            <tr class="tableSubFooter">
              <td colspan="5"><p style="color:#F00; text-align:left;"><label name="errMessage" id="errMessage" form="review_form"><?php if(isset($errMessage) && strlen($errMessage) > 0 && $errMessage != "&nbsp;") echo "{$errMessage}"; ?></label></p>  </td>
            </tr>
            <tr class="tableFooter">
              <td colspan="5"></td>
            </tr>
          </tfoot>
        </table>   
        </form>     
        </div>
        <div style="clear:both;"></div>
        </div>
        </p>
        
        <div class="footerBox">
            <footer>
                <ul class="navBar">
                <li><a href="reviewflags.php?action=index">Home</a></li>
                <li><a href="login.php?action=do_logout">Logout</a></li>
                </ul>
            </footer>
        </div>
        </div>
<?php
	}
	else
	{
		$errMsg = "Invalid lab id, or selected lab has not been flagged!";
		include ('inc/reviewflags_index.inc');	
	}

?>