$(function () {
    var $ppSignIn=$('#pp_sign_in').modalPopup({shClass:currentPage=='join.php'?'pp_shadow_empty':''});
    function signInClose() {
        if(!$ppSignIn.is(':visible'))return;
        $ppSignIn.close(false,function(){
            hideErrorLoginFrom('#form_login_user', $('input.inp, #form_login_submit, button', '#form_login'))
        })
    }

    $('#pp_sign_in_close').click(function(){
        signInClose();
        return false;
    })

    $('#pp_sign_in_open, #pp_sign_in_open1').click(function(){
        $ppSignIn.open();
        return false;
    })

    // OTP LOGIN
    var $ppOTPlogin=$('#pp_otp_login').modalPopup();
    $jq('#pp_login_with_otp, #pp_login_with_otp1').click(function(){
        $ppSignIn.close(false,function(){
            hideErrorLoginFrom('#form_login_user', $('input.inp, #form_login_submit, button', '#form_login'));
            $ppOTPlogin.open();
        })
        return false;
    })
    $jq('.pp_otp_login_close').click(function(){
        $ppOTPlogin.close(false,function(){
            $ppSignIn.open();
        })
        return false;
    })
    function forgotPassClose() {
        if(!$ppOTPlogin.is(':visible'))return;
        $jq('.pp_otp_login_close').click();
    }

    if(document.querySelector("#join_phone_number")) {
        var phone_number = window.intlTelInput(document.querySelector("#pp_otp_login #join_phone_number"), {
            separateDialCode: true,
            onlyCountries:["bd"],
            hiddenInput: "full",
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.3/build/js/utils.js"
        });
    }


    function validatePhoneNumberOTP(phoneNumber) {

        // Remove all non-digit characters for processing
        var cleaned = phoneNumber.replace(/\D/g, '');

        // Check if the phone number starts with '+880' and has a length of 14
        if (phoneNumber.startsWith('+880') && cleaned.startsWith('8801') && phoneNumber.length === 14) {
            return true;
        }

        return false; // Reject invalid Bangladeshi numbers
    }

    function enforceMaxLength(input) {
        var myVal = input.value;
        var maxLength = myVal.startsWith('0') ? 11 : 10;

        if (input.value.length > maxLength) {
            input.value = input.value.slice(0, maxLength);
        }
    }

    $("#pp_otp_login #join_phone_number").keyup(function(e) {
        enforceMaxLength(this);

        var full_number = phone_number.getNumber(intlTelInputUtils.numberFormat.E164);
        $('#pp_otp_login #full_phone_number').val(full_number);

        var full_number = $("#pp_otp_login #full_phone_number").val();
        if(validatePhoneNumberOTP(full_number)) {
            $("#pp_otp_login_submit").prop("disabled", false);
            $('#otp_phone_error').empty();
        } else {
            $("#pp_otp_login_submit").prop("disabled", true);
            $("#pp_otp_login #join_phone_number").focus();
            $('#otp_phone_error').html("{l_phone_number_is_not_valid}");
        }
    });
    $jq('#pp_otp_login_submit').click(function(){

        $jq('#pp_otp_login #join_phone_number').prop('disabled', true);
        $jq('#pp_otp_login_submit').html(getLoader('css_loader_btn', false, true)).prop("disabled", true);

        var phone_number = $jq('#pp_otp_login #full_phone_number').val();
        $.post(url_main+'login_with_otp.php', {ajax: 1, 'phone_number': phone_number}, function(data){
            var jsonResponse = JSON.parse(data);
            // console.log(jsonResponse);

            if(jsonResponse.status === true) {

                $("#OTP_DISPLAY1").hide();

                $("#otp_number").html(phone_number);
                $("#otp_phone_number").val(phone_number);
                $("#OTP_DISPLAY2").show();
                showTimer();
            } else {
                alertCustom(jsonResponse.msg,true,'');
                $("#pp_otp_login_submit").html(l('send_OTP')).prop("disabled", false);
                $('#pp_otp_login #join_phone_number').prop('disabled', false);
            }
        })
    })
    $("#pp_otp_login #otp_pin").keyup(function(e) {
        if (this.value.length > 4) {
            this.value = this.value.slice(0, 4);
        }

        if(this.value.length == 4) {
            $("#pp_otp_login_now").prop("disabled", false);
        } else {
            $("#pp_otp_login_now").prop("disabled", true);
            $(this).focus();
        }
    });
    $jq('#pp_otp_login_now').click(function(){
        var otp_pin = $jq('#pp_otp_login #otp_pin').val();
        if(otp_pin == "") {
            alertCustom("Please Type OTP",true,'');
            return true;
        }

        $jq('#pp_otp_login #otp_pin').prop('disabled', true);
        $("#pp_otp_login_now").html(getLoader('css_loader_btn', false, true)).prop("disabled", true);

        $.post(url_main+'login_with_otp.php', {
            ajax: 1,
            'otp_pin': otp_pin,
            'otp_phone_number': $jq('#pp_otp_login #otp_phone_number').val()
        }, function(data){
            var jsonResponse = JSON.parse(data);

            if(jsonResponse.status === true) {
                // login now
                $jq('#pp_otp_login #otp_pin').html('{l_login_in_progress}');
                window.location.href = url_main+jsonResponse.redirect_url;
            } else {
                alertCustom(jsonResponse.msg,true,'');
                $("#pp_otp_login_now").html(l('sign_in')).prop('disabled', false);
                $('#pp_otp_login #otp_pin').prop('disabled', false);
            }
        })
    })
    $jq('#request_pin_again').click(function(){
        $("#request_pin_again").html(getLoader('css_loader_btn', false, true)).prop("disabled", true);

        $.post(url_main+'login_with_otp.php', {
            ajax: 1,
            'resend': 1,
            'otp_phone_number': $jq('#pp_otp_login #otp_phone_number').val()
        }, function(data){
            var jsonResponse = JSON.parse(data);
            // console.log(jsonResponse);

            if(jsonResponse.status === true) {
                $("#request_pin_again").hide();
                alertCustom(l('resent_successfully'),true,'');
                $("#otp_pin").val('');

                showTimer();
            } else {
                alertCustom(jsonResponse.msg,true,'');
                $("#request_pin_again").html(l('request_pin_again')).prop('disabled', false);
            }
        })
    })
    function showTimer() {
        // Timer setup
        var timerDuration = 300; // 60 seconds
        var $resendCode = $('.resend_code');

        // Function to start and update the timer
        function startTimer(duration) {
            var remainingTime = duration;

            $resendCode.text('Resend code in ' + remainingTime + 's later');

            var timerInterval = setInterval(function () {
                remainingTime--;

                if (remainingTime > 0) {
                    $resendCode.text('Resend code in ' + remainingTime + 's later');
                } else {
                    clearInterval(timerInterval);
                    $resendCode.text(''); // Clear timer text
                    $("#request_pin_again")
                        .html(l('request_pin_again'))
                        .prop("disabled", false).show(); // Re-enable the button
                }
            }, 1000); // Update every second
        }

        // Start the timer
        startTimer(timerDuration);
    }
    // OTP LOGIN END






    var $ppForgotPass=$('#pp_resend_password').modalPopup();
    $jq('#pp_forgot_pass_open').click(function(){
        //var $shadow=$('<div class="pp_shadow"></div>').hide().prependTo('body').fadeIn('fast');
        $ppSignIn.close(false,function(){
            //$shadow.fadeOut('fast');
            hideErrorLoginFrom('#form_login_user', $('input.inp, #form_login_submit, button', '#form_login'));
            $ppForgotPass.open();
        })
        return false;
    })

    $jq('.pp_forgot_pass_close').click(function(){
        $ppForgotPass.close(false,function(){
            $ppSignIn.open();
        })
        return false;
    })
    function forgotPassClose() {
        if(!$ppForgotPass.is(':visible'))return;
        $jq('.pp_forgot_pass_close').click();
    }

    $jq('#pp_resend_password_email').on('change propertychange input',validateEmailForgotPass);
    function validateEmailForgotPass() {
        var email=trim($jq('#pp_resend_password_email').val()),
            is=checkEmail(email);
        hideErrorLoginFrom('#pp_resend_password_email', $jq('#pp_resend_password_email'));
        hideErrorLoginFrom('#pp_resend_password_error', $jq('#pp_resend_password_email'), '.successful');
        $jq('#pp_resend_password_submit').prop('disabled',!is);
        return is;
    }

    $jq('#pp_resend_password_submit').click(function(){
		var url=url_main+'forget_password.php?ajax=1&mail='+$jq('#pp_resend_password_email').val();
        $jq('#pp_resend_password_email').prop('disabled', true);
        $jq('#pp_resend_password_submit').html(getLoader('css_loader_btn', false, true));
		$.get(url, function(data){
            if(data == 'link_send'){
                siteLangParts.send_password=siteLangParts.send_again;
                showErrorLoginFrom('#pp_resend_password_error', siteLangParts.link_password_send, $jq('#pp_resend_password_submit'), '.successful')
            }else{
                showErrorLoginFrom('#pp_resend_password_email', data, $jq('#pp_resend_password_submit'));
            }
            $jq('#pp_resend_password_submit').html(siteLangParts.send_password);
            $jq('#pp_resend_password_email').prop('disabled', false);
		})
	})

	$('body').on('click', '.pp_wrapper', function(e){
		if($(e.target).is('.pp_wrapper')){
            signInClose();
            forgotPassClose();
        }
	})

    var $frmLogin = $('#form_login'),
		$frmLoginInput = $('input.inp, #form_login_submit, button', $frmLogin),
        $frmLoginSubmit = $('#form_login_submit'),
        $frmLoginUser = $('#form_login_user'),
        isFrmLoginSubmitAjax = false;

    $frmLogin.submit(function() {
        if(isFrmLoginSubmitAjax)return false;
        isFrmLoginSubmitAjax=true;
        $frmLoginUser.val($.trim($frmLoginUser.val()));
        $(this).ajaxSubmit({success: loginResponse});
        $frmLoginInput.prop('disabled', true);
        $frmLoginSubmit.html(getLoader('css_loader_login_form',false,true));
        return false;
    });

    $('input.inp', $frmLogin).on('change propertychange input', function(){
        hideErrorLoginFrom('#form_login_user', $frmLoginInput);
    })

	function loginResponse(data) {
        isFrmLoginSubmitAjax=false;
		if(data.substring(0, 11) == '#js:logged:') {
			//Without it will not work autocomplete form
			//frm_login.attr('action', data.substring(11)).submit();
			location.href = data.substring(11);
			return false;
		}
		if(data.substring(0, 10) == '#js:error:') {
            $frmLoginInput.prop('disabled', false);
            $frmLoginSubmit.html(siteLangParts.signIn);
            showErrorLoginFrom('#form_login_user', data.substring(10), $frmLoginInput);
			return false;
		}
		location.href = 'index.php';
	}

    function showErrorLoginFrom(el, text, $input, cl){
        var cl=cl||'.error',
            $el=$(el).focus(),$error=$el.next(cl);
        if ($error.is('.to_show')) {
            $error.html(text);
            return;
        }
        var h=$error.html(text).css('height', 'auto').height();
		$error.height(0);
		setTimeout(function(){
			$error.css({height:h}).addClass('to_show');
		},1);
        $input.prop('disabled', false);
    }
})

function hideErrorLoginFrom(el, $input,cl){
    cl=cl||'.error';
    $(el).next(cl).removeClass('to_show').css({height:0});
    $input.prop('disabled', false);
}