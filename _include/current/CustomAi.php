<?php

use Orhanerday\OpenAi\OpenAi;
include '_server/openai/vendor/autoload.php';

class CustomAi {

	function BiodataToJSON($text) {
		$pdfAiData = '';

		if($_ENV['OPENAI_ACTIVE']) {
			$cleanedText = mb_substr($text, 0, 2400, 'UTF-8');

			$openai_key = $_ENV['OPENAI_KEY'];
			$openai_model = $_ENV['OPENAI_MODEL'];
			// API KEY
			$open_ai = new OpenAi("$openai_key");

			// OPENAI COMPLETION
		    $content = $cleanedText.'
		    I will inquire about some details related to biodata:
		    {
		        "name": "",
		        "gender": "example: Male",
		        "phoneNumber": "example: 01712345678",
		        "emailAddress": "",
		        "dateOfBirth": "example: 1950-10-01",
		        "about": "",
		        "interested": "",
		        "maritalStatus": "",
		        "religion": "",
		        "bloodGroup": "",
		        "language": "",
		        "complexion": "",
		        "currentStreet": "",
		        "currentCity": "",
		        "currentState": "",
		        "currentCountry": "",
		        "permanentStreet": "",
		        "permanentCity": "",
		        "permanentState": "",
		        "permanentCountry": "",
		        "education",
		            {
		                "levelOfEducation",
		                "degreeTitle",
		                "subject",
		                "instituteName",
		                "instituteAddress",
		                "result",
		                "passingYear"
		            }
		        ],
		        "profession",
		            {
		                "professionType",
		                "position",
		                "companyName",
		                "companyAddress"
		            }
		        ],
		        "relatives",
		            {
		                "relativeName",
		                "relationship",
		                "maritalStatus",
		                "profession",
		                "position",
		                "company",
		                "degree"
		            }
		        ]
		    }
		    Please respond with JSON, and avoid providing any information outside of the JSON format. If there is no answer available, please leave the corresponding field "".
		    ';

		    $messages = [
			    [
			        'role' => 'system',
			        'content' => 'You are a helpful assistant.'
			    ],
			    [
			        'role' => 'user',
			        'content' => $content
			    ]
			];

		    $response = $open_ai->chat([
		        'model' 			=> "$openai_model",
		        'messages' 			=> $messages,
		        'max_tokens' 		=> 3000,
		        'temperature' 		=> 0.1,
		        'n' 				=> 1
		    ]);
		    $result = json_decode($response);
		    if($result && isset($result->choices)) {
		    	$aiChoice = $result->choices;
		    	if(sizeof($aiChoice))
		    		$pdfAiData = catchJson($aiChoice[0]->message->content); // JSON DATA
		    }
		}
		return $pdfAiData;
	}
	function matchRecommendation($UsersData) {
			
		$data = $UsersData["data"];
		$user1 = $UsersData["user1"];
		$user2 = $UsersData["user2"];

		/*return $matchAiData = json_encode([
		    "match_percentage" => "75",
		    "match_recommendation" => "You and Abed Ali share a good level of compatibility. Both of you are from Bangladesh and share the same religion, Islam, which is a strong foundation for shared values. Your age difference might bring a different perspective to the relationship, but it can also provide a balance, as Abed Ali's maturity can complement your youthful energy. Your preference for a partner with a religious beard is met by Abed Ali. However, there are some areas that might need compromise. Abed Ali's current location is not your favored location preference, and his profession is not among your preferred professions. But remember, these are just aspects that can be worked on. Overall, your compatibility score is quite promising."
		]);*/

		$matchAiData = '';

		if($_ENV['OPENAI_ACTIVE']) {

			$openai_key = $_ENV['OPENAI_KEY'];
			$openai_model = $_ENV['OPENAI_MODEL'];
			// API KEY
			$open_ai = new OpenAi("$openai_key");

		    $content = 'Please evaluate the compatibility between Me as mention User 1 and '.$user2.' on a scale of 0 to 100. Base this compatibility on shared values, similar attributes, and complementary traits. Religious match is essential. Religious mismatch will have a bigger impact than a match. Missing values of attributes of any candidates should be consider in calculating the perspective match. If religion of any candidate is missing, try to guess from the name.  Summarize your reasoning in positive vibe. Instead of using my name use "You".
			'.$data.'
			{
				"match_percentage": "",
				"match_recommendation": ""
			}
			Please respond with JSON, and avoid providing any information outside of the JSON format.';

			$messages = [
			    [
			        'role' => 'system',
			        'content' => 'You are an expert at relationship compatibility.'
			    ],
			    [
			        'role' => 'user',
			        'content' => $content
			    ]
			];
		    $response = $open_ai->chat([
		        'model' 			=> "$openai_model",
		        'messages' 			=> $messages,
		        'max_tokens' 		=> 3000,
		        'temperature' 		=> 0.1,
		        'n' 				=> 1
		    ]);
		    $result = json_decode($response);
		    if($result && isset($result->choices)) {
		    	$aiChoice = $result->choices;
		    	if(sizeof($aiChoice))
		    		$matchAiData = catchJson($aiChoice[0]->message->content); // JSON DATA
		    }
		}
		return $matchAiData;
	}

	function professionList($aiProfession) {
        $aiProfessionData = [];

        $aiProfession = is_multi_array($aiProfession) ? $aiProfession : array($aiProfession);
        foreach($aiProfession AS $key => $value) {
            $aiProfessionData[$key]['company'] = sanitizeAiData($value['companyName']);
            $aiProfessionData[$key]['address'] = sanitizeAiData($value['companyAddress']);
            $aiProfessionData[$key]['position'] = sanitizeAiData($value['position']) ? sanitizeAiData($value['position']) : sanitizeAiData($value['professionType']);
            
            $aiProfessionData[$key]['profession_type'] = sanitizeAiData($value['professionType']);
        }
        return $aiProfessionData;
	}
	function relativeList($airelatives) {
		$aiRelativesData = [];

		$aiRelativesData = is_multi_array($aiRelativesData) ? $aiRelativesData : array($aiRelativesData);
        foreach($airelatives AS $key => $value) {
            if($value['relativeName']) {
                $aiRelativesData[$key]['relative_name'] = sanitizeAiData($value['relativeName']);
                $aiRelativesData[$key]['relation'] = sanitizeAiData($value['relationship']);
                $aiRelativesData[$key]['position'] = sanitizeAiData($value['position']) ? sanitizeAiData($value['position']) : sanitizeAiData($value['profession']);
                $aiRelativesData[$key]['company'] = sanitizeAiData($value['company']);
                $aiRelativesData[$key]['degree_title'] = sanitizeAiData($value['degree']);
                $aiRelativesData[$key]['address'] = '';

                $aiMarital_status = sanitizeAiData($value['maritalStatus']);
                $marital_status = 0;
                if($aiMarital_status) {
                    $MSQuery = DB::row("SELECT * FROM var_marital_status WHERE title LIKE '%$aiMarital_status%'");
                    if(isset($MSQuery)) {
                        $marital_status = $MSQuery['id'];
                    }
                }
                $aiRelativesData[$key]['marital_status'] = $marital_status;

                $aiProfessionType = sanitizeAiData($value['profession']);
                $aiRelativesData[$key]['profession_type'] = $aiProfessionType;
            }
        }

        return $aiRelativesData;
	}
	function educationList($aiEducation) {
		$aiEducationData = [];

		$aiEducation = is_multi_array($aiEducation) ? $aiEducation : array($aiEducation);
        foreach($aiEducation AS $key => $value) {

            $aiEducationData[$key]['results'] = sanitizeAiData($value['result']);
            $aiEducationData[$key]['passing_year'] = sanitizeAiData($value['passingYear']) ? preg_replace('/[^0-9]/', '', $value['passingYear']) : '';
            $aiEducationData[$key]['address'] = sanitizeAiData($value['instituteAddress']);
            $aiEducationData[$key]['school_name'] = sanitizeAiData($value['instituteName']);
            $aiEducationData[$key]['subject_title'] = (sanitizeAiData($value['subject'])) ? sanitizeAiData($value['subject']) : sanitizeAiData($value['degreeTitle']);

            $levelOfEdu = sanitizeAiData($value['levelOfEducation']);
            $degreeTitle = sanitizeAiData($value['degreeTitle']);

            $levelOfEducation = level_of_education_details($levelOfEdu);
            $levelOfEducation1 = level_of_education_details($degreeTitle);

            $aiEducationData[$key]['education_level_id'] = ($levelOfEducation == 9) ? $levelOfEducation1 : $levelOfEducation;

            
            $degree_id = '';
            if($aiEducationData[$key]['education_level_id'] == 9) { // others
            	$aiEducationData[$key]['degree_title'] = $degreeTitle;
            	$aiEducationData[$key]['degree_id'] = $degree_id;
            } else {
	            if($degreeTitle) {
	                $degreeQuery = DB::row("SELECT * FROM user_education_degree WHERE degree_name LIKE '%$degreeTitle%'");
	                if(isset($degreeQuery)) {
	                    $degree_id = $degreeQuery['degree_id'];
	                }
	            }
	            $aiEducationData[$key]['degree_title'] = '';
	            $aiEducationData[$key]['degree_id'] = $degree_id;
	        }
            
        }
        return $aiEducationData;
	}

	function addressData($aiAddData) {

	    // INITIAL
	    $AddressData['country_id'] = $AddressData['state_id'] = $AddressData['city_id'] = $AddressData['permanent_country_id'] = $AddressData['permanent_state_id'] = $AddressData['permanent_city_id'] = $AddressData['current_street'] = $AddressData['permanent_street'] = $AddressData['gender'] = $AddressData['orientation'] = $AddressData['birth'] = '';

		if($aiAddData['currentStreet'])
	        $AddressData['current_street'] = $aiAddData['currentStreet'];
	    if($aiAddData['permanentStreet'])
	        $AddressData['permanent_street'] = $aiAddData['permanentStreet'];

	    // CURRENT COUNTRY
        $currentCountry = $aiAddData['currentCountry'];
        if($currentCountry) {
	        $CQuery = DB::row("SELECT country_id, country_title FROM geo_country WHERE hidden = 0 AND country_title LIKE '%$currentCountry%'");
	        if(isset($CQuery))
	        	$AddressData['country_id'] = $CQuery['country_id'];
	    }

        // CURRENT STATE
        $currentState = $aiAddData['currentState'];
        if($currentState) {
	        $CQuery = DB::row("SELECT state_id, state_title FROM geo_state WHERE hidden = 0 AND state_title LIKE '%$currentState%'");
	        if(isset($CQuery))
	        	$AddressData['state_id'] = $CQuery['state_id'];
	    }

        // CURRENT CITY
        if($AddressData['state_id']) {
            $currentCity = $aiAddData['currentCity'];
            if($currentCity) {
	            $CQuery = DB::row("SELECT city_id, city_title FROM geo_city WHERE hidden = 0 AND city_title LIKE '%$currentCity%' AND state_id = ".$AddressData['state_id']);
	            if(isset($CQuery))
	            	$AddressData['city_id'] = $CQuery['city_id'];
	        }
        }

        // CURRENT COUNTRY
        $permanentCountry = $aiAddData['permanentCountry'];
        if($permanentCountry) {
	        $CQuery = DB::row("SELECT country_id, country_title FROM geo_country WHERE hidden = 0 AND country_title LIKE '%$permanentCountry%'");
	        if(isset($CQuery))
	        	$AddressData['permanent_country_id'] = $CQuery['country_id'];
	    }

        // CURRENT STATE
        $permanentState = $aiAddData['permanentState'];
        if($permanentState) {
	        $CQuery = DB::row("SELECT state_id, state_title FROM geo_state WHERE hidden = 0 AND state_title LIKE '%$permanentState%'");
	        if(isset($CQuery))
	        	$AddressData['permanent_state_id'] = $CQuery['state_id'];
	    }

        // CURRENT CITY
        if($AddressData['permanent_state_id']) {
            $permanentCity = $aiAddData['permanentCity'];
            if($permanentCity) {
	            $CQuery = DB::row("SELECT city_id, city_title FROM geo_city WHERE hidden = 0 AND city_title LIKE '%$permanentCity%' AND state_id = ".$AddressData['permanent_state_id']);
	            if(isset($CQuery))
	            	$AddressData['permanent_city_id'] = $CQuery['city_id'];
	        }
        }


        // FIND COUNTRY NAME
        if(isset($AddressData['country_id']) && $AddressData['country_id'])
			$AddressData['country'] = Common::getLocationTitle('country', $AddressData['country_id']);
		if(isset($AddressData['state_id']) && $AddressData['state_id'])
			$AddressData['state'] = Common::getLocationTitle('country', $AddressData['state_id']);
		if(isset($AddressData['city_id']) && $AddressData['city_id'])
			$AddressData['city'] = Common::getLocationTitle('country', $AddressData['city_id']);


		// FIND GENDER
		if($aiAddData['gender']) {
			$gender = strtoupper($aiAddData['gender']);

	        if(isset($gender) && strlen($gender)) {
				$genderText = substr($gender, 0, 1);

				if(strlen($genderText) && ($genderText == "M" || $genderText == "F")) {

					$AddressData['gender'] = $genderText;
					$AddressData['orientation'] = ($genderText == "M") ? 1 : 2;
				}
			}
		}

		// DATE OF BIRTH
		if($aiAddData['dateOfBirth'])
			$AddressData['birth'] = isValid_date_of_birth($aiAddData['dateOfBirth']);

        return $AddressData;
	}
	function basicData($aiBasicData) {
		$BasicData = [];

		$BasicData['about_me'] = $aiBasicData['about_me'];
		$BasicData['interested_in'] = $aiBasicData['interested_in'];
		
		$BasicData['marital_status'] = '';
		if($aiBasicData['marital_status']) {
			$marital_status = $aiBasicData['marital_status'];
			$CQuery = DB::row("SELECT id FROM var_marital_status WHERE title LIKE '%$marital_status%'");
			if(isset($CQuery))
				$BasicData['marital_status'] = $CQuery['id'];
		}

		$BasicData['religion'] = '';
		if($aiBasicData['religion']) {
			$religion = $aiBasicData['religion'];
			$CQuery = DB::row("SELECT id FROM var_religion WHERE title LIKE '%$religion%'");
			if(isset($CQuery))
				$BasicData['religion'] = $CQuery['id'];
		}

		$BasicData['blood_group'] = '';
		if($aiBasicData['blood_group']) {
			$blood_group = $aiBasicData['blood_group'];
			$CQuery = DB::row("SELECT id FROM var_blood_group WHERE title LIKE '%$blood_group%'");
			if(isset($CQuery))
				$BasicData['blood_group'] = $CQuery['id'];
		}

		$BasicData['lang'] = '';
		if($aiBasicData['lang'] && is_string($aiBasicData['lang'])) {
			$lang = $aiBasicData['lang'];
			$CQuery = DB::row("SELECT id FROM var_language WHERE title LIKE '%$lang%'");
			if(isset($CQuery))
				$BasicData['lang'] = $CQuery['id'];
		}

		$BasicData['complexion'] = '';
		if($aiBasicData['complexion'] && is_string($aiBasicData['complexion'])) {
			$complexion = $aiBasicData['complexion'];
			$CQuery = DB::row("SELECT id FROM var_complexion WHERE title LIKE '%$complexion%'");
			if(isset($CQuery))
				$BasicData['complexion'] = $CQuery['id'];
		}

		return $BasicData;
	}

	function getAiData($pdfAiData) {
		$aiResult = json_decode($pdfAiData, true);
		$professionList = $relativeList = $educationList = $AddressData = $basicData = [];
		if(isset($aiResult) && is_array($aiResult)) {

	        if(isset($aiResult['profession']) && is_array($aiResult['profession'])) 
	            $professionList = $this->professionList($aiResult['profession']);

	        if(isset($aiResult['relatives']) && is_array($aiResult['relatives']))
	            $relativeList = $this->relativeList($aiResult['relatives']);

	        if(isset($aiResult['education']) && is_array($aiResult['education']))
	            $educationList = $this->educationList($aiResult['education']);

	        // ADDRESS                
	        $aiAddData = [
	            'currentCountry'    =>  sanitizeAiData($aiResult['currentCountry']),
	            'currentCity'       =>  sanitizeAiData($aiResult['currentCity']),
	            'currentState'      =>  sanitizeAiData($aiResult['currentState']),
	            'currentStreet'     =>  sanitizeAiData($aiResult['currentStreet']),
	            'permanentCountry'  =>  sanitizeAiData($aiResult['permanentCountry']),
	            'permanentCity'     =>  sanitizeAiData($aiResult['permanentCity']),
	            'permanentState'    =>  sanitizeAiData($aiResult['permanentState']),
	            'permanentStreet'   =>  sanitizeAiData($aiResult['permanentStreet']),
	            'gender' 			=> sanitizeAiData($aiResult['gender']),
	            'dateOfBirth' 		=> sanitizeAiData($aiResult['dateOfBirth']),
	        ];
	        $AddressData = $this->addressData($aiAddData);

	        // About
	        $about_me = sanitizeAiData($aiResult['about']);
	        $interested_in = sanitizeAiData($aiResult['interested']);

	        // BASIC DATA
	        $aiBasicData = [
	            'marital_status' => sanitizeAiData($aiResult['maritalStatus']),
	            'religion' => sanitizeAiData($aiResult['religion']),
	            'blood_group' => sanitizeAiData($aiResult['bloodGroup']),
	            'lang' => sanitizeAiData($aiResult['language']),
	            'complexion' => sanitizeAiData($aiResult['complexion']),
	            'about_me' => sanitizeAiData($aiResult['about']),
	            'interested_in' => sanitizeAiData($aiResult['interested']),
	        ];
	        $basicData = $this->basicData($aiBasicData);
	    }

        return [
            'professionList' => $professionList,
            'relativeList' => $relativeList,
            'educationList' => $educationList,
            'AddressData' => $AddressData,
            'basicData' => $basicData,
        ];
	}
	function saveAiData($user_id, $pdfAiData) {
		$data = $this->getAiData($pdfAiData);

		// UPDATE PROFESSION
		if (sizeof($data['professionList'])) {

			// DELETE IF EXISTS
			DB::delete('user_profession', '`user_id` =' . to_sql($user_id));

			foreach($data['professionList'] AS $profession) {
				if(isset($profession['profession_type']) && $profession['profession_type']) {

					$profession['user_id'] = $user_id;
					$profession['added_on'] = date("Y-m-d H:i:s");
					DB::insert('user_profession', $profession, '`user_id` = ' . to_sql($user_id));
				}
			}
		}

		// UPDATE RELATIVES
		if (sizeof($data['relativeList'])) {

			// DELETE IF EXISTS
			DB::delete('user_relatives', '`user_id` =' . to_sql($user_id));

			foreach($data['relativeList'] AS $relatives) {
				if(isset($relatives['relative_name']) && $relatives['relative_name'] && isset($relatives['relation']) && $relatives['relation']) {

					$relatives['user_id'] = $user_id;
					$relatives['added_on'] = date("Y-m-d H:i:s");
					DB::insert('user_relatives', $relatives, '`user_id` = ' . to_sql($user_id));
				}
			}
		}

		// UPDATE EDUCATION
		if (sizeof($data['educationList'])) {

			// DELETE IF EXISTS
			DB::delete('user_education', '`user_id` =' . to_sql($user_id));
			
			foreach($data['educationList'] AS $education) {
				if(isset($education['education_level_id']) && (isset($education['degree_id']) || isset($education['degree_title']))) {

					$education['user_id'] = $user_id;
					$education['added_on'] = date("Y-m-d H:i:s");
					DB::insert('user_education', $education, '`user_id` = ' . to_sql($user_id));
				}
			}
		}

		// UPDATE ADDRESS DATA
		$AddressData = $data['AddressData'];		
		
		// EMPTY SANITIZATION
		foreach($AddressData as $key => $bData) {
			if(empty($bData))
				unset($AddressData[$key]);
		}

		if($AddressData)
			DB::update('user', $AddressData, '`user_id` = ' . to_sql($user_id));

		// UPDATE ADDRESS DATA END

		// UPDATE BASIC DATA
		$basicData = $data['basicData'];

		$userInfo = [];
		if(isset($basicData['about_me']) && $basicData['about_me'])
			$userInfo['about_me'] = $basicData['about_me'];
		if(isset($basicData['interested_in']) && $basicData['interested_in'])
			$userInfo['interested_in'] = $basicData['interested_in'];
		
		if(isset($basicData['marital_status']) && $basicData['marital_status'])
			$userInfo['marital_status'] = $basicData['marital_status'];
		if(isset($basicData['blood_group']) && $basicData['blood_group'])
			$userInfo['blood_group'] = $basicData['blood_group'];
		if(isset($basicData['religion']) && $basicData['religion'])
			$userInfo['religion'] = $basicData['religion'];
		if(isset($basicData['complexion']) && $basicData['complexion'])
			$userInfo['complexion'] = $basicData['complexion'];

		if($userInfo)
			DB::update('userinfo', $userInfo, '`user_id` = ' . to_sql($user_id));
		// UPDATE BASIC DATA END

		return true;
	}
}
