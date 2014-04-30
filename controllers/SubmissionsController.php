<?php
if(!isset($pathCheck))
{	
	if($_SERVER['HTTP_HOST'] == "127.0.0.1")
	{
		$GLOBALS['base_url'] = "/mydla/";
	}
	else
	{
		$GLOBALS['base_url'] = "/~sentinus/";
	}
	header('Location: ' . $GLOBALS['base_url'] . 'index.php?src=' . $_SERVER['REQUEST_URI']);
}

/**
 * Submissions Controller - handles all the submission-related actions
 * @author Arthur Cavallari
 * @version 1.0
 * @created 26-May-2013 7:34:56 PM
 */
class SubmissionsController extends BaseController
{
	/**
	 * Submits a submission - makes it uneditable
	 * @param Request $request
	 * @return Request
	 */
	public static function &submit(Request &$request)
	{
		if($request->processJson())
		{
			$resultLab = self::_submit($request->data);

			if($resultLab['error'] == NULL )
			{
				$request->setData((object) array('submission_submit' => 'success' ) );				
				$request->request_status = REQUEST_STATUS::HANDLED;
			}
			else
			{
				$request->request_status = REQUEST_STATUS::FAILED;   
				$failedRequest = new FailedRequest($resultLab['error']);
				$request->setData($failedRequest);
				// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
			}
		}
		else
		{
			$request->request_status = REQUEST_STATUS::FAILED;   
			$failedRequest = new FailedRequest($request->data->getErrors());
			$failedRequest->addReasonKey("inner_json_failed", "Failed to process request's JSON inner data.");
			$request->setData($failedRequest);
			// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
		}
		return $request;
	}
	
	/**
	 * Internal function - processes the submitting a submission
	 * @param SubmitSubmissionRequest $submission
	 * @return multitype:NULL unknown
	 */
	private static function _submit($submission)
	{
		self::initDB();
		$result = array();
		$result['error'] = NULL;
		$result['data'] = NULL;
		
		$dateTimeSubmitted = date("Y-m-d H:i:s");
		
		$fields['isSubmitted'] 			= 1;
		$fields['dateTimeSubmitted'] 	= $dateTimeSubmitted;		
		
		$id = self::$db->update('submissions', $fields	, "id = '{$submission->getSubmissionID()}'");
		
		if(isset($id) && $id !== NULL)
		{
			$arrList = self::$db->QueryArray('submissions', '*', "id = '{$submission->getSubmissionID()}'");
			$result['data'] = $arrList[0];
		}
		else
		{			
			$result['error'] = $db->getErrors();
		}	
		
		return $result;	
	}
	
	/**
	 * DEPRECATED - Creates a submission
	 * @param Request $request
	 * @return Request
	 */
	public static function &create(Request &$request)
	{
		if($request->processJson())
		{
			$result = self::_create($request->data->laboratory_id, $request->data->user_id);
			if($result['error'] == NULL)
			{
				$submission = $request->data;
				$submission->set($result['data']);
				$request->setData($submission);
				
				$request->request_status = REQUEST_STATUS::HANDLED;
			}
			else
			{
				$request->request_status = REQUEST_STATUS::FAILED;   
				$failedRequest = new FailedRequest($result['error']);
				$request->setData($failedRequest);
				// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
			}
		}
		else
		{
			$request->request_status = REQUEST_STATUS::FAILED;   
			$failedRequest = new FailedRequest($request->data->getErrors());
			$failedRequest->addReasonKey("inner_json_failed", "Failed to process request's JSON inner data.");
			$request->setData($failedRequest);
			// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
		}
		return $request;
	}
	
	/**
	 * Internal function - processes the creation of a submission for a lab
	 * @param string $lab_id
	 * @param string $user_id
	 * @return multitype:NULL unknown
	 */
	private static function _create($lab_id, $user_id)
	{
		self::initDB();
		$result = array();
		$result['error'] = NULL;
		$result['data'] = NULL;

		$dateTimeCreated = date("Y-m-d H:i:s");
		
		$userInfo = self::$db->QueryArray('users', '*', "id = '{$user_id}'");
		$userInfo = $userInfo[0];
		
		// Check if user already has a submission for that lab
		$arrList = self::$db->QueryArray('submissions s', '*', "s.laboratory_id = '{$lab_id}' AND s.user_id = '{$user_id}'");
			
		if($arrList == false || count($arrList) == 0)
		{
			$id = self::$db->insert('submissions', 
										array('user_id'=>$user_id, 
											  'laboratory_id'=>$lab_id, 
											  'authorFirstName'=>$userInfo['firstName'], 
											  'authorLastName'=>$userInfo['lastName'], 
											  'dateTimeCreated'=>$dateTimeCreated
											  )
									);
			
			if(isset($id) && $id !== NULL)
			{
				$arrList = self::$db->QueryArray('submissions', '*', "id = '{$id}'");
				$result['data'] = $arrList[0];
			}
			else
			{			
				$result['error'] = $db->getErrors();
			}	
		}
		else
		{
			$result['data'] = $arrList[0]; 
		}
		
		return $result;	
	}

	/**
	 * Deletes a submission
	 * @param Request $request
	 * @return Request
	 */
	public static function &delete(Request &$request)
	{
		if($request->processJson())
		{
			$resultWidgets = self::_deleteWidgets($request->data->lab_id, $request->data->getSubmissionID());
			$result = self::_delete($request->data->lab_id, $request->data->getSubmissionID());
			if($result['error'] == NULL && $resultWidgets['error'] == NULL)
			{					
				$request->request_status = REQUEST_STATUS::HANDLED;
				$request->setData((object) array('submission_delete' => 'success' ));
			}
			else
			{
				$request->request_status = REQUEST_STATUS::FAILED;   
				$failedRequest = new FailedRequest($result['error']);
				$failedRequest->addReasonArray($resultWidgets['error']);
				$request->setData($failedRequest);
				// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
			}
		}
		else
		{
			$request->request_status = REQUEST_STATUS::FAILED;   
			$failedRequest = new FailedRequest($request->data->getErrors());
			$failedRequest->addReasonKey("inner_json_failed", "Failed to process request's JSON inner data.");
			$request->setData($failedRequest);
			// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
		}
		return $request;		
	}
	
	/**
	 * Internal function - processes the deletion of a submission
	 * @param string $lab_id
	 * @param string $submission_id
	 * @return array <multitype:NULL , string>
	 */
	private static function _delete($lab_id, $submission_id)
	{
		self::initDB();
		$result = array();
		$result['error'] = NULL;
		$result['data'] = NULL;
		
		$arrList = self::$db->delete('submissions', "id = '{$submission_id}' AND laboratory_id = '{$lab_id}'");
		
		if($arrList == false)
		{
			$result['error']['mysql_error'] = mysql_error(self::$db->getConnection());	
		}
		
		return $result;
	}
	
	/**
	 * Internal function - processes the deletion of a widgets from a submission
	 * @param string $lab_id
	 * @param string $submission_id
	 * @return array <multitype:NULL , string>
	 */
	private static function _deleteWidgets($lab_id, $submission_id)
	{
		self::initDB();
		$result = array();
		$result['error'] = NULL;
		$result['data'] = NULL;
		
		$fieldsInfo = self::$db->QueryArray('laboratory_fields', '*', "laboratory_id = '{$lab_id}' AND fieldType = '5'");

		// Delete uploaded images
		if($fieldsInfo != false && count($fieldsInfo) > 0)
		{
			foreach($fieldsInfo as &$field)
			{
				$id = $field['w_id'];
				
				$fileSearch = glob($GLOBALS['absolute_path'] . "uploadedFiles" . DIRECTORY_SEPARATOR . "{$submission_id}-{$id}.*");
				if($fileSearch !== FALSE && count($fileSearch) > 0)
				{
					// an image with this id exists
					if($GLOBALS['debug_mode'] === TRUE)
					{
						file_put_contents("deleted_ids.log", $fileSearch[0] . PHP_EOL, FILE_APPEND);
					}
					@unlink($fileSearch[0]);
				}
				$uploadDeleteResult = self::$db->delete('image_uploads', "w_id = '{$id}' AND submission_id = '{$submission_id}'");
				if($uploadDeleteResult == false)
				{
					$result['error']['uploaddelete_mysql_error'][] = mysql_error(self::$db->getConnection());	
				}
			}
		}
		
		$arrList = self::$db->delete('submission_fields', "submission_id = '{$submission_id}'");
		
		if($arrList == false)
		{
			$result['error']['mysql_error'] = mysql_error(self::$db->getConnection());	
		}	 
		
		return $result;
	}
	
	/**
	 * Retrieves a submission document
	 * @param unknown $request
	 * @return unknown
	 */
	public static function &retrieve($request)
	{
		if($request->processJson())
		{
			$result = self::_retrieve($request->data->lab_id, $request->getUserIDFromSession(NULL));
			if($result['error'] == NULL)
			{
				$submission = new SubmissionDocument($request->session_id);
				$submission->SubmissionMetaData = $result['data'];

				if (array_key_exists('id', $result['data'])) 
				{
					// Only attempt to retrieve submission widget data if there is a submission
					$result = self::_retrieveSubmissionWidgets($result['data']['id']);
					if($result['error'] == NULL)
					{
						$submission->SubmissionWidgetData = $result['data'];
					}
				}		
				
				$request->request_status = REQUEST_STATUS::HANDLED;
				$request->setData($submission);
			}
			else
			{
				$request->request_status = REQUEST_STATUS::FAILED;   
				$failedRequest = new FailedRequest($result['error']);
				$request->setData($failedRequest);
				// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
			}
		}
		else
		{
			$request->request_status = REQUEST_STATUS::FAILED;   
			$failedRequest = new FailedRequest($request->data->getErrors());
			$failedRequest->addReasonKey("inner_json_failed", "Failed to process request's JSON inner data.");
			$request->setData($failedRequest);
			// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
		}
		return $request;
	}

	/**
	 * Internal function - processes the retrieval of a submission document
	 * @param unknown $lab_id
	 * @param unknown $user_id
	 * @return Ambigous <multitype:NULL, multitype:NULL unknown >|Ambigous <string, multitype:multitype: NULL unknown >
	 */
	public static function _retrieve($lab_id, $user_id)
	{
		self::initDB();
		$result = array();
		$result['error'] = NULL;
		$result['data'] = NULL;
		
		if(is_numeric($lab_id) && is_numeric($user_id) && $lab_id > 0 && $user_id > 0)
		{
			$arrList = self::$db->QueryArray('submissions s', '*', "s.laboratory_id = '{$lab_id}' AND s.user_id = '{$user_id}'");
			
			if($arrList != false || count($arrList) == 0)
			{
				$result['data'] = $arrList[0]; 
			}
			else
			{
				return self::_create($lab_id, $user_id);
			}
		}
		else
		{
			$result['error'] = array();
			$result['error']['invalid_data'] = "Laboratory id or user id are invalid!";
		}
		
		return $result;
	}
	
	/**
	 * Internal function - processes the retrieval of submission widgets
	 * @param string $submission_id
	 * @return multitype:multitype: NULL unknown
	 */
	public static function _retrieveSubmissionWidgets($submission_id)
	{
		self::initDB();
		$result = array();
		$result['error'] = NULL;
		$result['data'] = array();
		
		$arrList = self::$db->QueryArray('submission_fields', '*', "submission_id = '{$submission_id}'");
		
		if($arrList != false)
		{
			$result['data'] = $arrList; 
		}		 
		
		return $result;
	}
	
	/**
	 * Updates a submission meta data
	 * @param Request $request
	 * @return Request
	 */
	public static function &updateSubmission(Request &$request)
	{
		if($request->processJson())
		{
			$resultSubmission = self::_updateSubmission($request->data->SubmissionMetaData);			
			if($resultSubmission['error'] == NULL)
			{
				$request->setData((object) array('submission_update' => 'success' ) );		
				$request->request_status = REQUEST_STATUS::HANDLED;
			}
			else
			{
				$request->request_status = REQUEST_STATUS::FAILED;   
				$failedRequest = new FailedRequest($resultSubmission['error']);
				$request->setData($failedRequest);
				// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
			}
		}
		else
		{
			$request->request_status = REQUEST_STATUS::FAILED;   
			$failedRequest = new FailedRequest($request->data->getErrors());
			$failedRequest->addReasonKey("inner_json_failed", "Failed to process request's JSON inner data.");
			$request->setData($failedRequest);
			// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
		}
		return $request;
	}

	/**
	 * Updates a submission document, including widgets
	 * @param Request $request
	 * @return Request
	 */
	public static function &update(Request &$request)
	{
		if($request->processJson())
		{
			$resultSubmission = self::_updateSubmission($request->data->SubmissionMetaData);			
			if($resultSubmission['error'] == NULL)
			{
				$resultWidgets = self::_updateWidgets($request->data->SubmissionWidgetData);
				if($resultWidgets['error'] != NULL)
				{
					$request->setData((object) array('submission_update' => 'failed to update widgets', 
													 'errors' => $resultWidgets['error'] ) );	
				}
				else
				{
					$request->setData((object) array('submission_update' => 'success' ) );		
				}
				$request->request_status = REQUEST_STATUS::HANDLED;
			}
			else
			{
				$request->request_status = REQUEST_STATUS::FAILED;   
				$failedRequest = new FailedRequest($resultSubmission['error']);
				/* resultWidgets doesnt exist over here 
				if(isset($resultWidgets))
				{
					$failedRequest->addReasonArray($resultWidgets['error']);
				}*/
				$request->setData($failedRequest);
				// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
			}
		}
		else
		{
			$request->request_status = REQUEST_STATUS::FAILED;   
			$failedRequest = new FailedRequest($request->data->getErrors());
			$failedRequest->addReasonKey("inner_json_failed", "Failed to process request's JSON inner data.");
			$request->setData($failedRequest);
			// return FailedRequest object with reasons, etc ==> $request->data->getErrors()
		}
		return $request;
	}
	
	/**
	 * Internal function - processes the updating of a submission meta data
	 * @param SubmissionMetaData $submission
	 * @return multitype:NULL multitype:string  |multitype:NULL multitype:string  unknown
	 */
	private static function _updateSubmission($submission)
	{
		self::initDB();
		$result = array();
		$result['error'] = NULL;
		$result['data'] = NULL;
		
		$userInfo = self::$db->QueryArray('users', '*', "id = '{$submission->user_id}'");
		$userInfo = $userInfo[0];
		
		$submissionInfo = self::$db->QueryArray('submissions', '*', "id = '{$submission->id}'");
		$submissionInfo = $submissionInfo[0];

		
		$fields = array('user_id'			=>$submission->user_id, 
		 				'laboratory_id'		=>$submission->laboratory_id,  
					    'authorFirstName'	=>$submission->authorFirstName, 
					    'authorLastName'	=>$submission->authorLastName
					  );
		if($submission->isSubmitted == 1 && ($submissionInfo['isSubmitted'] == 0 || $submissionInfo['isSubmitted'] == '0'))
		{
			$dateTimeSubmitted = date("Y-m-d H:i:s");
			
			$fields['isSubmitted'] 			= $submission->isSubmitted;
			$fields['dateTimeSubmitted'] 	= $dateTimeSubmitted;
		}
		elseif($submissionInfo['isSubmitted'] == 1 || $submissionInfo['isSubmitted'] == '1')
		{
			$result['error'] = array( 'submission_already_submitted' => 'Cannot update, this submission has already been submitted.' );
			return $result;
		}
		
		$id = self::$db->update('submissions', $fields	, "id = '{$submission->id}'");
		
		if(isset($id) && $id !== NULL)
		{
			$arrList = self::$db->QueryArray('submissions', '*', "id = '{$id}'");
			$result['data'] = $arrList[0];
		}
		else
		{			
			$result['error'] = $db->getErrors();
		}	
		
		return $result;	
	}
	
	/**
	 * Internal function - processes the updating of submission widgets
	 * @param unknown $widgetsArray
	 * @return multitype:multitype: NULL
	 */
	private static function _updateWidgets($widgetsArray)
	{
		self::initDB();
		$result = array();
		$result['error'] = NULL;
		$result['data'] = NULL;
		
		if($widgetsArray != NULL && is_array($widgetsArray))
		{
			foreach($widgetsArray as &$val)
			{
				$currentWidget = $val;
				$id = NULL;
				$widgetInfo = self::$db->QueryArray('submission_fields', '*', 
				"field_id = '{$currentWidget->field_id}' AND submission_id = '{$currentWidget->submission_id}'");
				
				$widgetID = -1;
				if($widgetInfo != false)
				{
					$widgetInfo = $widgetInfo[0];
					$widgetID = $widgetInfo['id'];
					// updating
				}
				
				
				$fields = array('submission_id'=>$currentWidget->submission_id, 
								'field_id'=>$currentWidget->field_id, 							
								'value'=>$currentWidget->value,
								'assessmentNotes'=>$currentWidget->assessmentNotes
								);
				
				if($widgetID == -1)
				{
					// creating new widget entry on the database
					$id = self::$db->insert('submission_fields', $fields );
				}
				else
				{
					// updating existing entry
					$id = self::$db->update('submission_fields', $fields, 
					"field_id = '{$currentWidget->field_id}' AND submission_id = '{$currentWidget->submission_id}'");
				}
				

				
				if(isset($id) && $id !== NULL)
				{
					//$arrList = self::$db->QueryArray('laboratories', '*', "id = '{$id}'");
					//$result['data'] = $arrList[0];
					// nothing to return
				}
				else
				{			
					if($result['error'] == NULL)
					{
						$result['error'] = array();
					}
					else
					{
						$result['error']['mysql_errors'] = $db->getErrors();
					}
				}	
			}
		}
		return $result;	
	}

}
?>