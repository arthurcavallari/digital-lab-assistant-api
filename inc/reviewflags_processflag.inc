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
		$lab = $flaggedLab[0];
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
	
		$flag =  $userFlags[0];		
		$reviewed = $flag['reviewed'];
		if($reviewed != 1 && $reviewed != '1')
		{
			$updateFields = array(
							'reviewer_user_id' => $currentUser['id'],
							'reviewer_notes' => $r_reviewer_notes,
							'reviewed' => '1',
							'reviewer_action' => $r_reviewer_action
							);
			$updateWhere = "uf.laboratory_id = '{$r_id}' AND uf.id = '{$r_flag_id}'";
			
			$id = $db->update('user_flags uf', $updateFields, $updateWhere);
			$mysqlErr = mysql_error();
			if (!$mysqlErr)
			{
				$mysqlActionErr = NULL;
				switch ($r_reviewer_action)
				{
					case 'dismiss':
						// Do nothing
					break;
					case 'delete':
						// add lab id to deleted_laboratories
						$insertFields = array(
											 'deleted_by_user_id' => $currentUser['id'],
											 'reason' => $r_reviewer_notes,
											 'laboratory_id' => $r_id,
											 'flag_id' => $r_flag_id
											 );
						$id = $db->insert('deleted_laboratories', $insertFields);
						$mysqlActionErr = mysql_error();
					break;
					case 'ban':
						$insertFields = array(
											 'banned_by_user_id' => $currentUser['id'],
											 'reason' => $r_reviewer_notes,
											 'user_id' => $lab['owner_user_id'],
											 'flag_id' => $r_flag_id
											 );
						$id = $db->insert('banned_users', $insertFields);
						$mysqlActionErr = mysql_error();
					break;
				}
				if (!$mysqlActionErr)
				{
					header('Location: reviewflags.php?action=review_lab&id=' . $r_id);
				}
				else
				{
					$errMessage = $mysqlActionErr;
					include ('inc/reviewflags_reviewflag.inc');	
				}				
			}
			else
			{
				$errMessage = $mysqlErr;
				include ('inc/reviewflags_reviewflag.inc');	
			}
		}
		else
		{
			$errMessage = "This user flag has already been reviewed!";
			include ('inc/reviewflags_reviewflag.inc');	
		}
	}
	else
	{
		$errMsg = "Invalid lab id, or selected lab has not been flagged!";
		include ('inc/reviewflags_index.inc');	
	}

?>