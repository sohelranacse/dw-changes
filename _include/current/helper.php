<?php

if(!function_exists('sanitizeAndValidateString')) {
function sanitizeAndValidateString($input) {
    // Remove leading and trailing whitespaces
    $cleanedInput = trim($input);

    // Remove HTML tags
    $cleanedInput = strip_tags($cleanedInput);

    // Convert special characters to HTML entities
    $cleanedInput = htmlspecialchars($cleanedInput, ENT_QUOTES, 'UTF-8');

    // Remove bullets and extra spaces
    $cleanedInput = preg_replace('/[\x{2022}\x{2023}\x{25E6}\x{2043}\x{2219}•◦∙‣⁌⁍‧⁌⁍]/u', '', $cleanedInput);
    $cleanedInput = preg_replace('/\s+/', ' ', $cleanedInput);

    // You can add more validation or sanitization steps as needed
    $cleanedInput = preg_replace('/[^A-Za-z0-9@.\- ]/', '', $cleanedInput); // Removing special characters

    return <<<EOT
    $cleanedInput
    EOT;
}
}
if(!function_exists('dd')) {
	function dd($array1='Debug 1', $array2='', $array3='') {
	    echo '<pre>';print_r($array1);echo '</pre>';

	    if($array2)
	    	echo '<hr><pre>';print_r($array2);echo '</pre>';

	    if($array3)
	    	echo '<hr><pre>';print_r($array3);echo '</pre>';
	    exit();
	}
}
if ( ! function_exists('dynamic_column')) {
	function dynamic_column($table, $where, $where_id, $return) {
		if($where_id) {
			$row = DB::row("SELECT $return FROM $table WHERE $where = $where_id");
			if (isset($row))
				return $row["$return"];
		}		
	}
}
if(!function_exists('level_of_education')) {
	function level_of_education() {
	    return [
	    	'3' => 'Secondary',
	    	'4' => 'Higher Secondary',
	    	'5' => 'Diploma',
	    	'6' => 'Bachelor/Honors',
	    	'7' => 'Masters',
	    	'8' => 'PhD (Doctor of Philosophy)',
	    	'9' => 'Other'
	    ];
	}
}
if(!function_exists('level_of_education_details')) {
	function level_of_education_details($levelOfEducation) {
		$educationLevels = array(
		    '3' => array(
		    	'SSC',
		        'Secondary',
		    	'High School',
		        'Secondary School Certificate (SSC)',
		        'High School Diploma',
		        'General Certificate of Secondary Education (GCSE)',
		        'O-Level (Ordinary Level)',
		        '10th Grade Completion',
		        'Junior School',
		        'Middle School Completion',
		        'Lower Secondary Education',
		        'Secondary Level Qualification',
		    ),
		    '4' => array(
		        'HSC',
		        'Higher Secondary',
		        'Higher Secondary Certificate (HSC)',
		        'A-Level (Advanced Level)',
		        '12th Grade Completion',
		        'Senior Secondary Education',
		        'Pre-University Certificate',
		        'Intermediate Certificate',
		        '12th Grade Diploma',
		        'College Preparatory Program',
		        'Elementary School',
		    ),
		    '5' => array(
		        'Diploma',
		        'Diploma of Higher Education',
		        'Vocational Diploma',
		        'Post-Secondary Diploma',
		        'Advanced Diploma',
		        'Professional Diploma',
		        'Associate\'s Degree',
		        'Technical Diploma',
		        'Undergraduate Certificate',
		        'Professional Development Diploma',
		        'Executive Diploma',
		    ),
		    '6' => array(
		        "BSc",
		        "Bachelor's",
		        'Bachelor of Arts (BA)',
		        'Bachelor of Science (BSc)',
		        'Bachelor of Commerce (BCom)',
		        'Bachelor of Engineering (BEng)',
		        'Honors Degree',
		        'Bachelor of Fine Arts (BFA)',
		        'Bachelor of Technology (BTech)',
		        'Bachelor of Business Administration (BBA)',
		        'Bachelor of Nursing (BN)',
		        'First Degree',
		    ),
		    '7' => array(
		        "MSc",
		        "Master's",
		        'Master of Arts (MA)',
		        'Master of Science (MSc)',
		        'Master of Business Administration (MBA)',
		        'Master of Engineering (MEng)',
		        'Postgraduate Degree',
		        'Master of Public Health (MPH)',
		        'Master of Social Work (MSW)',
		        'Master of Fine Arts (MFA)',
		        'Executive Master\'s Degree',
		        'Advanced Studies Degree',
		    ),
		    '8' => array(
		        'PhD',
		        'Doctor of Philosophy (PhD)',
		        'Doctorate Degree',
		        'DPhil (Doctor of Philosophy)',
		        'Doctor of Education (EdD)',
		        'Doctoral Degree',
		        'Doctor of Science (DSc)',
		        'Doctor of Business Administration (DBA)',
		        'Research Doctorate',
		        'Terminal Degree',
		        'Doctoral Thesis Award',
		    ),
		    '9' => array(
		        'Others',
		        "Other's",
		        'Other',
		    )
		);
		$isExist = 9;
		foreach ($educationLevels as $level => $variations) {
		    if (in_array("$levelOfEducation", $variations, true)) {
		        $isExist = $level;
		        break;
		    }
		}
		return $isExist;
	}
}
if(!function_exists('get_level_of_education')) {
	function get_level_of_education($key) {
	    $value = level_of_education();
	    if (array_key_exists($key, $value)) 
			return $value[$key];
	}
}
if(!function_exists('set_weights_of_profile_attributes')) {
	function set_weights_of_profile_attributes() {
		return [
		    'name' => 3,
		    'photos' => 3,
		    'gender' => 3,
		    'birth' => 3,

		    'profile_pdf' => 2,
		    'nid_verify_status' => 2,
		    'is_verified' => 2,

		    'current_address' => 5,
		    'permanent_address' => 5,
		    'religion' => 3,
		    'about' => 3,
		    'interested' => 3,
		    'favored_location_preference' => 4,
		    'unfavored_location_preference' => 4,
		    'education' => 5,
		    'profession' => 5,
		    'relatives' => 5,
		    'additional_information' => 2,

		    'income' => 2,
		    'height' => 2,
		    'weight' => 2,
		    'complexion' => 2,
		    'hair' => 2,
		    'eye' => 2,
		    'marital_status' => 2,
		    'blood_group' => 2,
		    'smoking' => 2,
		    'drinking' => 2,
		    'how_do_you_feel_about_partners_with_kids' => 2,
		    'are_kids_accompanying' => 2,
		    'partner_wearing_hijab' => 2,
		    'having_religious_beard' => 2,
		    'partner_s_religious_beard' => 2,
		    'how_important_is_wearing_hijab' => 2,
		    'hobbies' => 3,
		    'preferred_profession' => 3
		];
	}
}
if(!function_exists('_completed')) {
	function _completed($g_user) {
		$user_id = $g_user['user_id'];
		$alreadyUserWeight = $g_user['profile_completed'];

		$usersInfo = get_user_all_information($g_user);
		$keysWithWeights = set_weights_of_profile_attributes();

		// Exclude 'age' key
		$filteredKeys = array_diff(array_keys($usersInfo), ['age']);

		// Calculate the total weight based on filtered keys with values
		$totalUserWeight = 0;
		foreach ($filteredKeys as $key) {
		    if (isset($keysWithWeights[$key]) && !empty($usersInfo[$key])) {
		        $totalUserWeight += $keysWithWeights[$key];
		    }
		}

		// IF CHANGE PROFILE COMPLETED SCORE
		if($totalUserWeight != $alreadyUserWeight) {
			$data = [
				'profile_completed'	=> $totalUserWeight
			];
			DB::update('user', $data, '`user_id` = ' . to_sql($user_id));

			// UPDATE ALL MATCH PERCENTAGE AND RECOMMENDATION
			if($g_user['role'] == 'user') {
				DB::execute("UPDATE users_view SET match_percentage=null, match_recommendation = null WHERE (user_from = $user_id OR user_to = $user_id)");
			}
		}

		return $totalUserWeight;
	}
}
if(!function_exists('who_am_i')) {
	function who_am_i($user_id='') {

		$data = '';

		if($user_id) {
			$userinfo = DB::row('SELECT name, signup_as, poster_name FROM `user` WHERE user_id = '.to_sql($user_id, 'Number'));
			if(isset($userinfo)) {
				$signup_as =  $userinfo['signup_as'];
				$name =  $userinfo['name'];
				$poster_name =  $userinfo['poster_name'];

				if($signup_as == 'Matchmaker')
					$data = "Chatting with {$name} <label class='matchmakersLabel_in_chat'>".l('matchmaker')."</label>";
				elseif ($signup_as == 'Self')
					$data = "Chatting with {$name}";
				else
					$data = "Chatting with {$name}'s {$signup_as} {$poster_name}";
				
				return $data;
			}
		}
	}
}
if(!function_exists('_isAuthID')) {
	function _isAuthID($user_id='') {
		global $g_user;

		if($user_id) {
			if($g_user['user_id'] === $user_id)
				return true;
			elseif($g_user['role'] === 'group_admin') {
				if(DB::row('SELECT COUNT(*) AS have FROM `user` WHERE under_admin='.to_sql($g_user['user_id'], 'Number'))['have'])
					return true;
			} else
				_unAuthenticate();
		}
	}
}
if(!function_exists('_unAuthenticate')) {
	function _unAuthenticate() {
		echo "HTTP/1.1 403 Forbidden";die();
		// header("HTTP/1.1 403 Forbidden");
    	// exit('Access denied - user is unautenticate.');
	}
}
if(!function_exists('_is_my_candidate')) {
	function _is_my_candidate($user_id='') {
		global $g_user;

		if($user_id) {
			if($g_user['role'] === 'group_admin') {
				if(DB::row('SELECT COUNT(*) AS have FROM `user` WHERE user_id = '.to_sql($user_id, 'Number').' AND under_admin='.to_sql($g_user['user_id'], 'Number'))['have'])
					return true;
			}
		}
	}
}
if(!function_exists('_is_matchmaker')) {
	function _is_matchmaker() {
		global $g_user;
		if($g_user['role'] === 'group_admin')
			return true;
		else
			return false;
	}
}
if(!function_exists('candidate_disallowed')) {
	function candidate_disallowed($user_id='') {
		
		if($user_id) {
			$g_user = DB::row('SELECT under_admin FROM `user` WHERE under_admin IS NOT NULL AND user_id = '.to_sql($user_id, 'Number'));
			if(isset($g_user))
				return $g_user['under_admin'];
		}
	}
}
if(!function_exists('user_not_found')) {
	function user_not_found($user_id='') {
		
		if($user_id) {
			$g_user = DB::row('SELECT user_id FROM `user` WHERE ban_global = 0 AND user_id = '.to_sql($user_id, 'Number'));
			if(!isset($g_user))
				return true;
		}
	}
}


if(!function_exists('sendsms')) {
	function sendsms($phone_number, $message) {
		// return false;


	    if($_ENV['SMS_ACTIVE'] && !empty($phone_number) && !empty($message) && strlen($phone_number) == 13) {
	    	$sms_url = $_ENV['SMS_URL'];
	    	$sms_key = $_ENV['SMS_KEY'];

		    $data = [
		        "api_key"   => "$sms_key",
		        "senderid"  => $_ENV['SMS_SENDER_ID'],
		        "number"    => "$phone_number",
		        "message"   => $message
		    ];

		    $ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, "$sms_url");
		    curl_setopt($ch, CURLOPT_POST, 1);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		    $response = curl_exec($ch);
		    curl_close($ch);
		    return $response;
	    }
	}
}
if(!function_exists('unReadAblePDFtoText')) {
	function unReadAblePDFtoText($file_path) {
		$cvText = '';
		$ocr_key = $_ENV['OCR_KEY'];
		$ocr_url = $_ENV['OCR_URL'];

		// Set up the POST data
		$post_data = array(
		    'apikey' 	=> "$ocr_key",
		    'file' 		=> new CURLFile($file_path),
		);

		// Initialize cURL session
		$ch = curl_init();

		// Set cURL options
		curl_setopt($ch, CURLOPT_URL, "$ocr_url");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		// Set the headers
		$headers = array(
		    'Content-Type: multipart/form-data',
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		// Execute cURL session and get the response
		$response = curl_exec($ch);

		// Check for cURL errors
		// if (curl_errno($ch)) {
		//     echo 'Curl error: ' . curl_error($ch);
		// }

		// Close cURL session
		curl_close($ch);

		// Output the OCR response
		$result = json_decode($response, true);

		if($result && isset($result['ParsedResults'])) {
		    $cvData = $result['ParsedResults'];
		    

		    if(sizeof($cvData)) {
		        foreach($cvData as $key => $value) {
		            $cvText .= $value['ParsedText'];
		        }
		    }
		}
		return $cvText;
	}
}
if(!function_exists('sanitizeAiData')) {
	function sanitizeAiData($value) {
		if($value) {
			$result = str_replace('N/A', '', $value);
			return $result;
		} else 
			return '';
	}
}
if(!function_exists('is_multi_array')) {
	function is_multi_array($array) {
	    return count(array_filter($array, 'is_array')) > 0;
	}
}
if(!function_exists('generateComplexPassword')) {
	function generateComplexPassword($length = 8) {
	    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%()_+{}|[]<>';
	    $password = '';

	    $max = strlen($characters) - 1;

	    for ($i = 0; $i < $length; $i++) {
	        $password .= $characters[mt_rand(0, $max)];
	    }

	    return $password;
	}
}
if(!function_exists('signupAsArray')) {
	function signupAsArray() {
	    return ['Self', 'Matchmaker', 'Guardian', 'Sibling', 'Friend'];
	}
}
if(!function_exists('findsignUpAs')) {
	function findsignUpAs($data) {
	    $value = signupAsArray();
	    if (in_array($data, $value, true)) 
			return true;
	}
}
if(!function_exists('amIghotok')) {
	function amIghotok() {
		global $g_user;
		if($g_user['role'] == "group_admin")
			return true;
	}
}
if(!function_exists('userGhotok')) {
	function userGhotok() {
		$name_seo = get_param('name_seo', 'Text');
		if($name_seo) {
			return DB::result("SELECT user_id FROM user WHERE ban_global = 0 AND role = 'group_admin' AND name_seo = ".to_sql($name_seo, 'Text'));
		}
	}
}
if(!function_exists('userGhotokMobile')) {
	function userGhotokMobile() {
		$user_id = get_param('user_id', 'Number');
		if($user_id)
			return DB::result("SELECT user_id FROM user WHERE ban_global = 0 AND role = 'group_admin' AND user_id = ".to_sql($user_id, 'Number'));
	}
}
if(!function_exists('phpIniValue')) {
	function phpIniValue($string='') { // upload_max_filesize
		if($string)
			return ini_get($string);
	}
}
if(!function_exists('checkBanUserByEmail')) {
	function checkBanUserByEmail($mail) {
		if($mail)
			return DB::result("SELECT user_id FROM user WHERE ban_global = 1 AND mail = ".to_sql($mail, 'Text'));
	}
}
if(!function_exists('checkActiveUserByEmail')) {
	function checkActiveUserByEmail($mail) {
		if($mail)
			return DB::result("SELECT user_id FROM user WHERE ban_global = 0 AND mail = ".to_sql($mail, 'Text'));
	}
}

if(!function_exists('generatePassword')) {
	function generatePassword() {
	    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $numbers = '0123456789';

	    // Function to get a random character from a string
	    function getRandomElement($str) {
	        return $str[random_int(0, strlen($str) - 1)];
	    }

	    $passwordArray = [];

	    // Add 3 random characters
	    for ($i = 0; $i < 3; $i++) {
	        $passwordArray[] = getRandomElement($characters);
	    }

	    // Add 2 random numeric characters
	    for ($i = 0; $i < 3; $i++) {
	        $passwordArray[] = getRandomElement($numbers);
	    }

	    // Shuffle the array to ensure randomness
	    shuffle($passwordArray);

	    // Convert the array to a string
	    $password = implode('', $passwordArray);
	    return $password;
	}
}
if(!function_exists('email_verfication_required')) {
	function email_verfication_required() {
		DB::query("SELECT * FROM `config` WHERE `option` = 'email_verify' AND `value` = 1");
		return DB::num_rows();
	}
}
if(!function_exists('check_uploaded_photo')) {
	function check_uploaded_photo() {
		DB::query("SELECT * FROM `photo` WHERE `user_id` = ".guid());
		return DB::num_rows();
	}
}
if(!function_exists('check_uploaded_photo_of_user')) {
	function check_uploaded_photo_of_user($user_id) {
		DB::query("SELECT * FROM `photo` WHERE `user_id` = $user_id");
		return DB::num_rows();
	}
}
if (!function_exists('is_server')) {
	function is_server() {
		$ipchecklist = array("localhost", "127.0.0.1", "::1");
		if(!in_array($_SERVER['REMOTE_ADDR'], $ipchecklist))
			return true;
	}
}
if (!function_exists('black_listed_keyword_name')) {
	function black_listed_keyword_name() {
	    return [
	        'admin', 'administrator', 'root', 'support', 'contact', 'webmaster', 'info', 'hello', 'john',
	        'test', 'testing', 'service', 'demo', 'manager', 'editor', 'system', 'operator', 'owner',
	        'sales', 'marketing', 'help', 'email', 'example', 'no-reply', 'office', 'staff',
	        'hr', 'sex', 'sexy', 'adult', 'porn', 'john', 'doe', 'john doe', 'john smith',
	        'finance', 'billing', 'accounting', 'accounts', 'payroll', 'career', 'job', 'jobs',
	        'security', 'register', 'registration', 'signup', 'sign-up', 'log', 'login', 'user', 'username',
	        'guest', 'visitor', 'unknown', 'anonymous', 'temp', 'temporary', 'testuser', 'sample', 'user1',
	        'user2', 'user3', 'user4', 'user5', 'user6', 'user7', 'user8', 'user9', 'user10', 'superuser',
	        'guestuser', 'public', 'private', 'personal', 'family', 'friend', 'friends', 'partner', 'client',
	        'member', 'membership', 'vip', 'premium', 'free', 'standard', 'basic', 'pro', 'plus', 'business',
	        'corporate', 'enterprise', 'company', 'individual', 'professional', 'consultant', 'advisor',
	        'expert', 'master', 'leader', 'chief', 'boss', 'president', 'ceo', 'cto', 'cfo', 'coo', 'founder',
	        'director', 'executive', 'head', 'chairman', 'officer', 'principal', 'partner', 'associate',
	        'representative', 'agent', 'broker', 'dealer', 'trader', 'merchant', 'retailer', 'wholesaler',
	        'supplier', 'distributor', 'manufacturer', 'producer', 'contractor', 'developer', 'programmer',
	        'engineer', 'architect', 'designer', 'analyst', 'specialist', 'consultant', 'strategist',
	        'advisor', 'coach', 'trainer', 'educator', 'teacher', 'professor', 'instructor', 'mentor',
	        'guide', 'counselor', 'therapist', 'psychologist', 'doctor', 'nurse', 'physician', 'surgeon',
	        'dentist', 'pharmacist', 'lawyer', 'attorney', 'judge', 'solicitor', 'barrister', 'paralegal',
	        'accountant', 'auditor', 'bookkeeper', 'consultant', 'planner', 'analyst', 'researcher',
	        'scientist', 'technician', 'specialist', 'operator', 'worker', 'employee', 'staff', 'team',
	        'member', 'associate', 'partner', 'collaborator', 'volunteer', 'contributor', 'donor',
	        'supporter', 'patron', 'funder', 'investor', 'shareholder', 'stakeholder', 'benefactor',
	        'beneficiary', 'recipient', 'sponsor', 'underwriter', 'grantor', 'grantee', 'awardee',
	        'nominee', 'candidate', 'applicant', 'registrant', 'entrant', 'participant', 'competitor',
	        'contestant', 'rival', 'opponent', 'challenger', 'adversary', 'enemy', 'foe', 'nemesis',
	        'antagonist', 'villain', 'criminal', 'offender', 'culprit', 'perpetrator', 'suspect', 'accused',
	        'defendant', 'plaintiff', 'complainant', 'petitioner', 'respondent', 'appellant', 'appellee',
	        'witness', 'testifier', 'informant', 'source', 'spy', 'agent', 'operative', 'detective',
	        'investigator', 'inspector', 'examiner', 'auditor', 'monitor', 'reviewer', 'critic', 'commentator',
	        'analyst', 'pundit', 'expert', 'authority', 'guru', 'master', 'maestro', 'virtuoso', 'prodigy',
	        'genius', 'wizard', 'sorcerer', 'magician', 'illusionist', 'conjuror', 'juggler', 'acrobat',
	        'performer', 'entertainer', 'artist', 'artisan', 'craftsman', 'creator', 'designer', 'developer',
	        'builder', 'maker', 'fabricator', 'manufacturer', 'producer', 'constructor', 'erector', 'assembler',
	        'installer', 'operator', 'user', 'player', 'gamer', 'competitor', 'athlete', 'sportsman',
	        'sportswoman', 'sportsperson', 'racer', 'driver', 'rider', 'pilot', 'navigator', 'captain',
	        'skipper', 'commander', 'leader', 'chief', 'head', 'director', 'manager', 'supervisor', 'foreman',
	        'overseer', 'controller', 'coordinator', 'administrator', 'executive', 'officer', 'official',
	        'authority', 'regulator', 'inspector', 'auditor', 'examiner', 'reviewer', 'critic', 'commentator',
	        'analyst', 'consultant', 'advisor', 'coach', 'trainer', 'mentor', 'guide', 'instructor', 'teacher',
	        'educator', 'professor', 'lecturer', 'tutor', 'facilitator', 'moderator', 'facilitator', 'mediator',
	        'negotiator', 'arbitrator', 'adjudicator', 'judge', 'referee', 'umpire', 'official', 'marshal',
	        'warden', 'officer', 'patrol', 'guard', 'watchman', 'sentinel', 'lookout', 'spotter', 'scout',
	        'explorer', 'pathfinder', 'trailblazer', 'pioneer', 'innovator', 'inventor', 'originator',
	        'initiator', 'founder', 'creator', 'author', 'writer', 'novelist', 'poet', 'playwright',
	        'screenwriter', 'composer', 'songwriter', 'musician', 'singer', 'vocalist', 'instrumentalist',
	        'conductor', 'director', 'producer', 'filmmaker', 'cinematographer', 'photographer', 'artist',
	        'painter', 'sculptor', 'illustrator', 'designer', 'decorator', 'stylist', 'fashionista',
	        'model', 'actor', 'actress', 'performer', 'entertainer', 'showman', 'comedian', 'humorist',
	        'satirist', 'cartoonist', 'animator', 'puppeteer', 'clown', 'jester', 'buffoon', 'fool',
	        'trickster', 'prankster', 'mischief-maker', 'troublemaker', 'rebel', 'revolutionary', 'activist',
	        'campaigner', 'advocate', 'proponent', 'supporter', 'backer', 'follower', 'adherent', 'devotee',
	        'fanatic', 'enthusiast', 'fan', 'buff', 'aficionado', 'connoisseur', 'expert', 'specialist',
	        'authority', 'guru', 'master', 'maestro', 'virtuoso', 'genius', 'wizard', 'whiz', 'prodigy',
	        'whizz', 'hotshot', 'ace', 'star', 'champion', 'winner', 'victor', 'hero', 'savior', 'rescuer',
	        'defender', 'protector', 'guardian', 'custodian', 'caretaker', 'keeper', 'warden', 'watchman',
	        'sentinel', 'guard', 'patrol', 'officer', 'marshal', 'sheriff', 'policeman', 'policewoman',
	        'police', 'detective', 'investigator', 'inspector', 'sleuth', 'private eye', 'gumshoe', 'hawkshaw',
	        'shamus', 'tecs', 'sherlock', 'sleuthhound', 'scout', 'explorer', 'voyager', 'traveler', 'tourist',
	        'visitor', 'holidaymaker', 'backpacker', 'wanderer', 'rover', 'nomad', 'gypsy', 'vagabond',
	        'hobo', 'drifter', 'transient', 'wayfarer', 'journeyman', 'itinerant', 'migrant', 'emigrant',
	        'expatriate', 'refugee', 'exile', 'outcast', 'fugitive', 'runaway', 'escaper', 'escapee',
	        'absconder', 'deserter', 'renegade', 'turncoat', 'traitor', 'betrayer', 'defector', 'apostate',
	        'heretic', 'nonconformist', 'dissenter', 'iconoclast', 'individualist', 'maverick', 'freethinker',
	    ];
	}
}
if (!function_exists('invalid_name')) {
	function invalid_name($name) {
		$blackListed = black_listed_keyword_name();
		if(in_array(strtolower($name), $blackListed, true))
			return true;
	}
}
if (!function_exists('getCurrentUrl')) {
	function getCurrentUrl() {
	    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	    $currentUrl = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	    return $currentUrl;
	}
}
if (!function_exists('sslcommerz_config')) {
	function sslcommerz_config() {
		global $g;
		$URL = $g['path']['base_url_main_head'];

		return [
		    'success_url' 			=> $URL.'_pay/sslcommerz/success.php',
		    'failed_url' 			=> $URL.'_pay/sslcommerz/fail.php',
		    'cancel_url' 			=> $URL.'_pay/sslcommerz/cancel.php',
		    'ipn_url' 				=> $URL.'_pay/sslcommerz/ipn.php',

		    'apiDomain' 			=> $_ENV['IS_SANDBOX'] ? 'https://sandbox.sslcommerz.com' : 'https://securepay.sslcommerz.com',
		    'apiCredentials' 		=> [
		        'store_id' 			=> $_ENV['STORE_ID'],
		        'store_password' 	=> $_ENV['STORE_PASSWORD'],
		    ],
		    'apiUrl' => [
		        'make_payment' 		=> "/gwprocess/v4/api.php",
		        'order_validate' 	=> "/validator/api/validationserverAPI.php",
		    ],
		    'connect_from_localhost' => false,
		    'verify_hash' 			 => true,
		    'nid_verification_fee' 	 => $_ENV['NID_VERIFICATION_FEE'],

		    'cus_email' 	 		=> 'payment@deshiwedding.com', // Jdy@6xhd
		    'cus_add1' 	 			=> '147/H, Green Road, Tejgaon',
		    'cus_city' 	 			=> 'Dhaka',
		    'cus_postcode' 	 		=> '1215',
		    'cus_country' 	 		=> 'Bangladesh',
		    'cus_phone' 	 		=> '01713092756',
		];
	}
}
if (!function_exists('default_search_by_gender')) {
	function default_search_by_gender($gender) {
	    if($gender == 'M') 
	        return ' AND u.gender = "F" AND u.orientation = 2';
	    else if($gender == 'F') 
	        return ' AND u.gender = "M" AND u.orientation = 1';
	    else
	    	return '';
	}
}
if(!function_exists('update_profile_visit')) {
	function update_profile_visit($user_id) {
		if($user_id) {			
			DB::execute("
				UPDATE `user` 
				SET `profile_visit` = `profile_visit` + 1 
				WHERE `user_id` = ".to_sql($user_id)
			);
		}
	}
}
if(!function_exists('validate_phone_number')) {
	function validate_phone_number($phone) {
	    // Remove all non-numeric characters
	    $phone_number = preg_replace('/[^0-9]/', '', $phone);
	    
	    // Check if the number has 10 or 11 digits
	    if (strlen($phone_number) == 10) {
	        // If 10 digits, prepend the country code 880
	        $phone_number = '880' . $phone_number;
	    } elseif (strlen($phone_number) == 11 && substr($phone_number, 0, 1) == '0') {
	        // If 11 digits and starts with 0, replace leading 0 with 880 (Bangladesh country code)
	        $phone_number = '880' . substr($phone_number, 1);
	    }

	    return $phone_number;
	}
}
if(!function_exists('isJson')) {
	function isJson($string) {
		if(strlen($string)) {
	    	// Try decoding the string as JSON
	    	json_decode($string);

		    // Check if there was an error during decoding
		    return (json_last_error() === JSON_ERROR_NONE);
		}
	}
}
if(!function_exists('catchJson')) {
	function catchJson($json_input) {
		if (preg_match('/\{.*\}/s', $json_input, $matches)) {
		    $json_string = $matches[0];

		    // Decode the JSON string
		    $data = json_decode($json_string, true);

		    // Check if JSON decoding was successful
		    if (is_array($data)) {
		        // Sanitize each value
		        foreach ($data as $key => $value) {
				    if (is_array($value)) {
				        // Optionally handle arrays if needed, e.g., recursively sanitize
				        $data[$key] = $value; // Or handle nested arrays as per your requirement
				    } else {
				        $data[$key] = htmlspecialchars(strip_tags($value));
				    }
				}


		        // Encode back to JSON
		        $sanitized_json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

		        // Output the sanitized JSON
		        // header('Content-Type: application/json');
		        return $sanitized_json;
		    }
		}
	}
}
if(!function_exists('isValid_date_of_birth')) {
	function isValid_date_of_birth($string) {
	    // Try to convert the input to a timestamp
	    $timestamp = strtotime($string);
	    
	    if ($timestamp && $string !== "1950-10-01") {
	        // Convert the timestamp to Y-m-d format
	        $dateString = date('Y-m-d', $timestamp);
	        
	        // Validate the newly formatted date string
	        $date = DateTime::createFromFormat('Y-m-d', $dateString);
	        if ($date && $date->format('Y-m-d') === $dateString) {
	            $today = new DateTime('today');
	            $age = $date->diff($today)->y;

	            // Check if the age is between 0 and 120 years
	            if ($age >= 0 && $age <= 120) {
	                return $date->format('Y-m-d'); // Return date in YYYY-MM-DD format
	            }
	        }
	    }
	    
	    return false; // Invalid date of birth
	}

}
if (!function_exists('calculateAge')) {
	function calculateAge($birthdate) {
		if($birthdate) {
		    $birthDate = new DateTime($birthdate);
		    $currentDate = new DateTime();
		    $interval = $birthDate->diff($currentDate);
		    return "{$interval->y} years, {$interval->m} months, and {$interval->d} days";
		}
	}
}
if(!function_exists('get_parmanent_address')) {
	function get_parmanent_address($user_id='') {
		if($user_id) {
			$address_info = DB::row('
		        SELECT a.current_street, a.permanent_street,
		        (SELECT country_title FROM geo_country WHERE country_id = a.permanent_country_id) AS permanent_country,
		        (SELECT state_title FROM geo_state WHERE state_id = a.permanent_state_id) AS permanent_state,
		        (SELECT city_title FROM geo_city WHERE city_id = a.permanent_city_id) AS permanent_city
		        FROM user a
		        WHERE a.user_id = '.to_sql($user_id)
		    );
		    return implode(', ', array_filter([$address_info['permanent_street'], $address_info['permanent_city'], $address_info['permanent_state'], $address_info['permanent_country']]));
		}
	}
}
if(!function_exists('location_preference')) {
	function location_preference($user_id='') {
		if($user_id) {
			$favorite_un_address_info = DB::row('
		        SELECT 
		        (SELECT country_title FROM geo_country WHERE country_id = a.favorite_country_id) AS favorite_country,
		        (SELECT state_title FROM geo_state WHERE state_id = a.favorite_state_id) AS favorite_state,
		        (SELECT city_title FROM geo_city WHERE city_id = a.favorite_city_id) AS favorite_city,
		        (SELECT country_title FROM geo_country WHERE country_id = a.unfavorite_country_id) AS unfavorite_country,
		        (SELECT state_title FROM geo_state WHERE state_id = a.unfavorite_state_id) AS unfavorite_state,
		        (SELECT city_title FROM geo_city WHERE city_id = a.unfavorite_city_id) AS unfavorite_city
		        FROM user a
		        WHERE a.user_id = '.to_sql($user_id)
		    );
		    $favored = $unfavored = [];
		            
		    // favored
		    if($favorite_un_address_info['favorite_city'])
		        $favored['favorite_city'] = $favorite_un_address_info['favorite_city'];
		    if($favorite_un_address_info['favorite_state'])
		        $favored['favorite_state'] = $favorite_un_address_info['favorite_state'];
		    if($favorite_un_address_info['favorite_country'])
		        $favored['favorite_country'] = $favorite_un_address_info['favorite_country'];

		    // unfavored
		    if($favorite_un_address_info['unfavorite_city'])
		        $unfavored['unfavorite_city'] = $favorite_un_address_info['unfavorite_city'];
		    if($favorite_un_address_info['unfavorite_state'])
		        $unfavored['unfavorite_state'] = $favorite_un_address_info['unfavorite_state'];
		    if($favorite_un_address_info['unfavorite_country'])
		        $unfavored['unfavorite_country'] = $favorite_un_address_info['unfavorite_country'];

		    $favorite_address = implode(", ", $favored);
		    $unfavorite_address = implode(", ", $unfavored);

		    return [
		    	'favorite_address' => ($favorite_address) ? $favorite_address : '',
		    	'unfavorite_address' => ($unfavorite_address) ? $unfavorite_address : '',
		    ];
		}
	}
}
if(!function_exists('users_education')) {
	function users_education($user_id='') {
		if($user_id) {
			$educationList = DB::all("
		    	SELECT a.*, b.degree_name
		    	FROM user_education a
		    	LEFT JOIN user_education_degree b ON (a.degree_id = b.degree_id)
		    	WHERE a.user_id = {$user_id}
		    	ORDER BY a.added_on
		    ");
		    $educationArray = [];
		    if(sizeof($educationList)) {
		    	foreach($educationList as $key => $educationRow) {
		    		$educationData = '';

			    		if($educationRow['degree_id'] > 0)
			    			$educationData .= $educationRow['degree_name'].', ';
			    		elseif($educationRow['degree_title'])
			    			$educationData .= $educationRow['degree_title'].', ';

			    		if($educationData) {

					    if ($educationRow['subject_title'])
					    	$educationData .= "Subject: ".$educationRow['subject_title'].', ';

					    if ($educationRow['school_name'])
					    	$educationData .= "Institute Name: ".$educationRow['school_name'].', ';

					    if ($educationRow['address'] && !empty($educationRow['address']))
					        $educationData .= "Address: ".$educationRow['address'].', ';
					    
					    if ($educationRow['results'] && !empty($educationRow['results']))
					        $educationData .= "Results: ".$educationRow['results'].', ';
					    

					    if ($educationRow['passing_year'] > 0 && !empty($educationRow['passing_year']))
					        $educationData .= "Year: ".$educationRow['passing_year'];

					    $educationArray[] = $educationData;
					}
		    	}
		    }
		    return $educationArray;
		}
	}
}
if(!function_exists('users_profession')) {
	function users_profession($user_id='') {
		if($user_id) {
			
			$professionList = DB::all("
		    	SELECT a.*
		    	FROM user_profession a
		    	WHERE a.user_id = {$user_id}
		    	ORDER BY a.added_on
		    ");
		    
		    $professionArray = [];
		    if(sizeof($professionList)) {
		    	foreach ($professionList as $key => $professionRow) {
		    		$professionData = '';

				    $professionData .= $professionRow['position'] . ", ";

				    if($professionData) {

					    if ($professionRow['company'] && !empty($professionRow['company']))   
					    	$professionData .= 'Company: ' . $professionRow['company'] . ', ';
					   	
					   	if ($professionRow['profession_type'] && !empty($professionRow['profession_type']))    
					    	$professionData .= 'Department: ' . $professionRow['profession_type'] . ', ';
					    
					    if ($professionRow['address'] && !empty($professionRow['address']))
					        $professionData .= 'Address: ' . $professionRow['address'];

					    $professionArray[] = $professionData;
					}
				}
		    }
		    return $professionArray;
		}
	}
}
if(!function_exists('users_relatives')) {
	function users_relatives($user_id='') {
		if($user_id) {
			
			$relativeList = DB::all("
		    	SELECT a.*, c.title AS marital_title
		    	FROM user_relatives a
		    	LEFT JOIN var_marital_status c ON (a.marital_status = c.id)
		    	WHERE a.user_id = {$user_id}
		    	ORDER BY a.added_on
		    ");
		    $relativeArray = [];
		    if(sizeof($relativeList)) {
		    	foreach ($relativeList as $relativeRow) {
		    		$relativeData = $relativeRow['relative_name'] . ', ';
				    $relativeData .= 'Relation with: ' . $relativeRow['relation'] . ', ';

				    if($relativeData) {
				    
					    if ($relativeRow['marital_status'] && !empty($relativeRow['marital_status']))
					        $relativeData .= 'Marital Status ' . $relativeRow['marital_title'] . ', ';
					    
					    if ($relativeRow['address'] && !empty($relativeRow['address']))
					        $relativeData .= 'Address: ' . $relativeRow['address'] . ', ';
					    
					    if ($relativeRow['profession_type'] && !empty($relativeRow['profession_type']))
					        $relativeData .= 'Profession: ' . $relativeRow['profession_type'] . ', ';
					    
					    if ($relativeRow['position'] && !empty($relativeRow['position']))
					        $relativeData .= 'Position: ' . $relativeRow['position'] . ', ';
					    
					    if ($relativeRow['company'] && !empty($relativeRow['company']))
					        $relativeData .= 'Company: ' . $relativeRow['company'] . ', ';
					    
					    if ($relativeRow['degree_title'] && !empty($relativeRow['degree_title']))
					        $relativeData .= 'Degree: ' . $relativeRow['degree_title'];

					    $relativeArray[] = $relativeData;
					}
				}
		    }

		    return $relativeArray;
		}
	}
}
if(!function_exists('count_photos')) {
	function count_photos($user_id) {
		if($user_id) {
			return DB::result("SELECT COUNT(*) AS total FROM photo WHERE user_id = {$user_id}");
		}
	}
}
if(!function_exists('get_user_all_information')) {
	function get_user_all_information($row) {
		$option_field = ['income','height','weight','complexion','hair','eye','marital_status','blood_group','smoking','drinking','how_do_you_feel_about_partners_with_kids','are_kids_accompanying','partner_wearing_hijab','having_religious_beard','partner_s_religious_beard'];

        $user_field_data = [];
        foreach($option_field as $value) {
            $user_field_data[$value] = dynamic_column('var_'.$value.'', 'id', $row[$value], 'title');
        }
        $user_field_data['how_important_is_wearing_hijab'] = $row['how_important_is_wearing_hijab'];
        
        // hobbies, preferred_profession
        $userCheckboxData = [];
        if(sizeof($row['checkbox'])) {
            foreach($row['checkbox'] as $key => $checkboxValue) {
                $chValue = '';

                $query = DB::row("SELECT `option` FROM config WHERE id = $key");
                $chOption = $query['option'];                
                foreach($checkboxValue as $value) {
                    $chValue .= dynamic_column('var_'.$chOption.'', 'id', $value, 'title').', ';
                }
                $userCheckboxData[$chOption] = $chValue;
            }
        }

        $permanent_address = get_parmanent_address($row['user_id']);
        $location_preference = location_preference($row['user_id']);

        $user_data = [
            'name' => $row['name'],
            'photos' => count_photos($row['user_id']),
            'gender' => ($row['orientation'] == 1) ? "Male" : "Female",
            'birth' => $row['birth'],
            'age' => calculateAge($row['birth']),

            'profile_pdf' => $row['profile_pdf'], // uploaded biodata
            'nid_verify_status' => ($row['nid_verify_status'] == 1) ? "Yes" : "", // NID
            'is_verified' => ($row['is_verified'] == "Y") ? "Yes" : "", // phone number

            'current_address' => $row['current_street'].', '.$row['city'].', '.$row['state'].', '.$row['country'],
            'permanent_address' => $permanent_address,
            'religion' => dynamic_column('var_religion', 'id', $row['religion'], 'title'),
            'about' => $row['about_me'],
            'interested' => $row['interested_in'],
            'favored_location_preference' => $location_preference['favorite_address'],
            'unfavored_location_preference' => $location_preference['unfavorite_address'],

            'education' => users_education($row['user_id']),
            'profession' => users_profession($row['user_id']),
            'relatives' => users_relatives($row['user_id']),

            'additional_information' => $row['additional_info'],
        ];
        return array_merge($user_data, $user_field_data, $userCheckboxData);
	}
}
if(!function_exists('usersMatch')) {
	function usersMatch($uid) {
		$guid = guid();
        $guserInfo = User::getInfoFull($guid, DB_MAX_INDEX);
        $userInfo = User::getInfoFull($uid, DB_MAX_INDEX);

        $both_users[0] = $guserInfo;
        $both_users[1] = $userInfo;

        $UsersData = [];
        foreach($both_users as $row) {

        	$UsersData[] = get_user_all_information($row);  
        }
        return usersDataArrayToString($UsersData);
	}
}
if(!function_exists('reduceEmptyArray')) {
	function reduceEmptyArray($UsersData) {
		// Loop through each user's data and filter out empty values
		$UsersData = array_map(function($user) {
		    return array_filter($user, function($value) {
		        return !empty($value);
		    });
		}, $UsersData);

		// Loop through each user's data and filter out empty values
		$UsersData = array_map(function($user) {
		    return array_filter($user, fn($value) => !empty($value));
		}, $UsersData);

		// Merge the arrays by keeping only common keys
		$mergedData = [
		    array_intersect_key($UsersData[0], $UsersData[1]),
		    array_intersect_key($UsersData[1], $UsersData[0])
		];

		return $mergedData;
	}
}
if(!function_exists('usersDataArrayToString')) {
	function usersDataArrayToString($UsersData) {

		// $UsersData = reduceEmptyArray($UsersData);

		$blackListed = ['photos', 'birth', 'profile_pdf', 'nid_verify_status', 'is_verified'];
		$data = '';
        if (sizeof($UsersData)) { 
		    foreach ($UsersData as $key => $value) {

		        $data .= 'User '.($key+1).": \n";
		        
		        foreach ($value as $k => $row) {

		        	if(in_array($k, $blackListed))
		        		continue;

		            // Check if the value is an array (for fields like education and profession, relatives)
		            if (is_array($row)) {
		                $data .= str_replace("_", " ", ucfirst($k)) . ":\n";
		                
		                // Loop through each item in the sub-array
		                foreach ($row as $item) {
		                    $data .= "  - " . $item . "\n";
		                }
		            } else { // For other fields
		                $data .= str_replace("_", " ", ucfirst($k)) . ': ' . $row . "\n";
		            }
		        }
		        $data .= "\n";
		    }
		}
        return [
        	'data'  => $data,
        	'user1' => $UsersData[0]['name'],
        	'user2' => $UsersData[1]['name'],
        ];
	}
}
if(!function_exists('generateSecurePassword')) {
	function generateSecurePassword($length = 8) {
	    if ($length < 8) {
	        throw new Exception("Password length must be at least 8 characters.");
	    }

	    // Character pools
	    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
	    $numbers = '0123456789';
	    $symbols = '!@#$%^&*';

	    // Ensure the password contains at least one character from each pool
	    $password = [
	        $uppercase[random_int(0, strlen($uppercase) - 1)],
	        $lowercase[random_int(0, strlen($lowercase) - 1)],
	        $numbers[random_int(0, strlen($numbers) - 1)],
	        $symbols[random_int(0, strlen($symbols) - 1)],
	    ];

	    // Fill the remaining length with a mix of all characters
	    $allCharacters = $uppercase . $lowercase . $numbers . $symbols;
	    for ($i = 4; $i < $length; $i++) {
	        $password[] = $allCharacters[random_int(0, strlen($allCharacters) - 1)];
	    }

	    // Shuffle the password to avoid predictable patterns
	    shuffle($password);

	    // Convert the password array to a string and return
	    return implode('', $password);
	}
}
if(!function_exists('blurImage')) {
	function blurImage($filePath, $mime, $outputPath) {
	    // Create an image resource from the file
	    if ($mime === 'image/jpeg') {
	        $image = imagecreatefromjpeg($filePath);
	    } elseif ($mime === 'image/png') {
	        $image = imagecreatefrompng($filePath);
	    } else {
	        die('Unsupported image type.');
	    }

	    // Get original dimensions
	    $width = imagesx($image);
	    $height = imagesy($image);

	    // Shrink dimensions to a very small size (e.g., 5% of the original size)
	    $smallWidth = max(1, round($width * 0.05));  // Ensure it's an integer and at least 1 pixel
	    $smallHeight = max(1, round($height * 0.05)); // Ensure it's an integer and at least 1 pixel

	    // Create a temporary image for resizing
	    $tempImage = imagecreatetruecolor($smallWidth, $smallHeight);

	    // Shrink the image
	    imagecopyresampled($tempImage, $image, 0, 0, 0, 0, $smallWidth, $smallHeight, $width, $height);

	    // Enlarge the shrunk image back to original dimensions
	    imagecopyresampled($image, $tempImage, 0, 0, 0, 0, $width, $height, $smallWidth, $smallHeight);

	    // Free the temporary image memory
	    imagedestroy($tempImage);

	    // Save the blurred image to the output path
	    if ($mime === 'image/jpeg') {
	        imagejpeg($image, $outputPath, 100); // Save with maximum quality
	    } elseif ($mime === 'image/png') {
	        imagepng($image, $outputPath);
	    }

	    // Free the main image memory
	    imagedestroy($image);

	    return $outputPath;
	}

}