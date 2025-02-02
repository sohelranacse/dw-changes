<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "public";
include("./_include/core/main_start.php");

class CJoin2 extends UserFields//CHtmlBlock
{
	function action()
	{
        global $g;
        global $g_user;

        $isAjaxRequest = get_param('ajax');
        $cmd = get_param('cmd');
        if ($isAjaxRequest) {
            $responseData = false;
            if ($cmd == 'photo_upload') {
                $file = get_param('file');
                // $responseData = CProfilePhoto::validate($file); // comment by sohel
                if (!$responseData) {
                    $file = $_FILES[$file]['tmp_name'];
                    $name = 'tmp_join_impact_' . time() . '_' . mt_rand() . '_';
                    $saveFile = $g['path']['dir_files'] . 'temp/' . $name;
                    $im = new Image();
                    if ($im->loadImage($file)) {
                        $im->resizeCropped($g['image']['medium_x'], $g['image']['medium_y'], $g['image']['logo'], 0);
                        $im->saveImage($saveFile . 'm.jpg', $g['image']['quality']);
                        @chmod($saveFile . 'm.jpg', 0777);
                        @copy($file, $saveFile . 'src.jpg');
                        @chmod($saveFile . 'src.jpg', 0777);
                        $responseData = $name;
                    }
                }
            } elseif ($cmd == 'check_captcha') {
                $responseData = check_captcha_mod(get_param('captcha'), '', false, false, '', '');
                if ($responseData) {
                    foreach ($this->gFields as $key => $field) {
                        if (in_array($field['type'], array('text', 'textarea'))
                                && ($field['group'] == 0 || $field['group'] == 3)) {
                            if (!isset($field['join_status'])) {
                                $field['join_status'] = 1;
                            }
                            if ($field['join_status']) {
                                set_session("j_{$key}", get_param($key));
                            }
                        }
                    }
                    $fileName = get_param('photo');
                    $uid = User::add();
                    if (!$uid) {
                        $responseData = $this->getResponseData('exists_email', Common::pageUrl('join'));
                    } else {
                        $g_user['user_id'] = $uid;
                        /*if (self::isActiveSexuality()) {
                            $userSearchFilters = array('p_sexuality' =>
                                array(
                                    'field' => 'sexuality',
                                    'value' => array(get_session('j_f_sexuality')),
                                )
                            );
                            User::updateParamsFilterUserInfoForData('user_search_filters', $userSearchFilters);
                        }*/
                        if ($fileName) {
                            $photo = $g['path']['dir_files'] . 'temp/' . $fileName . 'src.jpg';
                            uploadphoto($g_user['user_id'], '', '', intval(Common::isOptionActive('photo_approval')), $photo);
                        } elseif (get_session('social_photo', false) != false) {
                            // uploadphoto($g_user['user_id'], '', '', (Common::isOptionActive('photo_approval') ? 0 : 1), '', get_session('social_photo')); // comment by sohel
                            set_session('social_photo', false);
                        }

                        // comment by sohel
                        /*if (Common::isOptionActive('manual_user_approval')) {
                            $responseData = $this->getResponseData('wait_approval', Common::pageUrl('login'));
                        } else {
                            $responseData = $this->getResponseData('redirect', Common::getHomePage());
                        }*/
                        
                        $u_info = User::getInfoFull($uid);                        
                        $responseData = ($u_info['role'] == 'user') ? $this->getResponseData('redirect', "profile_view") : $this->getResponseData('redirect', "add_user");
                    }
                }else{
                    $responseData = $this->getResponseData('error_captcha', '');
                }
            }
            die(getResponseDataAjax($responseData));
        } elseif ($cmd == 'action'){
			global $g;
			$this->message = "";
            //$this->message .= User::validateLocation(get_session('j_country'), get_param('state', ''), get_param('city', ''));
            $this->verification('texts');

			$paf = get_param('partner_age_from', '');
			$pat = get_param('partner_age_to', '');
			if ($paf > $pat) {
				$this->message .= l('partner_age_incorect') . '<br>';
			}

			if ($this->message == "")
			{
                set_session("j_state", get_param('state', ''));
				set_session("j_city", get_param("city", ''));
				set_session("j_partner_age_from", $paf);
				set_session("j_partner_age_to", $pat);
				set_session("j_relation", get_param("relation", ""));

				foreach ($g['user_var'] as $k => $v)
				{
					if ((substr($k, 0, 2) != "p_") && ($v['type'] != 'const'))
					{
						set_session("j_" . $k, get_param($k, ""));
					}
				}

				redirect('join3.php');
			}
		}
	}

    function getResponseData($name, $data) {
        return "<span class='" . $name . "'>" . strip_tags($data) . '</span>';
    }

    function parseLastStep(&$html)
	{
        $html->setvar('by_phone', get_session('j_by_phone'));
        $html->setvar('user_name', get_session('j_name'));
        $html->setvar('user_phone', get_session('j_phone'));
        $html->setvar('user_mail', get_session('j_mail'));
        $cityId = get_session('j_city');
        $html->setvar('user_city', l(Common::getLocationTitle('city', $cityId)));
        $month = get_session('j_month');
        $day = get_session('j_day');
        $year = get_session('j_year');
        $html->setvar('user_age', User::getAge($year, $month, $day));
        Common::parseCaptcha($html);
        $html->parse('photo', false);
    }

	function parseBlock(&$html)
	{
        global $g;

        $by_phone = get_session('j_by_phone');
        $by_phone_varified = get_session('j_by_phone_varified');

        $isCustomRegister = Common::isOptionActive('custom_user_registration', 'template_options');

        if ($isCustomRegister) { // true
            $html->setvar('usersinfo_pages_per_join', Common::getOption('usersinfo_pages_per_join', 'template_options'));

            $html->setvar('header_url_logo', Common::getUrlLogo());
            $html->setvar('url_logo', Common::getHomePage());

            $isOneStepRegistration = Common::getOption('join_impact') == 'one_foto';
            $isJoinWithPhotoOnly = Common::isOptionActive('join_with_photo_only');
            $html->setvar('join_with_photo_only', intval($isJoinWithPhotoOnly));
            $numberPhotoLikes = intval(Common::getOption('join_number_photo_likes'));
            if ($numberPhotoLikes <= 0) {
                $numberPhotoLikes = 1;
            }
            $html->setvar('number_photo_likes', $numberPhotoLikes);
            $html->setvar('slogan_2', lSetVars('teach_us_your_type_like_people', array('number' => $numberPhotoLikes)), 'toJsL');

            $this->parseLastStep($html);
            $filedsData = array();
            if (!$isOneStepRegistration) { // false
                foreach ($this->gFields as $key => $field) {
                    if (!isset($field['join_status'])) {
                        $field['join_status'] = 1;
                    }
                    if (in_array($field['type'], array('text', 'textarea'))
                            && ($field['group'] == 0 || $field['group'] == 3)) {
                        if ($field['join_status']) {
                            $html->setvar('maxlen', $field['length']);
                            $title = l($field['title']);
                            $html->setvar('field', $title);
                            $html->setvar('name', $key);
                            $lKeyDesc = "field_description_{$key}";
                            $lVal = l($lKeyDesc);
                            if ($lKeyDesc == $lVal){
                                $lVal = '';
                            }
                            $html->setvar('value', $lVal);
                            $clean = $field['type'] == 'text' ? 'textarea' : 'text';
                            $html->parse($field['type'], false);
                            $html->clean($clean);
                            $html->parse('basic');
                        }
                    } else {
                        $data = self::checkFiledQuestion($key);
                        if ($data && $field['join_status'] && isset($field['question_title']) && $field['question_title']) {
                            if (isset($field['answer'])) {
                                $answers = json_decode($field['answer'], true);
                                if ($data['type_field'] == 'checks') {
                                    foreach ($answers as $k => $rows) {
                                        foreach ($rows as $k => $answer) {
                                            if($answer['from'] || $answer['to']) {
                                                $filedsData[$key] = $field;
                                                break(1);
                                            }
                                        }
                                    }
                                } elseif($answers['no'] || $answers['yes']) {
                                    $filedsData[$key] = $field;
                                }
                            }
                        }
                    }
                }
            }
            if ($filedsData) { // false
                $countFields = count($filedsData);
                $i = $countFields;
                foreach ($filedsData as $key => $field) {
                    if ($i == 1) {
                        $html->parse('question_item_first', false);
                    }
                    $html->setvar('question_item_name', $key);
                    $vars = array('number' => $i--, 'number_all' => $countFields);
                    $html->setvar('question_item_number', lSetVars('number_question', $vars));
                    $html->setvar('question_item_question', l($field['question_title']));
                    $html->parse('question_item', true);
                }
                $html->setvar('slogan_1', lSetVars('answer_questions_to_calculate_your_best_matches', array('number' => $countFields)), 'toJsL');
                $html->parse('question', false);
                $html->parse('show_join_step', false);
                $html->parse('final_step_hide', false);
            } else { // true


                $html->parse('users_likes_js1', false);
                $html->parse('users_likes1', false);
            }
        }else{ // false
            $this->parseFieldsAll($html, 'join');
        }

        if ($html->varExists('photo_file_size_limit')) {
            $maxFileSize = Common::getOption('photo_size');
            $html->setvar('photo_file_size_limit', mb_to_bytes($maxFileSize));
            $html->setvar('max_photo_file_size_limit', lSetVars('max_file_size', array('size'=>$maxFileSize)));
        }

        // insert/update -> user_temp_data

        $socialSignUp = 0;
        $socialType=get_session('social_type');
        if($socialType){
            $socialID=get_session($socialType.'_id');
            if($socialID){
                $socialSignUp = 1;
            }
        }

        $mail = get_session('j_mail');
        $sql = 'SELECT * FROM user_temp WHERE mail = ' . to_sql($mail) . ' ORDER BY added_on DESC';
        $userInfo=DB::row($sql);

        $temp_data = [
            'name'                  => get_session('j_name'),
            'phone_number'          => validate_phone_number(get_session('j_phone')),
            'birth'                 => get_session('j_year') . '-' . get_session('j_month') . '-' . get_session('j_day'),
            'gender'                => get_session('j_orientation'),
            'mail'                  => $mail,
            'signup_as'             => get_session('j_signup_as'),
            'poster_name'           => get_session('j_poster_name'),
            'enabled_OTP_login'     => get_session('j_by_phone'),
            'socialSignUp'          => $socialSignUp,
        ];

        if(isset($userInfo)) { // update
            $id = $userInfo['id'];

            $temp_data['updated_on'] = date("Y-m-d H-i-s");
            
            DB::update(
                'user_temp', 
                $temp_data, 
                '`id` = ' . to_sql($id, 'Number')
            );

        } else {
            $temp_data['added_on'] = date("Y-m-d H-i-s");

            DB::insert('user_temp', $temp_data);
        }

        // insert/update -> user_temp_data end

        // set_session('j_by_phone_varified', ''); // for testing purpose
        if($by_phone && empty($by_phone_varified)) {
            $html->parse('with_phone', false);
        } else
            $html->parse('without_phone', false);

		if (isset($this->message)) $html->setvar('join_message', $this->message);
		parent::parseBlock($html);
	}
}

$page = new CJoin2("", $g['tmpl']['dir_tmpl_main'] . "join2.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");