<form id="frm_verify_phone_number" name="frm_verify_phone_number" method="POST" onsubmit="return Profile.submit_mobile_verification(event, this)">
    <input type="hidden" name="cmd" value="verify_phone_number" />
    <input type="hidden" name="e_user_id" value="<?php echo $e_user_id; ?>" />

    <div class="formdiv verify_phone_number_form" id="verifyPhone">        
        <div style="padding: 10px 0 0;">
            <label><?php echo l('phone_number'); ?>: <span style="font-weight: bold;"><?php echo $g_user['phone']; ?></span>
                <!-- (<button type="button" id="changeNumber" onclick="return load_phone_number()"><?php // echo l('change'); ?></button>) -->
            </label>
        </div>
        <div style="padding: 10px 0 0;">
            <button id="resendCode" name="resend_code" class="btn turquoise" type="button" onclick="return Profile.resendVCode()"><?php echo l('send_verification_code'); ?></button>
            <div id="timerContainer" style="display: none;padding-top: 10px;">
              <?php echo l('resend_code'); ?>: <span id="timer" style="font-weight: bold"></span> later
            </div>
        </div>
    	<div style="padding: 20px 0;">
            <label style="font-weight: bold;"><?php echo l('enter_verification_code'); ?>:</label>
            <input type="number" id="verification_code" name="verification_code" placeholder="123456" required pattern="\d{1,6}" onkeyup="return check_vnumber(this.value)" />
            <button id="vcode_submit" type="submit" class="btn small turquoise" disabled><?php echo l('verify'); ?></button>
        </div>
    </div>
</form>

<script>
/*function backToverify() {
    $("#verify_phone_number .head").html('<?php echo l('confirm_your_phone_number'); ?>');
    $("#verifyPhone").html(`
        <div style="padding: 10px 0 0;">
            <label><?php echo l('phone_number'); ?>: <span style="font-weight: bold;"><?php echo $g_user['phone']; ?></span> (<button type="button" id="changeNumber" onclick="return load_phone_number()"><?php echo l('change'); ?></button>)</label>
        </div>
        <div style="padding: 10px 0 0;">
            <button id="resendCode" name="resend_code" class="btn turquoise" type="button" onclick="return Profile.resendVCode()"><?php echo l('send_verification_code'); ?></button>
            <div id="timerContainer" style="display: none;padding-top: 10px;">
              <?php echo l('resend_code'); ?>: <span id="timer" style="font-weight: bold"></span> later
            </div>
        </div>
        <div style="padding: 20px 0;">
            <label style="font-weight: bold;"><?php echo l('enter_verification_code'); ?>:</label>
            <input type="number" id="verification_code" name="verification_code" placeholder="123456" required pattern="\d{1,6}" onkeyup="return check_vnumber(this.value)" />
            <button id="vcode_submit" type="submit" class="btn small turquoise" disabled><?php echo l('submit'); ?></button>
        </div>
    `)
}
function load_phone_number() {
    $("#verify_phone_number .head").html('<?php echo l('change_phone_number_title'); ?>');
    $("#verifyPhone").html(`
        <div style="padding: 10px 0 15px;">
            <label><?php echo l('phone_number'); ?>: <span style="font-weight: bold;"><?php echo $g_user['phone']; ?></span></label>
        </div>
        <div class="form-group-half">
            <input type="number" id="join_phone_number" class="inp phone" maxlength="11" placeholder="<?php echo l('placeholder_phone'); ?>">
            <input type="hidden" name="phone" id="full_phone_number"/>
        </div>
        <button type="button" id="cNumber_submit" onclick="return Profile.changePhoneNumber()" style="padding: 0 15px;text-transform: capitalize" class="btn small turquoise" disabled><?php echo l('change'); ?></button>

        <div style="padding: 10px 0 15px;">
            <button type="button" onclick="return backToverify()" style="padding: 0 15px;text-transform: capitalize;background: transparent;color: #888;border: 1px solid #ddd;" class="btn small"><?php echo l('cancel'); ?></button>
        </div>
    `);

    $(function(){

        // phone number
        var phone_number = window.intlTelInput(document.querySelector("#join_phone_number"), {
            separateDialCode: true,
            preferredCountries:["bd","us","gb","sa","ae"],
            hiddenInput: "full",
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.3/build/js/utils.js"
        })
        document.getElementById('join_phone_number').onkeyup = function(){
            var full_number = phone_number.getNumber(intlTelInputUtils.numberFormat.E164);
            $("#full_phone_number").val(full_number);

            var submitButton = $('#cNumber_submit');
            
            if(full_number === "<?php echo $g_user['phone']; ?>") {
                submitButton.prop('disabled', true);
                return false;
            }

            if (full_number.length === 14) {
                submitButton.prop('disabled', false);
            } else {
                if (full_number.length > 14) {
                    // Truncate the full_number to 6 characters
                    full_number = full_number.substring(0, 14);
                    // Update the input value
                    $('#full_phone_number').val(full_number);
                }

                submitButton.prop('disabled', full_number.length !== 14);
            }
        }
    })
}*/
function check_vnumber(code) {
    var submitButton = $('#vcode_submit');

    if (code.length === 6) {
        submitButton.prop('disabled', false);
    } else {
        if (code.length > 6) {
            // Truncate the code to 6 characters
            code = code.substring(0, 6);
            // Update the input value
            $('#verification_code').val(code);
        }

        submitButton.prop('disabled', code.length !== 6);
    }
}
</script>