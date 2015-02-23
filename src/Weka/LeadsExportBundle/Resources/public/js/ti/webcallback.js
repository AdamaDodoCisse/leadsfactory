baseUrl = 'http://preprod.weka.fr/leads-factory';

var webcallback = {
    step: 'init',
    phoneUtil: i18n.phonenumbers.PhoneNumberUtil.getInstance(),
    PNF: i18n.phonenumbers.PhoneNumberFormat,
    countryCode: '',
    validationCodeIsCorrect: false,
    interceptSubmit: true,
    callEnabled: true,
    callCounter: 0,
    maxCalls: 3,
    init: function(){
        jQuery('#callback-form').submit(function(e){

            if(!jQuery(this).validationEngine('validate')){
                e.preventDefault();
                return;
            }

            if(webcallback.interceptSubmit){
                e.preventDefault();
            }

            switch(webcallback.step){
                case 'call':
                    webcallback.call();
                    break;
                case 'check':
                    webcallback.check();
                    break;
                default:
                    break;
            }
        });
        jQuery('#lffield\\[pays\\]').change(function(){
            webcallback.countryCode = jQuery(this).val();
            if(jQuery.inArray(webcallback.countryCode, ['FR', 'BE', 'LU', 'CH', 'MC']) !== -1){
                jQuery('#callback-step2').show();
                webcallback.step = 'call';
            }else{
                jQuery('#callback-step2').hide();
                webcallback.step = 'init';
            }
        });
    },
    call: function(){
        if(!this.isCallEnabled()){
            jQuery('#callback-step2').hide();
            jQuery('#callback-step3').hide();
            return;
        }
        jQuery('#callback-step3').show();
        window.setTimeout(function () {
            jQuery('#webcallback-calling').hide();
            jQuery('#webcallback-new-call').show();
        }, 15000);
        var number = this.phoneUtil.parseAndKeepRawInput(jQuery('#lffield\\[phone\\]').val(), this.countryCode);
        this.formattedNumber = this.phoneUtil.format(number, this.PNF.E164);
        jQuery.ajax({
            url: baseUrl+'/web/twilio/call',
            data: {phone: this.formattedNumber},
            success: function(response){
                //alert(response)
            }
        })
        this.step = 'check';

    },
    newCall: function(){
        jQuery('#webcallback-calling').show();
        jQuery('#webcallback-new-call').hide();
        this.call();
    },
    check: function(){
        var code = jQuery('#lffield\\[twilio_validation\\]').val();
        jQuery.ajax({
            url: baseUrl+'/web/twilio/validate',
            data: {code: code},
            success: function(response){//alert(response.validate);
                webcallback.validationCodeIsCorrect = response.validate;
                if(webcallback.validationCodeIsCorrect){
                    webcallback.interceptSubmit = false;
                    webcallback.step = 'post';
                    webcallback.post();
                }else{
                    jQuery('#lffield\\[twilio_validation\\]').validationEngine('showPrompt', "Ce code n'est pas valide");
                }
            },
            dataType: 'json'
        });
    },
    post: function(){
        jQuery('#callback-form').submit();
    },
    isCallEnabled: function(){
        this.callCounter++;
        return this.callCounter < this.maxCalls ? true : false;
    }
};
