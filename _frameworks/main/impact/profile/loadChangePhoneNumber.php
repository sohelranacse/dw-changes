<form id="frm_add_phone_number" name="frm_add_phone_number" method="POST">
    <input type="hidden" name="cmd" value="add_phone_number" />
    <input type="hidden" name="e_user_id" value="<?php echo $e_user_id; ?>" />

    <div class="formdiv verify_phone_number_form" id="verifyPhone">
        <div style="padding: 10px 0 15px;">
            <label><?php echo l('phone_number'); ?>: <span style="font-weight: bold;"><?php echo $g_user['phone']; ?></span></label>
        </div>

        <div class="form-group-half">
            <input type="number" id="join_phone_number" class="inp phone" maxlength="11" placeholder="<?php echo l('placeholder_phone'); ?>">
            <input type="hidden" name="phone" id="full_phone_number"/>
        </div>
        <button type="button" id="cNumber_submit" onclick="return Profile.submit_change_phone_number()" style="padding: 0 15px;text-transform: capitalize" class="btn small turquoise" disabled><?php echo l('change'); ?></button>
    </div>
</form>
<script type="text/javascript">
	$(function(){

        // phone number
        var phone_number = window.intlTelInput(document.querySelector("#join_phone_number"), {
            separateDialCode: true,
            preferredCountries:["bd","us","gb","sa","ae"],
            hiddenInput: "full",
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.3/build/js/utils.js"
        })
        document.getElementById('join_phone_number').onkeyup = function(){
            enforceMaxLength(this);

            var full_number = phone_number.getNumber(intlTelInputUtils.numberFormat.E164);
            $("#full_phone_number").val(full_number);

            var submitButton = $('#cNumber_submit');

            if(validatePhoneNumberOTP(full_number)) {
                submitButton.prop('disabled', false);
            } else {
                submitButton.prop('disabled', true);
            }
        }
        function enforceMaxLength(input) {
            var myVal = input.value;
            var maxLength = myVal.startsWith('0') ? 11 : 10;

            if (input.value.length > maxLength) {
                input.value = input.value.slice(0, maxLength);
            }
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
    })
</script>