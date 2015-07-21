<?php

	// Initial values

	$limesurvey_api_endPoint = '<lime_survey_url>';  //(e.g. http://www.limesurveyinrootdomain.com/index.php/admin/)
	$username = '<username>';
	$password = '<password>';
	$survey_id = <survey_id>;
	$group_id = <group_id>;  // ensure the group is in the above survery
	$question_id = <question_id>;  // ensure the question is in the above group
	$language_code = 'es';  // make sure this language is in the above survey
	$language = 'spanish';  // make sure this language is in the above survey

	$survey_token_valid_from = '01/01/2020';
	$survey_token_valid_to = '31/12/2020';
	$survey_add_participent = array("{'email':'as@as.com','firstname':'some','lastname':'one'}");

	$arr = array();

	$arr['surveyID'] = $survey_id;
	$arr['groupID'] = $group_id;

	$arr['endPoints'] = array(
		'remoteControl' => $limesurvey_api_endPoint . 'remotecontrol',
		'exportSurvey' => $limesurvey_api_endPoint . 'export/sa/survey/action/exportstructurexml/surveyid/' . $survey_id,
		'deactivateSurvey' => $limesurvey_api_endPoint . 'survey/sa/deactivate/surveyid/' . $survey_id,
		'exportGroup' => $limesurvey_api_endPoint . 'export/sa/group/surveyid/' . $survey_id . '/gid/' . $group_id,
		'exportQuestion' => $limesurvey_api_endPoint . 'export/sa/question/surveyid/' . $survey_id . '/gid/' . $group_id . '/qid/' . $question_id,
		'login' => $limesurvey_api_endPoint.'authentication/sa/login'
	);

	$arr['results'] = array();

		// Log into LimeSurvey

	$arr['results'][] = array(
		'name' => 'Log into LimeSurvery',
		'method' => 'get_session_key',
		'params' => array(
			'username' => $username,
			'password' => $password
		),
		'id' => count($arr['results']) + 1
	);

		// Get all the Settings from the site

	//$get_site_settings_settings = array('DBVersion','SessionName','sitename','siteadminname','siteadminemail','siteadminbounce','defaultlang','updateversions','updateavailable','updatelastcheck','restrictToLanguages','updatecheckperiod','updatenotification','defaulthtmleditormode','defaultquestionselectormode','defaulttemplateeditormode','defaulttemplate','admintheme','adminthemeiconsize','emailmethod','emailsmtphost','emailsmtppassword','bounceaccounthost','bounceaccounttype','bounceencryption','bounceaccountuser','bounceaccountpass','emailsmtpssl','emailsmtpdebug','emailsmtpuser','filterxsshtml','shownoanswer','showxquestions','showgroupinfo','showqnumcode','repeatheadings','maxemails','iSessionExpirationTime','ipInfoDbAPIKey','googleMapsAPIKey','googleanalyticsapikey','googletranslateapikey','force_ssl','surveyPreview_require_Auth','RPCInterface','rpc_publish_api','timeadjust','usercontrolSameGroupPolicy','updatebuild','updateversion');
	$get_site_settings_settings = array('DBVersion','SessionName');

	foreach($get_site_settings_settings as $value) {
		$arr['results'][] = array(
			'name' => 'Get a Setting from the site ('.$value.')',
			'method' => 'get_site_settings',
			'params' => array(
				'sSessionKey' => '<holder>',
				'sSetttingName' => $value
			),
			'id' => count($arr['results']) + 1
		);
	}

		// List Users

	$arr['results'][] = array(
		'name' => 'List Users',
		'method' => 'list_users',
		'params' => array(
			'sSessionKey' => '<holder>'
		),
		'id' => count($arr['results']) + 1
	);



			/***********************************************/
			/*                                             */
			/*                IMPORT SURVEY                */
			/*                                             */
			/***********************************************/


		// Get a Survey as XML

	$arr['results'][] = array(
		'name' => 'Get a survey as XML',
		'method' => 'get_survey_as_XML',
		'params' => array(
			'username' => $username,
			'password' => $password
		),
		'id' => count($arr['results']) + 1
	);

		// Import the exported Base64(XML) survey

	$arr['results'][] = array(
		'name' => 'Import the exported XML survey',
		'method' => 'import_survey',
		'params' => array(
			'sSessionKey' => '<holder>',
			'sImportData' => '<holder>',
			'sImportDataType' => 'lss',
			// the name of the exported survey will be preffixed to this
			'sNewSurveyName' => ' (copy)',
			'DestSurveyID' => 1
		),
		'id' => count($arr['results']) + 1
	);

		// Delete the imported survey

	$arr['results'][] = array(
		'name' => 'Deletes the imported survey',
		'method' => 'delete_survey',
		'params' => array(
			'sSessionKey' => '<holder>',
			'iSurveyID' => '<holder>'
		),
		'id' => count($arr['results']) + 1
	);



			/********************************************/
			/*                                          */
			/*                ADD SURVEY                */
			/*                                          */
			/********************************************/


		// Add New Survey

	$arr['results'][] = array(
		'name' => 'Add a new survey',
		'method' => 'add_survey',
		'params' => array(
			'sSessionKey' => '<holder>',
			'iSurveryID' => 1,
			'sSurveyTitle' => 'Test Survey',
			'sSurveyLanguage' => 'en',
			'sFormat' => 'S'
		),
		'id' => count($arr['results']) + 1
	);

		// Survey properties

	$survey_properties = array(
		//'autonumber_start',
		//'emailnotificationto',
		//'nokeyboard',
		//'showwelcome',
		//'autoredirect',
		//'emailresponseto',
		//'owner_id',
		//'showxquestions',
		'admin' => 'as@as.com',
		'bounce_email' => 'as@as.com',
		'expires' => date("Y-m-d H:i:s", mktime(0, 0, 0, date("m")  , date("d")+7, date("Y"))),
		//'printanswers',
		'adminemail' => 'as@as.com',
		//'bounceaccountencryption',
		//'faxto',
		//'publicgraphs',
		'startdate' => date("Y-m-d H:i:s", mktime(0, 0, 0, date("m")  , date("d")-7, date("Y"))),
		//'alloweditaftercompletion',
		//'bounceaccounthost',
		//'format',
		//'publicstatistics',
		'template' => 'mint_idea',
		//'allowjumps',
		//'bounceaccountpass',
		//'googleanalyticsapikey',
		//'refurl',
		//'tokenanswerspersistence',
		//'allowprev',
		//'bounceaccounttype',
		//'googleanalyticsstyle',
		//'savetimings',
		//'tokenlength',
		//'allowregister',
		//'bounceaccountuser',
		//'htmlemail',
		//'sendconfirmation',
		//'usecaptcha',
		//'allowsave',
		//'bounceprocessing',
		//'ipaddr',
		//'showgroupinfo',
		//'usecookie',
		//'anonymized',
		//'bouncetime',
		'shownoanswer' => 'N',
		'usetokens' => 'Y',
		//'assessments',
		//'datecreated',
		//'listpublic',
		//'showprogress',
		//'attributedescriptions',
		//'datestamp',
		//'navigationdelay',
		//'showqnumcode'
	);

	$survey_properties = array(
		'admin' => 'as@as.com',
		'bounce_email' => 'as@as.com'
	);

		// Set new survey properties

	foreach($survey_properties as $key => $value) {
		$arr['results'][] = array(
			'name' => 'Set a survey setting ('.$key.')',
			'method' => 'set_survey_properties',
			'params' => array(
				'sSessionKey' => '<holder>',
				'iSurveyID' => '<holder>',
				'aSurveySettings' => array($key => $value)
			),
			'id' => count($arr['results']) + 1
		);
	}

		// Get new survey properties

	foreach($survey_properties as $key => $value) {
		$arr['results'][] = array(
			'name' => 'Get a survey setting ('.$key.')',
			'method' => 'get_survey_properties',
			'params' => array(
				'sSessionKey' => '<holder>',
				'iSurveyID' => '<holder>',
				'aSurveySettings' => array($key)
			),
			'expected' => array($key => $value),
			'id' => count($arr['results']) + 1
		);
	}

		// Delete New survey

	$arr['results'][] = array(
		'name' => 'Delete the created survey',
		'method' => 'delete_survey',
		'params' => array(
			'sSessionKey' => '<holder>',
			'iSurveyID' => '<holder>'
		),
		'id' => count($arr['results']) + 1
	);



			/***************************************************/
			/*                                                 */
			/*                SURVEY STATISTICS                */
			/*                                                 */
			/***************************************************/


		// List all Surveys belonging to a user

	$arr['results'][] = array(
		'name' => 'List surveys belonging to ' . $username,
		'method' => 'list_surveys',
		'params' => array(
			'sSessionKey' => '<holder>',
			'sUser' => $username
		),
		'id' => count($arr['results']) + 1
	);

		// Make sure the survey is active

	$arr['results'][] = array(
		'name' => 'Make sure the survey is active before getting statistics',
		'method' => 'activate_survey',
		'params' => array(
			'sSessionKey' => '<holder>',
			'iSurveyID' => $survey_id,
		),
		'id' => count($arr['results']) + 1
	);

		// Export survey statistics

	$arr['results'][] = array(
		'name' => 'Get the statistics for a survey',
		'method' => 'export_statistics',
		'params' => array(
			'sSessionKey' => '<holder>',
			'SurveyID' => $survey_id,
			'docType' => 'xls'
		),
		'id' => count($arr['results']) + 1
	);

		// Export survey statistics (Graph)

	$arr['results'][] = array(
		'name' => 'Get the statistics for a survey (graph)',
		'method' => 'export_statistics',
		'params' => array(
			'sSessionKey' => '<holder>',
			'SurveyID' => $survey_id,
			'docType' => 'pdf',
			'graph' => '1'
		),
		'id' => count($arr['results']) + 1
	);

		// Get survey summary stats

	//$get_survey_summery_stats = array('completed_responses','incomplete_responses','full_responses','token_count','token_invalid','token_sent','token_opted_out','token_completed');
	$get_survey_summery_stats = array('completed_responses','incomplete_responses','full_responses');

	foreach($get_survey_summery_stats as $value) {
		$arr['results'][] = array(
			'name' => 'Get a summary statistic for a survey (' . $value .')',
			'method' => 'get_summary',
			'params' => array(
				'sSessionKey' => '<holder>',
				'iSurveyID' => $survey_id,
				'sStatname' => $value
			),
			'id' => count($arr['results']) + 1
		);
	}



			/**********************************************/
			/*                                            */
			/*                ADD LANGUAGE                */
			/*                                            */
			/**********************************************/


		// Add a language

	$arr['results'][] = array(
		'name' => 'Add a language to a survey',
		'method' => 'add_language',
		'params' => array(
			'sSessionKey' => '<holder>',
			'SurveyID' => $survey_id,
			'aLanguage' => $language_code
		),
		'id' => count($arr['results']) + 1
	);

		// Language properties

	$language_properties = array(
		//'surveyls_survey_id'
		//'surveyls_language'
		'surveyls_title' => 'title ' . $language,
		'surveyls_description' => 'decription ' . $language,
		'surveyls_welcometext' => 'welcome text ' . $language,
		'surveyls_endtext' => 'end text ' . $language,
		'surveyls_url' => 'http://example.com/' . $language,
		'surveyls_urldescription' => 'simple url description ' . $language,
		'surveyls_email_invite_subj' => 'email invite subject' . $language,
		'surveyls_email_invite' => 'email invite ' . $language,
		'surveyls_email_remind_subj' => 'email reminder subject' . $language,
		'surveyls_email_remind' => 'email reminder ' . $language,
		'surveyls_email_register_subj' => 'email register subject' . $language,
		'surveyls_email_register' => 'email register ' . $language,
		'surveyls_email_confirm_subj' => 'email confirm subject' . $language,
		'surveyls_email_confirm' => 'email confirm ' . $language,
		'surveyls_dateformat' => 2,
		'surveyls_attributecaptions' => 'captions in ' . $language,
		'email_admin_notification_subj' => 'test admin email subject',
		'email_admin_notification' => 'test admin notification',
		'email_admin_responses_subj' => 'test admin response subject',
		'email_admin_responses' => 'test admin response',
		'surveyls_numberformat' => 2
	);
	$language_properties = array(
		'surveyls_title' => 'title ' . $language,
		'surveyls_description' => 'decription ' . $language,
	);

		// Set surveys language properties

	foreach($language_properties as $key => $value) {
		$arr['results'][] = array(
			'name' => 'Set a surveys language properties ('.$key.')',
			'method' => 'set_language_properties',
			'params' => array(
				'sSessionKey' => '<holder>',
				'iSurveyID' => $survey_id,
				'aSurveyLocaleData' => array($key => $value),
				'sLanguage' => $language_code
			),
			'id' => count($arr['results']) + 1
		);
	}

		// Get new language properties

	foreach($language_properties as $key => $value) {
		$arr['results'][] = array(
			'name' => 'Get a surveys language properties ('.$key.')',
			'method' => 'get_language_properties',
			'params' => array(
				'sSessionKey' => '<holder>',
				'iSurveyID' => $survey_id,
				'aSurveyLocaleSettings' => array($key),
				'slang' => $language_code
			),
			'expected' => array($key => $value),
			'id' => count($arr['results']) + 1
		);
	}

		// Delete a language

	$arr['results'][] = array(
		'name' => 'Delete a language from a survey',
		'method' => 'delete_language',
		'params' => array(
			'sSessionKey' => '<holder>',
			'SurveyID' => $survey_id,
			'aLanguage' => $language_code
		),
		'id' => count($arr['results']) + 1
	);



			/************************************************/
			/*                                              */
			/*                GROUP SETTINGS                */
			/*                                              */
			/************************************************/


		// Group properties

	$group_properties = array(
		//'gid' => ???
		//'sid' => ???
		'group_name' => 'Test group',
		'group_order' => '0',  // default
		'description' => 'Test group description',
		'language' => 'en',  // default
		'randomization_group' => 'asas',
		'grelevance' => '1'
	);

		// Set group properties

	foreach($group_properties as $key => $value) {
		$arr['results'][] = array(
			'name' => 'Set a groups properties ('.$key.')',
			'method' => 'set_group_properties',
			'params' => array(
				'sSessionKey' => '<holder>',
				'sGroupID' => $group_id,
				'aGroupData' => array($key => $value)
			),
			'id' => count($arr['results']) + 1
		);
	}

		// Get group properties

	foreach($group_properties as $key => $value) {
		$arr['results'][] = array(
			'name' => 'Get a groups properties ('.$key.')',
			'method' => 'get_group_properties',
			'params' => array(
				'sSessionKey' => '<holder>',
				'sGroupID' => $group_id,
				'aGroupSettings' => array($key)
			),
			'expected' => array($key => $value),
			'id' => count($arr['results']) + 1
		);
	}



			/***********************************************/
			/*                                             */
			/*                IMOPORT GROUP                */
			/*                                             */
			/***********************************************/




	// 	// Export a group to a survey

	// $arr['results'][] = array(
	// 	'name' => 'Export a group',
	// 	'method' => 'get_group_as_XML',
	// 	'params' => array(
	// 		'username' => $username,
	// 		'password' => $password
	// 	),
	// 	'id' => count($arr['results']) + 1
	// );

	// 	// Import a group from a survey

	// $arr['results'][] = array(
	// 	'name' => 'Import a group from a survey',
	// 	'method' => 'import_group',
	// 	'params' => array(
	// 		'sSessionKey' => '<holder>',
	// 		'SurveyID' => $survey_id,
	// 		'sImportData' => '<holder>',
	// 		'sGroupDescription' => 'lsg',
	// 		'sNewGroupName' => ' (copy)',
	// 		'sNewGroupDescription' => 'Test group imort description',
	// 	),
	// 	'id' => count($arr['results']) + 1
	// );

	// 	// Delete imported group

	// $arr['results'][] = array(
	// 	'name' => 'Delete the imported group',
	// 	'method' => 'delete_group',
	// 	'params' => array(
	// 		'sSessionKey' => '<holder>',
	// 		'SurveyID' => $survey_id,
	// 		'sGroupID' => '<holder>'
	// 	),
	// 	'id' => count($arr['results']) + 1
	// );



			/*******************************************/
			/*                                         */
			/*                ADD GROUP                */
			/*                                         */
			/*******************************************/


		// Make sure the survey is deactivated
		// NOTE: Doen't work because it's not an api call

	// $arr['results'][] = array(
	// 	'name' => 'Make sure the survey is deactivated',
	// 	'method' => 'deactivate_survey',
	// 	'params' => array(
	// 		'username' => $username,
	// 		'password' => $password
	// 	),
	// 	'id' => count($arr['results']) + 1
	// );

		// Add a group to a survey

	$arr['results'][] = array(
		'name' => 'Add a group to a survey',
		'method' => 'add_group',
		'params' => array(
			'sSessionKey' => '<holder>',
			'SurveyID' => $survey_id,
			'sGroupTitle' => 'Test Group',
			'sGroupDescription' => 'Test Group Description'
		),
		'id' => count($arr['results']) + 1
	);

		// Delete a group from a survey

	$arr['results'][] = array(
		'name' => 'Delete a group from a survey',
		'method' => 'delete_group',
		'params' => array(
			'sSessionKey' => '<holder>',
			'SurveyID' => $survey_id,
			'sGroupID' => '<holder>'
		),
		'id' => count($arr['results']) + 1
	);

		// List groups

	$arr['results'][] = array(
		'name' => 'List groups for a survey',
		'method' => 'list_groups',
		'params' => array(
			'sSessionKey' => '<holder>',
			'SurveyID' => $survey_id,
		),
		'id' => count($arr['results']) + 1
	);



			/******************************************/
			/*                                        */
			/*                QUESTION                */
			/*                                        */
			/******************************************/


		// List Question

	$arr['results'][] = array(
		'name' => 'List all the questions',
		'method' => 'list_questions',
		'params' => array(
			'sSessionKey' => '<holder>',
			'iSurveyID' => $survey_id,
			'iGroupID' => $group_id,
		),
		'id' => count($arr['results']) + 1
	);


		// Expot Question

	$arr['results'][] = array(
		'name' => 'Export a question',
		'method' => 'get_question_as_XML',
		'params' => array(
			'username' => $username,
			'password' => $password
		),
		'id' => count($arr['results']) + 1
	);

		// Import Question

	$arr['results'][] = array(
		'name' => 'Import a question',
		'method' => 'import_question',
		'params' => array(
			'sSessionKey' => '<holder>',
			'iSurveyID' => $survey_id,
			'iGroupID' => $group_id,
			'sImportData' => '<holder>',
			'sImportDataType' => 'lsq',
			// 'sMandatory' => 'No',
			// 'sNewQuestionTitle' => 'Q2',
			'sNewQuestion' => ' (copy)',
			// 'sNewQuestionHelp' => 'Test new import question help'
		),
		'id' => count($arr['results']) + 1
	);



			/***************************************************/
			/*                                                 */
			/*                QUESTION SETTINGS                */
			/*                                                 */
			/***************************************************/


		// Question properties

	$question_properties = array(
		//'gid' => ???
		//'parent_qid' => ???
		//'sid' => ???
		//'subquestions' => ???
		//'type' => ???
		'title' => 'Test question title',
		'question' => 'Test question text',
		//'preg' => ???
		//'attributes' => ???
		'help' => 'Test question help text',
		'other' => 'Y',
		'mandatory' => 'N',
		'question_order' => '2',
		//'attributes_lang' => ???
		//'language' => ???
		'scale_id' => '0',  // not to sure what this does (always 0)
		'same_default' => '0',  // not to sure what this does either (always 0)
		'relevance' => '1'
		//'answeroptions' => ???
	);


		// Set question properties

	foreach($question_properties as $key => $value) {
		$arr['results'][] = array(
			'name' => 'Set a question properties ('.$key.')',
			'method' => 'set_question_properties',
			'params' => array(
				'sSessionKey' => '<holder>',
				'sGroupID' => $question_id,
				'aGroupData' => array($key => $value)
			),
			'id' => count($arr['results']) + 1
		);
	}

		// Get queston properties

	foreach($question_properties as $key => $value) {
		$arr['results'][] = array(
			'name' => 'Get a question properties ('.$key.')',
			'method' => 'get_question_properties',
			'params' => array(
				'sSessionKey' => '<holder>',
				'sGroupID' => $question_id,
				'aGroupSettings' => array($key)
			),
			'expected' => array($key => $value),
			'id' => count($arr['results']) + 1
		);
	}




			/****************************************/
			/*                                      */
			/*                TOKENS                */
			/*                                      */
			/****************************************/



		// Activate tokens
/*
	$arr['results'][] = array(
		'name' => 'Activate tokens on a survey',
		'method' => 'activate_tokens',
		'params' => array(
			'sSessionKey' => '<holder>',
			'sSurveyID' => $survey_id
		),
		'id' => count($arr['results']) + 1
	);

		// Add Participant

	$arr['results'][] = array(
		'name' => 'Add a participant to survey (using tokens)',
		'method' => 'add_participant',
		'params' => array(
			'sSessionKey' => '<holder>',
			'sSurveyID' => $survey_id,
			'participentData' => $survey_add_participent,
			'craeteToken' => 'true'
		),
		'id' => count($arr['results']) + 1
	);

		// Delete Participant

	$arr['results'][] = array(
		'name' => 'Delete a participant to survey (using tokens)',
		'method' => 'delete_participant',
		'params' => array(
			'sSessionKey' => '<holder>',
			'sSurveyID' => $survey_id,
			'aTokenIDs' => '<holder>'
		),
		'id' => count($arr['results']) + 1
	);
*/


			/**************************************************/
			/*                                                */
			/*                TOKEN PROPERTIES                */
			/*                                                */
			/**************************************************/



		// Token properties
/*
	$token_properties = array(
		//'tid' => ???  // Read only property
		'completed' => 'N',
		'participant_id' => '1',
		'language' => 'en',
		'usesleft' => 'N',
		'firstname' => 'test',
		'lastname' => 'token',
		'email' => 'as@as.com',
		'blacklisted' => 'N',
		'validfrom' => $survey_token_valid_from,
		'sent' => 'N',
		'validuntil' => $survey_token_valid_to,
		'remindersent' => 'N',
		//'mpid' => ???  // no idea what this is
		'emailstatus' => 'OK',
		'remindercount' => '2'
	);


		// Set token properties

	foreach($token_properties as $key => $value) {
		$arr['results'][] = array(
			'name' => 'Set a token properties ('.$key.')',
			'method' => 'set_token_properties',
			'params' => array(
				'sSessionKey' => '<holder>',
				'sGroupID' => $question_id,
				'aGroupData' => array($key => $value)
			),
			'id' => count($arr['results']) + 1
		);
	}

		// Get token properties

	foreach($token_properties as $key => $value) {
		$arr['results'][] = array(
			'name' => 'Get a token properties ('.$key.')',
			'method' => 'get_token_properties',
			'params' => array(
				'sSessionKey' => '<holder>',
				'sGroupID' => $question_id,
				'aGroupSettings' => array($key)
			),
			'expected' => array($key => $value),
			'id' => count($arr['results']) + 1
		);
	}
*/


		// Release session key

	$arr['results'][] = array(
		'name' => 'Log out of LimeSurvery',
		'method' => 'release_session_key',
		'params' => array(
			'sSessionKey' => '<holder>'
		),
		'id' => count($arr['results']) + 1
	);

	echo json_encode($arr);
?>
