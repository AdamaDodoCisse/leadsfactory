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
    formId: '',
    init: function(form_id){
        this.formId = form_id;
        jQuery('#'+this.formId).submit(function(e){

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
                var redirect_url = webcallback.getRedirectUrl();
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
        jQuery('#'+this.formId).submit();
    },
    isCallEnabled: function(){
        this.callCounter++;
        return this.callCounter < this.maxCalls ? true : false;
    },
    getRedirectUrl: function(){
        var params = 'salutation='+jQuery('#lffield\\[salutation\\]').val();
        params += '&lastName='+jQuery('#lffield\\[lastName\\]').val();
        params += '&firstName='+jQuery('#lffield\\[firstName\\]').val();
        params += '&pays='+jQuery('#lffield\\[pays\\]').val();
        params += '&utmcampaign='+jQuery('#lffield\\[utmcampaign\\]').val();
        params += '&product_sku='+jQuery('#lffield\\[product_sku\\]').val();
        params += '&product_name='+jQuery('#lffield\\[product_name\\]').val();
        params += '&comment='+jQuery('#lffield\\[comment\\]').val();
        params += '&thematique='+jQuery('#lffield\\[thematique\\]').val();
        params += '&utmsource='+jQuery('#lffield\\[utmsource\\]').val();
        params += '&utmmedium='+jQuery('#lffield\\[utmmedium\\]').val();
        params += '&utmcontent='+jQuery('#lffield\\[utmcontent\\]').val();

        return encodeURI('/information-request.html?'+params);
    }
};
