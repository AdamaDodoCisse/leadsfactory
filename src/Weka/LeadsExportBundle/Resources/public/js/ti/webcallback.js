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
                jQuery('#di-msg').hide();
                webcallback.step = 'call';
            }else{
                jQuery('#callback-step2').hide();
                jQuery('#callback-step3').hide();
                jQuery('#di-msg').show();
                var redirect_url = webcallback.getRedirectUrl(); console.log(redirect_url);
                jQuery('#di-msg #di-link').click(function(){
                    window.location.href = redirect_url;
                });
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
    },
    getRedirectUrl: function(){
        var params = 'salutation='+jQuery('#lffield\\[twilio_validation\\]').val();
        params += '&lastName='+jQuery('#lffield\\[lastName\\]').val();
        params += '&lastName='+jQuery('#lffield\\[firstName\\]').val();
        params += '&lastName='+jQuery('#lffield\\[pays\\]').val();
        params += '&lastName='+jQuery('#lffield\\[utmcampaign\\]').val();
        params += '&lastName='+jQuery('#lffield\\[product_sku\\]').val();
        params += '&lastName='+jQuery('#lffield\\[product_name\\]').val();
        params += '&lastName='+jQuery('#lffield\\[comment\\]').val();
        params += '&lastName='+jQuery('#lffield\\[thematique\\]').val();
        params += '&lastName='+jQuery('#lffield\\[utmsource\\]').val();
        params += '&lastName='+jQuery('#lffield\\[utmmedium\\]').val();
        params += '&lastName='+jQuery('#lffield\\[utmcontent\\]').val();

        return encodeURI('/information-request.html?'+params);
    }
};
