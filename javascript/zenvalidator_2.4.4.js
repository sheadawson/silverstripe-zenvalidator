(function ($) {
    // Add a remote validator that reads error response text and stores it on field instance
    window.Parsley.addAsyncValidator('zenRemote', function (xhr) {
        if (xhr.status >= 400 && xhr.status <= 499) {
            if (xhr.responseText && xhr.responseText.length < 255) {
                this.options['remoteMessage'] = xhr.responseText;
            }
            return false;
        }
        this.options['remoteMessage'] = null;
        return true;
    });
    // Attach parsley to forms
    $.entwine('ss.zenvalidator', function ($) {
        $('form.parsley').entwine({
            onmatch: function () {
                // Fix validation for optionset based class (attributes not set on child options)
                $(this).find('.optionset').each(function () {
                    var attrs = $.makeArray(this.attributes);
                    var parsley = {};
                    for (var i = 0; i < attrs.length; i++) {
                        var att = attrs[i];
                        if (att.name.indexOf('parsley-') !== -1) {
                            parsley[att.name] = att.value;
                        }
                    }
                    $(this).find('input').attr(parsley);

                    // Clean attributes
                    var attributes = this.attributes;
                    var keys = $.map(attributes, function (key, value) {
                        return key.name;
                    });
                    for (var i = 0; i < keys.length; i++) {
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
    });
})(jQuery);