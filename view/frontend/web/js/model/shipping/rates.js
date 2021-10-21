define([
    'ko'
], function(ko) {
    'use strict';

    return {
        rates: ko.observable([]),
        isLoading: ko.observable(false)
    }
});
