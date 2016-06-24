<?php

    $username = '<username>';
    $password = '<password>';
    $get_site_settings = 'sitename';
    $add_survey = array(
        'title' => 'New Survey',
        'username' => $username,
        'email' => $username
    );
    $get_survey_properties = 'active,autonumber_start,emailnotificationto,nokeyboard,showwelcome,additional_languages,autoredirect,emailresponseto,owner_id,showxquestions,admin,bounce_email,expires,printanswers,sid,adminemail,bounceaccountencryption,faxto,publicgraphs,startdate,alloweditaftercompletion,bounceaccounthost,format,publicstatistics,template,allowjumps,bounceaccountpass,googleanalyticsapikey,refurl,tokenanswerspersistence,allowprev,bounceaccounttype,googleanalyticsstyle,savetimings,tokenlength,allowregister,bounceaccountuser,htmlemail,sendconfirmation,usecaptcha,allowsave,bounceprocessing,ipaddr,showgroupinfo,usecookie,anonymized,bouncetime,language,shownoanswer,usetokens,assessments,datecreated,listpublic,showprogress,attributedescriptions,datestamp,navigationdelay,showqnumcode';
    $test_survey = '<survey_id>';  // this is the survey you: select, edit, add questions to, delete, duplicate, etc...

?>

<!DOCTYPE html>
<html>
    <head>
        <title>
            Post to API
        </title>
        <!-- HTML Setup -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Semantic -->
        <link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/1.12.0/semantic.min.css">
        <!-- Loading Bar -->
        <link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/angular-loading-bar/0.7.1/loading-bar.min.css">
        <!-- Fonts -->
        <link href='https://fonts.googleapis.com/css?family=Ubuntu:400,700' rel='stylesheet' type='text/css'>
        <style type="text/css">
            html, body { font-family: 'Ubuntu', serif; }
            .messages_padding { padding: 10px 0; }
            .messages { padding: 10px 0; }
        </style>
    <body ng-controller="testsCtrl">



            <!-- ####################  MENU  #################### -->


        <div class="ui fixed main menu grid">
            <div class="item">
                AJ API Post Data Test
            </div>
        </div>



            <!-- ####################  PAGE  #################### -->


        <div class="ui page grid">
            <div class="column">


            <!-- ###############  SEGMENT  ############### -->

                <div class="ui segment" id="segment_tests">


                        <!-- ##########  MESSAGES  ########## -->

                    <div class="messages_padding">
                        <div class="messages">
                            <!-- <div class="ui green message">
                                <i class="close icon"></i>
                                Something
                            </div> -->
                        </div>
                    </div>


                        <!-- ##########  FORM  ########## -->

                    <form id="form_test_post" action="API.php" method="POST">
                        <select name="method" id="method" class="ui fluid dropdown" onchange="populate_attributes();">
                            <option value="">API Action</option>
                            <option value="test_api_url">Test API URL</option>
                            <option value="users">-------------------- SESSION --------------------</option>
                            <option value="start_session">Start Session</option>
                            <!-- <option value="get_session">Get Session</option> -->
                            <option value="end_session">End Session</option>
                            <option value="users">-------------------- USERS --------------------</option>
                            <option value="list_users">List Users</option>
                            <option value="settings">-------------------- SITE SETTINGS --------------------</option>
                            <option value="get_site_settings">Get Site Settings</option>
                            <option value="survey">-------------------- SURVEY --------------------</option>
                            <option value="add_survey">Add Survey</option>
                            <option value="delete_survey">Delete Survey</option>
                            <option value="duplicate_survey">Duplicate Survey</option>
                            <option value="get_survey_properties">Get Survey Properties</option>
                            <option value="set_survey_property">Set Survey Property</option>
                            <option value="list_surveys">List Surveys</option>
                            <option value="export_survey_responses">Export Survey Responses</option>
                            <option value="activate_survey">Activate Survey</option>
                            <option value="expire_survey">Expire Survey</option>
                            <option value="survey_summary">Survey Summary</option>
                            <option value="surveyLanguage">-------------------- SURVEY LANGUAGE --------------------</option>
                            <option value="get_survey_language_properties">Get Survey Language Properties</option>
                            <option value="set_survey_language_property">Set Survey Language Property</option>
                            <option value="question">-------------------- QUESTION --------------------</option>
                            <option value="add_survey_question">Add Survey Question</option>
                            <option value="delete_survey_question">Delete Survey Question</option>
                            <option value="list_survey_questions">List Survey Questions</option>
                            <option value="get_survey_question_properties">Get Survey Question Properties</option>
                            <option value="set_survey_question_properties">Set Survey Question Property</option>
                            <option value="group">-------------------- QUESTION GROUP --------------------</option>
                            <option value="add_question_group">Add Qusetion Group</option>
                            <option value="set_question_group_property">Set Qusetion Group Property</option>
                            <option value="list_question_groups">List Qusetion Groups</option>
                            <option value="delete_question_group">Delete Qusetion Group</option>
                            <option value="tokens">-------------------- TOKENS --------------------</option>
                            <option value="activate_survey_tokens">Activate Survey Tokens</option>
                            <option value="participants">-------------------- SURVEY PARTICIPANT --------------------</option>
                            <option value="list_survey_participants">List Survey Participants</option>
                            <option value="add_survey_participant">Add Survey Participant</option>
                            <option value="import_survey_participants">Import Survey Participants</option>
                            <option value="delete_survey_participant">Delete Survey Participant</option>
                            <option value="get_survey_participant_properties">Get Survey Participant Properties</option>
                            <option value="set_survey_participant_properties">Set Survey Participant Properties</option>
                            <option value="invite_all_survey_participants">Invite Survey Participants</option>
                            <option value="remind_all_survey_participants">Remind Survey Participants</option>
                            <option value="participants">-------------------- GLOBAL PARTICIPANT --------------------</option>
                            <option value="delete_global_participant">Delete Global Participant</option>
                            <option value="add_global_participant">Add Global Participant</option>
                            <option value="set_global_participant">Set Global Participant</option>
                            <option value="search_global_participants">Search Global Participants</option>
                            <option value="get_global_participants">Get Global Participants</option>
                        </select>
                        <div class="ui fluid input" style="margin: 20px 0;">
                            <input type="text" name="session_key" id="session_key" value="" placeholder="Session Key">
                        </div>
                        <div class="ui fluid input" style="margin: 20px 0;">
                            <input type="text" name="attrs" id="attrs" value="">
                        </div>
                        <button type="submit" class="ui green fluid button">Test Method</button>
                    </form>


                    <div class="ui divider"></div>


                        <!-- ########## RESULT  ########## -->

                    <code id="result"></code>


                </div><!-- END: Segment -->

            </div><!-- END: Column -->

        </div><!-- END: Grid -->



            <!-- ####################  SCRIPTS  #################### -->


        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/1.12.0/semantic.min.js"></script>
        <script type="text/javascript" src="encoder.js"></script>

        <script type="text/javascript">
            
            $('.dropdown').dropdown();

            function populate_attributes() {
                var survey_id = <?=$test_survey?>;
                // SESSIONS
                if($('#method').val() == 'start_session') { $('#attrs').val(''); }
                if($('#method').val() == 'start_session') { $('#attrs').val('{"username":"<?=$username?>","password":"<?=$password?>"}'); }
                //else if($('#method').val() == 'get_session') { $('#attrs').val(''); }
                else if($('#method').val() == 'end_session') { $('#attrs').val(''); }
                // USERS
                else if($('#method').val() == 'list_users') { $('#attrs').val(''); }
                // SITE SETTINGS
                else if($('#method').val() == 'get_site_settings') { $('#attrs').val('{"setting":"<?=$get_site_settings?>"}'); }
                // SURVEY
                else if($('#method').val() == 'list_surveys') { $('#attrs').val(''); }
                else if($('#method').val() == 'add_survey') { $('#attrs').val('{"title":"<?=$add_survey['title']?>","username":"<?=$add_survey['username']?>","email":"<?=$add_survey['email']?>"}'); }
                else if($('#method').val() == 'get_survey_properties') { $('#attrs').val('{"survey_id":"'+survey_id+'","setting":"<?=$get_survey_properties?>"}'); }
                else if($('#method').val() == 'set_survey_property') { $('#attrs').val('{"survey_id":"'+survey_id+'","setting":{"admin":"robert.johnstone@ors.org.uk"}}'); }
                else if($('#method').val() == 'survey_summary') { $('#attrs').val('{"survey_id":"'+survey_id+'","setting":"token_count"}'); }
                else if($('#method').val() == 'duplicate_survey') { $('#attrs').val('{"survey_id":"'+survey_id+'"}'); }
                else if($('#method').val() == 'expire_survey') { $('#attrs').val('{"survey_id":"'+survey_id+'"}'); }
                else if($('#method').val() == 'activate_survey') { $('#attrs').val('{"survey_id":"'+survey_id+'"}'); }
                else if($('#method').val() == 'delete_survey') { $('#attrs').val('{"survey_id":"'+survey_id+'"}'); }
                // SURVEY LANGUAGE
                else if($('#method').val() == 'get_survey_language_properties') { $('#attrs').val('{"survey_id":"'+survey_id+'","setting":"surveyls_welcometext,surveyls_description"}'); }
                else if($('#method').val() == 'set_survey_language_property') { $('#attrs').val('{"survey_id":"'+survey_id+'","setting":{"surveyls_welcometext":"Welcome to my simple survey"}}'); }
                // GLOBAL PARTICIPANT
                //else if($('#method').val() == 'get_global_participants') { $('#attrs').val(''); }
                else if($('#method').val() == 'get_global_participants') { $('#attrs').val('{"current_page":"2"}'); }
                else if($('#method').val() == 'search_global_participants') { $('#attrs').val('{"value":"some"}'); }
                else if($('#method').val() == 'set_global_participant') { $('#attrs').val('{"participant_id":"bcdb8134-ffa9-4c3b-8c54-6d83f009791e","first_name":"someone","last_name":"Else","email":"someone@else.com"}'); }
                else if($('#method').val() == 'add_global_participant') { $('#attrs').val('{"first_name":"Aladdin","last_name":"character","email":"alidin.character@disney.com"}'); }
                else if($('#method').val() == 'delete_global_participant') { $('#attrs').val('{"participant_id":"120e846c-5a17-41a8-b4d6-b4219663623b"}'); }
                // SURVEY PARTICIPANT
                else if($('#method').val() == 'activate_survey_tokens') { $('#attrs').val('{"survey_id":"'+survey_id+'"}'); }
                else if($('#method').val() == 'list_survey_participants') { $('#attrs').val('{"survey_id":"'+survey_id+'"}'); }
                else if($('#method').val() == 'add_survey_participant') { $('#attrs').val('{"survey_id":"'+survey_id+'","setting":{"email":"me@example.com","last_name":"Bond","first_name":"James"}}'); }
                else if($('#method').val() == 'import_survey_participants') { $('#attrs').val('{"survey_id":"'+survey_id+'","setting":[{"email":"james.bond@ors.org.uk","last_name":"Bond","first_name":"James"},{"email":"007@ors.org.uk","last_name":"Cover","first_name":"Under"}]}'); }
                else if($('#method').val() == 'delete_survey_participant') { $('#attrs').val('{"survey_id":"'+survey_id+'","token_id":"5"}'); }
                else if($('#method').val() == 'get_survey_participant_properties') { $('#attrs').val('{"survey_id":"'+survey_id+'","token_id":"7","setting":"email,firstname"}'); }
                else if($('#method').val() == 'set_survey_participant_properties') { $('#attrs').val('{"survey_id":"'+survey_id+'","token_id":"7","setting":{"email":"as@as.com","firstname":"Brian"}}'); }
                else if($('#method').val() == 'export_survey_responses') { $('#attrs').val('{"survey_id":"'+survey_id+'"}'); }
                else if($('#method').val() == 'invite_all_survey_participants') { $('#attrs').val('{"survey_id":"'+survey_id+'"}'); }
                else if($('#method').val() == 'remind_all_survey_participants') { $('#attrs').val('{"survey_id":"'+survey_id+'"}'); }
                // QUESTION
                else if($('#method').val() == 'list_survey_questions') { $('#attrs').val('{"survey_id":"'+survey_id+'"}'); }
                else if($('#method').val() == 'set_survey_question_properties') { $('#attrs').val('{"survey_id":"'+survey_id+'","question_id":"4696","setting":{"question":"New Question for Question 8888888888888888"}}'); }
                else if($('#method').val() == 'get_survey_question_properties') { $('#attrs').val('{"question_id":"4696","setting":"question,help"}'); }
                else if($('#method').val() == 'delete_survey_question') { $('#attrs').val('{"survey_id":"'+survey_id+'","question_id":"4696"}'); }
                // QUESTION GROUP
                else if($('#method').val() == 'add_question_group') { $('#attrs').val('{"survey_id":"'+survey_id+'","setting":{"group_title":"Simple Group 2"}}'); }
                else if($('#method').val() == 'list_question_groups') { $('#attrs').val('{"survey_id":"'+survey_id+'"}'); }
                else if($('#method').val() == 'delete_question_group') { $('#attrs').val('{"survey_id":"'+survey_id+'","group_id":"520"}'); }
                else if($('#method').val() == 'set_question_group_property') { $('#attrs').val('{"survey_id":"'+survey_id+'","group_id":"363","setting":{"group_name":"Simple Group 2"}}'); }
                else if($('#method').val() == 'add_survey_question') {
                    console.log('add question');
                    Encoder.EncodeType = "entity";
                    //var question = Encoder.htmlEncode('Test2 <strong>question </strong><em>text</em>');
                    //var help_text = Encoder.htmlEncode('<h1><span style="font-family:comic sans ms,cursive">Test2 & question help text</span></h1>');
                    var question = Encoder.htmlEncode('Test2 question text');
                    var help_text = Encoder.htmlEncode('Test2 & question help text');
                    $('#attrs').val('{"survey_id":"'+survey_id+'","setting":{"question":"'+question+'", "help_text":"'+help_text+'","type":"T","other":"Y","mandatory":"Y"}}');
                }
            }

            $('#form_test_post').submit(function( event ) {
                if($('#method').val() != 'export_survey_responses') {
                    $('#result').html('Loading...');
                    event.preventDefault();
                    var $form = $(this);
                    var meth = $form.find("select[name='method']").val();
                    var sessionKey = $form.find("input[name='session_key']").val();
                    var attrs = $form.find("input[name='attrs']").val();
                    var url = $form.attr('action');
                    var posting = $.post(url, {method: meth, attrs: attrs, session_key: sessionKey});
                    posting.done(function(data) {
                        var json_data = JSON.parse(data);
                        $('#result').html(data);
                        if($('#method').val() == 'start_session') {
                            $('#session_key').val(json_data.result);
                        }
                        if($('#method').val() == 'end_session') {
                            $('#session_key').val('');
                        }
                    });
                } else {
                    // TODO: Test this
                    var meth = $form.find("select[name='method']").val();
                    var attrs = $form.find("input[name='attrs']").val();
                    var url = $form.attr('action');
                    alert();
                    var getting = $.get(url, {method: meth, attrs: attrs});
                }
            });

            // http://stackoverflow.com/questions/770523/escaping-strings-in-javascript
            function addslashes( str ) {
                return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
            }

        </script>

    </body>
</html>