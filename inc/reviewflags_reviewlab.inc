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
	$fields[] = '(select laboratory_id from deleted_laboratories where laboratory_id = l.id group by laboratory_id) as labStatus';
	$fields[] = '(select user_id from banned_users where user_id = l.owner_user_id group by user_id) as authorStatus';
	
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
		
		$where = "uf.laboratory_id = '{$r_id}'";
		
		$userFlags = $db->QueryJoinArray('user_flags uf', 'left join users u on u.id=uf.user_id left join users u2 on u2.id=uf.reviewer_user_id', $fields, $where, "uf.id");
		$userFlagsCount = count($userFlags);
	
?>      <div class="prettyBorders">
      <h1>Control Panel</h1>
      <a href="reviewflags.php?action=index">Return to flagged labs</a>
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
		
		$labStatus = $lab['labStatus'];
		$authorStatus = $lab['authorStatus'];
		$status = "Available";
		
		if($authorStatus == $lab['owner_user_id'])
		{
			$status = "Author banned";
		}
		
		if($labStatus == $lab['id'])
		{
			if($status == "Available")
			{
				$status = "Laboratory deleted";
			}
			else
			{
				$status .= ", Laboratory deleted";
			}
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
            <tr>
              <td class="subHeader centerText">Status:</td>
              <td class="stock <?php if($status == "Available") { echo "in"; } else { echo "out"; } ?>"><?php echo $status; ?></td>
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
      User Flags:
      <p>      
      <table class="prettyTable">
          <thead>
            <tr>
              <th class="item" colspan="1">User</th>
              <th class="item" colspan="1">Notes</th>
              <th class="item" colspan="1">Reviewer</th>
              <th class="item" colspan="1">Reviewer Notes</th>
              <th class="item" colspan="1">Actions</th>
            </tr>
          </thead>
          <tbody>
<?php 

	 foreach ($userFlags as $flag)
	 {
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
			$reviewerNotes = "$reviewerNotes<span>Reviewer action = {$flag['reviewer_action']}</span>"; 
		}
		
			  
		?>
            <tr>
              <td class="centerText" style="width:15%;"><?php echo $userFullName; ?></td>
              <td style="width:30%;"><div style="overflow:auto; max-height:55px; "><?php echo $userNotes; ?></div></td>
              <td class="centerText" style="width:15%;"><?php echo $reviewerFullName; ?></td>
              <td style="width:30%;"><div style="overflow:auto; max-height:55px; "><?php echo $reviewerNotes; ?></div></td>
              <td class="centerText"><?php
			  if($flag['reviewed'] == 1 || $flag['reviewed'] == '1')
			  {
				  	echo "Already reviewed";
			  }
			  else
			  { 
              		echo '<a href="?action=review_flag&id=' . $flag['laboratory_id'] . '&flagid=' . $flag['id'] . '">Review</a>';
			  }
			  ?></td>
            </tr>
<?php 
	 } // foreach userFlags 
?>
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