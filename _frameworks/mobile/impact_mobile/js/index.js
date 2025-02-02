var CIndex = function() {
    var $this = this;

    this.isAjax = false;

    this.init = function(context){
        $(function(){
            $this.loginInit(context)
        })
    }

    this.loginInit = function(context){
        console.info('%cInit page Index, context:"'+context+'"','background: #fcffd6');
        $this.isAjax = false;
        $this.$frmLogin = $('#form_login', context);
        $this.$frmLoginInput = $('input', $this.$FrmLogin);
        $this.$frmLoginName = $('#form_login_user', context);
        $this.$frmInputPass = $('#form_login_pass', context)
        $('#icon_field_hint', context).click(function(){
            $this.$frmInputPass[0].type=$this.$frmInputPass[0].type=='text'?'password':'text';
            $(this).toggleClass('icon_field_hint icon_field_vis');
        })

        $this.$frmLoginInput.keydown(function(e){
            if (e.keyCode == 13) {
                $this.$frmLogin.submit();
                return false;
            }
        });

        $this.$frmLoginSubmit = $('#form_log_in_submit', context).click(function(){
            $this.$frmLogin.submit();
            return false;
        });

        $this.$frmLogin.submit(function(){
            if($this.isAjax)return false;
            if($this.$terms[0] && !$this.$terms.prop('checked')){
                showError($this.$terms, l('please_agree_to_the_terms'));
                return false;
            }
            $this.isAjax=true;
            $this.$frmLoginName.val($.trim($this.$frmLoginName.val()));
            $this.$frmLoginSubmit.prop('disabled', true).addLoader();
            showLayerBlockPageNoLoader();
            $this.$frmLogin.ajaxSubmit({success: $this.loginFrmResponse});
            return false;
        });

        $this.$frmLoginInput.on('change propertychange input',function(){
            resetError($this.$frmLoginName);
        }).focus(function(){
            showErrorWrongEl($this.$frmLoginName);
        }).blur(function(){
            hideError($this.$frmLoginName)
        })

        $this.$terms=$('#terms').on('change', function(){
            var el=$(this);
            if (el.prop('checked')){
                resetError($this.$terms);
            } else {
                showError($this.$terms,l('please_agree_to_the_terms'))
            }
        }).focus(function(){
            showErrorWrongEl($this.$terms);
        })
    }

    this.loginFrmInputDisabled = function(){
        hideLayerBlockPage();
        $this.$frmLoginSubmit.removeLoader().prop('disabled', false);
    }

    this.serverError = function(){
        hideLayerBlockPage();
        $this.loginFrmInputDisabled();
        serverError();
    }

    this.loginFrmResponse = function(data){
        var data = getDataAjax(data);
        $this.isAjax = false;
        if (data){
            if(data.substring(0, 11) == '#js:logged:') {
                $.ajax({
                    url:data.substring(11),
                    type:'POST',
                    data:{upload_page_content_ajax:1},
                    context:document.body,
                    beforeSend: function(){

                    },
                    success: function(res){
                        res=checkDataAjax(res);
                        responseHomePage(res,$this.serverError);
                    },
                    error: function(){
                        $this.serverError();
                    },
                    complete: function(){
                    }
                })
                return false;
            }
            if(data.substring(0, 10) == '#js:error:') {
                $this.loginFrmInputDisabled();
                showError($this.$frmLoginName.focus(),data.substring(10));
                return false;
            }
        }else{
            $this.serverError();
        }
    }

    this.goToSocialLogin = function($btn,url){
        if (!isIos) {
            $btn.addLoader();
        }
        redirectUrl(url);
    }

    this.checkPhoneNumber = function(phoneNumber) {
        console.log(phoneNumber)
    }

    $(function(){

        if(document.querySelector("#pp_otp_login #join_phone_number")) {
            var phone_number = window.intlTelInput(document.querySelector("#join_phone_number"), {
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
                $('#otp_phone_error').html(l('phone_number_is_not_valid'));
            }
        });

        $('#pp_otp_login_submit').click(function(){

            $jq('#pp_otp_login #join_phone_number').prop('disabled', true);
            $jq('#pp_otp_login_submit').html('Processing...').prop("disabled", true);

            var phone_number = $('#pp_otp_login #full_phone_number').val();
            $.post(url_main+'login_with_otp.php', {ajax: 1, 'phone_number': phone_number}, function(data){
                var jsonResponse = JSON.parse(data);

                if(jsonResponse.status === true) {

                    $("#OTP_DISPLAY1").hide();

                    $("#otp_number").html(phone_number);
                    $("#otp_phone_number").val(phone_number);
                    $("#OTP_DISPLAY2").show();
                    showTimer();
                } else {
                    showAlert(jsonResponse.msg,true,'fa-info-circle');
                    $("#pp_otp_login_submit").html(l('send_OTP')).prop("disabled", false);
                    $('#pp_otp_login #join_phone_number').prop('disabled', false);
                }
            })
        });

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
        $('#pp_otp_login_now').click(function(){
            var otp_pin = $jq('#pp_otp_login #otp_pin').val();
            if(otp_pin == "") {
                showAlert("Please Type OTP",true,'fa-info-circle');
                return true;
            }

            $('#pp_otp_login #otp_pin').prop('disabled', true);
            $("#pp_otp_login_now").html('Processing..').prop("disabled", true);

            $.post(url_main+'login_with_otp.php', {
                ajax: 1,
                'otp_pin': otp_pin,
                'otp_phone_number': $('#pp_otp_login #otp_phone_number').val()
            }, function(data){
                var jsonResponse = JSON.parse(data);

                if(jsonResponse.status === true) {
                    // login now
                    $('#pp_otp_login #otp_pin').html('{l_login_in_progress}');
                    window.location.href = url_main+jsonResponse.redirect_url;
                } else {
                    showAlert(jsonResponse.msg,true,'fa-info-circle');
                    $("#pp_otp_login_now").html(l('sign_in')).prop('disabled', false);
                    $('#pp_otp_login #otp_pin').prop('disabled', false);
                }
            })
        })
        $('#request_pin_again').click(function(){
            $("#request_pin_again").html(getLoader('css_loader_btn', false, true)).prop("disabled", true);

            $.post(url_main+'login_with_otp.php', {
                ajax: 1,
                'resend': 1,
                'otp_phone_number': $('#pp_otp_login #otp_phone_number').val()
            }, function(data){
                var jsonResponse = JSON.parse(data);
                // console.log(jsonResponse);

                if(jsonResponse.status === true) {
                    $("#request_pin_again").hide();
                    showAlert(l('resent_successfully'),true);
                    $("#otp_pin").val('');

                    showTimer();
                } else {
                    showAlert(jsonResponse.msg,true,'fa-info-circle');
                    $("#request_pin_again").html(l('request_pin_again')).prop('disabled', false);
                }
            })
        })
        function showTimer() {
            // Timer setup
            var timerDuration = 300; // 5 minutes
            var $resendCode = $('.resend_code');

            // Function to start and update the timer
            function startTimer(duration) {
                var remainingTime = duration;

                var timerInterval = setInterval(function () {
                    var minutes = Math.floor(remainingTime / 60); // Get minutes
                    var seconds = remainingTime % 60; // Get seconds

                    // Update the text with minutes and seconds
                    $resendCode.text('Resend code in ' + minutes + ' minutes ' + seconds + ' seconds later');

                    remainingTime--; // Decrease the remaining time

                    if (remainingTime < 0) {
                        clearInterval(timerInterval);
                        $resendCode.text(''); // Clear timer text
                        $("#request_otp_pin_again, #request_pin_again")
                            .html(l('request_pin_again'))
                            .prop("disabled", false).show(); // Re-enable the button
                    }
                }, 1000); // Update every second
            }

            // Start the timer
            startTimer(timerDuration);
        }



        // sign up

        $('#otp_signup_submit').click(function(){
            $('#otp_signup_submit').html('Processing..').prop("disabled", true);

            $.post(url_main+'signup_with_otp.php', {ajax: 1, "send_otp": 1}, function(data){
                var jsonResponse = JSON.parse(data);
                console.log(jsonResponse);

                if(jsonResponse.status === true) {

                    $("#OTP_DISPLAY1").hide();
                    $("#OTP_DISPLAY2").show();
                    showTimer();
                } else {
                    showAlert(jsonResponse.msg,true,'fa-info-circle');
                    $("#otp_signup_submit").html(l('send_OTP')).prop("disabled", false);
                }
            })
        })

        $("#opt_sign_up #otp_pin").keyup(function(e) {
            if (this.value.length > 4) {
                this.value = this.value.slice(0, 4);
            }

            if(this.value.length == 4) {
                $("#otp_signup_now").prop("disabled", false);
            } else {
                $("#otp_signup_now").prop("disabled", true);
                $(this).focus();
            }
        });
        $('#otp_signup_now').click(function(){

            var otp_pin = $('#opt_sign_up #otp_pin').val();

            if(otp_pin == "") {
                showAlert("Please Type OTP",true,'fa-info-circle');
                return true;
            }

            $('#opt_sign_up #otp_pin').prop('disabled', true);
            $("#otp_signup_now").html('Processing..').prop("disabled", true);

            $.post(url_main+'signup_with_otp.php', {
                ajax: 1,
                'otp_pin': otp_pin
            }, function(data){
                var jsonResponse = JSON.parse(data);

                if(jsonResponse.status === true) {
                    // submitted and verified
                    window.location.href = url_main+jsonResponse.redirect_url;
                } else {
                    showAlert(jsonResponse.msg,true,'fa-info-circle');
                    $("#otp_signup_now").html(l('verify')).prop('disabled', false);
                    $('#opt_sign_up #otp_pin').prop('disabled', false);
                }
            })
        })
        $('#request_otp_pin_again').click(function(){
            $("#request_otp_pin_again").html(getLoader('css_loader_btn', false, true)).prop("disabled", true);

            $.post(url_main+'signup_with_otp.php', {
                ajax: 1,
                'resend': 1
            }, function(data){
                var jsonResponse = JSON.parse(data);
                // console.log(jsonResponse);

                if(jsonResponse.status === true) {
                    $("#request_otp_pin_again").hide();
                    showAlert(l('resent_successfully'),true);
                    $("#otp_pin").val('');

                    showTimer();
                } else {
                    showAlert(jsonResponse.msg,true,'fa-info-circle');
                    $("#request_otp_pin_again").html(l('request_pin_again')).prop('disabled', false);
                }
            })
        })

    });

    return this;
}