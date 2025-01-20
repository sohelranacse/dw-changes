<?php
/*
	Created by Sohel Rana
	Dated: 17 October, 2023
*/
$g['mobile_redirect_off'] = true;
include("./_include/core/main_start.php");

checkByAuth();

require_once '_include/current/CustomAi.php';

class Input {
    private $data = [];

    public function __construct() {
        $this->data = $_POST;
    }

    public function post($key, $default = null) {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }
}
class Controller {
    protected $input;

    public function __construct() {
        $this->input = new Input();
    }
}


class ProfileAjax extends Controller {

	function action() {
		global $g, $g_user;

		if(isset($_POST)) {
			$cmd = $this->input->post('cmd');
			$mobile = $this->input->post('mobile') ? $this->input->post('mobile') : '';
			$htmlPath = $mobile ? $g['tmpl']['dir_tmpl_mobile'] : $g['tmpl']['dir_tmpl_main'];
			$cancel = l('cancel');
			$save = l('save');

			switch ($cmd) {

				case 'profile_info':
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    else
				    	$g_user = User::getInfoFull($g_user['user_id']);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();


				    $completed = _completed($g_user);

				    $data = [
				    	'profile_completed' => $completed,
				    ];
				    echo json_encode(['status' => true, 'data' => $data]);
					break;

				case "get_address_field":

					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    $stateList = $cityList = $permanent_stateList = $permanent_cityList = [];

				    $countryList = DB::all('SELECT `country_id`, `country_title` FROM `geo_country` WHERE (hidden = 0 OR country_id = 0) ORDER BY `first` DESC, `country_title` ASC');

				    if(isset($g_user['pdfAiData']) && !isset($g_user['current_street']) && !isset($g_user['permanent_street']) && !isset($g_user['country_id']) && !isset($g_user['permanent_country_id']) && !isset($g_user['current_state_id']) && !isset($g_user['permanent_state_id'])) {
				    	$aiResult = json_decode($g_user['pdfAiData']);
				    	$aiData = [
				            'currentCountry'    =>  sanitizeAiData($aiResult->currentCountry),
				            'currentCity'       =>  sanitizeAiData($aiResult->currentCity),
				            'currentState'      =>  sanitizeAiData($aiResult->currentState),
				            'currentStreet'     =>  sanitizeAiData($aiResult->currentStreet),
				            'permanentCountry'  =>  sanitizeAiData($aiResult->permanentCountry),
				            'permanentCity'     =>  sanitizeAiData($aiResult->permanentCity),
				            'permanentState'    =>  sanitizeAiData($aiResult->permanentState),
				            'permanentStreet'   =>  sanitizeAiData($aiResult->permanentStreet),
				        ];
				        $customAi = new CustomAi();
				    	$AddressData = $customAi->addressData($aiData);

				    	if($AddressData['current_street'])
							$g_user['current_street'] = $aiData['currentStreet'];
						if($AddressData['permanent_street'])
							$g_user['permanent_street'] = $aiData['permanentStreet'];

						if($AddressData['country_id']) {
							$g_user['country_id'] = $AddressData['country_id'];
							$stateList = DB::all('SELECT state_id, state_title FROM geo_state WHERE hidden = 0 AND country_id = '.to_sql($AddressData['country_id'], 'Number').' ORDER BY state_title');
						}
						if($AddressData['state_id']) {
							$g_user['state_id'] = $AddressData['state_id'];
							$cityList = DB::all('SELECT city_id, city_title FROM geo_city WHERE hidden = 0 AND state_id = '.to_sql($AddressData['state_id'], 'Number').' ORDER BY city_title');
						}
						if($AddressData['state_id'] && $AddressData['city_id'])
							$g_user['city_id'] = $AddressData['city_id'];

						// PERMANENT
						if($AddressData['permanent_country_id']) {
							$g_user['permanent_country_id'] = $AddressData['permanent_country_id'];
							$permanent_stateList = DB::all('SELECT state_id, state_title FROM geo_state WHERE hidden = 0 AND country_id = '.to_sql($AddressData['permanent_country_id'], 'Number').' ORDER BY state_title');
						}
						if($AddressData['permanent_state_id']) {
							$g_user['permanent_state_id'] = $AddressData['permanent_state_id'];
							$permanent_cityList = DB::all('SELECT city_id, city_title FROM geo_city WHERE hidden = 0 AND state_id = '.to_sql($AddressData['permanent_state_id'], 'Number').' ORDER BY city_title');
						}
						if($AddressData['permanent_state_id'] && $AddressData['permanent_city_id'])
							$g_user['permanent_city_id'] = $AddressData['permanent_city_id'];

				    } else {
				    	
				    	// current
						if($g_user['country_id'])
							$stateList = DB::all('SELECT state_id, state_title FROM geo_state WHERE hidden = 0 AND country_id = '.to_sql($g_user['country_id'], 'Number').' ORDER BY state_title');

						if($g_user['state_id'])
							$cityList = DB::all('SELECT city_id, city_title FROM geo_city WHERE hidden = 0 AND state_id = '.to_sql($g_user['state_id'], 'Number').' ORDER BY city_title');

						// permanent address
						if($g_user['permanent_country_id'])
							$permanent_stateList = DB::all('SELECT state_id, state_title FROM geo_state WHERE hidden = 0 AND country_id = '.to_sql($g_user['permanent_country_id'], 'Number').' ORDER BY state_title');

						if($g_user['permanent_state_id'])
							$permanent_cityList = DB::all('SELECT city_id, city_title FROM geo_city WHERE hidden = 0 AND state_id = '.to_sql($g_user['permanent_state_id'], 'Number').' ORDER BY city_title');	
				    }

					$country = l('country');
					$state = l('state');
					$city = l('city');
					$street = l('street');
					$current_address = l('current_address');
					$permanent_address = l('permanent_address');
					$title = l('address');

					ob_start();
					include $htmlPath.'profile/get_address_field.php';
					$data = ob_get_clean();

					echo json_encode(['status' => true,'data' => $data]);
					
					break;

				case 'get_state':
					$country_id = $this->input->post('country_id');
					$stateList = DB::all('SELECT state_id, state_title FROM geo_state WHERE hidden = 0 AND country_id = '.to_sql($country_id, 'Number').' ORDER BY state_title');

					$data = '<option value="">'.l('select_combo').'</option>';
					if(sizeof($stateList))
						foreach($stateList as $value) {
							$data .= '<option value="'.$value['state_id'].'">'.$value['state_title'].'</option>';
						}
					echo $data;
					break;

				case 'get_city':
					$state_id = $this->input->post('state_id');
					$cityList = DB::all('SELECT city_id, city_title FROM geo_city WHERE hidden = 0 AND state_id = '.to_sql($state_id, 'Number').' ORDER BY city_title');

					$data = '<option value="">'.l('select_combo').'</option>';
					if(sizeof($cityList))
						foreach($cityList as $value) {
							$data .= '<option value="'.$value['city_id'].'">'.$value['city_title'].'</option>';
						}
					echo $data;
					break;

				case 'update_address':
					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    $current_street = trim(Common::filterProfileText($this->input->post('current_street')));
				    $country_id = trim(Common::filterProfileText($this->input->post('country_id_current')));
				    $state_id = trim(Common::filterProfileText($this->input->post('state_id_current')));
				    $city_id = trim(Common::filterProfileText($this->input->post('city_id_current')));
				    $permanent_street = trim(Common::filterProfileText($this->input->post('permanent_street')));
				    $permanent_country_id = trim(Common::filterProfileText($this->input->post('country_id_permanent')));
				    $permanent_state_id = trim(Common::filterProfileText($this->input->post('state_id_permanent')));
				    $permanent_city_id = trim(Common::filterProfileText($this->input->post('city_id_permanent')));

				    $data = [
				    	'current_street'	=>	$current_street ? $current_street : '',
				    	'country_id'	=>	$country_id ? $country_id : '',
				    	'state_id'	=>	$state_id ? $state_id : '',
				    	'city_id'	=>	$city_id ? $city_id : '',

				    	'country'	=>	$country_id ? Common::getLocationTitle('country', $country_id) : '',
				    	'state'	=>	$state_id ? Common::getLocationTitle('state', $state_id) : '',
				    	'city'	=>	$city_id ? Common::getLocationTitle('city', $city_id) : '',

				    	'permanent_street'	=>	$permanent_street ? $permanent_street : '',
				    	'permanent_country_id'	=>	$permanent_country_id ? $permanent_country_id : '',
				    	'permanent_state_id'	=>	$permanent_state_id ? $permanent_state_id : '',
				    	'permanent_city_id'	=>	$permanent_city_id ? $permanent_city_id : '',
				    ];
				    DB::update('user', $data, '`user_id` = ' . to_sql($g_user['user_id']));

				    $user_info = DB::row('
				    	SELECT a.current_street, a.permanent_street,
				    	(SELECT country_title FROM geo_country WHERE country_id = a.country_id) AS current_country,
				    	(SELECT state_title FROM geo_state WHERE state_id = a.state_id) AS current_state,
				    	(SELECT city_title FROM geo_city WHERE city_id = a.city_id) AS current_city,
				    	(SELECT country_title FROM geo_country WHERE country_id = a.permanent_country_id) AS permanent_country,
				    	(SELECT state_title FROM geo_state WHERE state_id = a.permanent_state_id) AS permanent_state,
				    	(SELECT city_title FROM geo_city WHERE city_id = a.permanent_city_id) AS permanent_city
				    	FROM user a
				    	WHERE a.user_id = '.to_sql($g_user['user_id'])
				    );

				    $result['msg'] = "success";
				    $result['current_address'] = $result['current_address_title'] = $result['permanent_address'] = '';

				    $current_address = implode(', ', array_filter([$user_info['current_street'], $user_info['current_city'], $user_info['current_state'], $user_info['current_country']]));
				    $current_address_title = implode(', ', array_filter([$user_info['current_city'], $user_info['current_country']]));
				    $permanent_address = implode(', ', array_filter([$user_info['permanent_street'], $user_info['permanent_city'], $user_info['permanent_state'], $user_info['permanent_country']]));

				    if($current_address)
				    	$result['current_address'] = $current_address;
				    if($current_address_title)
				    	$result['current_address_title'] = $current_address_title;

				    $result['cityTitle'] = $user_info['current_city'];

				    if($permanent_address)
				    	$result['permanent_address'] = $permanent_address;
	                echo json_encode($result);
					break;
				case "loadFavoriteAddressEdit":

					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    $stateList = $cityList = $permanent_stateList = $permanent_cityList = [];

					$countryList = DB::all('SELECT `country_id`, `country_title` FROM `geo_country` WHERE (hidden = 0 OR country_id = 0) ORDER BY `first` DESC, `country_title` ASC');

					// favorite
					if($g_user['favorite_country_id'])
						$stateList = DB::all('SELECT state_id, state_title FROM geo_state WHERE hidden = 0 AND country_id = '.to_sql($g_user['favorite_country_id'], 'Number').' ORDER BY state_title');

					if($g_user['favorite_state_id'])
						$cityList = DB::all('SELECT city_id, city_title FROM geo_city WHERE hidden = 0 AND state_id = '.to_sql($g_user['favorite_state_id'], 'Number').' ORDER BY city_title');

					// unfavorite
					if($g_user['unfavorite_country_id'])
						$permanent_stateList = DB::all('SELECT state_id, state_title FROM geo_state WHERE hidden = 0 AND country_id = '.to_sql($g_user['unfavorite_country_id'], 'Number').' ORDER BY state_title');

					if($g_user['unfavorite_state_id'])
						$permanent_cityList = DB::all('SELECT city_id, city_title FROM geo_city WHERE hidden = 0 AND state_id = '.to_sql($g_user['unfavorite_state_id'], 'Number').' ORDER BY city_title');

					$country = l('country');
					$state = l('state');
					$city = l('city');
					$favored_location = l('favored_location');
					$unfavored_location = l('unfavored_location');
					$title = l('location_preference');

					ob_start();
					include $htmlPath.'profile/loadFavoriteAddressEdit.php';
					$data = ob_get_clean();
					echo json_encode(['status' => true,'data' => $data]);
					break;

				case 'update_fevorite_unfevorite_region':
					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    $favorite_country_id = trim(Common::filterProfileText($this->input->post('favorite_country_id')));
				    $favorite_state_id = trim(Common::filterProfileText($this->input->post('favorite_state_id')));
				    $favorite_city_id = trim(Common::filterProfileText($this->input->post('favorite_city_id')));
				    $unfavorite_country_id = trim(Common::filterProfileText($this->input->post('unfavorite_country_id')));
				    $unfavorite_state_id = trim(Common::filterProfileText($this->input->post('unfavorite_state_id')));
				    $unfavorite_city_id = trim(Common::filterProfileText($this->input->post('unfavorite_city_id')));

				    $data = [
				    	'favorite_country_id'	=>	$favorite_country_id,
				    	'favorite_state_id'	=>	$favorite_state_id,
				    	'favorite_city_id'	=>	$favorite_city_id,
				    	'unfavorite_country_id'	=>	$unfavorite_country_id,
				    	'unfavorite_state_id'	=>	$unfavorite_state_id,
				    	'unfavorite_city_id'	=>	$unfavorite_city_id,
				    ];
				    DB::update('user', $data, '`user_id` = ' . to_sql($g_user['user_id']));

				    $user_info = DB::row('
				    	SELECT 
				    	(SELECT country_title FROM geo_country WHERE country_id = a.favorite_country_id) AS favorite_country,
				    	(SELECT state_title FROM geo_state WHERE state_id = a.favorite_state_id) AS favorite_state,
				    	(SELECT city_title FROM geo_city WHERE city_id = a.favorite_city_id) AS favorite_city,
				    	(SELECT country_title FROM geo_country WHERE country_id = a.unfavorite_country_id) AS unfavorite_country,
				    	(SELECT state_title FROM geo_state WHERE state_id = a.unfavorite_state_id) AS unfavorite_state,
				    	(SELECT city_title FROM geo_city WHERE city_id = a.unfavorite_city_id) AS unfavorite_city
				    	FROM user a
				    	WHERE a.user_id = '.to_sql($g_user['user_id'])
				    );
				    $result['msg'] = "success";
				    $favored = $unfavored = [];

				    // favored
				    if($user_info['favorite_city'])
				    	$favored['favorite_city'] = $user_info['favorite_city'];
				    if($user_info['favorite_state'])
				    	$favored['favorite_state'] = $user_info['favorite_state'];
				    if($user_info['favorite_country'])
				    	$favored['favorite_country'] = $user_info['favorite_country'];

				    // unfavored
				    if($user_info['unfavorite_city'])
				    	$unfavored['unfavorite_city'] = $user_info['unfavorite_city'];
				    if($user_info['unfavorite_state'])
				    	$unfavored['unfavorite_state'] = $user_info['unfavorite_state'];
				    if($user_info['unfavorite_country'])
				    	$unfavored['unfavorite_country'] = $user_info['unfavorite_country'];

				    $result['favorite_address'] = implode(", ", $favored);
				    $result['unfavorite_address'] = implode(", ", $unfavored);
	                echo json_encode($result);
					break;

				case 'loadEducationEdit':

					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();


				    $level_of_edu_list = level_of_education();
				    $educationList = DB::all("
				    	SELECT a.*, b.degree_name
				    	FROM user_education a
				    	LEFT JOIN user_education_degree b ON (a.degree_id = b.degree_id)
				    	WHERE a.user_id = {$g_user['user_id']}
				    	ORDER BY a.added_on
				    ");
				    if(!$educationList && isset($g_user['pdfAiData'])) {
				    	$aiResult = json_decode($g_user['pdfAiData'], true);
				    	$customAi = new CustomAi();

				    	if(isset($aiResult['education']) && sizeof($aiResult['education'])) 
	            			$educationList = $customAi->educationList($aiResult['education']);
				    }

					$level_of_education = l('level_of_education');
					$degree_title = l('degree_title');
					$subject_title = l('subject_title');
					$institute_name = l('institute_name');
					$results = l('results');
					$passing_year = l('passing_year');
					$address = l('address');
					$title = l('education');

					ob_start();
					include $htmlPath.'profile/loadEducationEdit.php';
					$data = ob_get_clean();
					echo json_encode(['status' => true,'data' => $data]);
					break;

				case 'get_degree_como':
					$education_level_id = $this->input->post('education_level_id');
					$data = DB::all("SELECT degree_id, degree_name FROM user_education_degree WHERE education_level_id = $education_level_id");
					echo json_encode(['status' => true,'data' => $data]);
					break;

				case 'update_education':
					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    $education_level = $this->input->post('education_level_id');
				    $degree_id = $this->input->post('degree_id');
				    $degree_title = $this->input->post('degree_title');
				    $subject_title = $this->input->post('subject_title');

				    $school_name = $this->input->post('school_name');
				    $address = $this->input->post('address');
				    $results = $this->input->post('results');
				    $passing_year = $this->input->post('passing_year');

				    $i = 0;
				    DB::delete('user_education', '`user_id` =' . to_sql($g_user['user_id']));
				    if($this->input->post('degree_title') && sizeof($education_level)) {
					    foreach($education_level as $education_level_id) {
					    	// if($i == 0) continue;

					    	$data = [
				    			'education_level_id'	=>	trim(Common::filterProfileText($education_level_id)),
				    			'subject_title'	=>	trim(Common::filterProfileText($subject_title[$i])),
				    			'degree_title'		=>	(trim(Common::filterProfileText($degree_title[$i])) && (!isset($degree_id[$i]) || (isset($degree_id[$i]) && $degree_id[$i] == 0))) ? trim(Common::filterProfileText($degree_title[$i])) : '',

				    			'school_name'	=>	trim(Common::filterProfileText($school_name[$i])),
				    			'address'		=>	trim(Common::filterProfileText($address[$i])) ? trim(Common::filterProfileText($address[$i])) : '',
				    			'results'		=>	trim(Common::filterProfileText($results[$i])) ? trim(Common::filterProfileText($results[$i])) : '',
				    			'added_on'		=>	date("Y-m-d H:i:s"),
				    			'user_id'		=>	$g_user['user_id'],
				    		];

				    		if(isset($degree_id[$i]))
				    			$data['degree_id'] = trim(Common::filterProfileText($degree_id[$i]));
				    		if(isset($passing_year[$i]) && $passing_year[$i])
				    			$data['passing_year'] = trim(Common::filterProfileText($passing_year[$i]));

				    		DB::insert('user_education', $data);

						    $i++;
					    }	
					}					    

					$educationList = DB::all("
				    	SELECT a.*, b.degree_name
				    	FROM user_education a
				    	LEFT JOIN user_education_degree b ON (a.degree_id = b.degree_id)
				    	WHERE a.user_id = {$g_user['user_id']}
				    	ORDER BY a.added_on
				    ");
				    echo json_encode($educationList);
					break;

				case 'loadProfessionEdit':

					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    $professionList = DB::all("SELECT * FROM `user_profession` WHERE user_id = {$g_user['user_id']} ORDER BY added_on");

				    if(!$professionList && isset($g_user['pdfAiData'])) {
				    	$aiResult = json_decode($g_user['pdfAiData'], true);
				    	$customAi = new CustomAi();

				    	if (isset($aiResult['profession']) && is_array($aiResult['profession']) && sizeof($aiResult['profession']))
	            			$professionList = $customAi->professionList($aiResult['profession']);
				    }

					$profession_type = l('profession_type');
					$position = l('position');
					$address = l('address');
					$company = l('company');
					$title = l('profession');

					ob_start();
					include $htmlPath.'profile/loadProfessionEdit.php';
					$data = ob_get_clean();
					echo json_encode(['status' => true,'data' => $data]);
					break;

				case 'update_profession':
					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    $profession = $this->input->post('profession_type');
				    $position = $this->input->post('position');
				    $address = $this->input->post('address');
				    $company = $this->input->post('company');
				    
				    $i = 0;
				    DB::delete('user_profession', '`user_id` =' . to_sql($g_user['user_id']));
				    if($this->input->post('profession_type') && sizeof($profession)) {
					    foreach($profession as $profession_type) {

					    	$data = [
				    			'profession_type'	=>	trim(Common::filterProfileText($profession_type)),
				    			'position'		=>	trim(Common::filterProfileText($position[$i])),
				    			'company'		=>	trim(Common::filterProfileText($company[$i])),
				    			'address'		=>	trim(Common::filterProfileText($address[$i])) ? trim(Common::filterProfileText($address[$i])) : '',
				    			'added_on'		=>	date("Y-m-d H:i:s"),
				    			'user_id'		=>	$g_user['user_id'],
				    		];
				    		
				    		DB::insert('user_profession', $data);

						    $i++;
					    }	
					}					    

					$professionList = DB::all("
				    	SELECT a.*
				    	FROM user_profession a
				    	WHERE a.user_id = {$g_user['user_id']}
				    	ORDER BY a.added_on
				    ");
				    echo json_encode($professionList);
					break;

				case 'loadRelativesEdit':

					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    $maritalStatus = DB::all("SELECT * FROM `var_marital_status` ORDER BY title");
				    $relativeList = DB::all("SELECT * FROM `user_relatives` WHERE user_id = {$g_user['user_id']} ORDER BY added_on");

				    if(!$relativeList && isset($g_user['pdfAiData'])) {
				    	$aiResult = json_decode($g_user['pdfAiData'], true);
				    	$customAi = new CustomAi();

				    	if(isset($aiResult['relatives']) && sizeof($aiResult['relatives']))
	            			$relativeList = $customAi->relativeList($aiResult['relatives']);
				    }

					$highest_degree = l('highest_degree');
					$marital_status = l('marital_status');
					$name = l('name');
					$relation = l('relation');
					$address = l('address');
					$profession_type = l('profession_type');
					$position = l('position');
					$company = l('company');
					$title = l('relatives');

					ob_start();
					include $htmlPath.'profile/loadRelativesEdit.php';
					$data = ob_get_clean();
					echo json_encode(['status' => true,'data' => $data]);
					break;

				case 'update_relatives':
					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    $relatives = $this->input->post('relative_name');
				    $relation = $this->input->post('relation');
				    $marital_status = $this->input->post('marital_status');
				    $address = $this->input->post('address');
				    $profession_type = $this->input->post('profession_type');
				    $position = $this->input->post('position');
				    $company = $this->input->post('company');
				    $degree_title = $this->input->post('degree_title');

				    $i = 0;
				    DB::delete('user_relatives', '`user_id` =' . to_sql($g_user['user_id']));
				    if($this->input->post('relative_name') && sizeof($relatives)) {
					    foreach($relatives as $relative_name) {

					    	$data = [
				    			'relative_name'	=>	trim(Common::filterProfileText($relative_name)),
				    			'relation'		=>	trim(Common::filterProfileText($relation[$i])),
				    			'added_on'		=>	date("Y-m-d H:i:s"),
				    			'user_id'		=>	$g_user['user_id'],
				    		];

				    		// optional
				    		if($marital_status[$i]) $data['marital_status'] = trim(Common::filterProfileText($marital_status[$i]));
				    		if($address[$i]) $data['address'] = trim(Common::filterProfileText($address[$i]));
				    		if($profession_type[$i]) $data['profession_type'] = trim(Common::filterProfileText($profession_type[$i]));
				    		if($position[$i]) $data['position'] = trim(Common::filterProfileText($position[$i]));
				    		if($company[$i]) $data['company'] = trim(Common::filterProfileText($company[$i]));
				    		if($degree_title[$i]) $data['degree_title'] = trim(Common::filterProfileText($degree_title[$i]));

				    		DB::insert('user_relatives', $data);

						    $i++;
					    }	
					}					    

					$relativeList = DB::all("
				    	SELECT a.*, c.title AS marital_title
				    	FROM user_relatives a
				    	LEFT JOIN var_marital_status c ON (a.marital_status = c.id)
				    	WHERE a.user_id = {$g_user['user_id']}
				    	ORDER BY a.added_on
				    ");
				    echo json_encode($relativeList);
					break;

				case 'loadPostedByEdit':

					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

					$name = l('name');
					$phone_number = l('phone_number');
					$address = l('address');
					$title = l('posted_by');

					ob_start();
					include $htmlPath.'profile/loadPostedByEdit.php';
					$data = ob_get_clean();
					echo json_encode(['status' => true,'data' => $data]);
					break;

				case 'update_posted_by':
					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    $poster_name = trim(Common::filterProfileText($this->input->post('poster_name')));
				    $poster_phone = trim(Common::filterProfileText($this->input->post('poster_phone')));
				    $poster_address = trim(Common::filterProfileText($this->input->post('poster_address')));
				    $title = $this->input->post('posted_by');

				    $data = [
				    	'poster_name' => $poster_name ? $poster_name : '',
				    	'poster_phone' => $poster_phone ? $poster_phone : '',
				    	'poster_address' => $poster_address ? $poster_address : ''
				    ];
				    DB::update('user', $data, '`user_id` = ' . to_sql($g_user['user_id']));

				    echo json_encode($data);
					break;				

				

				case 'loadAdditionalInformationEdit':

					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    $title = l('additional_information');

				    $additional_info = $g_user['additional_info'];

					ob_start();
					include $htmlPath.'profile/loadAdditionalInformationEdit.php';
					$data = ob_get_clean();
					echo json_encode(['status' => true,'data' => $data]);
					break;

				
				case 'update_additional_information':
					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    $additional_info = trim(Common::filterProfileText($this->input->post('additional_info')));

				    $data = [
				    	'additional_info' => $additional_info
				    ];
				    DB::update('user', $data, '`user_id` = ' . to_sql($g_user['user_id']));

				    $result = [
				    	'success'			=>	'success',
				    	'additional_info'	=>	$additional_info
				    ];		
				    echo json_encode($result);
					break;

				case 'loadVerifyPhoneNumber':

					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();
				    
					$title = l('verify_phone_number');

					ob_start();
					include $htmlPath.'profile/loadVerifyPhoneNumber.php';
					$data = ob_get_clean();
					echo json_encode(['status' => true,'data' => $data]);
					break;
				case 'verify_phone_number':
					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    $verification_code = trim(Common::filterProfileText($this->input->post('verification_code')));
				    if($verification_code == $g_user['verification_code']) {

					    DB::execute('UPDATE user SET is_verified="Y", verification_code=null, vcode_resend_time=null WHERE `user_id` = ' . to_sql($g_user['user_id']));

					    $result = [
					    	'msg' => 'success'
					    ];

					    $g_user = User::getInfoFull($g_user['user_id']);
					    $result['status'] = "<span class='verify_blue'>".l('verified')."</span>";
				    }
				    else {
				    	$result = [
					    	'msg' => 'error'
					    ];
				    }				    			    		    

				    echo json_encode($result);
					break;
				case 'changePhoneNumber':

					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    $phone_number = $this->input->post('phone_number');

				    if (strlen($phone_number) === 14 && $g_user['enabled_OTP_login'] == 0) {

						$data = [
					    	'phone' 		=> validate_phone_number($phone_number),
					    	'is_verified' 	=> 'N',
					    ];
					    DB::update('user', $data, '`user_id` = ' . to_sql($g_user['user_id']));

					    $result = [
					    	'msg' => 'success'
					    ];
					} else {
						$result = [
					    	'msg' => 'Phone number is not valid!'
					    ];
					}

					echo json_encode($result);
					break;
				case 'resendVCode':

					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    $vcode_resend_time = $g_user['vcode_resend_time'] ? $g_user['vcode_resend_time'] : '-1 day';
				    $vcode_resend_datetime = new DateTime($vcode_resend_time);

				    $current_datetime = new DateTime();
				    $time_difference = $current_datetime->diff($vcode_resend_datetime);

				    if ($time_difference->y == 0 && $time_difference->m == 0 && $time_difference->d == 0 && $time_difference->h == 0 && $time_difference->i < 5) {

				    	$remaining_time = $vcode_resend_datetime->modify('+5 minutes')->format('h:i:s A');

				    	$result = [
					    	'msg' => 'You can request a new verification code at ' . $remaining_time
					    ];
					} else {
						$verification_code = random_int(100000, 999999);					
						
						$phone_number = preg_replace('/[^0-9]/', '', $g_user['phone']);
						$phone_number = validate_phone_number($phone_number);
						
						$m_messege = "DeshiWedding.com: Your verification code is {$verification_code}. Use it to verify your mobile number. Do not share this code.";

						// SEND MESSAGE
						sendsms($phone_number, $m_messege);

						$data = [
					    	'verification_code' => $verification_code,
					    	'vcode_resend_time' => date("Y-m-d H:i:s")
					    ];
					    DB::update('user', $data, '`user_id` = ' . to_sql($g_user['user_id']));

					    $result = [
					    	'msg' => 'success'
					    ];
						
					}

					echo json_encode($result);
					break;

				case 'loadChangePhoneNumber':
					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    ob_start();
					include $htmlPath.'profile/loadChangePhoneNumber.php';
					$data = ob_get_clean();
					echo json_encode(['status' => true,'data' => $data]);
					break;

				case 'loadAddPhoneNumber':
					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    ob_start();
					include $htmlPath.'profile/loadAddPhoneNumber.php';
					$data = ob_get_clean();
					echo json_encode(['status' => true,'data' => $data]);
					break;

				case 'addPhoneNumber':

					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    $phone_number = $this->input->post('phone_number');

				    if (strlen($phone_number) === 14 && $g_user['enabled_OTP_login'] == 0) {

						$data = [
					    	'phone' 			=> validate_phone_number($phone_number),
					    ];
					    DB::update('user', $data, '`user_id` = ' . to_sql($g_user['user_id']));

					    $result = [
					    	'msg' => 'success'
					    ];

					} else {
						$result = [
					    	'msg' => 'Phone number is not valid!'
					    ];
					}

					echo json_encode($result);
					break;

				case 'loadYearsInExperience':
					// only for mobile

					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    $years_in_business = $g_user['years_in_business'];

					ob_start();
					include $htmlPath.'profile/loadYearsInExperience.php';
					$data = ob_get_clean();
					echo json_encode(['status' => true,'data' => $data]);
					break;

				case 'update_years_in_business_data':
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    $years_in_business = $this->input->post('years_in_business');

				    
			    	$data = [
				    	'years_in_business' => $years_in_business
				    ];
				    DB::update('user', $data, '`user_id` = ' . to_sql($g_user['user_id']));

				    $result = [
				    	'msg' => '<i class="fa fa-calendar"></i> '.$years_in_business
				    ];

					
					echo json_encode($result);
					break;

				case 'loadGhotokSummary':
					// only for mobile

					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    $title = l('ghotok_summary');

				    if($g_user['ghotok_summary'] == '')
				    	$ghotok_summary = l('ghotok_default_summary');
				    else
				    	$ghotok_summary = $g_user['ghotok_summary'];

					ob_start();
					include $htmlPath.'profile/loadGhotokSummary.php';
					$data = ob_get_clean();
					echo json_encode(['status' => true,'data' => $data]);
					break;

				case 'update_ghotok_summary_data':
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    $ghotok_summary = $this->input->post('ghotok_summary');
				    
			    	$data = [
				    	'ghotok_summary' => $ghotok_summary
				    ];
				    DB::update('user', $data, '`user_id` = ' . to_sql($g_user['user_id']));

				    if(isset($_POST['from_mobile'])) {
					    $result = [
					    	'msg' => ($ghotok_summary == '') ? l('ghotok_placeholder') : nl2br($ghotok_summary)
					    ];
				    } else {
					    $result = [
					    	'msg' => ($ghotok_summary == '') ? l('ghotok_placeholder') : $ghotok_summary
					    ];
					}

					
					echo json_encode($result);
					break;				

				case 'loadNidUploadPage':
					// user information
					$e_user_id = $this->input->post('e_user_id');
				    if($e_user_id)
				        $g_user = User::getInfoFull($e_user_id);
				    isset($g_user['user_id']) ? _isAuthID($g_user['user_id']) : _unAuthenticate();

				    ob_start();
					include $htmlPath.'profile/loadNidUploadPage.php';
					$data = ob_get_clean();
					echo json_encode(['status' => true,'data' => $data]);
					break;

				default:
        			echo "Nothing!";
			}

		}		
	}

}

$page = new ProfileAjax();
$page->action();