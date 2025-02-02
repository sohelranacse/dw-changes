<?php

class LoginWithOTP extends CHtmlBlock
{
	var $message = '';

	function action($redirect = false)
	{

        if(isset($_POST['phone_number']) && is_numeric($_POST['phone_number']) && strlen($_POST['phone_number']) === 14) {

            $phone_number = trim(get_param('phone_number' , false));
            $phone_number = preg_replace('/[^0-9]/', '', $phone_number);

            $this->sendOTP($phone_number);

        } elseif(isset($_POST['otp_pin']) && is_numeric($_POST['otp_pin']) && strlen($_POST['otp_pin']) === 4 && isset($_POST['otp_phone_number']) && is_numeric($_POST['otp_phone_number']) && strlen($_POST['otp_phone_number']) === 14) {

            $otp_pin = trim(get_param('otp_pin' , false));

            $phone_number = trim(get_param('otp_phone_number' , false));
            $phone_number = preg_replace('/[^0-9]/', '', $phone_number);

            $sql = 'SELECT *, IFNULL(TIMESTAMPDIFF(MINUTE, otp_blocked_time, NOW()), 0) AS minutes_difference FROM user WHERE enabled_OTP_login = 1 AND phone = '.to_sql($phone_number);
            $user=DB::row($sql);

            if(isset($user)) {
                $user_id = $user['user_id'];

                $otp_pin_db = $user['otp_pin'];
                $otp_retrying = $user['otp_retrying'];
                $otp_blocked_time = $user['otp_blocked_time'];
                $minutes_difference = $user['minutes_difference'];

                if($otp_pin_db === $otp_pin && ($minutes_difference > 59 || $minutes_difference == 0) && $otp_retrying < 3) {

                    // update OTP
                    DB::execute("UPDATE user SET otp_pin=null, otp_sent_time=null, otp_retrying=0, otp_blocked_time=null WHERE user_id = $user_id");

                    // login session
                    set_session('user_id', $user_id);
                    set_session('user_id_verify', $user_id);
                    set_cookie('c_user', '', -1);
                    set_cookie('c_password', '', -1);

                    return $this->message = json_encode([
                        'status'            => true,
                        'redirect_url'      => ($user['role'] === 'group_admin') ? 'group_users' : 'search_results',
                    ]);
                } else { // 3 times later do blocked
                    $otp_retried = $otp_retrying+1;

                    if($otp_retried > 2) {

                        // update OTP
                        if($otp_retrying == 2) // update 3
                            DB::execute("UPDATE user SET otp_retrying=otp_retrying+1, otp_blocked_time=NOW() WHERE user_id = $user_id");

                        $otp_msg = "";
                        if($minutes_difference == 0 && $otp_retrying == 2)
                            $otp_msg = l('please_try_again_after_one_hour');
                        elseif($minutes_difference < 60) {

                            // time
                            $otp_blocked_datetime = new DateTime($otp_blocked_time);
                            $blocked_remaining_time = $otp_blocked_datetime->modify('+60 minutes')->setTimezone(new DateTimeZone('Asia/Dhaka'))->format('h:i:s A');
                            // time end

                            $otp_msg = 'You are temporarily blocked. Please try again after ' . $blocked_remaining_time;
                        }
                        else {
                            $otp_msg = l('otp_is_not_valid');
                            DB::execute("UPDATE user SET otp_retrying=1, otp_blocked_time=NOW() WHERE user_id = $user_id");
                        }

                        return $this->message = json_encode([
                            'status'    => false,
                            'msg'       => $otp_msg
                        ]);

                    } else {

                        // update OTP
                        DB::execute("UPDATE user SET otp_retrying=otp_retrying+1 WHERE user_id = $user_id");

                        return $this->message = json_encode([
                            'status'            => false,
                            'msg'               => l('otp_is_not_valid')
                        ]);
                    }
                }
            }

        } elseif(isset($_POST['resend']) && is_numeric($_POST['resend']) && $_POST['resend'] == 1 && isset($_POST['otp_phone_number']) && is_numeric($_POST['otp_phone_number']) && strlen($_POST['otp_phone_number']) === 14) {

            $phone_number = trim(get_param('otp_phone_number' , false));
            $phone_number = preg_replace('/[^0-9]/', '', $phone_number);

            $this->sendOTP($phone_number);

        }

    }

    function sendOTP($phone_number) {        

        $sql = 'SELECT *, IFNULL(TIMESTAMPDIFF(MINUTE, otp_blocked_time, NOW()), 0) AS minutes_difference FROM user WHERE enabled_OTP_login = 1 AND phone = ' . to_sql($phone_number);
        $user=DB::row($sql);

        if(isset($user)) { // User found

            $otp_retrying = $user['otp_retrying'];
            $otp_blocked_time = $user['otp_blocked_time'];
            $minutes_difference = $user['minutes_difference'];

            if($user['ban_global']==1){

                return $this->message = json_encode([
                    'status'    => false,
                    'user_id'   => false,
                    'msg'       => l('account_has_been_banned')
                ]);
            } elseif($otp_retrying == 3 && $minutes_difference < 60) {

                // time
                $otp_blocked_datetime = new DateTime($otp_blocked_time);
                $blocked_remaining_time = $otp_blocked_datetime->modify('+60 minutes')->setTimezone(new DateTimeZone('Asia/Dhaka'))->format('h:i:s A');
                // time end

                return $this->message = json_encode([
                    'status'            => false,
                    'msg'               => ($minutes_difference < 60) ? 'You are temporarily blocked. You can try again after ' . $blocked_remaining_time : l('otp_temp_blocked')
                ]);
            } else {

                if($user['otp_pin']) {  

                    // time
                    $otp_sent_time = $user['otp_sent_time'] ? $user['otp_sent_time'] : '-1 day';
                    $otp_resend_datetime = new DateTime($otp_sent_time);

                    $current_datetime = new DateTime();
                    $time_difference = $current_datetime->diff($otp_resend_datetime);
                    $otp_remaining_time = $otp_resend_datetime->modify('+5 minutes')->format('h:i:s A');
                    // time end                  

                    if ($time_difference->y == 0 && $time_difference->m == 0 && $time_difference->d == 0 && $time_difference->h == 0 && $time_difference->i < 5) {                        
                        
                        return $this->message = json_encode([
                            'status'    => false,
                            'msg'       => 'You can resend OTP after ' . $otp_remaining_time
                        ]);
                    }
                }

                $user_id = $user['user_id'];
                $otp_pin = random_int(1000, 9999);

                $m_messege = "DeshiWedding.com: Your One-Time PIN is {$otp_pin}. It will expire in 15 minutes.";

                // SEND MESSAGE
                sendsms($phone_number, $m_messege);

                // update OTP
                DB::execute("UPDATE user SET otp_pin=$otp_pin, otp_sent_time=NOW(), otp_retrying=0, otp_blocked_time=null WHERE user_id = $user_id");

                return $this->message = json_encode([
                    'status'    => true,
                    'msg'       => l('we_have_sent')
                ]);

            }

        } else { // User is not found for this phone number
            return $this->message = json_encode([
                'status'    => false,
                'msg'       => l('account_not_found')
            ]);
        }
    }

}
