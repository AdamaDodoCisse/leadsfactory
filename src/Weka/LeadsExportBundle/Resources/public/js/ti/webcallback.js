/**
 * Commun aux formulaires de webcallback, téléchargement d'extrait et téléchargement Riskev
 *
 * @type {{step: string, phoneUtil: *, PNF: (i18n.phonenumbers.PhoneNumberFormat|*), countryCode: string, validationCodeIsCorrect: boolean, interceptSubmit: boolean, callEnabled: boolean, callCounter: number, maxCalls: number, formId: string, trackingOrigin: string, init: Function, call: Function, newCall: Function, check: Function, post: Function, isCallEnabled: Function, isAllowedCountry: Function, getRedirectUrl: Function, setTrackingOrigin: Function, displayNumberExample: Function}}
 */
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
    trackingOrigin: '',
    init: function(form_id){
        this.formId = form_id;
        this.trackingOrigin = this.setTrackingOrigin();
        jQuery('#'+this.formId).submit(function(e){

            if(!jQuery(this).validationEngine('validate')){
                e.preventDefault();
                ga('send', 'pageview', virtualDomain + location.pathname + 'vrt/editionLogiciel_'+webcallback.trackingOrigin+'_error');
                return;
            }

            /**
             * Pour le cas du téléchargement d'extrait, on autorise la validation du formulaire pour tous
             * les pays hors FR, LU, BE, etc..., sans contrôle twilio
             */
            if(webcallback.interceptSubmit &&  webcallback.isAllowedCountry()){
                e.preventDefault();
            }

            switch(webcallback.step){
                case 'call':
                    webcallback.call();
                    ga('send', 'pageview', virtualDomain + location.pathname + 'vrt/editionLogiciel_'+webcallback.trackingOrigin+'_EnterCodeDI');
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
                jQuery('#callback-submit').show();
                jQuery('#phone-prefix').html('+'+webcallback.phoneUtil.getCountryCodeForRegion(webcallback.countryCode));
                webcallback.displayNumberExample();
                webcallback.step = 'call';
                ga('send', 'pageview', virtualDomain + location.pathname + 'vrt/editionLogiciel_'+webcallback.trackingOrigin+'_EnterTelDI');
            }else{
                jQuery('#callback-step2').hide();
                jQuery('#callback-step3').hide();
                jQuery('#callback-submit').hide();
                jQuery('#phone-prefix').html('+'+webcallback.phoneUtil.getCountryCodeForRegion(webcallback.countryCode));
                jQuery('#phone-txt').hide(); // téléchargement extrait
                jQuery('#di-msg').show();
                var redirect_url = webcallback.getRedirectUrl();
                jQuery('#di-msg #di-link').click(function(){
                    window.location.href = redirect_url;
                });
                webcallback.step = 'init';
                ga('send', 'pageview', virtualDomain + location.pathname + 'vrt/editionLogiciel_'+webcallback.trackingOrigin+'_formulaireDI_Export');
            }
        });
    },
    call: function(){

        var number = this.phoneUtil.parseAndKeepRawInput(jQuery('#lffield\\[phone\\]').val(), this.countryCode);

        if(!this.phoneUtil.isValidNumberForRegion(number, this.countryCode)){
            jQuery('#lffield\\[phone\\]').validationEngine('showPrompt', 'Numéro de téléphone invalide');
        }

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

        this.formattedNumber = this.phoneUtil.format(number, this.PNF.E164);
        jQuery.ajax({
            url: baseUrl+'/web/twilio/call',
            data: {phone: this.formattedNumber},
            success: function(response){
                //alert(response)
            }
        });
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
    isAllowedCountry: function(){
        return jQuery.inArray(this.countryCode, ['FR', 'BE', 'LU', 'CH', 'MC']) !== -1;
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
    },
    setTrackingOrigin: function()
    {
        if(jQuery('#lffield\\[trackingOrigin\\]').val() == 'extract'){
            return 'extrait';
        }else if(jQuery('#lffield\\[trackingOrigin\\]').val() == 'wcb'){
            return 'webCallBack';
        }
    },
    displayNumberExample: function(){
        switch (this.countryCode)
        {
            case 'FR':
                var example = '+33 1 53 35 20 20';
                break;
            case 'BE':
                var example = '+32 2 51 XX XX X ';
                break;
            case 'MC':
                var example = '+377 92 XX XX XX';
                break;
            case 'LU':
                var example = '+352 5 46 XX X';
                break;
            case 'CH':
                var example = '+41 31 XXX XX XX';
        }
        jQuery('#phone-txt').show(); // téléchargement extrait
        jQuery('#phone-example').html(example);
    }
};
