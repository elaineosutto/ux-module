var config = {
    map: {
        '*': {
            'Magento_Ui/js/lib/validation/rules':'TheITNerd_UX/js/lib/validation/rules',
            'Magento_PageBuilder/js/content-type/slider/appearance/default/widget':'TheITNerd_UX/js/content-type/slider/appearance/default/widget'
        }
    },
    config: {
        mixins: {
            'mage/validation' : {
                'TheITNerd_UX/js/validation-mixin': true
            }
        }
    }
}
