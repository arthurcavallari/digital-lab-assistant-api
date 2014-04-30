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
	require_once ('BaseController.php');
	require_once ('../utils/Request.php');
	require_once ('../data/UserData.php');
	require_once ('../db/DatabaseHandler.php');
	require_once ('../requests/FailedRequest.php');
	header('Location: ' . $GLOBALS['base_url'] . 'index.php?src=' . substr($_SERVER['REQUEST_URI'], strstr($_SERVER['REQUEST_URI'],$base_url)));
}


/**
 * Laboratories Controller - handles all the laboratory-related actions
 * @author Arthur Cavallari
 * @version 1.0
 * @created 26-May-2013 7:34:55 PM
 */
class LaboratoriesController extends BaseController
{
	/**
	 * Publishes a lab if it hasn't already been published.  
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
				$request->setData((object) array('lab_submit' => 'success' ) );				
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
	 * Internal function - processes a lab publication
	 * @param SubmitLabRequest $lab SubmitLabRequest object
	 * @return multitype:NULL unknown
	 */
	private static function _submit($lab)
	{
		self::initDB();
		$result = array();
		$result['error'] = NULL;
		$result['data'] = NULL;
		$dateTimePublished = date("Y-m-d H:i:s");
			
		$fields['isPublished'] = 1;
		$fields['dateTimePublished'] = $dateTimePublished;		
		
		$id = self::$db->update('laboratories', $fields	, "id = '{$lab->lab_id}'");
		
		if(isset($id) && $id !== NULL)
		{
			$arrList = self::$db->QueryArray('laboratories', '*', "id = '{$lab->lab_id}'");
			$result['data'] = $arrList[0];
		}
		else
		{			
			$result['error'] = $db->getErrors();
		}	
		
		return $result;	
	}
	
	/**
	 * Updates a laboratory meta data only - expects a LabMetaData inside the Request object
	 * @param Request $request
	 * @return Request
	 */
	public static function &updateLab(Request &$request)
	{
		if($request->processJson())
		{
			$resultLab = self::_updateLab($request->data);

			if($resultLab['error'] == NULL )
			{
				$request->setData((object) array('lab_update' => 'success' ) );				
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
	 * Updates a lab and widgets - expects a LabDocument inside the Request object
	 * @param Request $request
	 * @return Request
	 */
	public static function &update(Request &$request)
	{
		if($request->processJson())
		{
			$resultLab = self::_updateLab($request->data->MetaData);
			$resultWidgets['error'] = NULL;
			if($resultLab['error'] == NULL )
			{
				$resultWidgets = self::_updateWidgets($request->data->WidgetData, $request->data->DeletedWidgets);
			}
			if($resultLab['error'] == NULL && $resultWidgets['error'] == NULL)
			{
				$request->setData((object) array('lab_update' => 'success' ) );				
				$request->request_status = REQUEST_STATUS::HANDLED;
			}
			else
			{
				$request->request_status = REQUEST_STATUS::FAILED;   
				$failedRequest = new FailedRequest($resultLab['error']);
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
	 * Internal function - processes a lab meta data update
	 * @param LabMetaData $lab LabMetaData object
	 * @return multitype:NULL multitype:string  |multitype:NULL multitype:string  unknown
	 */
	private static function _updateLab($lab)
	{
		self::initDB();
		$result = array();
		$result['error'] = NULL;
		$result['data'] = NULL;
		
		$userInfo = self::$db->QueryArray('users', '*', "id = '{$lab->owner_user_id}'");
		$userInfo = $userInfo[0];
		
		$labInfo = self::$db->QueryArray('laboratories', '*', "id = '{$lab->id}'");
		$labInfo = $labInfo[0];

		
		$fields = array('owner_user_id'=>$lab->owner_user_id, 
					  'title'=>$lab->title, 
					  'authorFirstName'=>$lab->authorFirstName, 
					  'authorLastName'=>$lab->authorLastName, 
					  'description'=>$lab->description, 
					  'topic'=>$lab->topic, 
					  'area'=>$lab->area, 
					  'lastWidgetCounter'=>$lab->lastWidgetCounter, 
					  'organisation'=>$lab->organisation, 
					  'pages'=>$lab->pages
					  );
		if($lab->isPublished == 1 && ($labInfo['isPublished'] == 0 || $labInfo['isPublished'] == '0'))
		{
			$dateTimePublished = date("Y-m-d H:i:s");
			
			$fields['isPublished'] = $lab->isPublished;
			$fields['dateTimePublished'] = $dateTimePublished;
		}
		elseif($labInfo['isPublished'] == 1 || $labInfo['isPublished'] == '1')
		{
			$result['error'] = array( 'lab_already_published' => 'Cannot update, this lab has already been published.' );
			return $result;
		}
		
		$id = self::$db->update('laboratories', $fields	, "id = '{$lab->id}'");
		
		if(isset($id) && $id !== NULL)
		{
			$arrList = self::$db->QueryArray('laboratories', '*', "id = '{$id}'");
			$result['data'] = $arrList[0];
		}
		else
		{			
			$result['error'] = $db->getErrors();
		}	
		
		return $result;	
	}
	
	/**
	 * Internal function - processes an update for widgets, including widget deletion
	 * @param array $widgetsArray Array of WidgetData objects
	 * @param array $deletedWidgetsIDArray Array of widget ids to be deleted
	 * @return multitype:multitype: NULL
	 */
	private static function _updateWidgets($widgetsArray, $deletedWidgetsIDArray)
	{
		self::initDB();
		$result = array();
		$result['error'] = NULL;
		$result['data'] = NULL;
		
		if($widgetsArray != NULL && is_array($widgetsArray))
		{
			foreach($deletedWidgetsIDArray as &$id)
			{
				$fileSearch = glob($GLOBALS['absolute_path'] . "uploadedFiles" . DIRECTORY_SEPARATOR . "{$id}.*");
				if($fileSearch !== FALSE && count($fileSearch) > 0)
				{
					// an image with this id exists
					if($GLOBALS['debug_mode'] === TRUE)
					{
						file_put_contents("deleted_ids.log", $fileSearch[0] . PHP_EOL, FILE_APPEND);
					}
					@unlink($fileSearch[0]);
				}
				self::$db->delete('laboratory_fields', "w_id = '{$id}'");
				self::$db->delete('image_uploads', "w_id = '{$id}'");
			}
			
			foreach($widgetsArray as &$val)
			{
				$currentWidget = $val;
				$id = NULL;
				$widgetInfo = self::$db->QueryArray('laboratory_fields', '*', "w_id = '{$currentWidget->w_id}'");
				$widgetID = -1;
				if($widgetInfo != false)
				{
					$widgetInfo = $widgetInfo[0];
					$widgetID = $widgetInfo['id'];
					// updating
				}
				
				
				$fields = array('w_id'=>$currentWidget->w_id,
								'laboratory_id'=>$currentWidget->laboratory_id, 
								'fieldType'=>$currentWidget->fieldType, 
								'posZ'=>$currentWidget->posZ, 
								'posY'=>$currentWidget->posY, 
								'posX'=>$currentWidget->posX, 
								'width'=>$currentWidget->width, 
								'height'=>$currentWidget->height,
								'frameWidth'=>$currentWidget->frameWidth, 
								'frameHeight'=>$currentWidget->frameHeight, 
								'pageNumber'=>$currentWidget->pageNumber, 
								'label'=>$currentWidget->label, 
								'value'=>$currentWidget->value, 
								'readOnly'=>$currentWidget->readOnly, 
								'table_id'=>$currentWidget->table_id, 
								'tableCellRow'=>$currentWidget->tableCellRow, 
								'tableCellColumn'=>$currentWidget->tableCellColumn, 
								'tableRowCount'=>$currentWidget->tableRowCount, 
								'tableColumnCount'=>$currentWidget->tableColumnCount, 
								'timerType'=>$currentWidget->timerType, 
								//'timerTime'=>$currentWidget->timerTime, // not used anymore, now we just use value
								'isStoppable'=>$currentWidget->isStoppable, 
								'isPausable'=>$currentWidget->isPausable
								);
				
				if($widgetID == -1)
				{
					// creating new widget entry on the database
					$id = self::$db->insert('laboratory_fields', $fields );
				}
				else
				{
					// updating existing entry
					$id = self::$db->update('laboratory_fields', $fields, "id = '{$widgetID}'");
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
						$result['error'][] = $db->getErrors();
					}
				}	
			}
		}
		return $result;	
	}
	
	/**
	 * Creates a lab
	 * @param Request $request
	 * @return Request
	 */
	public static function &create(Request &$request)
	{
		if($request->processJson())
		{
			$result = self::_create($request->data);
			if($result['error'] == NULL)
			{
				$lab = $request->data;
				$lab->set($result['data']);
				$request->setData($lab);
				
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
	 * Internal function - processes the creation of a lab
	 * @param LabMetaData $lab LabMetaData object
	 * @return multitype:NULL unknown
	 */
	private static function _create($lab)
	{
		self::initDB();
		$result = array();
		$result['error'] = NULL;
		$result['data'] = NULL;

		$dateTimeCreated = date("Y-m-d H:i:s");
		
		$userInfo = self::$db->QueryArray('users', '*', "id = '{$lab->owner_user_id}'");
		$userInfo = $userInfo[0];

		/** Insert Records of users table */ 
		
		$id = self::$db->insert('laboratories', 
									array('owner_user_id'=>$lab->owner_user_id, 
										  'title'=>$lab->title, 
										  'authorFirstName'=>$userInfo['firstName'], 
										  'authorLastName'=>$userInfo['lastName'], 
										  'description'=>$lab->description, 
										  'topic'=>$lab->topic, 
										  'area'=>$lab->area, 
										  'lastWidgetCounter'=>$lab->lastWidgetCounter, 
										  'organisation'=>$lab->organisation, 
										  'pages'=>1, 
										  'dateTimeCreated'=>$dateTimeCreated
										  )
								);
		
		if(isset($id) && $id !== NULL)
		{
			$arrList = self::$db->QueryArray('laboratories', '*', "id = '{$id}'");
			$result['data'] = $arrList[0];
		}
		else
		{			
			$result['error'] = $db->getErrors();
		}	
		
		return $result;	
	}

	/**
	 * Deletes a laboratory
	 * @param Request $request
	 * @return Request
	 */
	public static function &delete(Request &$request)
	{
		if($request->processJson())
		{
			$resultWidgets = self::_deleteWidgets($request->data->lab_id);
			$result = self::_delete($request->data->lab_id);
			if($result['error'] == NULL && $resultWidgets['error'] == NULL)
			{					
				$request->request_status = REQUEST_STATUS::HANDLED;
				$request->setData((object) array('lab_delete' => 'success' ));
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
	 * Internal function - processes the deletion of a laboratory
	 * @param string $lab_id
	 * @return Ambigous <multitype:NULL , string>
	 */
	private static function _delete($lab_id)
	{
		self::initDB();
		$result = array();
		$result['error'] = NULL;
		$result['data'] = NULL;
		
		$arrList = self::$db->delete('laboratories', "id = '{$lab_id}'");
		
		if($arrList == false)
		{
			$result['error']['mysql_error'] = mysql_error(self::$db->getConnection());	
		}
		
		return $result;
	}
	
	/**
	 * Internal function - processes the deletion of widgets
	 * @param string $lab_id
	 * @return Ambigous <multitype:NULL , string>
	 */
	private static function _deleteWidgets($lab_id)
	{
		self::initDB();
		$result = array();
		$result['error'] = NULL;
		$result['data'] = NULL;
		
		$fieldsInfo = self::$db->QueryArray('laboratory_fields', '*', "laboratory_id = '{$lab_id}'");

		// Delete uploaded images
		if($fieldsInfo != false)
		{
			foreach($fieldsInfo as &$field)
			{
				if($field['fieldType'] == 5 || $field['fieldType'] == '5')
				{
					$id = $field['w_id'];
					
					$fileSearch = glob($GLOBALS['absolute_path'] . "uploadedFiles" . DIRECTORY_SEPARATOR . "{$id}.*");
					if($fileSearch !== FALSE && count($fileSearch) > 0)
					{
						// an image with this id exists
						if($GLOBALS['debug_mode'] === TRUE)
						{
							file_put_contents("deleted_ids.log", $fileSearch[0] . PHP_EOL, FILE_APPEND);
						}
						@unlink($fileSearch[0]);
					}
					$uploadDeleteResult = self::$db->delete('image_uploads', "w_id = '{$id}'");
					if($uploadDeleteResult == false)
					{
						$result['error']['uploaddelete_mysql_error'][] = mysql_error(self::$db->getConnection());	
					}
				}
			}
		}
		
		$arrList = self::$db->delete('laboratory_fields', "laboratory_id = '{$lab_id}'");
		
		if($arrList == false)
		{
			$result['error']['mysql_error'] = mysql_error(self::$db->getConnection());	
		}	 
		
		return $result;
	}
	
	/**
	 * DEPRECATED - This action is now handled by the client
	 * @param Request $request
	 */
	public static function &discard(Request &$request)
	{
	}
	
	/**
	 * Clones a lab
	 * @param Request $request
	 * @return Request
	 */
	public static function &cloneLab(Request &$request)
	{
		if($request->processJson())
		{
			$result = self::_cloneLab($request->data->lab_id, $request->session_id);
			if($result['error'] == NULL)
			{
				$laboratory = new LabDocument($request->session_id);
				$laboratory->MetaData = $result['data'];
				
				$result = self::_cloneWidgets($request->data->lab_id, $laboratory->MetaData->id, $request->session_id);
				if($result['error'] == NULL)
				{
					$laboratory->WidgetData = $result['data'];
				}
				
				$request->request_status = REQUEST_STATUS::HANDLED;
				$request->setData($laboratory);
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
	 * Internal function - processes the cloning of a lab
	 * @param string $lab_id
	 * @param string $session_id
	 * @return multitype:NULL LabMetaData
	 */
	private static function _cloneLab($lab_id, $session_id)
	{
		self::initDB();
		$result = array();
		$result['error'] = NULL;
		$result['data'] = new LabMetaData($session_id, FALSE);
				
		$userID = $result['data']->getUserIDFromSession($session_id);
		
		$dateTimeCreated = date("Y-m-d H:i:s");
		
		$userInfo = self::$db->QueryArray('users', '*', "id = '{$userID}'");
		$userInfo = $userInfo[0];
		
		$lab = self::$db->QueryArray('laboratories', '*', "id = '{$lab_id}'");
		$lab = $lab[0];
							  
		$id = self::$db->insert('laboratories', 
									array('owner_user_id'=>$userID, 
										  'title'=>$lab['title'], 
										  'authorFirstName'=>$userInfo['firstName'], 
										  'authorLastName'=>$userInfo['lastName'], 
										  'description'=>$lab['description'], 
										  'topic'=>$lab['topic'], 
										  'area'=>$lab['area'], 
										  'lastWidgetCounter'=>$lab['lastWidgetCounter'], 
										  'organisation'=>$lab['organisation'], 
										  'pages'=>$lab['pages'], 
										  'dateTimeCreated'=>$dateTimeCreated
										  )
								);
		
		if(isset($id) && $id !== NULL)
		{
			$arrList = self::$db->QueryArray('laboratories', '*', "id = '{$id}'");
			$result['data']->set($arrList[0]);
		}
		else
		{			
			$result['error'] = $db->getErrors();
		}	 
		
		return $result;
	}
	
	/**
	 * Internal function - processes the cloning of widgets for a lab clone
	 * @param string $old_lab_id
	 * @param string $new_lab_id
	 * @param string $session_id
	 * @return multitype:multitype: NULL unknown
	 */
	private static function _cloneWidgets($old_lab_id, $new_lab_id, $session_id)
	{
		self::initDB();
		$result = array();
		$result['error'] = NULL;
		$result['data'] = NULL;
	  
		$widgetsArr = self::$db->QueryArray('laboratory_fields', '*', "laboratory_id = '{$old_lab_id}'");
		
		if($widgetsArr != NULL && is_array($widgetsArr))
		{
			foreach($widgetsArr as &$widget)
			{	
				$oldWidgetID = $widget['w_id'];		
				$widget['w_id'] = str_replace($old_lab_id . "-", $new_lab_id . "-", $widget['w_id']);
				$widget['laboratory_id'] = $new_lab_id;
				
				if(($widget['fieldType'] == 5 || $widget['fieldType'] == '5') 
				&& ($widget['readOnly'] == 1 || $widget['readOnly'] == '1')
				&& $widget['value'] != NULL && $widget['value'] != "" && strlen($widget['value']) > 0)
				{
					// Image Widget - Read Only
					$source = $GLOBALS['absolute_path'] . "uploadedFiles" . DIRECTORY_SEPARATOR . $widget['value'];
					$fileSearch = glob($source);
					if($fileSearch !== FALSE && count($fileSearch) > 0)
					{
						// an image with this id exists
						if($GLOBALS['debug_mode'] === TRUE)
						{
							file_put_contents("clone_ids.log", $fileSearch[0] . PHP_EOL, FILE_APPEND);
						}
						
						$widget['value'] = str_replace($old_lab_id . "-", $new_lab_id . "-", $widget['value']);
						$destination = $GLOBALS['absolute_path'] . "uploadedFiles" . DIRECTORY_SEPARATOR . $widget['value'];
						
						
						// Image upload table
						$imageCheck = self::$db->QueryArray('image_uploads', array('*'), "w_id = '{$oldWidgetID}'");
						if($imageCheck != false && count($imageCheck) > 0)
						{
							$imageCheck = $imageCheck[0];
							$imageUploadFields = array('w_id'=>$widget['w_id'], 'checksum'=>$imageCheck['checksum']);
							self::$db->insert('image_uploads', $imageUploadFields);	
						}
					
						@copy($source, $destination);
					}
					else
					{
						$widget['value'] = NULL;	
					}
				}
				
				$fields = array('w_id'				=>$widget['w_id'],
								'laboratory_id'		=>$widget['laboratory_id'], 
								'fieldType'			=>$widget['fieldType'], 
								'posZ'				=>$widget['posZ'], 
								'posY'				=>$widget['posY'], 
								'posX'				=>$widget['posX'], 
								'width'				=>$widget['width'], 
								'height'			=>$widget['height'],
								'frameWidth'		=>$widget['frameWidth'], 
								'frameHeight'		=>$widget['frameHeight'], 
								'pageNumber'		=>$widget['pageNumber'], 
								'label'				=>$widget['label'], 
								'value'				=>$widget['value'], 
								'readOnly'			=>$widget['readOnly'], 
								'table_id'			=>$widget['table_id'], 
								'tableCellRow'		=>$widget['tableCellRow'], 
								'tableCellColumn'	=>$widget['tableCellColumn'], 
								'tableRowCount'		=>$widget['tableRowCount'], 
								'tableColumnCount'	=>$widget['tableColumnCount'],
								'timerType'			=>$widget['timerType'],
								//'timerTime'		=>$widget['timerTime'], // not used anymore, now we just use value
								'isStoppable'		=>$widget['isStoppable'], 
								'isPausable'		=>$widget['isPausable']
								);
					
				// creating new widget entry on the database
				$id = self::$db->insert('laboratory_fields', $fields );
	
			}
		}
		
		if(isset($id) && $id !== NULL)
		{
			$arrList = self::$db->QueryArray('laboratory_fields', '*', "laboratory_id = '{$new_lab_id}'");
			$result['data'] = $arrList;
		}
		elseif(!isset($id))
		{
			$result['data'] = array();
		}
		else
		{			
			$result['error'] = $db->getErrors();
		}	 
		
		return $result;
	}

	/**
	 * Flags a laboratory
	 * @param Request $request
	 * @return Request
	 */
	public static function &flag(Request &$request)
	{
		if($request->processJson())
		{
			$result = self::_flagLab($request->data, $request->session_id);
			if($result['error'] == NULL)
			{
				$request->request_status = REQUEST_STATUS::HANDLED;
				$request->setData((object) array('lab_flag' => 'success'));
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
	 * Internal function - processes the creation of a user flag for a lab
	 * @param unknown $flagRequest
	 * @param unknown $session_id
	 * @return multitype:NULL unknown
	 */
	private static function _flagLab($flagRequest, $session_id)
	{
		self::initDB();
		$result = array();
		$result['error'] = NULL;
		$result['data'] = NULL;
		
		$flagInfo = self::$db->QueryArray('user_flags', '*', "laboratory_id = '{$flagRequest->lab_id}' AND user_id = '{$flagRequest->user_id}'");
		$flagID  = -1;
		$id = -1;
		
		if($flagInfo != false)
		{
			$flagInfo = $flagInfo[0];
			$flagID = $flagInfo['id'];
			// updating
		}
		
		if($flagID == -1)
		{						  
			$id = self::$db->insert('user_flags', 
										array('user_id'=>$flagRequest->user_id, 
											  'laboratory_id'=>$flagRequest->lab_id, 
											  'notes'=>$flagRequest->notes
											  )
									);
		}
		else
		{
			$id = self::$db->update('user_flags', 
										array(
											  'notes'=>$flagRequest->notes
											  ),
											  "laboratory_id = '{$flagRequest->lab_id}' AND user_id = '{$flagRequest->user_id}'"
									);
		}
		if(isset($id) && $id !== NULL)
		{
			$result['data'] = $id; // just returning something for the sake of it
		}
		else
		{			
			$result['error'] = $db->getErrors();
		}	 
		
		return $result;
	}

	/**
	 * DEPRECATED - Retrieves widgets
	 * @param Request $request
	 * @return Request
	 */
	public static function &retrieveWidgets(Request &$request)
	{
		if($request->processJson())
		{
			$result = self::_retrieveWidgets($request->data->lab_id);
			if($result['error'] == NULL)
			{
				$widgets = new LabWidgets();
				$widgets->Widgets = $result['data'];
				$widgets->ResultsCount = count($result['data']);
				
				$request->request_status = REQUEST_STATUS::HANDLED;
				$request->setData($widgets);
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
	 * Retrieves a lab, including the widgets
	 * @param Request $request
	 * @return Request
	 */
	public static function &retrieve(Request &$request)
	{
		if($request->processJson())
		{
			$result = self::_retrieve($request->data->lab_id, $request->session_id);
			if($result['error'] == NULL)
			{
				$laboratory = new LabDocument($request->session_id);
				$laboratory->MetaData = ($result['data']);
				
				$result = self::_retrieveWidgets($request->data->lab_id);
				if($result['error'] == NULL)
				{
					$laboratory->WidgetData = $result['data'];
				}
				
				$userID = $request->data->user_id;
				$authorID = $laboratory->MetaData['owner_user_id'];
				$favInfo = self::$db->QueryArray('user_favourites', array('user_id'), "user_id = '{$userID}' and favouritedUser_Id = '{$authorID}'");
				if($favInfo != false)
				{
					$laboratory->MetaData['LabAuthorFavourited'] = true;
				}
				
				$flagInfo = self::$db->QueryArray('user_flags', '*', "user_id = '{$userID}' and laboratory_id = '{$request->data->lab_id}'");
				if($flagInfo != false)
				{
					$laboratory->MetaData['UserFlaggedLab'] = true;
					$laboratory->MetaData['UserFlaggedNotes'] = $flagInfo[0]['notes'];
				}				
				
				$request->request_status = REQUEST_STATUS::HANDLED;
				$request->setData($laboratory);
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
	 * Internal function - processes the retrieval of a laboratory
	 * @param string $lab_id
	 * @param string $session_id
	 * @return multitype:NULL LabMetaData unknown
	 */
	private static function _retrieve($lab_id, $session_id)
	{
		self::initDB();
		$result = array();
		$result['error'] = NULL;
		$result['data'] = new LabMetaData($session_id, FALSE);
		
		$arrList = self::$db->QueryArray('laboratories', '*', "id = '{$lab_id}'");
		
		if($arrList != false)
		{
			$result['data'] = $arrList[0]; 
		}		 
		
		return $result;
	}
	
	/**
	 * Internal function - processes the retrieval of widgets of a lab
	 * @param string $lab_id
	 * @return multitype:multitype: NULL unknown
	 */
	private static function _retrieveWidgets($lab_id)
	{
		self::initDB();
		$result = array();
		$result['error'] = NULL;
		$result['data'] = array();
		
		$arrList = self::$db->QueryArray('laboratory_fields', '*', "laboratory_id = '{$lab_id}'");
		
		if($arrList != false)
		{
			$result['data'] = $arrList; 
		}		 
		
		return $result;
	}

	/**
	 * Searches for the labs that a user has created
	 * @param Request $request
	 * @return Request
	 */
	public static function &search_created(Request &$request)
	{
		$user_id = $request->getUserIDFromSession();
		
		if($user_id != -1)
		{
			$query = array("owner_user_id" => $user_id);
			$result = self::_search($query, 0, $user_id, $user_id);
			if($result['error'] == NULL)
			{
				$searchResponse = new SearchResponse();
				$searchResponse->setResults($result['data']);	
				$searchResponse->ResultsCount = count($result['data']);
				
				$request->request_status = REQUEST_STATUS::HANDLED;
				$request->setData($searchResponse);
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
			$failedRequest = new FailedRequest();
			$failedRequest->addReasonArray(array("sessionid_invalid" => "Session id is invalid!"));
			$request->setData($failedRequest);
		}
		
		return $request;
	}
	
	/**
	 * Searches for the labs that a user has a finished submission
	 * @param Request $request
	 * @return Request
	 */
	public static function &search_finished(Request &$request)
	{
		$user_id = $request->getUserIDFromSession();
		
		if($user_id != -1)
		{
			$result = self::_search_submissions(1, $user_id);
			if($result['error'] == NULL)
			{
				$searchResponse = new SearchResponse();
				$searchResponse->setResults($result['data']);	
				$searchResponse->ResultsCount = count($result['data']);
				
				$request->request_status = REQUEST_STATUS::HANDLED;
				$request->setData($searchResponse);
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
			$failedRequest = new FailedRequest();
			$failedRequest->addReasonArray(array("sessionid_invalid" => "Session id is invalid!"));
			$request->setData($failedRequest);
		}
		
		return $request;
	}
	/**
	 * Searches for the labs which a user has an unfinished submission
	 * @param Request $request
	 * @return Request
	 */
	public static function &search_unfinished(Request &$request)
	{
		$user_id = $request->getUserIDFromSession();
		
		if($user_id != -1)
		{
			$result = self::_search_submissions(0, $user_id);
			if($result['error'] == NULL)
			{
				$searchResponse = new SearchResponse();
				$searchResponse->setResults($result['data']);	
				$searchResponse->ResultsCount = count($result['data']);
				
				$request->request_status = REQUEST_STATUS::HANDLED;
				$request->setData($searchResponse);
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
			$failedRequest = new FailedRequest();
			$failedRequest->addReasonArray(array("sessionid_invalid" => "Session id is invalid!"));
			$request->setData($failedRequest);
		}
		
		return $request;
	}
	
	/**
	 * Searches for the published labs of all the users that the current user has favourited
	 * @param Request $request
	 * @return Request
	 */
	public static function &search_favourites(Request &$request)
	{
		$user_id = $request->getUserIDFromSession();
		
		if($user_id != -1)
		{
			$result = self::_search_favourites($user_id);
			if($result['error'] == NULL)
			{
				$searchResponse = new SearchResponse();
				$searchResponse->setResults($result['data']);	
				$searchResponse->ResultsCount = count($result['data']);
				
				$request->request_status = REQUEST_STATUS::HANDLED;
				$request->setData($searchResponse);
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
			$failedRequest = new FailedRequest();
			$failedRequest->addReasonArray(array("sessionid_invalid" => "Session id is invalid!"));
			$request->setData($failedRequest);
		}
		
		return $request;
	}
	 
	/**
	 * General search query
	 * @param Request $request
	 * @return Request
	 */ 
	public static function &search(Request &$request)
	{
		if($request->processJson())
		{
			$user_id = $request->getUserIDFromSession();
			
			$result = self::_search($request->data->Query, $request->data->Page, -1, $user_id);
			if($result['error'] == NULL)
			{
				$searchResponse = new SearchResponse();
				$searchResponse->setResults($result['data']);	
				$searchResponse->ResultsCount = count($result['data']);
				
				$request->request_status = REQUEST_STATUS::HANDLED;
				$request->setData($searchResponse);
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
	 * Internal function - processes search queries
	 * @param string $Query
	 * @param int $Page
	 * @param string $author_id
	 * @param string $user_id
	 * @return multitype:NULL unknown
	 */
	private static function &_search($Query, $Page = 1, $author_id = -1, $user_id = -1)
	{
		self::initDB();
		
		$result = array();
		$result['error'] = NULL; //array( "failed_authentication" => "Password is invalid!" );
		$result['data'] = NULL;
		$sqlWhere = "";
		$sqlLimit = "";
		
		if(is_array($Query))
		{
			$sqlWhere = self::$db->buildWhereString($Query);
		}
		else
		{
			$sqlWhere = "concat_ws(' ',title, authorFirstName, authorLastName, topic, area, organisation) LIKE '%{$Query}%'";
			if($author_id > -1)
			{
				$sqlWhere .= " AND ( (isPublished = '1') OR (owner_user_id = '{$author_id}') )";
			}
			else
			{
				$sqlWhere .= " AND id NOT IN (select laboratory_id from deleted_laboratories) AND owner_user_id NOT IN (select user_id from banned_users)";	
			}
			
			
		}
		$sqlOrder = 'dateTimeCreated, dateTimePublished, title';
		
		if($Page > 0)
		{
			$sqlLimit = 10;
		}
		
		$arrList = self::$db->QueryArray('laboratories', '*', $sqlWhere, $sqlOrder, $sqlLimit, $Page);
		
		if($arrList != false)
		{
			foreach($arrList as &$laboratory)
			{			
				$authorID = $laboratory['owner_user_id'];
				$favInfo = self::$db->QueryArray('user_favourites', array('user_id'), "user_id = '{$user_id}' and favouritedUser_Id = '{$authorID}'");
				if($favInfo != false)
				{
					$laboratory['LabAuthorFavourited'] = true;
				}
				
				$flagInfo = self::$db->QueryArray('user_flags', '*', "user_id = '{$user_id}' and laboratory_id = '{$laboratory['id']}'");
				if($flagInfo != false)
				{
					$laboratory['UserFlaggedLab'] = true;
					$laboratory['UserFlaggedNotes'] = $flagInfo[0]['notes'];
				}
				
				$submissionInfo = self::$db->QueryArray('submissions', '*', "user_id = '{$user_id}' and laboratory_id = '{$laboratory['id']}'");
				if($submissionInfo != false)
				{
					$laboratory['UserHasSubmission'] = true;
					$laboratory['UserSubmissionSubmitted'] = ($submissionInfo[0]['isSubmitted'] == 1 || $submissionInfo[0]['isSubmitted'] == '1' ? true : false);
				}
			}
			$result['data'] = $arrList; 
		}		 
		
		return $result;
	}
	
	/**
	 * Internal function - processes searches related to submissions of an user
	 * @param int $isSubmitted 1 or 0, where 1 = TRUE, 0 = FALSE
	 * @param string $user_id
	 * @return multitype:NULL unknown
	 */
	private static function &_search_submissions($isSubmitted = 1, $user_id)
	{
		self::initDB();
		
		$result = array();
		$result['error'] = NULL; 
		$result['data'] = NULL;
		/*$sqlFields = 's.id as "SubmissionID", s.user_id as "SubmissionUserID", s.authorFirstName as "SubmissionAuthorFirstName", s.authorLastName as "SubmissionAuthorLastName", s.dateTimeCreated as "SubmissionDateTimeCreated", s.dateTimeLastUpdated as "SubmissionDateTimeLastUpdated", s.isSubmitted as "SubmissionIsSubmitted", s.dateTimeSubmitted as "SubmissionDateTimeSubmitted", s.dateTimeAssessed as "SubmissionDateAssessed", l.*';*/
		$sqlFields = 'l.*';
		//$sqlJoin = "left join submissions s on s.laboratory_id = l.id";		
		//$sqlWhere = "s.user_id = '{$user_id}' and s.isSubmitted = '{$isSubmitted}'";
		$sqlWhere = "l.isPublished = '1' AND l.id in (select laboratory_id from submissions s where s.user_id = '{$user_id}' and s.isSubmitted = '{$isSubmitted}') AND id NOT IN (select laboratory_id from deleted_laboratories) AND owner_user_id NOT IN (select user_id from banned_users)";
		
		$sqlOrder = 'l.dateTimeCreated, l.dateTimePublished, l.title';
		
		//$arrList = self::$db->QueryJoinArray('laboratories l', $sqlJoin, $sqlFields, $sqlWhere, $sqlOrder, "", 0);
		$arrList = self::$db->QueryArray('laboratories l', '*', $sqlWhere, $sqlOrder, "", 0);
		
		if($arrList != false)
		{
			foreach($arrList as &$laboratory)
			{			
				$authorID = $laboratory['owner_user_id'];
				$favInfo = self::$db->QueryArray('user_favourites', array('user_id'), "user_id = '{$user_id}' and favouritedUser_Id = '{$authorID}'");
				if($favInfo != false)
				{
					$laboratory['LabAuthorFavourited'] = true;
				}
				
				$flagInfo = self::$db->QueryArray('user_flags', '*', "user_id = '{$user_id}' and laboratory_id = '{$laboratory['id']}'");
				if($flagInfo != false)
				{
					$laboratory['UserFlaggedLab'] = true;
					$laboratory['UserFlaggedNotes'] = $flagInfo[0]['notes'];
				}	
				
				$submissionInfo = self::$db->QueryArray('submissions', '*', "user_id = '{$user_id}' and laboratory_id = '{$laboratory['id']}'");
				if($submissionInfo != false)
				{
					$laboratory['UserHasSubmission'] = true;
					$laboratory['UserSubmissionSubmitted'] = ($submissionInfo[0]['isSubmitted'] == 1 || $submissionInfo[0]['isSubmitted'] == '1' ? true : false);
				}	
			}
				
			$result['data'] = $arrList; 
		}		 
		
		return $result;
	}
	
	/**
	 * Internal function - processes the search for labs created by user favourites
	 * @param unknown $user_id
	 * @return multitype:NULL unknown
	 */
	private static function &_search_favourites($user_id)
	{
		self::initDB();
		
		$result = array();
		$result['error'] = NULL; 
		$result['data'] = NULL;
		$sqlWhere = "l.isPublished = '1' and l.owner_user_id in (select favouritedUser_id from user_favourites f where f.user_id = '{$user_id}') AND id NOT IN (select laboratory_id from deleted_laboratories) AND owner_user_id NOT IN (select user_id from banned_users)";
		
		$sqlOrder = 'dateTimeCreated, dateTimePublished, title';
		
		$arrList = self::$db->QueryArray('laboratories l', '*', $sqlWhere, $sqlOrder, "", 0);
		
		if($arrList != false)
		{
			foreach($arrList as &$laboratory)
			{			
				$authorID = $laboratory['owner_user_id'];
				$favInfo = self::$db->QueryArray('user_favourites', array('user_id'), "user_id = '{$user_id}' and favouritedUser_Id = '{$authorID}'");
				if($favInfo != false)
				{
					$laboratory['LabAuthorFavourited'] = true;
				}
				
				$flagInfo = self::$db->QueryArray('user_flags', '*', "user_id = '{$user_id}' and laboratory_id = '{$laboratory['id']}'");
				if($flagInfo != false)
				{
					$laboratory['UserFlaggedLab'] = true;
					$laboratory['UserFlaggedNotes'] = $flagInfo[0]['notes'];
				}	
				
				$submissionInfo = self::$db->QueryArray('submissions', '*', "user_id = '{$user_id}' and laboratory_id = '{$laboratory['id']}'");
				if($submissionInfo != false)
				{
					$laboratory['UserHasSubmission'] = true;
					$laboratory['UserSubmissionSubmitted'] = ($submissionInfo[0]['isSubmitted'] == 1 || $submissionInfo[0]['isSubmitted'] == '1' ? true : false);
				}		
			}
			$result['data'] = $arrList; 
		}		 
		
		return $result;
	}

	/**
	 * Uploads an image to the server linked to a widget
	 * @param Request $request
	 * @return Request
	 */
	public static function &uploadImage(Request &$request)
	{
		if($request->processJson())
		{
			$result = self::_uploadImage($request->data);
			if($result['error'] == NULL)
			{				
				$request->request_status = REQUEST_STATUS::HANDLED;
				$request->setData((object) array('image_upload' => 'success'));
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
	 * Compares the checksum of an image file in the server with the given checksum 
	 * @param Request $request
	 * @return Request
	 */
	public static function &checkImage(Request &$request)
	{
		if($request->processJson())
		{				
			$request->request_status = REQUEST_STATUS::HANDLED;
			$request->setData((object) array('checksum_match' => 'true'));
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
	 * Internal function - processes the image uploading
	 * @param UploadImageRequest $imageInfo
	 * @return multitype:NULL string
	 */
	private static function &_uploadImage($imageInfo)
	{
		self::initDB();

		$result = array();
		$result['error'] = NULL; 
		$result['data'] = NULL;
		
		$data = base64_decode($imageInfo->image_data);
		
		$filePath = $GLOBALS['absolute_path'] . 'uploadedfiles/' . $imageInfo->image_name;
		
		$fhandle = fopen($filePath, 'w');
		if(fwrite($fhandle, $data) === false)
		{
			$result['error'] = "Could not save image file!"; 
		}	
		else
		{
			$id = -1;
			$submissionCheck = "";
			if($imageInfo->sub_id > -1)
			{
				$submissionCheck = " AND submission_id = '{$imageInfo->sub_id}'";
			}
			$imageCheck = self::$db->QueryArray('image_uploads', array('*'), "w_id = '{$imageInfo->widget_id}' {$submissionCheck}");
			$fields = array('w_id'=>$imageInfo->widget_id, 'checksum'=>$imageInfo->imageChecksum, 'submission_id' => $imageInfo->sub_id);
			if($imageCheck != false && count($imageCheck) > 0)
			{	
				$id = self::$db->update('image_uploads', $fields, "w_id = '{$imageInfo->widget_id}' $submissionCheck");	
			}
			else
			{
				$id = self::$db->insert('image_uploads', $fields);	
			}
		}
		if($fhandle !== FALSE) fclose($fhandle);	 
		
		return $result;
	}

}
?>