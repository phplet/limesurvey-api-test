<?php

include "JsonRPC/Client.php";



                    /*******************************************/
                    /*                                         */
                    /*               CONFIG DATA               */
                    /*                                         */
                    /*******************************************/



$limesurvey_url = 'https://www.whereyouhaveyourlimesurveyinstall.com/index.php/admin/';


$total_global_participants = 1000000;



				/***************  CONFIG DATA: END  ***************/



$jrf = new json_rpc_functions($limesurvey_url,$total_global_participants);

// prepare for the worst, code for the best
$bad_method_passed = TRUE;




                    /*******************************************/
                    /*                                         */
                    /*               METHOD TYPE               */
                    /*                                         */
                    /*******************************************/




// get the functions for the 'json_rpc_functions' class
$jrf_methods = get_class_methods($jrf);

// if it's a post method
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	// loop over the function
	foreach ($jrf_methods as $method_name) {
		// make sure 'method' was passed
		if(isset($_POST['method'])) {
			// see if they match the post data
			if($_POST['method'] == $method_name) {
				// if so run the function
				eval('$jrf->' . $method_name . '();');
				$bad_method_passed = FALSE;
			}
		}
	}

	// catch the bad methods
	if($bad_method_passed) {
		// set the message
		$jrf->method_not_found($_POST['method']);
		// return the default error status
		echo json_encode($jrf->get_json_output());
	}
}

// if it's a get request
if($_SERVER['REQUEST_METHOD'] == 'GET') {
	// loop over the functions
	foreach ($jrf_methods as $method_name) {
		// make sure the 'method' value was attached to the URL
		if(isset($_POST['method'])) {
			// see if they match the post data
			if($_POST['method'] == $method_name && in_array($_POST['method'],$jrf->get_functions)) {
				// if so run the function
				eval('$jrf->' . $method_name . '();');
				$bad_method_passed = FALSE;
			}
		}
	}

	// catch the bad methods
	if($bad_method_passed) {
		// set the message
		if (!isset($_POST['method'])) {
			$_POST['method'] = 'GET';
		}
		$jrf->method_not_found($_POST['method']);
		// return the default error status
		echo json_encode($jrf->get_json_output());
	}
}



                    /**********************************************/
                    /*                                            */
                    /*               JSON-RPC CLASS               */
                    /*                                            */
                    /**********************************************/



class json_rpc_functions {

	protected $limesurvey_url;
	protected $username = '';  // better to store here then in the session
	protected $password;  // better to store here then in the session
	protected $sessionKey;
	protected $client;
	protected $json_output;  // this is the json we are going to return to the client
	protected $json_input;   // this is the json back from the JSON-RPC call
	protected $return_array_error_statuses;
	protected $return_string_error_statuses;
	protected $survey_properties_you_can_read;
	protected $survey_properties_you_can_write;
	protected $survey_question_properties_you_can_read;
	protected $survey_question_properties_you_can_write;
	protected $survey_token_properties_you_can_read;
	protected $survey_token_properties_you_can_write;
	public $get_functions;



		/***************  CONSTRUCTOR  ***************/


	public function __construct($limesurvey_url,$global_participants_per_page) {
		$this->limesurvey_url = $limesurvey_url;
		$this->global_participants_per_page = $global_participants_per_page;
		$this->client = new Client($this->_build_remote_control_endpoint());
		$this->json_output = array('status' => 'error', 'result' => '');
		$this->json_input = array();
		// 'API returned an error' is an error implemented in this class and is referenced towards the bottom of the document
		// TODO: Put these into a config.ini
		$this->return_array_error_statuses = array('API returned an error','No such property','No Data','No Data, survey table does not exist','No permission','No valid Data','No available data','No token table','No Tokens found','Tokens not found','No questions found','No surveys found','No survey response table','Invalid session key','Invalid user name or password','Invalid user','Invalid setting','Invalid surveyid','Invalid Period','Invalid language','Invalid extension','Cannot remove base language','Creation Failed','Data is not available','Faulty parameters','Error','Group with depencdencies - deletion not allowed','Group deletion failed','Survey is active and not editable','Cannot delete Question. Others rely on this question','Really Invalid extension','Permission denied','Token table could not be created','Unable to add response','No Data, survey table does not exist.','No Data, could not get max id','No Response found for Token');
		$this->return_string_error_statuses = array('No permission','Unable to edit response','Invalid session key','Error: ','Not all answer options were saved');
		$this->site_settings = array('DBVersion','SessionName','sitename','siteadminname','siteadminemail','siteadminbounce','defaultlang','updateversions','updateavailable','updatelastcheck','restrictToLanguages','updatecheckperiod','updatenotification','defaulthtmleditormode','defaultquestionselectormode','defaulttemplateeditormode','defaulttemplate','admintheme','adminthemeiconsize','emailmethod','emailsmtphost','emailsmtppassword','bounceaccounthost','bounceaccounttype','bounceencryption','bounceaccountuser','bounceaccountpass','emailsmtpssl','emailsmtpdebug','emailsmtpuser','filterxsshtml','shownoanswer','showxquestions','showgroupinfo','showqnumcode','repeatheadings','maxemails','iSessionExpirationTime','ipInfoDbAPIKey','googleMapsAPIKey','googleanalyticsapikey','googletranslateapikey','force_ssl','surveyPreview_require_Auth','RPCInterface','rpc_publish_api','timeadjust','usercontrolSameGroupPolicy','updatebuild','updateversion');
		$this->survey_properties_you_can_read = array('active','autonumber_start','emailnotificationto','nokeyboard','showwelcome','additional_languages','autoredirect','emailresponseto','owner_id','showxquestions','admin','bounce_email','expires','printanswers','sid','adminemail','bounceaccountencryption','faxto','publicgraphs','startdate','alloweditaftercompletion','bounceaccounthost','format','publicstatistics','template','allowjumps','bounceaccountpass','googleanalyticsapikey','refurl','tokenanswerspersistence','allowprev','bounceaccounttype','googleanalyticsstyle','savetimings','tokenlength','allowregister','bounceaccountuser','htmlemail','sendconfirmation','usecaptcha','allowsave','bounceprocessing','ipaddr','showgroupinfo','usecookie','anonymized','bouncetime','language','shownoanswer','usetokens','assessments','datecreated','listpublic','showprogress','','attributedescriptions','datestamp','navigationdelay','showqnumcode');
		$this->survey_properties_you_can_write = array('autonumber_start','emailnotificationto','nokeyboard','showwelcome','autoredirect','emailresponseto','owner_id','showxquestions','admin','bounce_email','expires','printanswers','adminemail','bounceaccountencryption','faxto','publicgraphs','startdate','alloweditaftercompletion','bounceaccounthost','format','publicstatistics','template','allowjumps','bounceaccountpass','googleanalyticsapikey','refurl','tokenanswerspersistence','allowprev','bounceaccounttype','googleanalyticsstyle','savetimings','tokenlength','allowregister','bounceaccountuser','htmlemail','sendconfirmation','usecaptcha','allowsave','bounceprocessing','ipaddr','showgroupinfo','usecookie','anonymized','bouncetime','shownoanswer','usetokens','assessments','datecreated','listpublic','showprogress','','attributedescriptions','datestamp','navigationdelay','showqnumcode');
		$this->survey_language_properties_you_can_read = array('surveyls_survey_id','surveyls_url','surveyls_email_register_subj','email_admin_notification_subj','surveyls_language','surveyls_urldescription','surveyls_email_register','email_admin_notification','surveyls_title','surveyls_email_invite_subj','surveyls_email_confirm_subj','email_admin_responses_subj','surveyls_description','surveyls_email_invite','surveyls_email_confirm','email_admin_responses','surveyls_welcometext','surveyls_email_remind_subj','surveyls_dateformat','surveyls_numberformat','surveyls_endtext','surveyls_email_remind','surveyls_attributecaptions');
		$this->survey_language_properties_you_can_write = array('surveyls_url','surveyls_email_register_subj','email_admin_notification_subj','surveyls_urldescription','surveyls_email_register','email_admin_notification','surveyls_title','surveyls_email_invite_subj','surveyls_email_confirm_subj','email_admin_responses_subj','surveyls_description','surveyls_email_invite','surveyls_email_confirm','email_admin_responses','surveyls_welcometext','surveyls_email_remind_subj','surveyls_dateformat','surveyls_numberformat','surveyls_endtext','surveyls_email_remind','surveyls_attributecaptions');
		$this->survey_summary_settings = array('all','completed_responses','token_count','incomplete_responses','token_invalid','full_responses','token_sent','token_opted_out','token_completed');
		$this->survey_question_group_properties_you_can_read = array('sid','group_name','group_order','description','language');
		$this->survey_question_group_properties_you_can_write = array('group_name','group_order','description');
		$this->survey_question_properties_you_can_read = array('type','help','language','parent_qid','title','other','scale_id','sid','gid','question','mandatory','same_default','preg','question_order','relevance','subquestions','attributes','attributes_lang','answeroptions');
		$this->survey_question_properties_you_can_write = array('help','title','other','scale_id','gid','question','mandatory','same_default','preg','question_order','relevance');
		$this->survey_token_properties_you_can_read = array('tid','completed','participant_id','language','usesleft','firstname','lastname','email','blacklisted','validfrom','sent','validuntil','remindersent','mpid','emailstatus','remindercount');
		$this->survey_token_properties_you_can_write = array('completed','participant_id','language','usesleft','firstname','lastname','email','blacklisted','validfrom','sent','validuntil','remindersent','mpid','emailstatus','remindercount');
		$this->get_functions = array('export_survey_responses');
	}



		/******************************************************************************/
		/****************************                      ****************************/
		/****************************    MAIN FUNCTIONS    ****************************/
		/****************************                      ****************************/
		/******************************************************************************/




	/**
	 *      TEST API EXISTS
	 *      ---------------
	 *       
	 *      [LINK: none]
	 */

	public function test_api_url() {
		// run the JSON-RPC call
		$this->json_input = $this->client->execute('api_exists',array('sSessionKey' => ''));
		// check the return value
		if(isset($this->json_input)) {
			$this->_set_status_success();
			$this->_set_result($this->json_input['status']);
		} else { 
			$this->_set_result('No API found at: ' . $this->limesurvey_url);
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}





	                    	/****************************************/
    	            	    /*                                      */
        	    	        /*               SESSIONS               */
        		            /*                                      */
    	        	        /****************************************/





	/**
	 *      START SESSION
	 *      -------------
	 *       
	 *      [LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#get_session_key]
	 */


	public function start_session() {
		if($this->_session_key_was_sent()) {
			// get the attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// run the JSON-RPC call
			$this->json_input = $this->client->execute(
				'get_session_key',
				array(
					'username' => $attrs['username'],
					'password' => $attrs['password']
				)
			);
			if($this->_no_errors_in_json_input()) {
				// test the input for errors and update json_output
				$this->_build_json_output_success($this->json_input);  // checks json_input as well
			} else {
				// send the error from the remote control or a defualt one
				if($this->json_input.status) { $this->_set_result($this->json_input['status']); }
				else { $this->_set_result('No session key was returned'); }
			}
		}
		// send a json response back to the page
		echo json_encode($this->json_output);
	}


	/**
	 *      RELEASE SESSION KEY
	 *      -------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#release_session_key]
	 *
	 *      NOTE: because the sessionkey is stored in the session
	 *            nothing has to be passed to this function
	 */

	public function end_session() {
		if($this->_session_key_was_sent()) {
			// run the JSON-RPC call
			$this->json_input = $this->client->execute('release_session_key', array('sSessionKey' => $_POST['session_key']));
			// update json_output
			$this->_build_json_output_success('Session finished');  // checks json_input as well
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}



	/**
	 *      IS USER LOGGED IN
	 *      -----------------
	 *       
	 *      [LINK: none]
	 */

	public function is_user_logged_in() {
		if($this->_session_key_was_sent()) {
			// run the JSON-RPC call
			$this->json_input = $this->client->execute(
				'session_key_is_valid',
				array('sSessionKey' => $_POST['session_key'])
			);
			// check the return value
			if($this->_no_errors_in_json_input()) {
				$this->_set_status_success();
			}
			$this->_set_result($this->json_input['status']);
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}




	                    	/*************************************/
    	            	    /*                                   */
        	    	        /*               USERS               */
        		            /*                                   */
    	        	        /*************************************/




	/**
	 * 		LIST USERS
	 * 		----------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#list_users]
	 * 		
	 */
	public function list_users() {
		echo json_encode($this->_list_users_function());
	}
	protected function _list_users_function() {
		if($this->_session_key_was_sent()) {
			// run the JSON-RPC call
			$this->json_input = $this->client->execute('list_users', array('sSessionKey' => $_POST['session_key']));
			// build output
			$users = array();
			foreach ($this->json_input as $value) {
				$users[] = array('uid' => $value['uid'], 'username' => $value['users_name'], 'full_name' => $value['full_name'], 'email' => $value['email']);
			}
			// update json_output
			$this->_build_json_output_success($users);  // checks json_input as well
		}
		// send a json responce back to the page
		return $this->json_output;
	}



	                    	/************************************/
    	            	    /*                                  */
        	    	        /*               SITE               */
        		            /*                                  */
    	        	        /************************************/




	/**
	 * 		GET SITE SETTINGS
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#get_site_settings]
	 * 		
	 */
	public function get_site_settings() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get the attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure setting is inside post attributes
			if($this->_post_attributes_contains(array('setting'))) {
				// make sure it's a real setting being queried
				if(in_array($attrs['setting'],$this->site_settings)) {
					// run the JSON-RPC call
					$this->json_input = $this->client->execute(
						'get_site_settings',
						array(
							'sSessionKey' => $_POST['session_key'],
							'sSetttingName' => $attrs['setting']
						)
					);
					// check the return value
					if(isset($this->json_input)) {
						$this->_build_json_output_success($this->json_input);  // checks json_input as well
					} else { $this->_set_result('No value was returned'); }
				} else { $this->_set_result($attrs['setting'] . ' is not a site setting'); }
			}
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}



	                    	/***************************************************/
    	            	    /*                                                 */
        	    	        /*               GLOBAL PARTICIPANTS               */
        		            /*                                                 */
    	        	        /***************************************************/




	/**
	 * 		SEARCH GLOBAL PARTICIPANT
	 * 		------------------------- 		
	 */
	public function search_global_participants() {
		echo json_encode($this->_search_global_participants_function());
	}


	/**
	 * 		GET GLOBAL PARTICIPANT
	 * 		---------------------- 		
	 */
	public function get_global_participants() {
		echo json_encode($this->_get_global_participants_function());
	}



	/**
	 * 		EDIT GLOBAL PARTICIPANT
	 * 		----------------------- 		
	 */
	public function set_global_participant() {
		echo json_encode($this->_set_global_participant_function());
	}



	/**
	 * 		ADD GLOBAL PARTICIPANT
	 * 		---------------------- 		
	 */
	public function add_global_participant() {
		echo json_encode($this->_add_global_participant_function());
	}



	/**
	 * 		DELETE GLOBAL PARTICIPANT
	 * 		------------------------- 		
	 */
	public function delete_global_participant() {
		echo json_encode($this->_delete_global_participant_function());
	}





	                    	/**************************************/
    	            	    /*                                    */
        	    	        /*               SURVEY               */
        		            /*                                    */
    	        	        /**************************************/



	/**
	 * 		ADD SURVEY
	 * 		----------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#add_survey]
	 * 		
	 */
	public function add_survey() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// make sure attributes contains the variables we need
			if($this->_post_attributes_contains(array('title','username','email'))) {
				// get attributes as an array
				$attrs = json_decode($_POST['attrs'],true);
				// build the values for the RPC call
				$new_survey_array = array(
					'sSessionKey' => $_POST['session_key'],
					// needs to be a rand because if there is not a entry with a pk of '1' it will create one (and i'll look a bit odd)
					// if the survey_id already exists the API will create a random one
					'iSurveyID' => rand(100000,999999),  // the survey_id we'd like to have
					'sSurveyTitle' => $attrs['title'],
					'sSurveyLanguage' => 'en',
					'sFormat' => 'S'  // 'Single' - Show questions one at a time ('G' - show questions in a group, 'A' - all questions)
				);
				// run the JSON-RPC call
				$new_survey_id = $this->json_input = $this->client->execute('add_survey', $new_survey_array);
				// check the returned data
				if(ctype_digit($new_survey_id)) {
					$full_name = $attrs['username'];
					$email = $attrs['email'];
					// ... update the survey with their full_name and email
					// update admin
					$_POST['attrs'] = '{"survey_id":"' . $new_survey_id . '","setting":{"admin":"' . $full_name . '"}}';
					$add_admin_to_survey_result = $this->_set_survey_property_function();
					if($add_admin_to_survey_result['status'] == 'success') {
						// update admin_email
						$_POST['attrs'] = '{"survey_id":"' . $new_survey_id . '","setting":{"adminemail":"' . $email . '"}}';
						$add_admin_email_to_survey_result = $this->_set_survey_property_function();
						if($add_admin_email_to_survey_result['status'] == 'success') {
							// update bounce_email
							$_POST['attrs'] = '{"survey_id":"' . $new_survey_id . '","setting":{"bounce_email":"' . $email . '"}}';
							$add_bounce_email_to_survey_result = $this->_set_survey_property_function();
							if($add_bounce_email_to_survey_result['status'] == 'success') {
								// if the survey was created successfully
								$this->_build_json_output_success('Survey created (' . $new_survey_id . ')');  // checks json_input as well
							} else { $this->_set_result('Additional survey value (bounce_email) could not be set'); }
						} else { $this->_set_result('Additional survey value (admin_email) could not be set'); }
					} else { $this->_set_result('Additional survey value (admin) could not be set'); }
				} else {
					$return_value = $new_survey_id;
					if(gettype($new_survey_id) == 'array') { $return_value = implode(',',$new_survey_id); }
					$this->_set_result('An unexpected return value was recieved (' . $return_value . ')');
				}
			}
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}


	/**
	 * 		DELETE SURVEY
	 * 		-------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#delete_survey]
	 * 		
	 */
	public function delete_survey() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure their is a survey_id
			if($this->_post_attributes_contains(array('survey_id'))) {
				// make suret he survey_id is an interger
				if($this->_is_int($attrs['survey_id'])) {
					$this->json_input = $this->client->execute('delete_survey', array('sSessionKey' => $_POST['session_key'], 'iSurveyID' => $attrs['survey_id']));
					if($this->json_input['status'] == 'OK') {
						// the survey was deleted successfully
						$this->_build_json_output_success('Survey deleted (' . $attrs['survey_id'] . ')');  // checks json_input as well
					} else { $this->_set_result('Could not delete the survey (' . implode(',',$$this->json_input) . ')'); }
				}
			}
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}


	/**
	 * 		IMPORT SURVEY
	 * 		-------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#import_survey]
	 * 		
	 */
	// don't need this as we have 'duplicate_survey'
	public function import_survey() {
		$this->_set_result('This function is not provided');
		echo json_encode($this->json_output);
	}


	/**
	 * 		DUPLICATE SURVEY (custom)
	 * 		----------------
	 *
	 * 		[LINK: (none)]
	 * 		
	 */
	public function duplicate_survey() {
		echo json_encode($this->_duplicate_survey_function());
	}


	/**
	 * 		GET SURVEY PROPERTIES
	 * 		---------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#get_survey_properties]
	 * 		
	 */
	public function get_survey_properties() {
		echo json_encode($this->_get_survey_properties_function());
	}
	protected function _get_survey_properties_function() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure their is a 'survey_id' and 'setting' in post attributes
			if($this->_post_attributes_contains(array('survey_id','setting'))) {
				// make suret he survey_id is an interger
				if($this->_is_int($attrs['survey_id'])) {
					// get all the properties being queried
					$properties = explode(',',$attrs['setting']);
					$all_survey_properties_exist = TRUE;
					// make sure all properties passed exist
					foreach ($properties as $value) {
						// if it doesn't exist we don't proceed
						if(!in_array($value,$this->survey_properties_you_can_read)) { $all_survey_properties_exist = FALSE; }
					}
					// make sure it's a real setting being set
					if($all_survey_properties_exist) {
						// build the array we are going to use with RPC call
						$get_survey_properties_array = array(
							'sSessionKey' => $_POST['session_key'],
							'iSurveyID' => $attrs['survey_id'],
							'aSurveySetting' => $properties
							//'sLanguage' => 'en'
						);
						$this->json_input = $this->client->execute('get_survey_properties', $get_survey_properties_array);
						// check the json_input and update the json output
						$this->_build_json_output_success($this->json_input);
					} else { $this->_set_result('One of these is not a survey property (' . $attrs['setting'] . ')'); }
				}
			}  // _is_json has it's own errors
		}
		// return the JSON
		return $this->json_output;
	}


	/**
	 * 		SET SURVEY PROPERTIES
	 * 		---------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#set_survey_properties]
	 * 		
	 */
	public function set_survey_property() {
		echo json_encode($this->_set_survey_property_function());
	}
	protected function _set_survey_property_function() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get the attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure setting does not contain more then one property to change
			if(count($attrs['setting']) > 1) {
				$this->_set_result('Can not change more then one property at a time (' . json_encode($attrs['setting']) . ')');
				return $this->json_output;
			}
			$property_name = '(not assigned)';
			$property_value = '(not assigned)';
			foreach ($attrs['setting'] as $key => $value) {
				$property_name = $key;
				$property_value = $value;
			}
			// make sure the setting is in the right format
			if($property_name !== '(not assigned)' && $property_value !== '(not assigned)') {
				// make sure it's a real setting being set
				if(in_array($property_name,$this->survey_properties_you_can_write)) {
					// make sure 'survey_id' and 'setting' are in attrributes
					if($this->_post_attributes_contains(array('survey_id','setting'))) {
						// make suret he survey_id is an interger
						if($this->_is_int($attrs['survey_id'])) {
							// populate the array we're going to send
							$survey_property_array = array(
								'sSessionKey' => $_POST['session_key'],
								'iSurveyID' => $attrs['survey_id'],
								'aSurveySetting' => $attrs['setting']
								//'sLanguage' => 'en'
							);
							// run the JSON-RPC call
							$this->json_input = $this->client->execute('set_survey_properties', $survey_property_array);
							// check the json_input and update the json output
							$this->_build_json_output_success('Survey (' . $attrs['survey_id'] . ') ' . $property_name . ' property changed');
						}
					}
				} else { $this->_set_result($property_name . ' is not a survey property you can change'); }
			} else { $this->_set_result('the setting is not in the correct format (' . $_POST['attrs'] . ')'); }
		}
		// send a json responce back to the page
		return $this->json_output;
	}


	/**
	 * 		LIST SURVEYS
	 * 		------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#list_surveys]
	 * 		
	 */
	public function list_surveys() {
		if($this->_session_key_was_sent()) {
			// run the JSON-RPC call
			$surveys = $this->client->execute('list_surveys', array('sSessionKey' => $_POST['session_key']));
			// loop through the json and add additional values

				// NOTE: This is not included any more because the
				//       query is just to slow for production

			/*foreach ($surveys as $key => $value) {

					// START/END DATE

				$get_survey_properties_array = array(
					'sSessionKey' => $_POST['session_key'],
					'iSurveyID' => $surveys[$key]['sid'],
					'aSurveySetting' => array('startdate','expires')
					//'sLanguage' => 'en'
				);
				$this->json_input = $this->client->execute('get_survey_properties', $get_survey_properties_array);
				//if($this->_no_errors_in_json_input() && strtotime($this->json_input['start_date']) && strtotime($this->json_input['end_date'])) {
				if($this->_no_errors_in_json_input()) {
					if(strtotime($this->json_input['startdate'])) {
						$surveys[$key]['start_date'] = $this->_human_readable_timedate(strtotime($this->json_input['startdate']));
					} else {
						$surveys[$key]['start_date'] = null;
					}
					if(strtotime($this->json_input['expires'])) {
						$surveys[$key]['end_date'] = $this->_human_readable_timedate(strtotime($this->json_input['expires']));
					} else {
						$surveys[$key]['end_date'] = null;
					}
				} else {
					$surveys[$key]['start_date'] = null;
					$surveys[$key]['end_date'] = null;
				}

					// TOTAL PARTICIPANTS

				$list_total_participants_array = array(
					'sSessionKey' => $_POST['session_key'],
					'iSurveyID' => $surveys[$key]['sid'],
					'iStart' => 0,
					'iLimit' => 10000000,
					'bUnused' => false
				);
				// run the JSON-RPC call
				$this->json_input = $this->client->execute('list_participants', $list_total_participants_array);
				if($this->_no_errors_in_json_input()) { $surveys[$key]['participants_total'] = count($this->json_input); }
				else { $surveys[$key]['participants_total'] = 0; }

					// UNUSED PARTICIPANTS

				$list_unused_participants_array = array(
					'sSessionKey' => $_POST['session_key'],
					'iSurveyID' => $surveys[$key]['sid'],
					'iStart' => 0,
					'iLimit' => 10000000,
					'bUnused' => true
				);
				// run the JSON-RPC call
				$this->json_input = $this->client->execute('list_participants', $list_unused_participants_array);
				if($this->_no_errors_in_json_input()) { $surveys[$key]['participants_unused'] = count($this->json_input); }
				else { $surveys[$key]['participants_unused'] = 0; }

					// USED PARTICIPANTS

				$surveys[$key]['participants_used'] = $surveys[$key]['participants_total'] - $surveys[$key]['participants_unused'];

			}*/
			// have to be re-populated because it was used by the JSON_RPC functions above
			$this->json_input = $surveys;
			// update json_output
			$this->_build_json_output_success($this->json_input);  // checks json_input as well
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}


	/**
	 * 		SURVEY PARTICIPANTS STATS
	 * 		-------------------------
	 *
	 * 		[LINK: ]
	 * 		
	 */
	public function survey_participants_stats() {
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get the attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure a value has been passed
			if($this->_post_attributes_contains(array('survey_id'))) {
				// make suret he survey_id is an interger
				if($this->_is_int($attrs['survey_id'])) {

					$participant_numbers = array();

						// COMPLETED

					$get_survey_summary_array = array(
						'sSessionKey' => $_POST['session_key'],
						'iSurveyID' => $attrs['survey_id'],
						'sStatName' => 'full_responses'
					);
					$participant_numbers['completed'] = $this->client->execute('get_summary', $get_survey_summary_array);

					if(empty($participant_numbers['completed']) || (isset($participant_numbers['completed']['status']))) {
						$participant_numbers['completed'] = 0;
					} else {
						$participant_numbers['completed'] = (int) $participant_numbers['completed'];
					}

						// TOTAL PARTICIPANTS

					$list_total_participants_array = array(
						'sSessionKey' => $_POST['session_key'],
						'iSurveyID' => $attrs['survey_id'],
						'iStart' => 0,
						'iLimit' => 10000000,
						'bUnused' => false
					);
					// run the JSON-RPC call
					$this->json_input = $this->client->execute('list_participants', $list_total_participants_array);
					if($this->_no_errors_in_json_input()) {
						$participant_numbers['total'] = $this->json_input;
					} else {
						if($this->json_input['status'] == 'No Tokens found') {
							$participant_numbers['total'] = [];
						} else {
							$participant_numbers['total'] = 'n/a';
						}
					}

						// UNUSED PARTICIPANTS

					$list_unused_participants_array = array(
						'sSessionKey' => $_POST['session_key'],
						'iSurveyID' => $attrs['survey_id'],
						'iStart' => 0,
						'iLimit' => 10000000,
						'bUnused' => true
					);
					// run the JSON-RPC call
					$this->json_input = $this->client->execute('list_participants', $list_unused_participants_array);
					if($this->_no_errors_in_json_input()) {
						$participant_numbers['unused'] = $this->json_input;
					} else {
						if($this->json_input['status'] == 'No Tokens found') {
							$participant_numbers['unused'] = [];
						} else {
							$participant_numbers['unused'] = 'n/a';
						}
					}

						// USED PARTICIPANTS

					if($participant_numbers['total'] == 'n/a' || $participant_numbers['unused'] == 'n/a') {
						$participant_numbers['used'] = 0;
					} else {
						$participant_numbers['used'] = count($participant_numbers['total']) - count($participant_numbers['unused']);
					}

					// (not really needed but put in anyway)
					if(empty($participant_numbers['total']) || isset($participant_numbers['total']['status'])) {
						$participant_numbers['total'] = 0;
					} else {
						$participant_numbers['total'] = count($participant_numbers['total']);
					}

					if(empty($participant_numbers['unused']) || isset($participant_numbers['unused']['status'])) {
						$participant_numbers['unused'] = 0;
					} else {
						$participant_numbers['unused'] = count($participant_numbers['unused']);
					}

					// update json_output
					// (can't use this because if the last API call returns 'Tokens not found' then
					//  $this->_build_json_output_success() will trigger an error in $this->
					//  _no_errors_in_json_input())
					// The down side of this is that all errors fail silently
					//  
					//   $this->_build_json_output_success($participant_numbers);
					//
					$this->_set_status_success();
					$this->_set_result($participant_numbers);
				}
			}
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}


	/**
	 * 		ACTIVATE SURVEY
	 * 		---------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#activate_survey]
	 * 		
	 */
	// TODO: you have to deactivate a expired survey (activa, expires) before you activate it again
	public function activate_survey() {
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get the attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure a value has been passed
			if($this->_post_attributes_contains(array('survey_id'))) {
				// make suret he survey_id is an interger
				if($this->_is_int($attrs['survey_id'])) {
					// run the JSON-RPC call
					$this->json_input = $this->client->execute('activate_survey', array('sSessionKey' => $_POST['session_key'], 'SurveyID' => $attrs['survey_id']));
					// update json_output
					$this->_build_json_output_success('Survey (' . $attrs['survey_id'] . ') is now active');  // checks json_input as well
				}
			}
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}


	/**
	 * 		DEACTIVATE SURVEY (custom)
	 * 		-----------------
	 *
	 * 		[LINK: (none)]
	 * 		
	 */
	public function deactivate_survey() {
		$this->expire_survey();
	}


	/**
	 * 		EXPIRE SURVEY (custom)
	 * 		-------------
	 *
	 * 		[LINK: (none)]
	 * 		
	 */
	// Same as deactivating the survey in Lime Survey but preserves the data
	public function expire_survey() {
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get the attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure a value has been passed
			if($this->_post_attributes_contains(array('survey_id'))) {
				// make suret he survey_id is an interger
				if($this->_is_int($attrs['survey_id'])) {
					// get the 'active status' of the survey
					$_POST['attrs'] = '{"survey_id":"' . $attrs['survey_id'] . '","setting":"active"}';
					$is_survey_active = $this->_get_survey_properties_function();
					// make sure the query went well
					if($is_survey_active['status'] == 'success') {
						// make sure the survey is active
						if($is_survey_active['result']['active'] == 'Y') {
							// set the expiry date to one minute before now
							$expiry_datetime = date("Y-m-d H:i:s", mktime(date('H'), date('i')-1, date('s'), date('m'),   date('d'),   date('Y')));
							// set the 'expires' value using _set_survey_property_function()
							$_POST['attrs'] = '{"survey_id":"' . $attrs['survey_id'] . '","setting":{"expires":"' . $expiry_datetime . '"}}';
							$expire_survey_result = $this->_set_survey_property_function();
							if($expire_survey_result['status'] == 'success') {
								// update json_output
								$this->_build_json_output_success('Survey (' . $attrs['survey_id'] . ') expired');  // checks json_input as well
							} else { $this->_set_result('The survey could not be expired (' . implode(',',$expire_survey_result) . ')'); }
						} else { $this->_set_result('Could not expire the survey as the survey is not active'); }
					} else { $this->_set_result('Could not expire the survey as could not activate the survey (' .  implode(': ',$is_survey_active) . ')'); }  // this will always be an array as we are using our own function
				}
			}
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}


	/**
	 * 		EXPORT SURVEY STATISTICS
	 * 		------------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#export_statistics]
	 * 		
	 */
	// Don't need it and it to bloody hard (returning a file via ajax)
	// NOT WORKING
	/*public function export_survey_statistics() {
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get the attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure a value has been passed
			if(isset($attrs['survey_id'])) {
				// make suret he survey_id is an interger
				if(ctype_digit($attrs['survey_id'])) {
					$this->json_input = $this->client->execute('export_statistics', array('sSessionKey' => $_POST['session_key'], 'iSurveyID' => $attrs['survey_id']));
					// as long as it's not an array ...
					if(gettype($this->json_input) !== 'array') {
						// ... return the file
						$size = (int) (strlen(rtrim($this->json_input, '=')) * 3 / 4);
						header('Content-Disposition: attachment; filename="survey_' . $attrs['survey_id'] . '_statistics_' . date('YmdHi') .'.pdf"');
						header('Content-type: ' . $size);
						echo $this->json_input;
					} else {
						// if the error is an array split it up and show them ...
						if(gettype($this->json_input) == 'array') {
							$this->_set_result('The survey could not be expired (' . implode(',',$this->json_input) . ')');
						// ... else just show them the type
						} else {
							$this->_set_result('The survey could not be expired (The API sent back a ' . gettype($this->json_input) . ')');
						}
					}
				} else { $this->_set_result('survey_id was not in the right format ([' . gettype($attrs['survey_id']) . '] - ' . implode(',',$attrs) . ')'); }
			} else { $this->_set_result('No survey_id was sent'); }
		}
		// send a json responce back to the page
		//echo json_encode($this->json_output);
	}*/


	/**
	 * 		SURVEY SUMMARY
	 * 		--------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#get_summary]
	 * 		
	 */

	public function survey_summary() {
		// make sue we have a session key and the post data is in the right format
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get the attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure a value has been passed
			if($this->_post_attributes_contains(array('survey_id','setting'))) {
				// make suret he survey_id is an interger
				if($this->_is_int($attrs['survey_id'])) {
					// make sure it's a real setting being queried
					if(in_array($attrs['setting'],$this->survey_summary_settings)) {
						// build the array we are going to use with RPC call
						$get_survey_summary_array = array(
							'sSessionKey' => $_POST['session_key'],
							'iSurveyID' => $attrs['survey_id'],
							'sStatName' => $attrs['setting']
						);
						$this->json_input = $this->client->execute('get_summary', $get_survey_summary_array);
						// the survey property was retrived successfully
						$this->_build_json_output_success($this->json_input);  // checks json_input as well
					} else { $this->_set_result($attrs['setting'] . ' is not a survey summary property'); }
				}
			}
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}




	                    	/***********************************************/
    	            	    /*                                             */
        	    	        /*               SURVEY LANGUAGES              */
        		            /*                                             */
    	        	        /***********************************************/





	/**
	 * 		ADD SURVEY LANGUAGE
	 * 		-------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#add_language]
	 * 		
	 */
	
	public function add_survey_language() {
		$this->_set_result('This function is not provided');
		echo json_encode($this->json_output);
	}


	/**
	 * 		DELETE SURVEY LANGUAGE
	 * 		----------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#delete_survey_language]
	 * 		
	 */
	
	public function delete_survey_language() {
		$this->_set_result('This function is not provided');
		echo json_encode($this->json_output);
	}


	/**
	 * 		GET SURVEY LANGUAGE PROPERTIES
	 * 		------------------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#get_language_properties]
	 * 		
	 */
	
	public function get_survey_language_properties() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure their is a 'survey_id' and 'setting' in post attributes
			if($this->_post_attributes_contains(array('survey_id','setting'))) {
				// make suret he survey_id is an interger
				if($this->_is_int($attrs['survey_id'])) {
					// get all the properties being queried
					$properties = explode(',',$attrs['setting']);
					$all_survey_language_properties_exist = TRUE;
					// make sure all properties passed exist
					foreach ($properties as $value) {
						// if it doesn't exist we don't proceed
						if(!in_array($value,$this->survey_language_properties_you_can_read)) { $all_survey_language_properties_exist = FALSE; }
					}
					// make sure it's a real setting being set
					if($all_survey_language_properties_exist) {
						// build the array we are going to use with RPC call
						$get_survey_language_properties_array = array(
							'sSessionKey' => $_POST['session_key'],
							'iSurveyID' => $attrs['survey_id'],
							'aSurveyLocaleSettings' => $properties
							//sLang' => 'en'
						);
						$this->json_input = $this->client->execute('get_language_properties', $get_survey_language_properties_array);
						// check the json_input and update the json output
						$this->_build_json_output_success($this->json_input);
					} else { $this->_set_result('One of these is not a survey property (' . implode(',',$properties) . ')'); }
				}
			}  // _is_json has it's own errors
		}
		// return the JSON
		echo json_encode($this->json_output);
	}


	/**
	 * 		SET SURVEY LANGUAGE PROPERTIES
	 * 		------------------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#set_language_properties]
	 * 		
	 */
	
	public function set_survey_language_property() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get the attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure setting does not contain more then one property to change
			if(count($attrs['setting']) > 1) {
				$this->_set_result('Can not change more then one property at a time (' . json_encode($attrs['setting']) . ')');
				return $this->json_output;
			}
			$property_name = '(not assigned)';
			$property_value = '(not assigned)';
			foreach ($attrs['setting'] as $key => $value) {
				$property_name = $key;
				$property_value = $value;
			}
			// make sure the setting is in the right format
			if($property_name !== '(not assigned)' && $property_value !== '(not assigned)') {
				// make sure it's a real setting being set
				if(in_array($property_name,$this->survey_language_properties_you_can_write)) {
					// make sure 'survey_id' and 'setting' are in attrributes
					if($this->_post_attributes_contains(array('survey_id','setting'))) {
						// make suret he survey_id is an interger
						if($this->_is_int($attrs['survey_id'])) {
							// populate the array we're going to send
							$survey_property_array = array(
								'sSessionKey' => $_POST['session_key'],
								'iSurveyID' => $attrs['survey_id'],
								'aSurveyLocaleData' => $attrs['setting']
								//'sLang' => 'en'
							);
							// run the JSON-RPC call
							$this->json_input = $this->client->execute('set_language_properties', $survey_property_array);
							// check the json_input and update the json output
							$this->_build_json_output_success('Survey (' . $attrs['survey_id'] . ') ' . $property_name . ' property changed');
						}
					}
				} else { $this->_set_result($property_name . ' is not a survey language property you can change'); }
			} else { $this->_set_result('property is in the incorrect format (' . $_POST['attrs'] . ')'); }
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}



	                    	/***********************************************/
    	            	    /*                                             */
        	    	        /*               QUESTION GROUPS               */
        		            /*                                             */
    	        	        /***********************************************/



	/**
	 * 		ADD QUESTION GROUP
	 * 		------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#add_group]
	 * 		
	 */
	public function add_question_group() {
		echo json_encode($this->_add_question_group_function($_POST['attrs']));
	}
	protected function _add_question_group_function($json_data) {
		// make sue we have a session key and the post data is in the right format
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get the attributes as an array
			$attrs = json_decode($json_data,true);
			// make sure the survey_id and setting is inside the attributes post data
			if($this->_post_attributes_contains(array('survey_id','setting'))) {
				// make sure the survey_id is an interger
				if($this->_is_int($attrs['survey_id'])) {
					// make sure the new group name is in the
					if(isset($attrs['setting']['group_title'])) {
						// build the array we are going to use with RPC call
						$set_new_question_group_array = array(
							'sSessionKey' => $_POST['session_key'],
							'iSurveyID' => $attrs['survey_id'],
							'sGroupTitle' => $attrs['setting']['group_title'],
							'sGroupDescription' => ''
						);
						$this->json_input = $this->client->execute('add_group', $set_new_question_group_array);
						// the survey property was retrived successfully
						$this->_build_json_output_success($this->json_input);  // checks json_input as well
					} else { $this->_set_result('no name set'); }
				}
			}
		}
		// send an array back
		return $this->json_output;
	}


	/**
	 * 		DELETE QUESTION GROUP
	 * 		---------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#delete_group]
	 * 		
	 */
	
	public function delete_question_group() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure we have what we need in the post data
			if($this->_post_attributes_contains(array('survey_id','group_id'))) {
				// make sure they are all intergers
				if($this->_is_int($attrs['survey_id']) && $this->_is_int($attrs['group_id'])) {
					$delete_group_attrs = array(
						'sSessionKey' => $_POST['session_key'],
						'iSurveyID' => $attrs['survey_id'],
						'iGroupID' => $attrs['group_id']
					);
					$deleted_group = $this->client->execute('delete_group', $delete_group_attrs);
					// check the json_input and update the json output
					$this->_build_json_output_success('Group ('.$attrs['group_id'].') deleted');
				}
			}
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}


	/**
	 * 		IMPORT QUESTION GROUP
	 * 		---------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#import_group]
	 * 		
	 */
	
	public function import_question_group() {
		$this->_set_result('This function is not provided');
		echo json_encode($this->json_output);
	}


	/**
	 * 		GET QUESTION GROUP PROPERTIES
	 * 		-----------------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#get_group_properties]
	 * 		
	 */
	
	public function get_question_group_properties() {
		$this->_set_result('This function is not provided');
		echo json_encode($this->json_output);
	}


	/**
	 * 		SET QUESTION GROUP PROPERTIES
	 * 		-----------------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#set_group_properties]
	 * 		
	 */
	
	public function set_question_group_property() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get the attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure setting does not contain more then one property to change
			if(count($attrs['setting']) == 1) {
				if(is_array($attrs['setting'])) {
					$property_name = '(not assigned)';
					$property_value = '(not assigned)';
					foreach ($attrs['setting'] as $key => $value) {
						$property_name = $key;
						$property_value = $value;
					}
					// make sure the setting is in the right format
					if($property_name !== '(not assigned)' && $property_value !== '(not assigned)') {
						// make sure we have what we need in the post attributes data
						if($this->_post_attributes_contains(array('group_id','setting'))) {
							// make sure group id is an intergers
							if($this->_is_int($attrs['group_id'])) {
								// make sure it's a real setting being set
								if(in_array($property_name,$this->survey_question_group_properties_you_can_write)) {
									// populate the array we're going to send
									$survey_property_array = array(
										'sSessionKey' => $_POST['session_key'],
										'iGroupID' => $attrs['group_id'],
										'aGroupData' => $attrs['setting'],
										'sLanguage' => 'en'
									);
									// run the JSON-RPC call
									$this->json_input = $this->client->execute('set_group_properties', $survey_property_array);
									// check the json_input and update the json output
									$this->_build_json_output_success('Question group (' . $attrs['group_id'] . ') ' . $property_name . ' changed to ' . $property_value);
								} else { $this->_set_result($property_name . ' is not a question property you can change'); }
							}
						}
					} else { $this->_set_result('Setting needs to be an array consisting of a property and value (' . $attrs['setting'] . ')'); }
				} else { $this->_set_result('Setting is not in the correct format (' . gettype($attrs['setting']) . ')'); }
			} else { $this->_set_result('Can not change more then one property at a time (' . json_encode($attrs['setting']) . ')'); }
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}


	/**
	 * 		LIST QUESTION GROUPS
	 * 		--------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#list_groups]
	 * 		
	 */
	
	public function list_question_groups() {
		echo json_encode($this->_list_question_groups_function());
	}
	protected function _list_question_groups_function() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure survey_id is in the post data
			if($this->_post_attributes_contains(array('survey_id'))) {
				// make sure survey_id is an interger
				if($this->_is_int($attrs['survey_id'])) {
					// run the JSON-RPC call
					$this->json_input = $this->client->execute('list_groups', array('sSessionKey' => $_POST['session_key'], 'iSurveyID' => $attrs['survey_id']));
					// check the json_input and update the json output
					$this->_build_json_output_success($this->json_input);
				}
			}
		}
		// send an array back to the function call
		return $this->json_output;
	}



	                    	/************************************************/
    	            	    /*                                              */
        	    	        /*               SURVEY QUESTIONS               */
        		            /*                                              */
    	        	        /************************************************/



	/**
	 * 		ADD SURVEY QUESTION (custom)
	 * 		-------------------
	 *
	 * 		[LINK: (none)]
	 * 		
	 */
	
	public function add_survey_question() {
		echo json_encode($this->_add_question_function());
	}



	/**
	 * 		DELETE SURVEY QUESTION
	 * 		----------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#delete_question]
	 * 		
	 */

	public function delete_survey_question() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure we have what we need in the post data
			if($this->_post_attributes_contains(array('survey_id','question_id'))) {
				// make sure they are all intergers
				if($this->_is_int($attrs['survey_id']) && $this->_is_int($attrs['question_id'])) {
					$deleted_question_return_value = $this->client->execute('delete_question', array('sSessionKey' => $_POST['session_key'], 'iQuestionID' => $attrs['question_id']));
					// check the json_input and update the json output
					$this->_build_json_output_success('Question deleted (' . $deleted_question_return_value . ')');
				}
			}
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}



	/**
	 * 		GET SURVEY QUESTION PROPERTIES
	 * 		------------------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#import_question]
	 * 		
	 */
	public function import_survey_question() {
		$this->_set_result('This function is not provided');
		echo json_encode($this->json_output);
	}



	/**
	 * 		GET SURVEY QUESTION PROPERTIES
	 * 		------------------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#get_question_properties]
	 * 		
	 */
	public function get_survey_question_properties() {
		echo json_encode($this->_get_survey_question_properties_function());
	}
	protected function _get_survey_question_properties_function() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure their is a survey_id
			if($this->_post_attributes_contains(array('question_id','setting'))) {
				// make suret he survey_id is an interger
				if($this->_is_int($attrs['question_id'])) {
					// get all the properties to query
					$properties = explode(',',$attrs['setting']);
					$all_survey_question_properties_exist = TRUE;
					// make sure all properties passed exist
					foreach ($properties as $value) {
						// if it doesn't exist we don't proceed
						if(!in_array($value,$this->survey_question_properties_you_can_read)) { $all_survey_question_properties_exist = FALSE; }
					}
					// make sure it's a real question property being set
					if($all_survey_question_properties_exist) {
						// build the array we're going to send with the RPC call
						$get_question_properties_array = array(
							'sSessionKey' => $_POST['session_key'],
							'iQuestionID' => $attrs['question_id'],
							'aQuestionSettings' => $properties
							//'sLanguage' => 'en'
						);
						$this->json_input = $this->client->execute('get_question_properties', $get_question_properties_array);
						// check the json_input and update the json output
						$this->_build_json_output_success($this->json_input);
					} else { $this->_set_result('One of these is not a question property (' . $attrs['setting'] . ')'); }
				}
			}
		}
		return $this->json_output;
	}



	/**
	 * 		SET SURVEY QUESTION PROPERTY
	 * 		----------------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#set_question_properties]
	 * 		
	 */
	public function set_survey_question_properties() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get the attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure setting does not contain more then one property to change
			if(is_array($attrs['setting'])) {
				// get all the properties to query
				$properties = explode(',',$attrs['setting']);
				$all_survey_question_properties_exist = TRUE;
				// make sure all properties passed exist
				foreach ($properties as $value) {
					// if it doesn't exist we don't proceed
					if(!in_array($value,$this->survey_question_properties_you_can_write)) { $all_survey_question_properties_exist = FALSE; }
				}
				// make sure it's a real question property being set
				if($all_survey_question_properties_exist) {
					// make sure we have what we need in the post attributes data
					if($this->_post_attributes_contains(array('survey_id','question_id','setting'))) {
						// make sure they are all intergers
						if($this->_is_int($attrs['survey_id']) && $this->_is_int($attrs['question_id'])) {
							// populate the array we're going to send
							$survey_property_array = array(
								'sSessionKey' => $_POST['session_key'],
								'iQuestionID' => $attrs['question_id'],
								'aQuestionData' => $attrs['setting'],
								'sLanguage' => 'en'
							);
							// checks to see if it's a dry run
							if(isset($attrs['test'])) { $survey_property_array['test'] = TRUE; }
							// run the JSON-RPC call
							$this->json_input = $this->client->execute('set_question_properties', $survey_property_array);
							// check the json_input and update the json output
							$this->_build_json_output_success($this->json_input);
							//$this->_build_json_output_success('Question (' . $attrs['question_id'] . ') updated');
						}
					}
				} else { $this->_set_result('One of these is not a question property you can change (' . $attrs['setting'] . ')'); }
			} else { $this->_set_result('Setting is not in the correct format (' . gettype($attrs['setting']) . ')'); }
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}


	/**
	 * 		LIST SURVEY QUESTIONS
	 * 		---------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#list_questions]
	 * 		
	 */
	public function list_survey_questions() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get the attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure survey_id is in the post attributes data
			if($this->_post_attributes_contains(array('survey_id'))) {
				// make sure survey_is is an interger
				if($this->_is_int($attrs['survey_id'])) {
					// (don't have to repopulate $_POST as it the same for this function)
					// get the groups for this survey
					$survey_groups_return_value = $this->_list_question_groups_function();
					// make sure we got the groups successfully
					if($survey_groups_return_value['status'] == 'success') {
						// check the result was an array
						if(is_array($survey_groups_return_value['result'])) {
							$output = array();
							$questions = $survey_groups_return_value['result'];
							// get the last question
							end($questions);
							$last_question = key($questions);
							reset($questions);
							// loop over the question groups
							foreach ($questions as $key => $value) {
								// build the array we are going to send to the RPC function
								$list_survey_questions_array = array(
									'sSessionKey' => $_POST['session_key'],
									'iSurveyID' => $attrs['survey_id'],
									'iGroupID' => $value['id']['gid']
									//'sLanguage' => 'en'
								);
								// run the JSON-RPC call
								$this->json_input = $this->client->execute('list_questions', $list_survey_questions_array);
								// check the json_input and update the json output
								$this->_build_json_output_success($this->json_input);
								// if the returned value was good ...
								if($this->json_output['status'] == 'success') {
									// .. add it to the output array
									$output = array_merge($output, $this->json_input);
									// if it's the last question ...
									if($key === $last_question) {
										// ... add the final result to json_output
										$this->_set_result($output);
									}
								} else {
									$this->_set_result('There was an error retrieving the questions ('.$this->json_input.')');
									echo json_encode($this->json_output);
									return;
								}
							}
						} else { $this->_set_result('The groups were returned in the wrong format (' . gettype($survey_groups_return_value['result'][0]) . ')'); }
					} else { $this->_set_result('Could not get a group for the survey (' . $survey_question_return_values['result'][0] . ')'); }
				}
			}
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}



	/**
	 * 		GET SURVEY QUESTION PROPERTIES
	 * 		------------------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#import_question]
	 * 		
	 */
	public function list_question_group_questions() {
		$this->_set_result('This function is not provided');
		echo json_encode($this->json_output);
	}


	/**
	 * 		RE-ORDER QUESTIONS
	 * 		------------------
	 *
	 * 		[LINK: none]
	 * 		
	 */
	public function order_survey_question() {
		echo json_encode($this->_order_survey_questions());
	}




	                    	/****************************************************/
    	            	    /*                                                  */
        	    	        /*        QUESTION SUB QUESTIONS AND ANSWERS        */
        		            /*                                                  */
    	        	        /****************************************************/


    	    // NOTE: Your right this is confusing. Answers are the answers provided for list, multiple
    	    //       choice and grid questions



	/**
	 * 		GET QUESTION ANSWERS
	 * 		--------------------
	 * 
	 * 		[LINK: none]
	 * 		
	 */
	
	public function get_question_answers() {
		echo json_encode($this->_get_question_answers());
	}


	/**
	 * 		SET QUESTION SUB QUESTIONS
	 * 		--------------------------
	 *
	 * 		[LINK: none]
	 * 		
	 */

	public function set_question_answers() {
		echo json_encode($this->_set_question_answers());
	}

	/**
	 * 		GET QUESTION ANSWERS
	 * 		--------------------
	 * 
	 * 		[LINK: none]
	 * 		
	 */
	
	public function get_question_sub_questions() {
		echo json_encode($this->_get_question_sub_questions());
	}


	/**
	 * 		SET QUESTION SUB QUESTIONS
	 * 		--------------------------
	 *
	 * 		[LINK: none]
	 * 		
	 */

	public function set_question_sub_questions() {
		echo json_encode($this->_set_question_sub_questions());
	}



	                    	/*********************************************/
    	            	    /*                                           */
        	    	        /*               SURVEY TOKENS               */
        		            /*                                           */
    	        	        /*********************************************/




	/**
	 * 		ACTIVATE SURVEY TOKENS
	 * 		----------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#activate_tokens]
	 * 		
	 */
	
	public function activate_survey_tokens() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure survey_id is in the post data
			if($this->_post_attributes_contains(array('survey_id'))) {
				// make sure survey_id is an interger
				if($this->_is_int($attrs['survey_id'])) {
					// run the JSON-RPC call
					$this->json_input = $this->client->execute('activate_tokens', array('sSessionKey' => $_POST['session_key'], 'iSurveyID' => $attrs['survey_id']));
					// check the json_input and update the json output
					$this->_build_json_output_success('Survey (' . $attrs['survey_id'] . ') tokens activated');
				}
			}
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}



	/**
	 * 		LIST SURVEY TOKENS PARTICIPANTS
	 * 		-------------------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#list_participants]
	 * 		
	 */
	
	public function list_survey_participants() {
		echo json_encode($this->_list_survey_participants_function());
	}
	protected function _list_survey_participants_function() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure survey_id is in the post data
			if($this->_post_attributes_contains(array('survey_id'))) {
				// make sure survey_id is an interger
				if($this->_is_int($attrs['survey_id'])) {
					// have they asked for un-used tokens?
					$bUnused = False;
					if($this->_post_attributes_contains(array('unused'))) {
						$bUnused = true;
					}
					// build the array we're going to send to the RPC function
					$list_participants_array = array(
						'sSessionKey' => $_POST['session_key'],
						'iSurveyID' => $attrs['survey_id'],
						'iStart' => 0,
						'iLimit' => 10000000,
						'bUnused' => $bUnused
					);
					// run the JSON-RPC call
					$this->json_input = $this->client->execute('list_participants', $list_participants_array);
					// check the json_input and update the json output
					$this->_build_json_output_success($this->json_input);
				}
			}
		}
		// send a array back to the function
		return $this->json_output;
	}



	/**
	 * 		ADD SURVEY PARTICIPANT
	 * 		----------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#add_participants]
	 * 		
	 */
	
	public function import_survey_participants() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure survey_id is in the post data
			if($this->_post_attributes_contains(array('survey_id','setting'))) {
				// make sure survey_id is an interger
				if($this->_is_int($attrs['survey_id'])) {
					$participant_data_is_complete = true;
					$participant_data = array();
					// so we can check the data and append additional variables
					foreach ($attrs['setting'] as $key => $value) {
						if(!$value['email']) {
							$participant_data_is_complete = false;
							$this->_set_result('One of the Participants was missing an Email Address (Line: ' . $key . ')');
							break;
						} else if(!$value['first_name']) {
							$participant_data_is_complete = false;
							$this->_set_result('One of the Participants was missing their First Name (Line: ' . $key . ')');
							break;
						} else if(!$value['last_name']) {
							$participant_data_is_complete = false;
							$this->_set_result('One of the Participants was missing their Last Name (Line: ' . $key . ')');
							break;
						// all went well, build the participant
						} else {
							$participant_data[$key]['email'] = $value['email'];
							$participant_data[$key]['lastname'] = $value['last_name'];
							$participant_data[$key]['firstname'] = $value['first_name'];
							$participant_data[$key]['emailstatus'] = 'OK';
							$participant_data[$key]['language'] = 'en';
						}
					}
					// only save if the data is good
					if($participant_data_is_complete) {
						// check for duplicates in the data
						// (only passes through the first instance of the duplicate)
						$already_copied = array();
						$participant_data_unique = array();
						foreach($participant_data as $key => $value) {
						    if(!in_array($value['email'],$already_copied)) {
						        $participant_data_unique[] = $value;
						    }
						    $already_copied[] = $value['email'];
						}
						// check the list against the participants already attached to the survey
						$_POST['attrs'] = '{"survey_id":"' . $attrs['survey_id'] . '"}';
						$survey_participants_returned_array = $this->_list_survey_participants_function();
						// re-set $json_outhput['status'] back to error
						// (because the previous function call if successful set it to 'success')
						$this->_set_status_error();
						$survey_participants_in_json = '';
						$participant_data_duplicates_removed = array();
						if($survey_participants_returned_array['status'] == 'success' || 
						   strpos($survey_participants_returned_array['result'],'No Tokens found') !== FALSE) {
							// only test the emails if they were returned
							if(is_array($survey_participants_returned_array['result'])) {
								// get a json string of all the emails to make comparison eaiser
								$survey_participants_in_json = json_encode($survey_participants_returned_array['result']);
							}
						}
						foreach ($participant_data_unique as $k => $v) {
							if(!strpos($survey_participants_in_json, $v['email'])) {
								$participant_data_duplicates_removed[] = $v;
							}
						}
						// build the array we're going to send to the RPC function
						$add_participants_array = array(
							'sSessionKey' => $_POST['session_key'],
							'iSurveyID' => $attrs['survey_id'],
							'participantData' => json_encode($participant_data_duplicates_removed)
						);
						// run the JSON-RPC call
						$this->json_input = $this->client->execute('add_participants', $add_participants_array);
						// check the json_input and update the json output
						$this->_build_json_output_success($this->json_input);
					}
				}
			}
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}



	/**
	 * 		ADD SURVEY PARTICIPANT
	 * 		----------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#add_participants]
	 * 		
	 */
	
	public function add_survey_participant() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure survey_id is in the post data
			if($this->_post_attributes_contains(array('survey_id','setting'))) {
				// make sure 'setting' contains the values we are going to pass
				if(isset($attrs['setting']['email']) && isset($attrs['setting']['first_name']) && isset($attrs['setting']['last_name'])) {
					// get a list of participants to check against
					$_POST['attrs'] = '{"survey_id":"' . $attrs['survey_id'] . '"}';
					$survey_participants_returned_array = $this->_list_survey_participants_function();
					// re-set $json_outhput['status'] back to error
					// (because the previous function call if successful set it to 'success')
					$this->_set_status_error();
					// make sure we got the participants or there are no participants to loop over
					if($survey_participants_returned_array['status'] == 'success' || strpos($survey_participants_returned_array['result'],'No Tokens found') !== FALSE) {
						$survey_participant_is_unique = TRUE;
						// only test the emails if they were returned
						if(is_array($survey_participants_returned_array['result'])) {
							// check to see if there is a duplicate
							foreach ($survey_participants_returned_array['result'] as $key => $value) {
								if($value['participant_info']['email'] == $attrs['setting']['email']) {
									$survey_participant_is_unique = FALSE;
								}
							}
						}
						// don't proceed if there is a duplicate
						if($survey_participant_is_unique) {
							// make sure survey_id is an interger
							if($this->_is_int($attrs['survey_id'])) {
								// build the array we're going to send to the RPC function
								$add_participant_array = array(
									'sSessionKey' => $_POST['session_key'],
									'iSurveyID' => $attrs['survey_id'],
									'participantData' => '[{"email":"' . $attrs['setting']['email'] . '","lastname":"' . $attrs['setting']['last_name'] . '","firstname":"' . $attrs['setting']['first_name'] . '","emailstatus":"OK","language":"en"}]'
								);
								// run the JSON-RPC call
								$this->json_input = $this->client->execute('add_participants', $add_participant_array);
								// check the json_input and update the json output
								$this->_build_json_output_success($this->json_input);
							}
						} else { $this->_set_result('Email address has already been added'); }
					} else { $this->_set_result($survey_participants_returned_array['result']); }
				// wrong values in post data
				} else {
					if(is_array($attrs['setting'])) { $this->_set_result('Post data missing either first_name, last_name or email (' . implode(',',$attrs['setting']) . ')'); }
					else { $this->_set_result('Post data missing either first_name, last_name or email (' . $attrs['setting'] . ')'); }
				}
			}
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}



	/**
	 * 		DELETE SURVEY PARTICIPANTS
	 * 		--------------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#delete_participants]
	 * 		
	 */
	
	public function delete_survey_participant() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure survey_id is in the post data
			if($this->_post_attributes_contains(array('survey_id','token_id'))) {
				// make sure post attributes contains the the token id to delete
				if(isset($attrs['token_id'])) {
					// make sure survey_id and the token id are both intergers
					if($this->_is_int($attrs['survey_id']) && $this->_is_int($attrs['token_id'])) {
						// build the array we're going to send to the RPC function
						$delete_participant_array = array(
							'sSessionKey' => $_POST['session_key'],
							'iSurveyID' => $attrs['survey_id'],
							'aTokenIDs' => array($attrs['token_id'])
						);
						// run the JSON-RPC call
						$this->json_input = $this->client->execute('delete_participants', $delete_participant_array);
						// check the json_input and update the json output
						$this->_build_json_output_success($this->json_input);
					}
				// wrong values in post data
				} else { $this->_set_result('No token to delete (' . json_encode($attrs) . ')'); }
			}
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}



	/**
	 * 		GET SURVEY PARTICIPANT PROPERTIES
	 * 		---------------------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#get_participant_properties]
	 * 		
	 */
	
	// TODO: need a check here to make sure they are querying a value that actually exists
	public function get_survey_participant_properties() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure survey_id is in the post data
			if($this->_post_attributes_contains(array('survey_id','token_id','setting'))) {
				// make sure 'setting' contains the the token id to delete
				if(isset($attrs['survey_id']) && isset($attrs['token_id'])) {
					// make sure survey_id and the token id are both intergers
					if($this->_is_int($attrs['survey_id']) && $this->_is_int($attrs['token_id'])) {
						// build the array we're going to send to the RPC function
						$get_participant_properties_array = array(
							'sSessionKey' => $_POST['session_key'],
							'iSurveyID' => $attrs['survey_id'],
							'iTokenID' => $attrs['token_id'],
							'aTokenProperties' => explode(',',$attrs['setting'])
						);
						// run the JSON-RPC call
						$this->json_input = $this->client->execute('get_participant_properties', $get_participant_properties_array);
						// check the json_input and update the json output
						$this->_build_json_output_success($this->json_input);
					}
				// wrong values in post data
				} else { $this->_set_result('No survey ID or token ID sent (' . $_POST['attrs'] . ')'); }
			}
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}



	/**
	 * 		SET SURVEY PARTICIPANT PROPERTY
	 * 		-------------------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#set_participant_properties]
	 * 		
	 */
	public function set_survey_participant_properties() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure survey_id is in the post data
			if($this->_post_attributes_contains(array('survey_id','token_id','setting'))) {
				// make sure survey_id and token_id are present
				if(isset($attrs['survey_id']) && isset($attrs['token_id'])) {
					// make sure the setting data is an array
					if(is_array($attrs['setting']))  {
						// check to see if the email is being set
						$survey_participant_is_unique = TRUE;
						if(isset($attrs['setting']['email'])) {
							// query the email against the other participant emails
							$_POST['attrs'] = '{"survey_id":"' . $attrs['survey_id'] . '"}';
							$survey_participants_returned_array = $this->_list_survey_participants_function();
							// re-set $json_outhput['status'] back to error
							// (because the previous function call if successful set it to 'success')
							$this->_set_status_error();
							// make sure we got the participants without a hitch
							if($survey_participants_returned_array['status'] == 'success' || strpos($survey_participants_returned_array['result'],'No Tokens found') !== FALSE) {
								// only test the emails if they were returned
								if(is_array($survey_participants_returned_array['result'])) {
									// check to see if there is a duplicate (and it's not the one we're changing. Doh!)
									foreach ($survey_participants_returned_array['result'] as $key => $value) {
										if($value['participant_info']['email'] == $attrs['setting']['email'] &&
										   $value['tid'] !== $attrs['token_id']) {
											$survey_participant_is_unique = FALSE;
										}
									}
								}
							// the lookup failed
							} else {
								$this->_set_result('Could not check for duplicate email addresses (' . $survey_participants_returned_array['result'] . ')');
								$survey_participant_is_unique = FALSE;
							}
						}
						// don't proceed if there is a duplicate
						if($survey_participant_is_unique) {
							// make sure survey_id and the token id are both intergers
							if($this->_is_int($attrs['survey_id']) && $this->_is_int($attrs['token_id'])) {
								// build the array we're going to send to the RPC function
								$set_participant_properties_array = array(
									'sSessionKey' => $_POST['session_key'],
									'iSurveyID' => $attrs['survey_id'],
									'iTokenID' => $attrs['token_id'],
									'aTokenData' => $attrs['setting']
								);
								// run the JSON-RPC call
								$this->json_input = $this->client->execute('set_participant_properties', $set_participant_properties_array);
								// check the json_input and update the json output
								$this->_build_json_output_success($this->json_input);
							}
						} else { $this->_set_result('A participant with this email address already exists'); }
					// setting is not an array of data to set
					} else { $this->_set_result('The setting data is not in the right format (' . $_POST['attrs'] . ')'); }
				// wrong values in post data
				} else { $this->_set_result('No survey ID or token ID sent (' . $_POST['attrs'] . ')'); }
			}
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}



	/**
	 * 		INVITE SURVEY PARTICIPANT
	 * 		-------------------------
	 *
	 */

	public function invite_survey_participant() {
		echo json_encode($this->_invite_survey_participant());
	}



	/**
	 * 		INVITE SURVEY PARTICIPANTS
	 * 		--------------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#invite_participants]
	 *
	 * 		RETURN DATA: {"status":"success","result":{"16":{"name":"James Bond","email":"robert.johnstone@ors.org.uk","status":"OK"},"17":{"name":"Simon Smith","email":"robert.johnstone.admin@ors.org.uk","status":"OK"},"18":{"name":"as as","email":"robert.johnstone.user@ors.org.uk","status":"OK"},"status":"0 left to send"}}
	 */

	public function invite_all_survey_participants() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure survey_id is in the post data
			if($this->_post_attributes_contains(array('survey_id'))) {
				// make sure survey_id is set
				if(isset($attrs['survey_id'])) {
					// run the JSON-RPC call
					$this->json_input = $this->client->execute('invite_participants', array('sSessionKey' => $_POST['session_key'],'iSurveyID' => $attrs['survey_id']));
					// check the json_input and update the json output
					$this->_build_json_output_success($this->json_input);
				// wrong values in post data
				} else { $this->_set_result('No survey ID sent (' . $_POST['attrs'] . ')'); }
			}
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}



	/**
	 * 		REMIND SURVEY PARTICIPANT
	 * 		-------------------------
	 *
	 */

	public function remind_survey_participant() {
		echo json_encode($this->_remind_survey_participant());
	}



	/**
	 * 		REMIND SURVEY PARTICIPANTS
	 * 		--------------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#remind_participants]
	 * 		
	 */

	public function remind_all_survey_participants() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure survey_id is in the post data
			if($this->_post_attributes_contains(array('survey_id'))) {
				// make sure survey_id is set
				if(isset($attrs['survey_id'])) {
					// run the JSON-RPC call
					$this->json_input = $this->client->execute('remind_participants', array('sSessionKey' => $_POST['session_key'],'iSurveyID' => $attrs['survey_id']));
					// check the json_input and update the json output
					$this->_build_json_output_success($this->json_input);
				// wrong values in post data
				} else { $this->_set_result('No survey ID sent (' . $_POST['attrs'] . ')'); }
			}
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}



	                    	/************************************************/
    	            	    /*                                              */
        	    	        /*               SURVEY RESPONSES               */
        		            /*                                              */
    	        	        /************************************************/




	/**
	 * 		GET SURVEY RESPONSES
	 * 		--------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#add_response]
	 * 		
	 */
	public function add_survey_response() {
		$this->_set_result('This function is not provided');
		echo json_encode($this->json_output);
	}



	/**
	 * 		EXPORT SURVEY RESPONSES
	 * 		-----------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#export_responses]
	 * 		
	 */
	// Function not required
	// NOT WORKING (never completed)
	/*public function export_survey_responses() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent()) {
			// get attributes as an array
			$attrs = json_decode($_GET['attrs'],true);
			// make sure get->attributes is avaliable as json
			if($this->_is_json($attrs) {
				// make sure survey_id is set
				if(isset($attrs['survey_id'])) {
					$export_survey_responses_file_array = array(
						'sSessionKey' => $_POST['session_key'],
						'iSurveyID' => $attrs['survey_id'],
						'$sDocumentType' => 'pdf',
						'$sLanguageCode' => 'en',
					);
					// run the JSON-RPC call
					$export_responces_return_data = $this->client->execute('export_responses', array());
					// make sure we got the right format back
					if(gettype($export_responces_return_data) == 'object') {
						header("Content-type: text/plain");
						header("Content-Disposition: attachment; filename=savethis.txt");
						print 'Dave!';
					else {
						// check the json_input and update the json output
						$this->_build_json_output_success($export_responces_return_data);
					}
				// wrong values in post data
				} else { $this->_set_result('No survey ID sent (' . $_POST['attrs'] . ')'); }
			}
		}
		// send a json responce back to the page
		echo json_encode($this->json_output);
	}*/




	/**
	 * 		EXPORT SURVEY RESPONSES BY TOKEN
	 * 		--------------------------------
	 *
	 * 		[LINK: https://manual.limesurvey.org/index.php?title=RemoteControl_2_API&oldid=66111#export_responses_by_token]
	 * 		
	 */
	public function export_survey_responses_by_token() {
		$this->_set_result('This function is not provided');
		echo json_encode($this->json_output);
	}

	



		/********************************************************************************/
		/****************************                        ****************************/
		/****************************    CUSTOM FUNCTIONS    ****************************/
		/****************************                        ****************************/
		/********************************************************************************/



	/**
	 *       Export Survey (CUSTOM)
	 */
/*
	public function export_survey() {
		if($this->_session_key_was_sent()) {
			$this->json_input = $this->client->execute('export_survey', array('sSessionKey' => $_POST['session_key'], 'iSurveyID' => '453482'));
			// pass the returned data
			$xml = XMLReader::xml($this->json_input);
			$xml->setParserProperty(XMLReader::VALIDATE, true);
			// validate that the returned data is XML
			if($xml->isValid()) {
				// stored the returned data locally
				return TRUE;
			} else { return FALSE; }
		}
	}
*/

	/**
	 *       Duplicate Survey (CUSTOM)
	 */
	protected function _duplicate_survey_function() {
		// make sue we have a session key and the post data is in the right format
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get the attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure their is a survey_id in post attributes
			if($this->_post_attributes_contains(array('survey_id'))) {
				// make suret he survey_id is an interger
				if($this->_is_int($attrs['survey_id'])) {
					// call returns true or false
					$this->json_input = $this->client->execute(
						'duplicate_survey',
						array(
							'sSessionKey' => $_POST['session_key'],
							'iSurveyID' => $attrs['survey_id']
						)
					);

					// duplicated the survey successfully
					$this->_build_json_output_success('Survey duplicated');  // checks json_input as well
				}
			}
		}
		// send an array back to the public function
		return $this->json_output;
	}


	/**
	 *       Add Question (CUSTOM)
	 */

	protected function _add_question_function() {
		// make sue we have a session key and the post data is in the right format
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get the attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure their is a survey_id in post attributes
			if($this->_post_attributes_contains(array('survey_id'))) {
				// make suret he survey_id is an interger
				if($this->_is_int($attrs['survey_id'])) {
					// catch the missing attributes
					$attribute_missing = array();
					if(!isset($attrs['setting']['question'])) { $attribute_missing[] = 'question'; }
					if(!isset($attrs['setting']['help_text'])) { $attribute_missing[] = 'help_text'; }
					if(!isset($attrs['setting']['type'])) { $attribute_missing[] = 'type'; }
					if(!isset($attrs['setting']['mandatory'])) { $attribute_missing[] = 'mandatory'; }
					if(!isset($attrs['setting']['other'])) { $attribute_missing[] = 'other'; }
					// make sure none of the attributes are missing before proceeding
					if(count($attribute_missing) < 1) {
						// (I haven't changed this because may be in the future other question types will be added)
						if(in_array($attrs['setting']['type'], array('F',':',':','H','1','D','*','U','!','L','O','T','M','K','Q','N','R','S','X'))) {
							$method_attrs = array(
								'sSessionKey' => $_POST['session_key'],
								'iSurveyID' => $attrs['survey_id'],
								'question' => $attrs['setting']['question'],
								'help_text' => $attrs['setting']['help_text'],
								'type' => $attrs['setting']['type'],
								'mandatory' => $attrs['setting']['mandatory'],
								'other' => $attrs['setting']['other'],
							);
							// call returns an error or the id of the new question
							$this->json_input = $this->client->execute('add_question', $method_attrs);
							// the question was created successfully
							$this->_build_json_output_success('Question created (' . $this->json_input['status'] . ')');  // checks json_input as well
						} else { $this->_set_result('Question type not recognised'); }
					} else { $this->_set_result('Attributes are missing (' . implode(', ',$attribute_missing).')'); }
				}
			}
		} // function does it's own errors
		// send a array back to the function call
		return $this->json_output;
	}


	/**
	 *       Order Survey Questions
	 */

	// NOTE: Submit data in this format: list[g517]=root&list[q5204]=g517&list[q5205]=g517&list[q5196]=g517&list[q5192]=g517
	protected function _order_survey_questions() {
		// make sue we have a session key and the post data is in the right format
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get the attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure their is a survey_id in post attributes
			if($this->_post_attributes_contains(array('survey_id','setting'))) {
				if(count($attrs['setting']) > 2) {
					$method_attrs = array(
						'sSessionKey' => $_POST['session_key'],
						'iSurveyID' => $attrs['survey_id'],
						'data' => implode('&', $attrs['setting'])
					);
					// call the RPC function
					$this->json_input = $this->client->execute('order_questions', $method_attrs);
					// check the json_input and update the json output
					$this->_build_json_output_success($this->json_input);
				} else { $this->_set_result('Not enough items to re-organise'); }
			}
		}
		// send a array back to the function call
		return $this->json_output;
	}


	/**
	 *       Search Global Participants (CUSTOM)
	 */

	/*protected function _set_questions_answers() {
		// make sue we have a session key and the post data is in the right format
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get the attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure their is a survey_id and question_id in post attributes
			if($this->_post_attributes_contains(array('survey_id','question_id','setting'))) {
				$method_attrs = array(
					'sSessionKey' => $_POST['session_key'],
					'iSurveyID' => $attrs['survey_id'],
					'iQuestionID' => $attrs['question_id'],
					'data' => implode('&', $attrs['setting'])
				);
				// call the RPC function
				$this->json_input = $this->client->execute('set_question_answer_options', $method_attrs);
				// check the json_input and update the json output
				$this->_build_json_output_success($this->json_input);
			}
		}
		// send a array back to the function call
		return $this->json_output;
	}*/


	/**
	 * 		Get Question Answers (CUSTOM)
	 */
	
	protected function _get_question_answers() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure survey_id is in the post data
			if($this->_post_attributes_contains(array('survey_id','question_id'))) {
				// make sure survey_id and question_id are intergers
				if($this->_is_int($attrs['survey_id']) && $this->_is_int($attrs['question_id'])) {
					// run the JSON-RPC call
					$this->json_input = $this->client->execute('get_question_answers', array('sSessionKey' => $_POST['session_key'], 'iSurveyID' => $attrs['survey_id'], 'iQuestionID' => $attrs['question_id']));
					// check the json_input and update the json output
					$this->_build_json_output_success($this->json_input);
				}
			}
		}
		// send a json responce back to the page
		return $this->json_output;
	}


	/**
	 * 		Set Question Sub-Questions (CUSTOM)
	 */

	protected function _set_question_answers() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure survey_id is in the post data
			if($this->_post_attributes_contains(array('survey_id','question_id','setting'))) {
				// make sure survey_id and question_id are intergers
				if($this->_is_int($attrs['survey_id']) && $this->_is_int($attrs['question_id'])) {
					// build the array we are going to send to the RPC function
					$set_question_answers_array = array(
						'sSessionKey' => $_POST['session_key'],
						'iSurveyID' => $attrs['survey_id'],
						'iQuestionID' => $attrs['question_id'],
						'data' => $attrs['setting']
						//'sLanguage' => 'en'
					);
					// run the JSON-RPC call
					$this->json_input = $this->client->execute('set_question_answers', $set_question_answers_array);
					// check the json_input and update the json output
					$this->_build_json_output_success($this->json_input);
				}
			}
		}
		// send a json responce back to the page
		return $this->json_output;
	}

	/**
	 * 		Get Questions Answers (CUSTOM)
	 */
	
	protected function _get_question_sub_questions() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure survey_id is in the post data
			if($this->_post_attributes_contains(array('survey_id','question_id'))) {
				// make sure survey_id and question_id are intergers
				if($this->_is_int($attrs['survey_id']) && $this->_is_int($attrs['question_id'])) {
					// run the JSON-RPC call
					$this->json_input = $this->client->execute('get_question_sub_questions', array('sSessionKey' => $_POST['session_key'], 'iSurveyID' => $attrs['survey_id'], 'iQuestionID' => $attrs['question_id']));
					// check the json_input and update the json output
					$this->_build_json_output_success($this->json_input);
				}
			}
		}
		// send a json response back to the page
		return $this->json_output;
	}


	/**
	 * 		Set Questions Sub-Questions (CUSTOM)
	 */

	protected function _set_question_sub_questions() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure survey_id is in the post data
			if($this->_post_attributes_contains(array('survey_id','question_id','setting'))) {
				// make sure survey_id and question_id are intergers
				if($this->_is_int($attrs['survey_id']) && $this->_is_int($attrs['question_id'])) {
					// build the array we are going to send to the RPC function
					$set_question_answers_array = array(
						'sSessionKey' => $_POST['session_key'],
						'iSurveyID' => $attrs['survey_id'],
						'iQuestionID' => $attrs['question_id'],
						'data' => $attrs['setting']
						//'sLanguage' => 'en'
					);
					// run the JSON-RPC call
					$this->json_input = $this->client->execute('set_question_sub_questions', $set_question_answers_array);
					// check the json_input and update the json output
					$this->_build_json_output_success($this->json_input);
				}
			}
		}
		// send a json responce back to the page
		return $this->json_output;
	}


	/**
	 *       Search Global Participants (CUSTOM)
	 */

	protected function _search_global_participants_function() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get the attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure the right variables are in the post attributes (and populated)
			//if($this->_post_attributes_contains(array('value','field')) && strlen($attrs['field']) !== 0) {
			if($this->_post_attributes_contains(array('value'))) {
				$method_attrs = array(
					'sSessionKey' => $_POST['session_key'],
					//'field' => $attrs['field'],
					'value' => $attrs['value']
				);
				// if we are looking at a different position in the search, that needs to be passed on
				if($this->_post_attributes_contains(array('page'))) {
					$method_attrs['page'] = $attrs['page'];
				}
				// call the RPC function
				$this->json_input = $this->client->execute('search_global_participants', $method_attrs);
				// check the json_input and update the json output
				$this->_build_json_output_success($this->json_input);
			} else { $this->_set_result('No value to search for'); }
		}
		return $this->json_output;
	}


	/**
	 *       Get Global Participants (CUSTOM)
	 */

	public function _get_global_participants_function() {
		// check the session is alive
		if($this->_session_key_was_sent()) {
			// get the attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// at a minium we have to send this
			$method_attrs = array(
				'sSessionKey' => $_POST['session_key'],
				'search' => null,
				'page' => null,
				'results_per_page' => $this->global_participants_per_page
			);
			// if there are search attributes, pass them through
			// (if page is not specified it will default to page 1)
			if($this->_attributes_are_in_post_data_and_json()) {
				if($this->_post_attributes_contains(array('page'))) { $method_attrs['page'] = $attrs['page']; }
			}
			// call the RPC function
			$this->json_input = $this->client->execute('get_global_participants', $method_attrs);
			// check the json_input and update the json output
			$this->_build_json_output_success($this->json_input);
		}
		return $this->json_output;
	}


	/**
	 *       Edit Global Participant (CUSTOM)
	 */

	protected function _set_global_participant_function() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get the attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure the right variables are in the post attributes
			if($this->_post_attributes_contains(array('participant_id','first_name','last_name','email'))) {
				$method_attrs = array(
					'sSessionKey' => $_POST['session_key'],
					'pID' => $attrs['participant_id'],
					'first_name' => $attrs['first_name'],
					'last_name' => $attrs['last_name'],
					'email' => $attrs['email']
				);
				// call the RPC function
				$this->client->execute('set_global_participant', $method_attrs);
				// make sure the values have changed in the db
				$global_participants =  $this->_get_global_participants_function();
				// loop of all the participants and make sure the participant exists
				$participant_exists = null;
				$participant_check = null;
				foreach ($global_participants['result']['rows'] as $key => $value) {
					if($attrs['participant_id'] == $value['id']) {
						// make sure the data has been changed
						if ($value['cell'][0] == $attrs['participant_id'] &&
							$value['cell'][2] == $attrs['first_name'] &&
							$value['cell'][3] == $attrs['last_name'] &&
							$value['cell'][4] == $attrs['email']) {
							$participant_check = 'passed';
						}
						$participant_exists = $attrs['participant_id'];
					}
				}
				if($participant_exists) {
					if($participant_check) {
						// check the json_input and update the json output
						$this->_build_json_output_success('Contact ' . $attrs['first_name'] . ' ' . $attrs['last_name'] . ' saved');
					} else { $this->_set_result('The values were not saved'); }
				} else { $this->_set_result('Participant does not exist'); }
			}
		}
		return $this->json_output;
	}


	/**
	 *       Add Global Participant (CUSTOM)
	 */

	protected function _add_global_participant_function() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get the attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure the right variables are in the post attributes
			if($this->_post_attributes_contains(array('first_name','last_name','email'))) {
				// make sure the email doesn't exist in the db already
				$global_participants =  $this->_get_global_participants_function();
				// reset the class variables (because _get_global_participants_function() has populated them)
				$this->_set_status_error();
				$this->_set_result('');
				// loop of all the participants to see if there is a duplicate email
				$participant_exists = null;
				foreach ($global_participants['result']['rows'] as $key => $value) {
					if(strpos(strtolower($attrs['email']),strtolower($value['cell'][4])) !== FALSE) {
						$participant_exists = true;
					}
				}
				if(!$participant_exists) {
					$method_attrs = array(
						'sSessionKey' => $_POST['session_key'],
						'first_name' => $attrs['first_name'],
						'last_name' => $attrs['last_name'],
						'email' => $attrs['email']
					);
					// call the RPC function
					$json_input = $this->client->execute('add_global_participant', $method_attrs);
					//echo json_encode($json_input);
					// make sure the values have changed in the db
					$global_participants =  $this->_get_global_participants_function();
					// reset the class variables (because _get_global_participants_function() has populated them)
					$this->_set_status_error();
					$this->_set_result($json_input);
					// loop of all the participants and make sure the participant exists
					$participant_id = null;
					$participant_saved = null;
					$test = 'a';
					foreach ($global_participants['result']['rows'] as $key => $value) {
						$test .= 'b ['.$attrs['email'].' - '.$value['cell'][4].']';
						if($attrs['email'] == $value['cell'][4]) {
							$test = 'c'.$value['cell'][4];
							// make sure the data has been saved
							if ($value['cell'][2] == $attrs['first_name'] &&
								$value['cell'][3] == $attrs['last_name'] &&
								$value['cell'][4] == $attrs['email']) {
								$test = 'd';
								$participant_saved = 'success';
							}
							$participant_id = $value['id'];
						}
					}
					if($participant_id) {
						if($participant_saved) {
							// check the json_input and update the json output
							$this->_build_json_output_success('Contact ' . $attrs['first_name'] . ' ' . $attrs['last_name'] . ' saved');
						} else { $this->_set_result('The values were not saved correctly. Please check the participants data'); }
					} else { $this->_set_result('Participant was not saved'); }
				} else { $this->_set_result('Duplicate participant exists'); }
			}
		}
		return $this->json_output;
	}


	/**
	 *       Delete Global Participant (CUSTOM)
	 */

	protected function _delete_global_participant_function() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get the attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure the right variables are in the post attributes
			if($this->_post_attributes_contains(array('participant_id'))) {
				// call the RPC function
				if($json_input = $this->client->execute('delete_global_participant', array('sSessionKey' => $_POST['session_key'],'participantID' => $attrs['participant_id']))) {
					$this->_build_json_output_success('Participant has been deleted');
				} else {
					$this->_set_result('Failed to delete participant (' . $attrs['participant_id'] . ')');
				}
			} else { $this->_set_result('Participant ID required'); }
		}
		return $this->json_output;
	}



	/**
	 * 		Invite Survey Participant
	 */

	protected function _invite_survey_participant() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure survey_id is in the post data
			if($this->_post_attributes_contains(array('survey_id','token_id'))) {
				// make sure survey_id is set
				if(isset($attrs['survey_id']) && isset($attrs['token_id'])) {
					$invite_participant_array = array(
						'sSessionKey' => $_POST['session_key'],
						'iSurveyID' => $attrs['survey_id'],
						'iTokenID' => $attrs['token_id']
					);
					// run the JSON-RPC call
					$this->json_input = $this->client->execute('invite_participant', $invite_participant_array);
					// check the json_input and update the json output
					$this->_build_json_output_success($this->json_input);
				// wrong values in post data
				} else { $this->_set_result('No survey ID or token ID (' . $_POST['attrs'] . ') sent'); }
			}
		}
		// send a json responce back to the page
		return $this->json_output;
	}



	/**
	 * 		Remind Survey participant
	 */

	protected function _remind_survey_participant() {
		// check the session and attributes in the post data
		if($this->_session_key_was_sent() && $this->_attributes_are_in_post_data_and_json()) {
			// get attributes as an array
			$attrs = json_decode($_POST['attrs'],true);
			// make sure survey_id is in the post data
			if($this->_post_attributes_contains(array('survey_id','token_id'))) {
				// make sure survey_id is set
				if(isset($attrs['survey_id']) && isset($attrs['token_id'])) {
					$invite_participant_array = array(
						'sSessionKey' => $_POST['session_key'],
						'iSurveyID' => $attrs['survey_id'],
						'iTokenID' => $attrs['token_id']
					);
					// run the JSON-RPC call
					$this->json_input = $this->client->execute('remind_participant', $invite_participant_array);
					// check the json_input and update the json output
					$this->_build_json_output_success($this->json_input);
				// wrong values in post data
				} else { $this->_set_result('No survey ID or token ID (' . $_POST['attrs'] . ') sent'); }
			}
		}
		// send a json responce back to the page
		return $this->json_output;
	}





		/*******************************************************************************/
		/****************************                       ****************************/
		/****************************    CLASS FUNCTIONS    ****************************/
		/****************************                       ****************************/
		/*******************************************************************************/





	/**
	 *       GET JSON OUTPUT
	 */

	public function get_json_output() {
		return $this->json_output;
	}


	/**
	 *       METHOD NOT FOUND
	 */

	public function method_not_found($bad_method_name) {
		$this->json_output['result'] = 'Method not found (' . $bad_method_name . ')';
	}


	/**
	 *       VARIOUS END POINTS
	 */
	protected function _build_remote_control_endpoint() { return $this->limesurvey_url . 'remotecontrol'; }
	//protected function _build_export_survey_endpoint($survey_id) { return $this->limesurvey_url . 'export/sa/survey/action/exportstructurexml/surveyid/' . $survey_id; }
	//protected function _build_deactivate_survey_endpoint($survey_id) { return $this->limesurvey_url . 'survey/sa/deactivate/surveyid/' . $survey_id; }
	//protected function _build_export_group_endpoint($survey_id, $group_id) { return $this->limesurvey_url . 'export/sa/group/surveyid/' . $survey_id . '/gid/' . $group_id; }
	//protected function _build_export_question_endpoint($survey_id, $group_id, $question_id) { return $this->limesurvey_url . 'export/sa/question/surveyid/' . $survey_id . '/gid/' . $group_id . '/qid/' . $question_id; }
	//protected function _build_login_endpoint() { return $this->limesurvey_url.'authentication/sa/login'; }


	/**
	 *       SESSION KEY IS AVALIABLE
	 * 
	 *       TODO: Delete this!
	 */

	protected function _session_key_avaliable() {
		if(isset($_POST['session_key'])) { return TRUE; }
		else { $this->_set_result('There is no session or the session has expired'); return FALSE; }
	}

	/**
	 *       SESSION KEY IS SENT
	 */

	protected function _session_key_was_sent() {
		if(isset($_POST['session_key'])) {
			return TRUE;
		}
		$this->_set_result('No session key was sent');
		return FALSE;
	}


	/**
	 *       ARRTIBUTES ARE IN THE POST DATA
	 */

	protected function _attributes_are_in_post_data_and_json() {
		if(isset($_POST['attrs'])) { if($this->_is_json($_POST['attrs'])) { return TRUE; } else { return FALSE;} }
		else { $this->_set_result('There are no attributes or post data'); return FALSE; }
	}


	/**
	 *       POST ATTRIBUTES CONTAINS
	 */

	protected function _post_attributes_contains($array) {
		// just incase it's called before '_attributes_are_in_post_data_and_json()'
		if($this->_attributes_are_in_post_data_and_json()) {
			if(gettype($array) == 'array') {
				foreach ($array as $value) {
					$post_attrs_array = json_decode($_POST['attrs'],true);
					// if the value is not in the post attributes array return false
					if(!array_key_exists($value,$post_attrs_array)) {
						$this->_set_result(ucwords($value) . ' does not exist in the attributes array (' . implode(',',$post_attrs_array) . ')');
						return FALSE;
					}
				}
				return TRUE;
			} else { $this->_set_result('Array not passed to function _post_attributes_contains (' . gettype($array) .')'); return FALSE; }
		}
	}


	/**
	 *       IS INTERGER
	 */

	protected function _is_int($value) {
		if(ctype_digit($value)) { return TRUE; }
		else {
			if($value === NULL) { $this->_set_result('no value was passed'); }
			else { $this->_set_result($value .' is not a number'); }
			return FALSE;
		}
	}


	/**
	 *       SET STATUS SUCCESS
	 */

	protected function _set_status_success() {
		$this->json_output['status'] = 'success';
	}


	/**
	 *       SET STATUS ERROR
	 */

	protected function _set_status_error() {
		$this->json_output['status'] = 'error';
	}


	/**
	 *       SET RESULT
	 */

	protected function _set_result($input) {
		$this->json_output['result'] = $input;
	}


	/**
	 *      BUILD JSON OUTPUT
	 */
	
	protected function _build_json_output_success($value_to_return) {
		// as long as there wasn't an error in json_input ...
		if($this->_no_errors_in_json_input()) { 
			// ... change json_output
			$this->_set_status_success();
			$this->_set_result($value_to_return);
		}
	}



	/**
	 *      no errors in $this->json_input
	 */
	
	protected function _no_errors_in_json_input() {
		$no_errors_in_json_input = TRUE;
		// test for errors returned arrays
		if(gettype($this->json_input) == 'array') {
			// loop through the error codes
			foreach($this->return_array_error_statuses as $es) {
				//if the status is not an array ...
				if (!isset($this->json_input['status'])) {
					$this->json_input['status'] = '';
				}
				if(gettype($this->json_input['status'] == 'string')) {
					// .. compare it against the error
					if(strpos(strtolower($this->json_input['status']),strtolower($es)) !== FALSE) {
						//$this->_set_result('API returned an error [array]('.$this->json_input['status'].')');
						$this->_set_result($this->json_input['status']);
						$no_errors_in_json_input = FALSE;
						break;
					}
				}
			}
		// test for errors in returned strings
		} elseif (gettype($this->json_input) == 'string') {
			// loop through the error codes
			foreach($this->return_string_error_statuses as $es) {
				if(strpos(strtolower($this->json_input),strtolower($es)) !== FALSE) {
					//$this->_set_result('API returned an error [string]('.$this->json_input.')');
					$this->_set_result($this->json_input);
					$no_errors_in_json_input = FALSE;
					break;
				}
			}
		}
		// return the outcome
		return $no_errors_in_json_input;
	}


	/**
	 *      IS A STRING JSON
	 */
	
	protected function _is_json($string) {
		// only strings should be passed to this function
		if(gettype($string) !== 'string') { $this->_set_result('JSON string is not a string (' . gettype($string) . ')'); return FALSE; }
		// decode it
		json_decode($string);
		// make sure there are no errors in the decoding
		if(json_last_error() == JSON_ERROR_NONE) {
			// test the begining and end of the string
			if(substr($string,0,1) == '[' && substr($string,-1) == ']') { return TRUE; }
			else if(substr($string,0,1) == '{' && substr($string,-1) == '}') { return TRUE; }
			// if it doesn't have these it's not json
			else { $this->_set_result('JSON string does not have delimiters'); return FALSE; }
		} else { $this->_set_result('JSON string is not JSON (' . $string . ')'); return FALSE; }
	}


	/**
	 *      A HUNMAN READABLE TIME/DATE
	 */

	protected function _human_readable_timedate($time) {
		//if($time < time()) { $prefix = ''; $suffix = ' ago'; } else { $prefix = 'in '; $suffix = ''; }
		if($time < time()) { $prefix = ''; $suffix = ' ago'; } else { $prefix = 'in '; $suffix = ''; }
	    $time = time() - $time;
	    $tokens = array (31536000 => 'year',2592000 => 'month',604800 => 'week',86400 => 'day',3600 => 'hour',60 => 'minute',1 => 'second');
	    foreach ($tokens as $unit => $text) {
	        if ($time < $unit) continue;
	        $numberOfUnits = floor($time / $unit);
	        //return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
	        return $prefix.$numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'').$suffix;
	    }
	}
}