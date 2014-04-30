<?php
	
	$uploadDirectory = "../uploadedfiles/";
	
	if(is_uploaded_file(@$_FILES['file']['tmp_name']))
	{
		$uploadedFile = $uploadDirectory . basename(@$_FILES['file']['name']);	
		
		if(move_uploaded_file(@$_FILES['file']['tmp_name'], $uploadedFile))
		{
			// success	
		}
		else
		{
			// failed to upload	
		}
	}
	
?>