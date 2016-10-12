(function () {
    window.Parsley.addValidator('notequalto', {
        validateString: function (value, refOrValue) {
            var $reference = $(refOrValue);
            if ($reference.length)
                return value !== $reference.val();
            else
                return value !== refOrValue;
        },
        priority: 256
    });
})();