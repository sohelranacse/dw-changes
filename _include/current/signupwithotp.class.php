<?php

class SignUpWithOTP extends CHtmlBlock
{
	var $message = '';

	function action()
	{
        $phone_number = get_session('j_phone');
        $by_phone = get_session('j_by_phone');
        $j_by_phone_varified = get_session('j_by_phone_varified');

        if((isset($_POST['send_otp']) && $_POST['send_otp'] == 1) || (isset($_POST['resend']) && $_POST['resend'] == 1)){

            if($phone_number && $by_phone) {
                $phone_number = preg_replace('/[^0-9]/', '', $phone_number);

                $this->sendOTP($phone_number);

            }

        } elseif(isset($_POST['otp_pin']) && is_numeric($_POST['otp_pin']) && strlen($_POST['otp_pin']) === 4) {

            $otp_pin = trim(get_param('otp_pin' , false));

            if($phone_number && $by_phone) {
                $phone_number = preg_replace('/[^0-9]/', '', $phone_number);

                $sql = 'SELECT * FROM user_temp WHERE otp_pin = ' . to_sql($otp_pin).' AND phone_number = '.to_sql($phone_number);
                $userInfo=DB::row($sql);

                if(isset($userInfo)) {
                    $id = $userInfo['id'];

                    // update OTP
                    DB::execute("UPDATE user_temp SET otp_pin=null, otp_sent_time=null, is_verified=1 WHERE id = $id");

                    // login session - for main version
                    set_session('j_by_phone_varified', 1);

                    // insert user for mobile
                    if(Common::isMobile()) {
                        $uid = User::add();
                        
                        // upload photo
                        if(get_session('j_temp_photo')) {
                            uploadphoto($uid, '', '', 1, get_session('j_temp_photo'));
                            @unlink(get_session('j_temp_photo'));
                        }
                    }

                    return $this->message = json_encode([
                        'status'         => true,
                        'redirect_url'   => ($userInfo['signup_as'] === 'matchmaker') ? 'group_users' : 'search_results',
                    ]);
                } else {
                    return $this->message = json_encode([
                        'status'    => false,
                        'msg'       => l('otp_is_not_valid')
                    ]);
                }
            }

        }

    }

    function sendOTP($phone_number) { 

        $otp_pin = random_int(1000, 9999);
        $m_messege = "DeshiWedding.com: Your One-Time PIN is {$otp_pin}. It will expire in 15 minutes.";


        $sql = 'SELECT * FROM user_temp WHERE phone_number = ' . to_sql($phone_number) . ' ORDER BY added_on DESC';
        $userInfo=DB::row($sql);

        if(isset($userInfo)) { // update

            $otp_sent_time = $userInfo['otp_sent_time'] ? $userInfo['otp_sent_time'] : '-1 day';
            $otp_resend_datetime = new DateTime($otp_sent_time);

            $current_datetime = new DateTime();
            $time_difference = $current_datetime->diff($otp_resend_datetime);

            if ($time_difference->y == 0 && $time_difference->m == 0 && $time_difference->d == 0 && $time_difference->h == 0 && $time_difference->i < 5) {
                $remaining_time = $otp_resend_datetime->modify('+5 minutes')->setTimezone(new DateTimeZone('Asia/Dhaka'))->format('h:i:s A');
                return $this->message = json_encode([
                    'status'    => false,
                    'msg'       => 'You can send OTP after ' . $remaining_time
                ]);
            } else { // update

                $id = $userInfo['id'];

                // SEND MESSAGE
                sendsms($phone_number, $m_messege);

                $ip_address = $_SERVER['REMOTE_ADDR'];

                DB::execute("UPDATE user_temp SET otp_pin=$otp_pin, otp_sent_time=NOW(), ip_address='$ip_address' WHERE id = $id");

                return $this->message = json_encode([
                    'status'    => true,
                    'msg'       => l('we_have_sent')
                ]);
            }


        } else { // insert

            return $this->message = json_encode([
                'status'    => true,
                'msg'       => 'Something went wrong'
            ]);
        }
    }

}
