var CJoin = function() {
    var $this = this;

    this.isAjax = false;

    this.initForgot = function(message,redirect){
        $(function(){
            $this.initForgotPage(message,redirect)
        })
    }

    this.initForgotPage = function(message){
        console.info('%cInit page Forgot','background: #fcffd6');
        $this.isAjax=false;
        if(message!=''){
            showConfirm(message, function(){
                redirectUrl(urlPagesSite.index);
            }, false, l('ok'), '', true,true);
        }

        $this.$frmForgot = $('#form_forgot_password');
        $this.$frmForgotMail = $('#form_forgot_password_mail');
        $this.$frmForgotSubmit = $('#form_forgot_password_submit');

        $this.$frmForgotMail.keydown(function(e){
            if (e.keyCode == 13) {
                $this.$frmForgotSubmit.click();
                return false;
            }
        });

        $this.$frmForgot.submit(function(){
            if($this.isAjax)return false;
            if(!$this.validateEmailPass()){
                return false;
            }
            $this.isAjax=true;
            $this.$frmForgotSubmit.addLoader().prop('disabled', true);
            $this.$frmForgot.ajaxSubmit({success: $this.forgotFrmResponse});
            $this.$frmForgotMail.prop('disabled', true);
            return false;
        });

        $this.$frmForgotMail.on('change propertychange input',function(){
            resetError($(this));
        }).focus(function(){
            resetError($(this));
        }).blur(function(){
            resetError($(this));
        })
    }

    this.forgotFrmResponse = function(data){
        var data = getDataAjax(data);
        $this.isAjax=false;
        $this.$frmForgotSubmit.removeLoader();
        if (data){
            if(data == 'link_send') {
                showConfirmToPage(l('the_link_for_changing_password_has_been_sent'), urlPagesSite.index)
            } else {
                $this.$frmForgotMail.prop('disabled', false);
                showError($this.$frmForgotMail.focus(),data);
                $this.$frmForgotSubmit.prop('disabled', false);
            }
        }else{
            serverError();
            $this.$frmForgotMail.prop('disabled', false);
            $this.$frmForgotSubmit.prop('disabled', false);
        }
    }

    this.validateEmailPass = function(){
        var val=$.trim($this.$frmForgotMail.val()),is=false;
        if (!checkEmail(val)) {
            showError($this.$frmForgotMail.focus(),l('this_email_address_is_not_correct'));
        } else {
            resetError($this.$frmForgotMail);
            is=true;
        }
        return is;
    }

    /* JOIN */
    this.initJoin = function(){
        $(function(){
            $this.initJoinPage();
        })
    }

    this.initJoinPage = function(){
        $this.$formBox=$('#form_box');
        $this.isAjax=false;
        $this.$agree=$('#agree');
        $this.dataFrm={};
        $this.isErrorRegister=false;

        $this.initLocation();
        $this.initBirthday();
        $this.initEmail();
        $this.initPass();
        $this.initName();
        $this.initAll();

        $this.$btnSubmit=$('#form_register_submit').click($this.submitJoin);

        for (var key in joinDefaultData) {
            var $el=$('#'+key);
            if (key=='agree') {
                $el.prop('checked',joinDefaultData[key]?true:false);
            }else{
                $el.val(joinDefaultData[key]);
            }
        }
        //joinDefaultData = {};
    }

    this.initLocation = function(){
        $this.$selectLocation=$('.location',$this.$formBox);
        $this.$locationLabel=$('#country_box').find('label > span');
        $this.$country=$('#country');
        $this.$state=$('#state');
        $this.$city=$('#city');

        var fnSetLocationCookie = function(reset){
            if (reset||0) {
                $.cookie('impact_mobile_join_country_default', '');
                $.cookie('impact_mobile_join_state_default', '');
                $.cookie('impact_mobile_join_city_default', '');
            } else {
                $.cookie('impact_mobile_join_country_default', $this.$country.val());
                $.cookie('impact_mobile_join_state_default', $this.$state.val());
            }
        }

        fnSetLocationCookie(true);

        var $loader;
        $('#city').change(function(){
            fnSetLocationCookie();
            setCookie('impact_mobile_join_city_default', this.value);
        }).change();

        $('#country, #state').change(function(){
            fnSetLocationCookie();

            var cmd = $(this).data('location');
            $.ajax({type: 'POST',
                    url: url_page,
                    data: {cmd:cmd,
                           ajax:1,
                           select_id:this.value},
                    beforeSend: function(){
                        $this.$selectLocation.prop('disabled',true);
                        $loader=getLoader('loader_register',false,true,true).appendTo($this.$locationLabel);
                    },
                    success: function(res){
                        var data=getDataAjax(res);
                        if (data) {
                            var option='<option value="0">'+l('choose_a_city')+'</option>';
                            if (cmd == 'states') {
                                $this.$state.html('<option value="0">'+l('choose_a_state')+'</option>' + data.list);
                                $this.$city.html(option);
                                $.cookie('impact_mobile_join_state_default', 0);
                            } else {
                                $this.$city.html(option + data.list);
                            }
                            $.cookie('impact_mobile_join_city_default', 0);
                        }
                        $this.$selectLocation.prop('disabled',false);
                    },
                    complete: function(){
                       $loader.remove();
                    }
            })
        })

        $this.$country.on('change', function(e){
            resetError($this.$state);
            resetError($this.$city);
        })

        $this.$state.on('change', function(e){
            if($this.isErrorRegister){
                var val=this.value*1;
                if(val){
                    resetError($this.$state);
                }else{
                    $this.showError($this.$state,l('state_is_required'))
                }
            }
        }).focus(function(){
            showErrorWrongEl($this.$state)
        }).blur(function(){
            hideError($this.$state)
        })

        $this.$city.on('change', function(e){
            if($this.isErrorRegister){
                var val=this.value*1;
                if(val){
                    resetError($this.$city);
                }else{
                    $this.showError($this.$city,l('state_is_required'))
                }
            }
        }).focus(function(){
            showErrorWrongEl($this.$city)
        }).blur(function(){
            hideError($this.$city)
        })
    }

    /* Birthday */
    this.initBirthday = function(){
        $this.$birthday=$('.s_birthday',$this.$formBox);
        $this.$day=$('#day');
        $this.$frmBirthday=$('#form_birthday');

        $this.$birthday.change(function(){
            joinDefaultData[this.id]=this.value;
            if(this.id!='day'){
                var firstValue=false;
                if(isIos){
                    firstValue=l('please_choose_empty');
                    if(!firstValue)firstValue=' ';
                }
                updateDay(this.id,'frm_date','year','month','day',false,firstValue);
            }
            $this.validateBirthday();
        }).focus(function(){
            showErrorWrongEl($this.$frmBirthday)
        }).blur(function(){
            hideError($this.$frmBirthday)
        })
    }

    this.validateBirthday = function(show){
        var isError=false,show=defaultFunctionParamValue(show,1);
        if($this.birthDateToAge()){
            $this.resetErrorBirthday();
            isError=true;
        }else{
            $this.showErrorBirthday(show);
        }
        return isError;
    }

    this.birthDateToAge = function() {
        var birth=new Date($('#year').val(), $('#month').val()-1, $('#day').val()),
            now = new Date(),
            age = now.getFullYear() - birth.getFullYear();
            age = now.setFullYear(1972) < birth.setFullYear(1972) ? age - 1 : age;
        return age>=minAge;
    }

    this.showErrorBirthday = function(show){
        var show=defaultFunctionParamValue(show,1);
        $this.$birthday.addClass('wrong');
        $this.showError($this.$frmBirthday,l('incorrect_date'),show,$this.$frmBirthday,'+5');
    }

    this.resetErrorBirthday = function(){
        $this.$birthday.removeClass('wrong');
        resetError($this.$frmBirthday);
    }
    /* Birthday */
    /* Email */
    this.initEmail = function(){
        $this.$email=$('#email').on('change propertychange input', function(e){
            $this.isErrorRegister&&$this.validateEmail();
            joinDefaultData['email']=trim($this.$email.val());
        }).focus(function(){
            showErrorWrongEl($this.$email);
        }).blur(function(){
            hideError($this.$email)
        })
    }

    this.validateEmail = function(show){
        var val=$.trim($this.$email.val()),isError=false;
        if(val == "")
            $('#email_error').html(l('the_field_is_required'));
        else if(!checkEmail(val)){
            $this.showError($this.$email,l('incorrect_email'),show);
            $('#email_error').empty()
        }else{
            isError=true;
            resetError($this.$email);
            $('#email_error').empty()
        }
        return isError;
    }
    /* Email */
    /* Password */
    this.initPass = function(){
        $this.$pass=$('#password').on('change propertychange input', function(e){
            $this.isErrorRegister&&$this.validatePass();
            joinDefaultData['password']=$this.$pass.val();
        }).focus(function(){
            showErrorWrongEl($this.$pass);
        }).blur(function(){
            hideError($this.$pass)
        })
    }

    this.validatePass = function(show){

        var show=defaultFunctionParamValue(show,1),v=$this.$pass.val(),ln=$.trim(v).length,isError=false;
        
        if(!validatePassword($("#password").val())) {
            $this.showError($this.$pass, l('format_password'), show)
        } else if(ln<minCahrPass||ln>maxCahrPass){
            $this.showError($this.$pass,lMaxMinLengthPassword,show)
        }else if(~v.indexOf("'")<0) {
            $this.showError($this.$pass,l('invalid_password_contain'),show)
        }else{
            isError=true;
            resetError($this.$pass)
        }
        return isError;
    }
    /* Password */
    /* Name */
    this.initName = function(){
        $this.$name=$('#user_name').on('change propertychange input', function(){
            $this.isErrorRegister&&$this.validateName();
            joinDefaultData['user_name'] = trim($this.$name.val());
        }).focus(function(){
            $this.initScrollToEl($this.$name);
            showErrorWrongEl($this.$name);
        }).blur(function(){
            hideError($this.$name)
        })
        //setTimeout(function(){$this.isSendRegister=true},500);
    }

    this.validateName = function(show){
        var show=defaultFunctionParamValue(show,1),v=$this.$name.val(),ln=$.trim(v).length,isError=false;
        if (/[#&'"\/\\<]/.test(v)){
            $this.showError($this.$name,l('invalid_username'),show)
        } else if (ln<minCahrName||ln>maxCahrName) {
            $this.showError($this.$name,lMaxMinLengthUsername,show)
        } else {
            isError=true;
            resetError($this.$name);
        }
        return isError;
    }
    /* Name */
    /* All */
    this.initAll = function(){
        $this.$orientation=$('#orientation').on('change', function(e){
            var val=this.value*1;
            joinDefaultData['orientation']=val;
            if($this.isErrorRegister){
                if(val){
                    resetError($this.$orientation);
                }else{
                    $this.showError($this.$orientation,l('orientation_is_required'))
                }
            }
        }).focus(function(){
            showErrorWrongEl($this.$orientation)
        }).blur(function(){
            hideError($this.$orientation)
        })
        if (isRecaptcha) {
            $this.$recaptchaBl=$('#recaptcha_bl');
            $this.$recaptcha=$('#recaptcha');
            $win.on('resize orientationchange', $this.prepareReCaptcha).resize();
            $('#main_wrap').on('click',function(e){
                var $el=$(e.target);
                if($el.is('#tip_recaptcha')||$el.closest('#tip_recaptcha')[0]||$el.is('#form_register_submit')){
                }else resetError($this.$recaptcha)
            })
        } else {
            $this.$captcha = $('#captcha').on('change propertychange input', function(){
                resetError($this.$captcha);
            }).focus(function(){
                $this.initScrollToEl($this.$captcha);
                showErrorWrongEl($this.$captcha);
            }).blur(function(){
                hideError($this.$captcha)
            })
        }

        $this.$agree=$('#agree').on('change', function(){
            var el=$(this);
            if (el.prop('checked')){
                resetError($this.$agree);
            } else {
                $this.showError($this.$agree,l('please_agree_to_the_terms'))
            }
            joinDefaultData['agree']=el.prop('checked');
        }).focus(function(){
            showErrorWrongEl($this.$agree);
        })

        $('#main_wrap').on('click',function(e){
            var $el=$(e.target);
            if($el.is('#tip_agree')||$el.closest('#tip_agree')[0]
                ||$el.closest('.custom_checkbox.no_hide')[0]||$el.is('#form_register_submit')){
            }else resetError($this.$agree)
        })
    }
    /* All */

    this.checkPhoneNumber = function(phone_number) {
        var full_number = phone_numberTel.getNumber();
        $("#full_phone_number").val(full_number);

        if(validatePhoneNumber(phone_number)) { // true
            $('#phone_error').empty();
        } else { // wrong
            $("#join_phone_number").focus();
            $('#phone_error').html(joinLangParts.incorrect_phone);
        }
    }

    function validatePhoneNumber(phoneNumber) {

        var country_data = phone_numberTel.getSelectedCountryData();
        var country_code = country_data.dialCode;

        // Remove all non-digit characters
        var cleaned = phoneNumber.replace(/\D/g, '');

        if(country_code == "880") {

            // Check if the phone number starts with '01' and has a length of 11
            if (cleaned.startsWith('01') && cleaned.length === 11) {
                return true;
            }

            // Check if the phone number starts with '1' and has a length of 10
            if (cleaned.startsWith('1') && cleaned.length === 10) {
                return true;
            }

            // Check if the phone number starts with '+880' and has a length of 14
            if (phoneNumber.startsWith('+880') && phoneNumber.length === 14) {
                return true;
            }
        } else {
            if (cleaned.length > 9 && cleaned.length < 13) {
                return true;
            }
        }

        return false;
    }

    this.bl_join_signup_as = function(signup_as) {
        if(signup_as == "") {
            $('#bl_join_signup_as_error').html(l('the_field_is_required'));
            $('#poster_name_div').empty();
        } else if (signup_as === 'self') {
            $('#bl_join_signup_as_error').empty();
            $('#poster_name_div').empty();
        } else {
            $('#bl_join_signup_as_error').empty();

            $('#poster_name_div').html(`
                <label>${l('name')} <span class="r_required">*</span></label>
                <div class="bl_inp_pos to_show">
                    <input id="poster_name" class="inp name" name="poster_name" maxlength="100" type="text" placeholder="${l('type_name')}" autocomplete="off" onkeyup="clJoin.checkPosterName(this.value)" />
                    <div id="poster_name_error" class="error" style="font-size: 12px;color: yellow">&nbsp;</div>
                </div>
            `);

            $("#poster_name").keyup(function() {
                var candidate_name = this.value;

                if(candidate_name == "") {
                    $('#poster_name_error').html(l('the_field_is_required'));
                } else if (candidate_name.length < nameLengthMin) {
                    $('#poster_name_error').html(joinLangParts.incorrect_name_length);
                } else if (/[!?/@#$%^&*]/.test(candidate_name)) {
                    $('#poster_name_error').html(joinLangParts.incorrect_name);
                } else {
                    $('#poster_name_error').empty();
                }
            });
        }
    }

    this.checkPosterName = function(poster_name) {
        if(poster_name == "") {
            $('#poster_name_error').html(l('the_field_is_required'));
        } else {
            $('#poster_name_error').empty();
        }
    }

    function validatePassword(password) {
        // Regular expression to check the password
        var regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/;

        if (regex.test(password))
            return true;
        else
            return false;
    }
    this.validatePassCustom = function(password) {

        var regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/;
        if (regex.test(password)) {
            $("#password_check").removeClass("to_hide");
            $('#password_requirements').empty();
        } else {
            $("#password_check").addClass("to_hide");
            $('#password_requirements').html(l('format_password')).addClass("password_requirements_error");
        }
    }

    function nameValid(candidate_name) {
        if (candidate_name === "") 
            return false;   
        else if (candidate_name && candidate_name.length < nameLengthMin) 
            return false;   
        else if (/[!?/@#$%^&*]/.test(candidate_name)) 
            return false; // Restrict only specific special characters
        else 
            return true;
    }

    this.validateNameCustom = function(candidate_name) {
        if(candidate_name == "") {
            $('#join_name_error').html(l('the_field_is_required'));
        } else if (candidate_name.length < nameLengthMin) {
            $('#join_name_error').html(joinLangParts.incorrect_name_length);
        } else if (/[!?/@#$%^&*]/.test(candidate_name)) {
            $('#join_name_error').html(joinLangParts.incorrect_name);
        } else {
            $('#join_name_error').empty();
        }
    }
    this.orientationCustom = function(orientation) {
        if(orientation) {
            $('#orientation_error').empty();
        } else {
            $('#orientation_error').html(l('the_field_is_required'));
        }
    }
    this.birthdayCustom = function() {
        let day = $("#day").val()
        let month = $("#month").val()
        let year = $("#year").val()

        if(day == "" || month == "" || year == "") {
            $('#birth_error').html(joinLangParts.incorrect_date);
        } else {
            $('#birth_error').empty();
        }
    }
    function checkFormDisable(callback_error) {
        let bl_join_signup_as = $("#bl_join_signup_as").val();
        let join_phone_number = $("#join_phone_number").val();
        let email = $("#email").val();
        let password = $("#password").val();

        let join_name = $("#user_name").val();
        let bl_join_done_orientation = $("#orientation").val();

        let month = $("#month").val();
        let day = $("#day").val();
        let year = $("#year").val();

        let agree = $("#agree").prop('checked');

        var isValid = validatePhoneNumber(join_phone_number);

        var poster = 0;
        if(document.getElementById("poster_name")) {
            let poster_name = $("#poster_name").val();
            if(poster_name !== "")
                poster = 1;
        } else
            poster = 1;

        // console.log("bl_join_signup_as => "+bl_join_signup_as, "poster => "+poster, "join_phone_number => "+join_phone_number, "email => "+email, "checkEmail => "+checkEmail(email), "validatePassword => "+validatePassword(password), "join_name => "+join_name, "nameValid => "+nameValid(join_name), "bl_join_done_orientation => "+bl_join_done_orientation, "month => "+month, "day => "+day, "year => "+year, "agree => "+agree, "isValid => "+isValid);
        // return false;

        if(
            bl_join_signup_as && poster > 0 &&
            join_phone_number && isValid &&
            email && checkEmail(email) && 
            password && validatePassword(password) &&
            join_name && nameValid(join_name) &&
            bl_join_done_orientation &&
            month &&
            day &&
            year
        ) {
            // alert('Validated');
        } else {
            var err_message = '';
            let listIcon = "<i class='fa fa-circle' style='color: #555; font-size: 16px'></i> ";

            if(bl_join_signup_as == "") {
                $('#bl_join_signup_as_error').html(l('the_field_is_required'));
                err_message += listIcon+"Profile Posted by field is required!<br>";
            }

            if(document.getElementById("poster_name")) {
                var poster_name_m = document.getElementById("poster_name").value;
                if(poster_name_m == "") {
                    $('#poster_name_error').html(l('the_field_is_required'));
                    err_message += listIcon+"Poster Name field is required!<br>";
                } else if(!nameValid(poster_name_m)) {
                    err_message += listIcon+"Poster Name is not valid!<br>";
                }
            }

            if(join_phone_number == "") {
                $('#phone_error').html(l('the_field_is_required'));
                err_message += listIcon+"Phone number field is required!<br>";
            } else if(!isValid) {
                $('#phone_error').html(joinLangParts.incorrect_phone);
                err_message += listIcon+joinLangParts.incorrect_phone+"<br>";
            }

            if(email == "") {
                $('#email_error').html(l('the_field_is_required'));
                err_message += listIcon+"Email field is required!<br>";
            } else if(!checkEmail(email)) {
                err_message += listIcon+l('this_email_address_is_not_correct')+"!<br>";
            }

            if(password == "") {
                $('#password_requirements').html(l('format_password')).addClass('password_requirements_error');
                err_message += listIcon+"Password field is required!<br>";
            } else if(!validatePassword(password)) {
                err_message += listIcon+"Invalid password. "+l('format_password')+"<br>";
            }

            if($("#bl_join_signup_as").val() == 'matchmaker')
                var cname = "Matchmaker name";
            else
                var cname = "Candidate name";

            if(join_name == "") {
                $('#join_name_error').html(l('the_field_is_required'));
                err_message += listIcon+cname+" field is required!<br>";
            } else if(!nameValid(join_name)) {
                err_message += listIcon+cname+" is not valid!<br>";
            }

            if(bl_join_done_orientation == "") {
                $('#orientation_error').html(l('the_field_is_required'));
                err_message += listIcon+"Gender field is required!<br>";
            }

            if(day == "" || month == "" || year == "") {
                if(day || month || year) {
                    $('#birth_error').html(joinLangParts.incorrect_date);
                    err_message += listIcon+joinLangParts.incorrect_date+"<br>";
                } else {
                    $('#birth_error').html(l('the_field_is_required'));
                    err_message += listIcon+"Birthdate field is required!<br>";
                }
            }

            
            callback_error(true, err_message);

        }
    }
    this.submitJoin = function(){
        if($this.isAjax)return false;
        $this.isErrorRegister=true;

        var haveError = false;
        checkFormDisable(function(error, err_message) {
            if(error) {
                haveError = true;
                console.log(err_message);
                showAlert(err_message,true,'fa-info-circle');
            }
        });

        if(haveError)
            return;


        var $focus,
        notError=$this.validateEmail(false);
        if(!notError)$focus=$this.$email;
        if(!$this.validatePass(false)&&notError){
            notError=false;
            $focus=$this.$pass;
        }
        if(!$this.validateName(false)&&notError){
            notError=false;
            $focus=$this.$name;
        }
        if (!isIos) {
            if(!$this.validateBirthday(false)&&notError){
                notError=false;
                $focus=$this.$day;
            }
            var isEmpty=$this.$orientation.val()==0;
            if(isEmpty){
                $this.showError($this.$orientation,l('orientation_is_required'),false)
            }
            if(isEmpty&&notError){
                notError=false;
                $focus=$this.$orientation;
            }
            isEmpty=$this.$state.val()==0;
            if(isEmpty){
                $this.showError($this.$state,l('state_is_required'),false);
            }
            if(isEmpty&&notError){
                notError=false;
                $focus=$this.$state;
            }
            isEmpty=$this.$city.val()==0;
            if(isEmpty){
                $this.showError($this.$city,l('city_is_required'),false)
            }
            if(isEmpty&&notError){
                notError=false;
                $focus=$this.$city;
            }
        }

        if(isRecaptcha){
            responseRecaptcha=grecaptcha.getResponse(recaptchaWd);
            if(responseRecaptcha==''){
                showError($this.$recaptcha,l('incorrect_captcha'));
                notError=false;
                $focus=[];
            }else{
                $this.dataFrm['recaptcha']=responseRecaptcha;
            }
        } else {
            isEmpty=$.trim($this.$captcha.val())=='';
            if(isEmpty){
                $this.showError($this.$captcha,l('incorrect_captcha'),false)
                if(notError){
                    notError=false;
                    $focus=$this.$captcha;
                }
            }
        }

        var isTerms=$this.$agree.prop('checked');
        if(!isTerms){
            $this.showError($this.$agree,l('please_agree_to_the_terms'),false)
            if(notError){
                notError=false;
                $focus=$this.$agree.change();
            }
        }

        if (!notError) {
            $focus[0]&&$focus.focus();
            return false;
        }

        $('input.inp, input.ajax, select',$this.$formBox).each(function(){
            $this.dataFrm[$(this).attr('name')]=$(this).val();
        })

        $this.isAjax=true;
        $this.$btnSubmit.addLoader().prop('disabled',true);
        var $fields=$('input.inp, select',$this.$formBox).prop('disabled',true);
        $this.dataFrm['geo_position']=geoPoint;

        $.post(url_page, $this.dataFrm,
            function(data){
                $this.isAjax=false;

                var data=getDataAjax(data),fnError=function(){
                    serverError();
                    $fields.prop('disabled',false);
                    $this.$btnSubmit.removeLoader().prop('disabled',false);
                };
                if (data===false){
                    fnError();
                    return;
                }
                var res=$(data).filter('.redirect');
                if(res[0]){
                    uploadHomePage(res.text(),fnError);
                    return;
                }
                $this.$btnSubmit.removeLoader();
                res=$(data).filter('.wait_approval');
                if(res[0]){
                    showConfirm(l('no_confirmation_account'), function(){
                        goToPage($('.pp_btn_ok_bl:visible').data('url',urlPagesSite.login));
                    }, false, l('ok'), '', true,true);
                }else{
                    $this.$btnSubmit.prop('disabled',false);
                    $fields.prop('disabled',false);

                    if(isRecaptcha){
                        grecaptcha.reset(recaptchaWd);
                    }else{
                        $('#img_join_captcha').click();
                        $this.$captcha.val('');
                    }
                    var dataBlocks = {'.mail' : $this.$email,
                                      '.password' : $this.$pass,
                                      '.name' : $this.$name,
                                      '.birthday' : $this.$day,
                                      '.captcha' : $this.$captcha,
                                      '.recaptcha' : $('#recaptcha_box'),
                    };
                    $this.showErrorFromData(data, dataBlocks);
                    showAlert(data,true,'fa-info-circle');
                }
        })
        return false;
    }

    this.showErrorFromData = function(data, dataBlocks){
        var dataBlock = '',notError=true,$focus;
        for(var dataBlocksKey in dataBlocks) {
            dataBlock = $(data).filter(dataBlocksKey);
            if(dataBlock.length) {
                var el=dataBlocks[dataBlocksKey];
                $this.showError(el,dataBlock.text(),false);
                if(notError){
                    $focus=el;
                    notError=false;
                }
                if(dataBlocksKey=='.birthday'){
                    $this.$birthday.addClass('wrong');
                }
            }
        }
        if(!notError)$focus.focus();
    }

    this.refreshCaptcha = function(){
        $jq('#img_join_captcha').attr('src', '../_server/securimage/securimage_show_custom.php?site_part_mobile=1&sid=' + Math.random());
        return false;
    }

    this.prepareReCaptcha = function(){
        if($this.$recaptcha==undefined)return;
        var $renderC=$this.$recaptcha.children('div');
        if(!$renderC[0])return;
        $this.$recaptcha.removeAttr('style');
        var wb=$this.$recaptchaBl.width(),wr=$renderC.width(),
            sc=parseFloat(wb/wr).toFixed(3);
        if(sc>1.1)sc=1.1;
        $this.$recaptcha.css({transform:'scale('+sc+')', transformOrigin:'0 0'});
        $this.$recaptchaBl.addClass('to_show');

        /* Fix app position */
        if (typeof grecaptcha == 'object') {
            var responseRecaptcha=grecaptcha.getResponse(recaptchaWd);
            if(responseRecaptcha == ''){
                console.log('Join recaptcha reset');
                grecaptcha.reset(recaptchaWd);
            }
        }
        /* Fix app position */
    }

    this.showError = function(el,msg,vis,wr,d){
        showError(el,msg,vis,false,wr,d)//$this.$formBox
    }

    this.initScrollToEl = function($el){
        $win.one(getEventOrientation(),function(){
            var top=$el.closest('.bl').offset().top+$('#main').scrollTop()-$('#main')[0].offsetHeight+94;
            $('#main').stop().animate({scrollTop:top},300,'easeInOutCubic')
        })
    }
    /* JOIN */

    $(function(){
        $('#show_password').change(function () {
            const passwordField = $('#password');
            if ($(this).is(':checked')) {
                passwordField.attr('type', 'text');
            } else {
                passwordField.attr('type', 'password');
            }
        });
    })

    return this;
}