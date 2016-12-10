/*
 * jQuery Auto ajax loader indicator v1.0
 * Autor: Yuriy Antokhin
 * Mail: yuriyant@gmail.com
 * Script to automatically indication ajax requests
 * Requires: jQuery v1.7+
 */
+function($) {
    "use strict";
    var $this,
            requests = {};

    var AjaxIndicator = function(options) {
        this.options = this.getParams(options)
        this.createTpl();
        publicMethod.on();
    }
    // Default options
    // =======================
    AjaxIndicator.DEFAULTS = {
        displayError: 1000
                , template: '<div id="atpl"><div class="overlay"></div><span id="aiMsg" class="imgLoader"> Loading...</span></div>'
                , idTpl: 'atpl'
                , idMsg: 'aiMsg'
    }
    AjaxIndicator.prototype = {
        constructor: AjaxIndicator,
        // Helper functions
        // =======================
        createTpl: function() {
            !$('#' + this.options.idTpl).length
                    ?
                    $('body').append($(this.options.template).css('display', 'none'))
                    : ''
        },
        error: function(error, options) {
            var status = 0;
            options = $this.getParams(options);
            if (('aiMask' in options)) {
                if ($('#' + options.aiMask + ' .aimask').is(":visible")) {
                    status = options.displayError;
                }
            } else {
                if (options.error && $('#' + options.idTpl).is(":visible")) {
                    status = options.displayError;
                }
            }
            if (status) {
                $('#' + options.idMsg)
                        .removeClass('imgLoader')
                var oldContent = publicMethod.getMsg(options);
                publicMethod.setMsg('<b>Error</b>: ' + error, options)
                setTimeout(function() {
                    $this.hide(options)
                    publicMethod.setMsg(oldContent, options);
                    $('#' + options.idMsg).addClass('imgLoader')
                }, options.displayError);
            } else {
                $this.hide(options)
            }
        },
        getLoader: function(options) {
            options = $this.getParams(options);
            var selector = '#' + options.idTpl;
            return ($(selector).length) ? $(selector) : $(selector, window.parent.document);
        },
        getParams: function(options) {
            options = $.extend({}, this.getDefaults(), options)
            $.each(options, function(key, v) {
                if ($.isFunction(options[key])) {
                    options[key] = options[key]()
                }
            });
            return options
        },
        getDefaults: function() {
            return $.extend({},AjaxIndicator.DEFAULTS,this.options)
        },
        getXHRParams: function(jqXHR) {
            return (jqXHR == undefined || !("aiParams" in jqXHR)) ? this.getParams() : jqXHR.aiParams;
        },
        hide: function(options) {
            options = $this.getParams(options);
            var idRequest = $this.getIdRequest(options)
            if (requests[idRequest] == undefined) {
                return;
            }
            requests[idRequest].shift() // -1 request
            if (!requests[idRequest].length) { // if all group request done
                !("aiMask" in options) ? publicMethod.hide(options) : publicMethod.unmask(options);
            }
        },
        getIdRequest: function(options) {
            return ('aiMask' in options) ? options.aiMask : options.idTpl;
        },
        isDisable: function(ajaxSettings) {
            if (ajaxSettings == undefined) {
                return false;
            }
            if (('data' in ajaxSettings)
                    && (typeof ajaxSettings.data == 'string') && ajaxSettings.data.search('aiDisable') != -1) {
                return true;
            }
            if (('url' in ajaxSettings)
                    && (typeof ajaxSettings.url == 'string') && ajaxSettings.url.search('aiDisable') != -1) {
                return true;
            }
        }
    }



    // Public functions
    // Usage format: 
    // $.ai.hide();     
    // =======================

    var publicMethod = $['ai'] = function(options) {
        if (options) {
            $this = new AjaxIndicator(options);
        }
        return this;
    };
    publicMethod.mask = function(options) {
        if ($('#' + options.aiMask + ' .aimask').length) {
            return;
        }
        var $maskedElement = $('#' + options.aiMask)
        if ($maskedElement.css("position") == "static") {
            $maskedElement.addClass("aimasked-relative");
        }
        $maskedElement.addClass("aimasked")
                .append('<div class="aimask"></div>');
        var content = publicMethod.getMsg(options)
        if (('aiMaskmsg' in options)) {
            content = options.aiMaskmsg
        }
        var maskMsgDiv = $('<div class="aimask-msg">' + content + '</div>');
        $maskedElement.append(maskMsgDiv);
        var totalWidth = maskMsgDiv.outerWidth(true);
        var totalHeight = maskMsgDiv.outerHeight(true);
        //calculate center position
        maskMsgDiv.css("top", Math.round($maskedElement.height() / 2 - totalHeight / 2) + "px")
                .css("left", Math.round($maskedElement.width() / 2 - totalWidth / 2) + "px")
                .fadeIn('fast');
    }
    publicMethod.unmask = function(options) {
        $('#' + options.aiMask)
                .removeClass('aimasked-relative aimasked aimask')
                .find('.aimask, .aimask-msg')
                .remove();
    }
    publicMethod.getMsg = function(options) {
        options = $this.getParams(options);
        return $($this.getLoader(options)).find('#' + options.idMsg).html()
    }
    publicMethod.setMsg = function(content, options) {
        options = $this.getParams(options);
        !('aiMask' in options) ?
                $($this.getLoader(options)).find('#' + options.idMsg).html(content)
                :
                $('#' + options.aiMask + ' .aimask-msg').html(content)

    }
    publicMethod.hide = function(options) {
        $($this.getLoader($this.getParams(options))).hide()
    }
    publicMethod.show = function(options) {
        options = $this.getParams(options);
        ("aiMask" in options) ?
                publicMethod.mask(options)
                :
                $($this.getLoader(options)).show(10)

    }
    publicMethod.off = function() {
        $(document).off('.ai')
    }
    publicMethod.on = function() {
        publicMethod.off();
        $(document).on('ajaxSend.ai', function(event, jqXHR, ajaxSettings) {
            if (dataAPIOPTIONS) {
                jqXHR = $.extend(jqXHR, {aiParams: $this.getParams(dataAPIOPTIONS)});
                dataAPIOPTIONS = false;
            } else {
                jqXHR = $.extend(jqXHR, {aiParams: $this.options});
            }
            if (!$this.isDisable(ajaxSettings) && !('aiDisable' in jqXHR.aiParams)) {
                var idRequst = $this.getIdRequest(jqXHR.aiParams);
                if (requests[idRequst] == undefined) {
                    requests[idRequst] = [];
                }
                requests[idRequst].push(jqXHR);
                publicMethod.show(jqXHR.aiParams)
            }
        }).on('ajaxError.ai', function(event, jqXHR, ajaxSettings) {
            jqXHR.aiParams.error = true; //Create marker for skip other event end ajax               
            $this.error(jqXHR.statusText, $this.getXHRParams(jqXHR));
        }).on('ajaxComplete.ai', function(event, jqXHR, ajaxSettings) {
            !jqXHR.aiParams.error ? $this.hide($this.getXHRParams(jqXHR)) : jqXHR.aiParams.error = false;
        })
    }


    // AjaxIndicator PLUGIN DEFINITION
    // =======================

    var old = $.ai

    $.ai.Constructor = AjaxIndicator

    var setActiveParamsDATAAPI = function(e) {
        e.preventDefault()
        dataAPIOPTIONS = $this.getParams($.extend({}, $(e.target).data(), {error: false}, {target: e.target}));
    }

    // AjaxIndicator NO CONFLICT
    // =================

    $.ai.noConflict = function() {
        $.ai = old
        return this
    }
    $.fn.ai = function(options) {
        return this.each(function() {
            var $el = $(this),
                    data = $el.data()
            $el.data($.extend({}, options, data))
                    .on('click.ai-api', function(e) {
                setActiveParamsDATAAPI(e)
            })
        })
    }
    var dataAPIOPTIONS;
    $(function() {
        //DATA-API
        $('[data-id-tpl], [data-id-msg], [data-ai-mask], [data-ai-maskmsg], [data-ai-disable]', document)
                .on('click.ai-api, keyup.ai-api, ', function(e) {
            setActiveParamsDATAAPI(e)
        })
        // Auto run
        // ==============
        $this = new AjaxIndicator();
    })
}(window.jQuery);


