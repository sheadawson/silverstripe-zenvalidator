(function($) {
    $.entwine('ss.zenvalidator', function($) {
        $('form.parsley').entwine({
            onmatch: function() {
                    // Fix validation for optionset based class (attributes not set on child options)
                    $(this).find('.optionset').each(function() {
                        var attrs = $.makeArray(this.attributes);
                        var parsley = {};
                        for(var i=0; i < attrs.length; i++) {
                            var att = attrs[i];
                            if(att.name.indexOf('parsley-') !== -1) {
                                parsley[att.name] = att.value;
                            }
                        }
                        $(this).find('input').attr(parsley);

                        // Clean attributes
                        var attributes = this.attributes;
                        var keys = $.map(attributes , function(key, value) {
                            return key.name;
                        });
                        for(var i = 0; i < keys.length; i++) {
                            if (keys[i].indexOf('data-parsley') === 0) {
                                $(this).removeAttr(keys[i]);
                            }
                        }
                        $(this).removeData();
                    });

                    $(this).parsley({
                            excluded: 'input[type=button], input[type=submit], input[type=reset], input[type=hidden], .ignore-validation',
                            errorsContainer: function (el) {
                                return el.$element.closest(".field");
                            }
                    });
            }
        });

        // Listen for error message when doing remote validation
        // See issue https://github.com/guillaumepotier/Parsley.js/issues/560
        $.listen('parsley:field:error', function(fieldInstance) {
            if(!fieldInstance._xhr) {
                return;
            }
            if(fieldInstance._xhr.status < 400 || fieldInstance._xhr.status > 499) {
                return;
            }
            fieldInstance.options['remoteMessage'] = fieldInstance._xhr.responseText;
        });

        // Add action to submitted form for parsley.remote
        // See issue https://github.com/guillaumepotier/Parsley.js/issues/826
        var submitActor = null;
        $('form.parsley [type=submit]').click(function() {
            submitActor = $(this);
        });
        $.listen('parsley:form:success', function(formInstance) {
            if(submitActor) {
                formInstance.$element.find('input.parsley-submit-actor').remove();
                formInstance.$element.append('<input class="parsley-submit-actor" type="hidden" name="'+submitActor.attr('name')+'" value="'+submitActor.attr('value')+'" />');
            }
            // If no action is specified, default action (the first) is used by Silverstripe (eg: if form is submitted with enter)
        });

        // Bypass validation on :hidden fields
        $.listen('parsley:field:validate', function(fieldInstance){
            if (fieldInstance.$element.parents('.field').is(":hidden")) {
                fieldInstance._asyncIsValidField = function() {
                    var deferred = $.Deferred();
                    return deferred.resolveWith(this);
                };
            }
        });
        $.listen('parsley:field:validated', function(fieldInstance){
            if (fieldInstance.$element.parents('.field').is(":hidden")) {
                fieldInstance._ui.$errorsWrapper.css('display', 'none');
                fieldInstance.validationResult = true;
            }
        });
    });

})(jQuery);
