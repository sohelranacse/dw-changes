<div id="<?php echo $cmd; ?>" class="pp_popup_editor visible">
<form id="frm_change_phone_number" name="frm_change_phone_number" method="POST">
    <input type="hidden" name="cmd" value="add_phone_number" />
    <input type="hidden" name="e_user_id" value="<?php echo $e_user_id; ?>" />
    <div class="bl arrow">
        <div class="title"><?php echo l('change_phone_number_title'); ?>
        <div class="cl"></div>
        </div>
    </div>

    <div class="bl_frm verify_phone_number_form" id="verifyPhone">
        
        <div class="bl" style="margin-bottom: 10px">
            <label><?php echo l('phone_number'); ?>: <span style="font-weight: bold;"><?php echo $g_user['phone']; ?></span></label>
        </div>
        <div class="bl" style="display: flex;justify-content: space-between;">
            <div class="field" style="width: 60%">
                <input type="number" id="join_phone_number" class="inp phone" maxlength="11" placeholder="<?php echo l('placeholder_phone'); ?>">
                <input type="hidden" name="phone" id="full_phone_number"/>
            </div>
            <button type="button" id="cNumber_submit" onclick="return clProfile.changePhoneNumber()" style="padding: 0 15px;text-transform: capitalize;width: 37%" class="btn small turquoise" disabled><?php echo l('change'); ?></button>
        </div>

    </div>
    <div class="frm_btn frm_edit">
        <div class="double">
            <span class="l">
                <!-- <button type="submit" id="profile_field_save" class="btn small pink frm_editor_save"><?php // echo $save; ?></button> -->
            </span>
            <span class="r" id="cancelButton">
                <button type="button" onclick="return clProfile.loadTabs('#tabs-1');" id="pp_profile_looking_cancel" class="btn small white_frame frm_editor_cancel"><?php echo l('back'); ?></button>
            </span>
        </div>
    </div>
</form>
</div>

<script>
$(function(){

    // phone number
    var phone_number = window.intlTelInput(document.querySelector("#join_phone_number"), {
        separateDialCode: true,
        preferredCountries:["bd","us","gb","sa","ae"],
        hiddenInput: "full",
        utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.3/build/js/utils.js"
    });
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