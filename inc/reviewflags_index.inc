<?php 

	$db = new DatabaseHandler();
	$db->initialize();
	//($strTable, $strJoin, $arFields = '*', $strWhere = '', $strOrder = '',$intRecords=10, $intPage = 1 )
	
	$fields[] = 'l.*';
	$fields[] = 'u.firstName';
	$fields[] = 'u.lastName';
	$fields[] = 'u.email';
	$fields[] = 'concat_ws(" ", l.authorFirstName, l.authorLastName) as authorFullName';
	$fields[] = '(select count(laboratory_id) from user_flags where laboratory_id = l.id group by laboratory_id) as countReports';
	$whereReviewed = "";
	if($showReviewed)
	{
		$whereReviewed = "l.id IN (select distinct laboratory_id from user_flags where reviewed = 1)";
	}

	$where = "l.id IN (select distinct laboratory_id from user_flags where reviewed = 0)";
	
	
	$flaggedLabs = $db->QueryJoinArray('laboratories l', 'left join users u on u.id=l.owner_user_id', $fields, $where, "l.owner_user_id, l.id");
	
	$flaggedLabsReviewed = false;
	if($whereReviewed != "" && $showReviewed)
	{
		$flaggedLabsReviewed = $db->QueryJoinArray('laboratories l', 'left join users u on u.id=l.owner_user_id', $fields, $whereReviewed, "l.owner_user_id, l.id");
	}

	//var_dump($flaggedLabs);
	$flaggedCount = 0;
	if($flaggedLabs != false)
	{
		$flaggedCount = count($flaggedLabs);
	}
	$flaggedReviewedCount = 0;
	if($flaggedLabsReviewed != false)
	{
		$flaggedReviewedCount = count($flaggedLabsReviewed);
	}
	
?>      <div class="prettyBorders">
      <h1>Control Panel</h1>
      <?php 
	  if(isset($errMsg))
	  {
		  echo "<p style='color:#F00;'>ERROR: $errMsg</p>";
	  }
	  ?>
      <?php 
	  if($flaggedCount > 0) 
	  { 
	  	$prefix = "are"; 
		if($flaggedCount == 1) 
		{
			$prefix = "is";
		} 
		echo "There $prefix $flaggedCount unreviewed flagged laboratories. "; 
	  } 
	  else 
	  { 
	  	echo "There are no unreviewed flagged laboratories. "; 
	  } 
	  if($showReviewed)
	  {
		  if($flaggedReviewedCount > 0) 
		  { 
			$prefix = "are"; 
			if($flaggedReviewedCount == 1) 
			{
				$prefix = "is";
			} 
			echo "There $prefix $flaggedReviewedCount already reviewed flagged laboratories."; 
		  } 
		  else 
		  { 
			echo "There are no already reviewed flagged laboratories."; 
		  } 
	  }
	  ?>
      <?php 
		if($showReviewed == false) 
		{ 
			echo '<span style="float:right;"><a href="reviewflags.php?action=index&subaction=show_reviewed">Show reviewed labs</a></span>'; 
		}
		else
		{
			echo '<span style="float:right;"><a href="reviewflags.php?action=index&subaction=show_unreviewed">Show only unreviewed Labs</a></span>'; 
		}
	  ?>
      <p>
      <table class="prettyTable">
          <thead>
            <tr>
              <th class="item" colspan="1">Flagged Laboratories</th>
              <th class="item" colspan="1">Details</th>
              <th class="qty">Reports</th>
              <th class="price">Actions</th>
            </tr>
          </thead>
          <tbody>
<?php 

	if($flaggedLabs != false && $flaggedCount > 0)
	foreach($flaggedLabs as $lab)
	{
		$author = trim($lab['authorFullName']);
		if(strlen($author) == 0)
		{
			$author = trim($lab['firstName'] . " " . $lab['lastName']);
		}
		if(strlen($author) == 0)
		{
			$author = trim($lab['email']);
		}
		if(strlen($lab['organisation']) > 0)
		{
			$author .= " ({$lab['organisation']})"; 
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
              <td class="item"><?php echo $lab['title']; ?>

              <span>By <?php echo $author; ?></span></td>
              <td class="centerText"><?php echo $description; ?>
              <?php echo $topic; ?>
              <?php echo $area; ?></td>
              <td class="reportCount centerText"><?php echo $countReports; ?></td>
              <td class="actions centerText"><a href="?action=review_lab&id=<?php echo $lab['id']; ?>">Review</a></td>
            </tr>
        <?php
	}
	if($showReviewed && $flaggedLabsReviewed != false && $flaggedReviewedCount > 0)
	foreach($flaggedLabsReviewed as $lab)
	{
		$author = trim($lab['authorFullName']);
		if(strlen($author) == 0)
		{
			$author = trim($lab['firstName'] . " " . $lab['lastName']);
		}
		if(strlen($author) == 0)
		{
			$author = trim($lab['email']);
		}
		if(strlen($lab['organisation']) > 0)
		{
			$author .= " ({$lab['organisation']})"; 
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
              <td class="item"><?php echo $lab['title']; ?>

              <span>By <?php echo $author; ?></span></td>
              <td class="centerText"><p style='color:#F00;'>Already reviewed!</p>
			  <?php echo $description; ?>
              <?php echo $topic; ?>
              <?php echo $area; ?></td>
              <td class="reportCount centerText"><?php echo $countReports; ?></td>
              <td class="actions centerText"><a href="?action=review_lab&id=<?php echo $lab['id']; ?>">Review</a></td>
            </tr>
        <?php
	}

?>

          </tbody>
          <tfoot>

            <tr class="tableSubFooter">
              <td colspan="4"></td>
            </tr>
            <tr class="tableFooter">
              <td colspan="4"></td>
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