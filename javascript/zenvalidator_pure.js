(function ($) {
    // Add a remote validator that reads error response text and stores it on field instance
    window.Parsley.addAsyncValidator("zenRemote", function (xhr) {
        if (xhr.status >= 400 && xhr.status <= 499) {
            if (xhr.responseText && xhr.responseText.length < 255) {
                this.options["remoteMessage"] = xhr.responseText;
            }
            return false;
        }
        this.options["remoteMessage"] = null;
        return true;
    });
    // Attach parsley to forms
    $("form.parsley").each(function () {
        // Initialize Parsley for this form
        $(this).parsley({
            excluded:
                "input[type=button], input[type=submit], input[type=reset], input[type=hidden], .ignore-validation",
            errorsContainer: function (el) {
                return el.$element.closest(".field");
            },
            // include focusout to make sure copy paste is properly validated
            triggerAfterFailure: "input focusout",
        });
    });
    // Bypass validation on hidden fields by Display Logic
    // TODO: check compatibility with recent versions
    window.Parsley.on("field:validated", function (fieldInstance) {
        if (
            fieldInstance.$element.hasClass("display-logic") &&
            fieldInstance.$element.is(":hidden")
        ) {
            fieldInstance._ui.$errorsWrapper.css("display", "none");
            fieldInstance.validationResult = true;
            return true;
        }
    });
})(jQuery);
