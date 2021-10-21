<?php

namespace TheITNerd\UX\Plugin\Checkout;

class LayoutProcessor
{

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array                                            $jsLayout
    )
    {
        //change shipping address form
        $this->changeAddressFormFields($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']);

        //change all billing address forms for payments
        foreach ($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'] as $code => &$payment) {
            if (isset($payment['children']['form-fields']['children'])) {
                $this->changeAddressFormFields($payment['children']['form-fields']['children']);
            }
        }

        //Change checkout billing address form
        if(isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children'])) {
            $this->changeAddressFormFields($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children']);
        }

        return $jsLayout;
    }

    /**
     * @param $config
     * @return $this
     */
    protected function changeAddressFormFields(&$config): self
    {

        //Manipulate street field labels
        $config['street']['children'][0]['label'] = __('Street');
        $config['street']['children'][0]['validation']['required-entry'] = true;
        $config['street']['children'][1]['label'] = __('Number');
        $config['street']['children'][1]['validation']['required-entry'] = true;
        $config['street']['children'][2]['label'] = __('Complement');
        $config['street']['children'][3]['label'] = __('Neighborhood');
        $config['street']['children'][3]['validation']['required-entry'] = true;

        //Change postcode template
        $config['postcode']['config']['elementTmpl'] = 'TheITNerd_UX/form/element/postcode-input';

        //Change vat id template
        $config['vat_id']['config']['elementTmpl'] = 'TheITNerd_UX/form/element/vatid-input';
        $config['vat_id']['validation']['required-entry'] = true;
        $config['vat_id']['validation']['validate-cpf-cnpj'] = true;

        //Change telephone template
        $config['telephone']['config']['elementTmpl'] = 'TheITNerd_UX/form/element/telephone-input';

        //sorting shipping fields
        $config['telephone']['sortOrder'] = 45;
        $config['vat_id']['sortOrder'] = 50;
        $config['postcode']['sortOrder'] = 60;
        $config['city']['sortOrder'] = 90;
        $config['region']['sortOrder'] = 100;
        $config['country_id']['sortOrder'] = 110;

        return $this;
    }

}
