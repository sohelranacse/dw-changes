<?php

class AddCandidateModel {
	var $message = "";

	function addCandidate() {
		global $g_user, $g;
		$register = date("Y-m-d H:i:s");
        $_message = [];

        $have_cv = $_POST['have_cv'];

        if(isset($_FILES['photo_file'])) {
            if($have_cv == 1) {

                $fileType = strtolower(pathinfo($_FILES["candidate_cv"]["name"], PATHINFO_EXTENSION)); 
                $fileName =  md5(time()).'.'.$fileType;
                // $targetDir = is_server() ? '_files/pdf/' : '../_files/pdf/';
                // $targetDir = $g['path']['base_url_main_head'].'_files/pdf/';
                $targetDir = $g['path']['dir_files'].'pdf/';
                $targetFilePath = $targetDir . $fileName;

                // FILE SIZE
                $file_size = $_FILES["candidate_cv"]["size"]; // File size in bytes
                $maxSize = 1 * 1024 * 1024; // 1 MB in bytes

                if($fileType == "pdf" || $fileType == "docx") {

                    $text = '';

                    if($fileType == "pdf") {
                        $PARSE_SUCCESS = false;

                        // READ PDF
                        include '_server/read-pdf/vendor/autoload.php';
                        $parser = new \Smalot\PdfParser\Parser();

                        try {
                            // PARSE FILE
                            $pdf = $parser->parseFile($_FILES["candidate_cv"]["tmp_name"]);

                            try {
                                $text = $pdf->getText();
                                $PARSE_SUCCESS = true;
                            } catch (Exception $e) {

                            }                        

                        } catch (Exception $e) {
                            // echo 'Error parsing PDF: ',  $e->getMessage();
                        }

                        if ($file_size < $maxSize) {
                            
                            // UPLOADED
                            $uploadCV = move_uploaded_file($_FILES["candidate_cv"]["tmp_name"], $targetFilePath);

                            if($PARSE_SUCCESS == false || strlen($text) < 100 && $uploadCV){
                                try {
                                    $text = unReadAblePDFtoText($targetFilePath);
                                } catch (Exception $e) {

                                }
                            }
                        }

                    } else { // docx

                        if(move_uploaded_file($_FILES["candidate_cv"]["tmp_name"], $targetFilePath)){

                            require_once '_include/current/CustomReadDocx.php';
                            $CustomReadDocx = new CustomReadDocx();

                            try {
                                $text = $CustomReadDocx->ConvertDocxToText($targetFilePath);
                            } catch (Exception $e) {

                            }                        
                        }
                    }

                    $pdfText = sanitizeAndValidateString($text);                
                    
                    if(strlen($pdfText) > 100) {

                        require_once 'CustomAi.php';
                        $customAi = new CustomAi();

                        try {
                            $pdfAiData = $customAi->BiodataToJSON($pdfText);
                        } catch (Exception $e) {
                            $pdfAiData = '';
                        }

                        if(isJson($pdfAiData)) {
                            $pdfArray = json_decode($pdfAiData, true);

                            $name = $pdfArray['name'];
                            $mail = $pdfArray['emailAddress'];
                            $phone = $pdfArray['phoneNumber'];

                            if(isset($pdfArray['name']) && invalid_name($name)) {
                                $_message = [
                                    'error'     => 1,
                                    'success'   => 0,
                                    'message'   => l('something_wrong'),
                                ];
                            }
                            /*elseif(isset($pdfArray['emailAddress']) && strlen($mail) && Common::validateField('mail', $mail)) {
                                $_message = [
                                    'error'     => 1,
                                    'success'   => 0,
                                    'message'   => l('exists_email'),
                                ];
                            }*/
                            else {
                            
                                // insert candidate
                                $name_seo = Router::getNameSeo($name);

                                $insertData = [
                                    'role'          => 'user',
                                    'under_admin'   => $g_user['user_id'],
                                    'name'          => to_sql($name, "Plain"),
                                    'name_seo'      => to_sql($name_seo, "Plain"),
                                    'password'      => to_sql($g_user['password'], "Plain"),
                                    'register'      => $register,
                                    'last_ip'       => IP::getIp(),
                                    'active'        => 0,
                                ];

                                // FIRST NAME, LAST NAME
                                $full_name = explode(" ", $name);
                                $insertData['first_name'] = $full_name[0];
                                if(isset($full_name[1]))
                                    $insertData['last_name'] = $full_name[sizeof($full_name)-1];
                                // FIRST NAME, LAST NAME END


                                if(isset($pdfArray['emailAddress']) && strlen($mail)) {
                                    if(Common::validateField('mail', $mail) == '')
                                        $dataInsert['mail'] = to_sql($mail, "Plain");
                                }

                                if(isset($pdfArray['phoneNumber']) && $phone && (strlen($phone) == 11 || strlen($phone) == 13 || strlen($phone) == 14) && $phone !== "01712345678") {
                                    $insertData['phone'] = validate_phone_number($phone);
                                }
                                DB::insert('user', $insertData);



                                $q = DB::row("SELECT user_id FROM user WHERE register = '".$register."' ORDER BY register DESC LIMIT 1");
                                $newUserID = $q['user_id'];
                                DB::execute("INSERT INTO userinfo (user_id) VALUES (".$newUserID.")");
                                DB::execute("INSERT INTO userpartner (user_id) VALUES (".$newUserID.")");

                                // upload photo
                                $g['options']['photo_approval'] = 'N';
                                $g['options']['nudity_filter_enabled'] = 'N';
                                uploadphoto($newUserID, '', 'upload', 1, '../', false, 'photo_file');

                                $data = [
                                    'profile_pdf'       =>  $fileName,
                                    // 'pdfText'        =>  $pdfText,
                                    'pdfAiData'         =>  $pdfAiData,
                                    'aiDataAdded_on'    => date("Y-m-d H:i:s")
                                ];
                                DB::update('userinfo', $data, '`user_id` = ' . to_sql($newUserID));

                                // finally save
                                $customAi->saveAiData($newUserID, $pdfAiData);

                                // profile completed progress bar
                                _completed(User::getInfoFull($newUserID));


                                $_message = [
                                    'error'     => 0,
                                    'success'   => 1,
                                    'message'   => $name_seo,
                                ];
                            }
                        } else {
                            $_message = [
                                'error'     => 1,
                                'success'   => 0,
                                'message'   => l('document_not_readable'),
                            ];
                        }
                    } else {
                        $_message = [
                            'error'     => 1,
                            'success'   => 0,
                            'message'   => l('file_not_readable'),
                        ];
                    }

                } else {
                    $_message = [
                        'error'     => 1,
                        'success'   => 0,
                        'message'   => l('format_incorrect'),
                    ];
                }

            } else {

                $orientation = get_param('orientation', 'Number');
                $gender = ($orientation == 1) ? "M" : "F";

                // DATE OF BIRTH START
                $month  = (int) get_param('month', 1);
                $day    = (int) get_param('day', 1);
                $year   = (int) get_param('year', 1990);

                $birth = date('Y-m-d', strtotime($year . '-' . $month . '-' .  $day));
                $h = zodiac($birth);
                // DATE OF BIRTH START
                
                $name = trim(get_param('username'));
                $phone = trim(get_param('phone'));
                $mail = get_param('email', '');

                $country = get_param('country', '');
                $state   = get_param('state', '');
                $city    = get_param('city', '');

                // $this->message .= User::validateName($name);
                if($mail)
                    $this->message .= Common::validateField('mail', $mail) ? l('exists_email') . '<br>' : '';
                // $this->message .= Common::validateField('phone', $phone) ? l('phone_email') . '<br>' : '';

                
                $fileTemp = $g['path']['dir_files'] . 'temp/admin_upload_user_profile_' . time();
                Common::uploadDataImageFromSetData($fileTemp, 'photo_file');
                $this->message .= User::validatePhoto("photo_file");
            

                if ($this->message == '')
                {
                    // PHONE NUMBER
                    $phone_number = validate_phone_number($phone);

                    // USER NAME
                    $name_seo = Router::getNameSeo($name);

                    $dataInsert = [
                        'role'              => 'user',
                        'under_admin'       => $g_user['user_id'],
                        'name'              => to_sql($name, 'Plain'),
                        'name_seo'          => to_sql($name_seo, 'Plain'),
                        'password'          => to_sql($g_user['password'], 'Plain'),

                        'country_id'        => to_sql($country, "Plain"),
                        'state_id'          => to_sql($state, "Number"),
                        'city_id'           => to_sql($city, "Number"),
                        'country'           => to_sql(Common::getLocationTitle('country', $country), 'Plain'),
                        'state'             => to_sql(Common::getLocationTitle('state', $state), 'Plain'),
                        'city'              => to_sql(Common::getLocationTitle('city', $city), 'Plain'),

                        'birth'             => "$birth",
                        'orientation'       => to_sql($orientation, 'Number'),
                        'gender'            => to_sql($gender, "Plain"),
                        'horoscope'         => "$h",
                        'register'          => "$register",
                        'last_ip'           => to_sql(IP::getIp()),
                        'active'            => 0,

                        'phone'             => to_sql($phone_number, "Plain"),
                        'profile_visit'     => 2, // default
                    ];

                    // FIRST NAME, LAST NAME
                    $full_name = explode(" ", $name);
                    $dataInsert['first_name'] = $full_name[0];
                    if(isset($full_name[1]))
                        $dataInsert['last_name'] = $full_name[sizeof($full_name)-1];
                    // FIRST NAME, LAST NAME END

                    if($mail)
                        $dataInsert['mail'] = to_sql($mail, "Plain");

                    DB::insert("user", $dataInsert);

                    $q = DB::row("SELECT user_id FROM user WHERE register = '".$register."' ORDER BY register DESC LIMIT 1");
                    $newUserID = $q['user_id'];
                    DB::execute("INSERT INTO userinfo (user_id) VALUES (".$newUserID.")");
                    DB::execute("INSERT INTO userpartner (user_id) VALUES (".$newUserID.")");

                    // upload photo
                    $g['options']['photo_approval'] = 'N';
                    $g['options']['nudity_filter_enabled'] = 'N';
                    uploadphoto($newUserID, '', 'upload', 1, '../', false, 'photo_file');

                    // profile completed progress bar
                    _completed(User::getInfoFull($newUserID));

                    // user create success
                    $_message = [
                        'error'     => 0,
                        'success'   => 1,
                        'message'   => $name_seo,
                    ];
                    // $this->message = $name_seo;
                } else {
                    $_message = [
                        'error'     => 1,
                        'success'   => 0,
                        'message'   => $this->message,
                    ];
                }
            }
        } else {
            $_message = [
                'error'     => 1,
                'success'   => 0,
                'message'   => l('please_enter_candidate_photo'),
            ];
        }

        return json_encode($_message);
	}
}