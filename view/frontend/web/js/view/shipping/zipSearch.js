define([
    'uiComponent',
    'ko',
    'mage/url',
    'underscore',
    'jquery',
    'loader'
], function (Component, ko, url, _, $) {
    'use strict';

    return Component.extend({
        result: ko.observable([]),

        initialize: function () {
            this._super();

            let options = {
                zipFieldSelector: "#product-shipping-postcode",
                zipButtonSelector: "#product-shipping-button",
                zipSearchFormSelector: "#zipsearch-form"
            };

            this.options = {...options, ...this.options};

            this.selectZip.bind(this);
            this.searchData.bind(this);
        },

        hasData: function () {
            return this.result().length > 0;
        },

        searchData: function (ui, e) {
            e.preventDefault();

            let self = this;
            let form = $(this.options.zipSearchFormSelector);
            form.loader();

            $.ajax({
                type: 'GET',
                url: url.build('rest/V1/searchZIP'),
                data: $(e.currentTarget).serialize(),
                beforeSend: function () {
                    form.loader('show');
                },
                success: function (data) {
                    if(typeof data == 'object') {
                        _.each(data, function(item) {
                            item.options = self.options;
                        });

                        self.result(data);
                    }
                    form.loader('hide');
                },
                error: function () {
                    form.loader('hide');
                }
            });

            return false;
        },

        selectZip: function(address, e) {
            $(this.options.zipFieldSelector).val(address.cep);
            $(this.options.zipButtonSelector).click();
            $(this.options.zipSearchFormSelector).closest('.modal-popup').find('.action-close').click();

        }

    });
});
