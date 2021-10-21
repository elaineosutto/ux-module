define([
    'jquery',
    'jquery/ui',
    'TheITNerd_UX/js/lib/mask'
], function ($) {
   'use strict';

   $.widget('theitnerd.inputmask', {
        options: {},
       _create: function () {

            if(this.options.hasOwnProperty('mask')) {
                this.element.mask(this.options.mask);

                if(this.options.hasOwnProperty('placeholder')) {
                    this.element.attr('placeholder', this.options.placeholder);
                } else {
                    this.element.attr('placeholder', this.options.mask.replace(/\d/g,'_'));
                }
            }
       },
       remove: function (options) {
           this.element.data('mask').remove();
           this.element.removeAttr('placeholder');
           this.destroy();
       }
   });

   return $.theitnerd.inputmask;
});
