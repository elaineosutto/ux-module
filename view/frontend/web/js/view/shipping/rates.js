define([
    'uiComponent',
    'ko',
    'TheITNerd_UX/js/model/shipping/rates'
], function(Component, ko, ratesModel) {
    'use strict';

    return Component.extend({
        model: ratesModel,

        initialize: function() {
            this._super();
        },

        hasData: function() {
            return this.model.rates().length > 0;
        }
    });
});
