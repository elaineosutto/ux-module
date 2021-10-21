define([
    'jquery',
    'jquery/ui',
    'TheITNerd_UX/js/inputMask',
    'mage/translate'
], function ($) {
    'use strict';

    $.widget('theitnerd.cpfCnpjField', {
        options: {
            cnpj: true,
            cpf: true,
            masks: {
                cpf: '000.000.000-00',
                cnpj: '00.000.000/0000-00'
            },
            parent: 'div.field',
            label: 'label.label > span'
        },
        _create: function () {

            this.options.uniqueID = Math.random().toString(36).substr(2, 9);
            this.options.parentElement = this.element.closest(this.options.parent);
            this.options.labelElement = this.options.parentElement.find(this.options.label);

            if (!this.options.cpf || !this.options.cnpj) {
                if(this.options.cpf) {
                    this.changeField(false);
                }

                if (this.options.cnpj) {
                    this.changeField(true);
                }
            } else {
                this.createCnpjCheckbox();
            }
        },

        createCnpjCheckbox: function () {
            let html = '<div class="cnpj-checkbox"><label><input type="checkbox" id="' + this.options.uniqueID + '"/> ' + $.mage.__('Use CNPJ') + '</label></div>';
            this.options.parentElement.append(html);

            let self = this;

            $('#' + this.options.uniqueID).on('click', function () {
                self.changeField($(this).is(':checked'));
            });

            this.changeField(false);
        },

        changeField: function (isCNPJ) {

            if(typeof this.element.data('theitnerdInputmask') != 'undefined') {
                this.element.data('theitnerdInputmask').remove();
            }

            if (isCNPJ) {
                this.element.inputmask({mask: this.options.masks.cnpj});
                this.element.removeClass('validate-cpf');
                this.element.addClass('validate-cnpj')
                this.options.labelElement.html($.mage.__('CNPJ'))
            } else {
                this.element.inputmask({mask: this.options.masks.cpf});
                this.element.removeClass('validate-cnpj')
                this.element.addClass('validate-cpf');
                this.options.labelElement.html($.mage.__('CPF'))
            }
        }
    });

    return $.theitnerd.cpfCnpjField;
});
