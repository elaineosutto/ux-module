define([
    'jquery',
    'mage/url',
    'Magento_Customer/js/customer-data',
    'mage/validation',
    'mage/cookies'
], function ($, url, customerData) {
    'use strict';

    return function (config, element) {
        class Contact {
            constructor(element) {
                this.element = element;
                this.form = this.element.find('form');
                this.action = this.element.find('.actions-toolbar .action-send');

                let self = this;

                this.form.mage('validation', {
                    ignore: ':hidden'
                });


                this.action.on('click', function () {
                    if (self.form.validation('isValid')) {
                        let formData = new FormData(self.form[0]);

                        formData.append('form_key', $.mage.cookies.get('form_key'));

                        self.element.trigger('processStart');

                        fetch(url.build('rest/V1/sendmail'), {
                            method: 'POST',
                            body: formData
                        }).then(function () {
                            customerData.reload(['messages']);
                            self.element.trigger('processStop');
                        });
                    }
                });
            }
        }

        return new Contact($(element));
    }
})
