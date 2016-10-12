(function () {
    var parseRequirement = function (requirement) {
        if (isNaN(+requirement))
            return parseFloat($(requirement).val());
        else
            return +requirement;
    };


    // Greater than validator
    window.Parsley.addValidator('gt', {
        validateString: function (value, requirement) {
            return parseFloat(value) > parseRequirement(requirement);
        },
        priority: 32
    });

    // Greater than or equal to validator
    window.Parsley.addValidator('gte', {
        validateString: function (value, requirement) {
            return parseFloat(value) >= parseRequirement(requirement);
        },
        priority: 32
    });

    // Less than validator
    window.Parsley.addValidator('lt', {
        validateString: function (value, requirement) {
            return parseFloat(value) < parseRequirement(requirement);
        },
        priority: 32
    });

    // Less than or equal to validator
    window.Parsley.addValidator('lte', {
        validateString: function (value, requirement) {
            return parseFloat(value) <= parseRequirement(requirement);
        },
        priority: 32
    });

})();