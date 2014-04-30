<?php
	ob_start();

  $username = "dlaadmin";
  $password = "password123buddy";

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Digital Lab Assistant ~ Install Control Panel</title>
</head>

<body bgcolor="#CCCCCC">
<?php
/**
 * Installs the database tables, etc
 */
function InstallWebsite()
{
	require_once ('globals.php');
	require_once ('db/DatabaseHandler.php');

	@include('installed.php');
	if(isset($installed) && $installed == 1)
	{
		echo "Website has already been installed!<br>
		Contact the administrator to verify the case.";
	}
	else
	{		
		// set your infomation.
		$dbhost = $GLOBALS['db_server']; // most likely, this will be localhost 
		$dbname = $GLOBALS['db_name']; // enter the name of your database      
		$dbuser = $GLOBALS['db_username']; // enter your db username           
		$dbpass = $GLOBALS['db_password']; // enter your db password           
	
		echo "Initializing installation...<br><br>";
		// connect to the mysql database server.
		echo "Connectiong to MySQL server...<br>";
		
		$dbh=mysql_connect ("$dbhost", "$dbuser", "$dbpass") or die ('ERROR: I cannot connect to the MySQL server because: ' . mysql_error());
		echo "Connection to MySQL was server successful!<br>";
		echo "...<br>";
		
		if(mysql_select_db ("$dbname") == FALSE)
		{
			echo "Creating database '$dbname'...<br>";	
			// create the database.
			if (!mysql_query("CREATE DATABASE $dbname")) die('ERROR: I cannot create the database because: ' . mysql_error());
			echo "Database created successfully!<br><br>";
			
			mysql_select_db ("$dbname") 
			or die("Select DB Error: " . mysql_error());
		}
		
	
		echo "Creating tables in '$dbname'...<br>";
		echo "...<br>";
	
		echo "Creating table: 'users' ...<br>";
		mysql_query("CREATE TABLE `users` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `username` varchar(255) NOT NULL,
					  `password` varchar(255) NOT NULL,
					  `firstName` varchar(60) DEFAULT NULL,
					  `lastName` varchar(60) DEFAULT NULL,
					  `organisation` varchar(80) DEFAULT NULL,
					  `email` varchar(255) NOT NULL,
					  `country` varchar(60) DEFAULT NULL,
					  `isAdmin` tinyint(1) NOT NULL DEFAULT '0',
					  `dateTimeRegistered` datetime NOT NULL,
					  `dateTimeLastUpdated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `username_UNIQUE` (`username`)
					) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;")
		or die("Create table 'users' Error: ".mysql_error());		
		echo "Table 'users' created successfully!<br><br>";
		
		echo "Adding primary admin user...<br>";
		$sql = "INSERT INTO `users` VALUES (1,'admin@example.com','5f4dcc3b5aa765d61d8327deb882cf99','John','Smith','Company','admin@example.com','Australia',1,'" . date("Y-m-d H:i:s") . "','0000-00-00 00:00:00');";
		mysql_query($sql,$dbh) or die ("Insert primary admin user error: " . mysql_error());
		echo "Primary admin user added successfully!<br><br>";
		
		echo "Creating table: 'user_sessions' ...<br>";
		mysql_query("CREATE TABLE `user_sessions` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `user_id` int(11) NOT NULL,
					  `session_id` varchar(80) NOT NULL,
					  `ip` varchar(80) NOT NULL,
					  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					  PRIMARY KEY (`id`,`user_id`,`session_id`),
					  UNIQUE KEY `session_id_UNIQUE` (`session_id`)
					) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;")
		or die("Create table 'user_sessions' Error: ".mysql_error());
		echo "Table 'user_sessions' created successfully!<br><br>";
	
		echo "Creating table: 'user_reset_code' ...<br>";
		mysql_query("CREATE TABLE `user_reset_code` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `user_id` int(11) NOT NULL,
					  `resetcode` varchar(80) NOT NULL,
					  `expiry_date` datetime NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=MyISAM DEFAULT CHARSET=latin1;")
		or die("Create table 'user_reset_code' Error: ".mysql_error());
		echo "Table 'user_reset_code' created successfully!<br><br>";
	
		echo "Creating table: 'user_flags' ...<br>";
		mysql_query("CREATE TABLE `user_flags` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `user_id` int(11) NOT NULL,
					  `laboratory_id` int(11) NOT NULL,
					  `notes` varchar(255) DEFAULT NULL,
					  `reviewed` tinyint(4) DEFAULT '0',
					  `reviewer_user_id` int(11) DEFAULT NULL,
					  `reviewer_notes` varchar(255) DEFAULT NULL,
					  `reviewer_action` varchar(45) DEFAULT NULL,
					  PRIMARY KEY (`id`),
					  KEY `userId_idx` (`user_id`),
					  KEY `labId_idx` (`laboratory_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1;")
		or die("Create table 'user_flags' Error: ".mysql_error());
		echo "Table 'user_flags' created successfully!<br><br>";
		
		echo "Creating table: 'user_favourites' ...<br>";
		mysql_query("CREATE TABLE `user_favourites` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `user_id` int(11) NOT NULL,
					  `favouritedUser_id` int(11) NOT NULL,
					  PRIMARY KEY (`id`),
					  KEY `userId_idx` (`user_id`),
					  KEY `favouritedUserId_idx` (`favouritedUser_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1;")
		or die("Create table 'user_favourites' Error: ".mysql_error());
		echo "Table 'user_favourites' created successfully!<br><br>";
				
		echo "Creating table: 'laboratories' ...<br>";
		mysql_query("CREATE TABLE `laboratories` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `owner_user_id` int(11) NOT NULL,
					  `title` varchar(80) NOT NULL,
					  `authorFirstName` varchar(60) NOT NULL,
					  `authorLastName` varchar(60) NOT NULL,
					  `description` varchar(200) DEFAULT NULL,
					  `topic` varchar(100) NOT NULL,
					  `area` varchar(100) NOT NULL,
					  `organisation` varchar(80) DEFAULT NULL,
					  `pages` int(11) NOT NULL DEFAULT '1',
					  `isPublished` tinyint(1) NOT NULL,
					  `dateTimeCreated` datetime NOT NULL,
					  `dateTimeLastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					  `dateTimePublished` datetime DEFAULT NULL,
					  `lastWidgetCounter` int(11) DEFAULT '0',
					  PRIMARY KEY (`id`),
					  KEY `authorId_idx` (`owner_user_id`)
					) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;")
		or die("Create table 'laboratories' Error: ".mysql_error());
		echo "Table 'laboratories' created successfully!<br><br>";
			
		echo "Creating table: 'laboratory_fields' ...<br>";
		mysql_query("CREATE TABLE `laboratory_fields` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `w_id` varchar(45) NOT NULL,
					  `laboratory_id` int(11) NOT NULL,
					  `fieldType` int(11) NOT NULL,
					  `posZ` int(11) NOT NULL DEFAULT '0',
					  `posX` int(11) NOT NULL DEFAULT '0',
					  `posY` int(11) NOT NULL DEFAULT '0',
					  `width` int(11) NOT NULL,
					  `height` int(11) NOT NULL,
					  `pageNumber` int(11) NOT NULL,
					  `label` varchar(120) DEFAULT NULL,
					  `value` longblob,
					  `readOnly` tinyint(1) NOT NULL DEFAULT '0',
					  `table_id` int(11) DEFAULT NULL COMMENT 'references to a table block',
					  `tableCellRow` int(11) DEFAULT NULL COMMENT 'specific to blocks linked to a table',
					  `tableCellColumn` int(11) DEFAULT NULL COMMENT 'specific to blocks linked to a table',
					  `tableRowCount` int(11) DEFAULT NULL COMMENT 'property of the table block',
					  `tableColumnCount` int(11) DEFAULT NULL COMMENT 'property of the table block',
					  `timerType` tinyint(4) DEFAULT NULL,
					  `isStoppable` tinyint(1) DEFAULT NULL,
					  `isPausable` tinyint(1) DEFAULT NULL,
					  `frameWidth` int(11) DEFAULT NULL,
					  `frameHeight` int(11) DEFAULT NULL,
					  PRIMARY KEY (`id`),
					  KEY `labId_idx` (`laboratory_id`),
					  KEY `fk_fields_idx` (`table_id`)
					) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;")
		or die("Create table 'laboratory_fields' Error: ".mysql_error());
		echo "Table 'laboratory_fields' created successfully!<br><br>";

		echo "Creating table: 'tableinfo' ...<br>";
		mysql_query("CREATE TABLE `tableinfo` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `type` tinyint(4) NOT NULL COMMENT '0 - row.... 1- col',
					  `title` varchar(30) NOT NULL COMMENT 'col or row title',
					  PRIMARY KEY (`id`),
					  KEY `fk_table_has_tableInfo_idx` (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1;")
		or die("Create table 'tableinfo' Error: ".mysql_error());
		echo "Table 'tableinfo' created successfully!<br><br>";
			
		echo "Creating table: 'submissions' ...<br>";
		mysql_query("CREATE TABLE `submissions` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `user_id` int(11) NOT NULL,
					  `laboratory_id` int(11) NOT NULL,
					  `authorFirstName` varchar(60) NOT NULL,
					  `authorLastName` varchar(60) NOT NULL,
					  `dateTimeCreated` datetime NOT NULL,
					  `dateTimeLastUpdated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					  `isSubmitted` tinyint(1) NOT NULL DEFAULT '0',
					  `dateTimeSubmitted` datetime DEFAULT NULL,
					  `dateTimeAssessed` datetime DEFAULT NULL,
					  PRIMARY KEY (`id`),
					  KEY `authorId_idx` (`user_id`),
					  KEY `labId_idx` (`laboratory_id`)
					) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;")
		or die("Create table 'submissions' Error: ".mysql_error());
		echo "Table 'submissions' created successfully!<br><br>";
	
		echo "Creating table: 'submission_fields' ...<br>";
		mysql_query("CREATE TABLE `submission_fields` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `submission_id` int(11) NOT NULL,
					  `field_id` varchar(45) NOT NULL,
					  `value` longblob,
					  `assessmentNotes` blob,
					  PRIMARY KEY (`id`),
					  KEY `submissionId_idx` (`submission_id`),
					  KEY `field_idx` (`field_id`)
					) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;")
		or die("Create table 'submission_fields' Error: ".mysql_error());
		echo "Table 'submission_fields' created successfully!<br><br>";
	
		echo "Creating table: 'laboratory_sessions' ...<br>";
		mysql_query("CREATE TABLE `laboratory_sessions` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `user_id` int(11) NOT NULL,
					  `lab_id` int(11) NOT NULL,
					  `startDateTime` datetime NOT NULL,
					  `endDateTime` datetime DEFAULT NULL,
					  PRIMARY KEY (`id`),
					  KEY `fk_user_doing_lab_idx` (`user_id`),
					  KEY `fk_session_of_lab_idx` (`lab_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1;")
		or die("Create table 'laboratory_sessions' Error: ".mysql_error());
		echo "Table 'laboratory_sessions' created successfully!<br><br>";

		echo "Creating table: 'image_uploads' ...<br>";
		mysql_query("CREATE TABLE `image_uploads` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `w_id` varchar(45) DEFAULT NULL,
					  `checksum` varchar(120) DEFAULT NULL,
					  `dateTimeLastUpdated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					  `submission_id` int(11) DEFAULT '-1',
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1;")
		or die("Create table 'image_uploads' Error: ".mysql_error());
		echo "Table 'image_uploads' created successfully!<br><br>";
		
		echo "Creating table: 'deleted_laboratories' ...<br>";
		mysql_query("CREATE TABLE `deleted_laboratories` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `laboratory_id` int(11) NOT NULL,
					  `flag_id` int(11) NOT NULL,
					  `deleted_by_user_id` int(11) NOT NULL,
					  `reason` varchar(255) NOT NULL,
					  PRIMARY KEY (`id`),
					  KEY `labId_idx` (`laboratory_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1;")
		or die("Create table 'deleted_laboratories' Error: ".mysql_error());
		echo "Table 'deleted_laboratories' created successfully!<br><br>";
		
		echo "Creating table: 'banned_users' ...<br>";
		mysql_query("CREATE TABLE `banned_users` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `user_id` int(11) NOT NULL,
					  `flag_id` int(11) NOT NULL,
					  `banned_by_user_id` int(11) NOT NULL,
					  `reason` varchar(255) NOT NULL,
					  PRIMARY KEY (`id`),
					  KEY `user_id_idx` (`user_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1;")
		or die("Create table 'banned_users' Error: ".mysql_error());
		echo "Table 'banned_users' created successfully!<br><br>";
	
		echo "...<br>";
		echo 'Installation completed!<br><br>';
	
		$fp = fopen("install/installed.php","w");
		fwrite($fp, 
'<?php
	$installed = 1;
?>');
		fclose($fp);
	} // end if($installed != 1)
} // end function InstallWebsite()

	$l_username = @$_POST['l_username'];
	$l_password = @$_POST['l_password'];

	if($l_username == $username and $l_password == $password)
	{
		InstallWebsite(); 
	}
	else
	{
		  echo '<form method=post><table width="25%" border="1" cellpadding="2" cellspacing="4" align="center">
	<tr>
	<td width="10%">
		  Username:</td><td><input type="text" name="l_username" size="23"></td>
	</tr>
	<tr>
	<td>
			Password:</td><td><input type="password" name="l_password" size="23"></td>
	</tr>
	<tr>
	<td colspan="2" align="center">		
			<input type="submit" value="Login">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="reset" value="Reset">
	</td>
	</tr>
	</table>
	</form>';
		}
?>
</body>
</html><?php
  ob_end_flush();
?>