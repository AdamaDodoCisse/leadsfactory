baseUrl = 'http://preprod.weka.fr/leads-factory';

var webcallback = {
    step: 'init',
    phoneUtil: i18n.phonenumbers.PhoneNumberUtil.getInstance(),
    PNF: i18n.phonenumbers.PhoneNumberFormat,
    countryCode: '',
    validationCodeIsCorrect: 0,
    init: function(){
        jQuery('#callback-form').submit(function(e){ e.preventDefault();

            switch(webcallback.step){
                case 'init':
                    break;
                case 'call':
                    webcallback.call();
                    break;
                case 'check':
                    webcallback.check();
                    break;
                case 'send':
                    jQuery('#callback-form').submit();
                    break;
            }
        });
        jQuery('#lffield\\[pays\\]').change(function(){
            if(jQuery.inArray(jQuery(this).val(), ['FR', 'BE', 'LU', 'CH', 'MC']) !== -1){
                jQuery('#callback-step2').show();
                webcallback.step = 'call';
            }else{
                jQuery('#callback-step2').hide();
                webcallback.step = 'init';
            }
        });
    },
    call: function(){
        jQuery('#callback-step3').show();
        window.setTimeout(function () {
            jQuery('#webcallback-calling').hide();
            jQuery('#webcallback-new-call').show();
        }, 15000);
        jQuery.ajax({
            url: baseUrl+'/web/twilio/call',
            data: {phone: jQuery('#lffield\\[phone\\]').val()},
            success: function(response){alert(response)}
        })
        this.step = 'check';
    },
    newCall: function(){
        this.call();
    },
    check: function(){
        var code = jQuery('#lffield\\[twilio_validation\\]').val();
        jQuery.ajax({
            url: baseUrl+'/web/twilio/validate',
            data: {code: code},
            success: function(response){
                webcallback.validationCodeIsCorrect = response.valid;
                alert(response.valid);
                //webcallback.post();
            },
            dataType: 'json'
        });
    },
    post: function(){

    }


};

jQuery(document).ready(function($){
    webcallback.init();
    $('#webcallback-new-call').on('click', function (e) {
        webcallback.newCall();
    });
});






