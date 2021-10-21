<?php
namespace TheITNerd\UX\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Directory\Model\Country;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 * @package TheITNerd\UX\Helper
 */
class Data extends AbstractHelper
{

    public const NEWSLETTER_PRIVACY_POLICY_TEXT_CONFIG_PATH = 'theitnerd_newsletter/privacy_policy/privacy_policy_text';
    public const NEWSLETTER_PRIVACY_POLICY_ACTIVE_CONFIG_PATH = 'theitnerd_newsletter/privacy_policy/active';

    /**
     * @var Country
     */
    private Country $country;

    /**
     * Data constructor.
     * @param Context $context
     * @param Country $country
     */
    public function __construct(
        Context $context,
        Country $country
    )
    {
        $this->country = $country;
        parent::__construct($context);
    }

    /**
     * @param ProductInterface $product
     * @return string
     */
    public function getProductRoundel(ProductInterface $product): string
    {
        $html = $this->getPromoRoundel($product);

        if(!empty($html)){
            return $html;
        }

        return $this->getTextRoundel($product);

    }

    /**
     * @param ProductInterface $product
     * @return string
     */
    private function getTextRoundel(ProductInterface $product) : string
    {
        $html = '';

        $roundelText = $product->getData('roundel_text');
        $roundelColor = $product->getData('roundel_color') ?? '#ffffff';
        $roundelBgColor = $product->getData('roundel_bg_color') ?? '#ff0000';

        if(!empty($roundelText)) {
            $html = "<div class='discount-roundel' style='background: {$roundelBgColor}; color: {$roundelColor}'>{$roundelText}</div>";
        }

        return $html;
    }

    /**
     * @param ProductInterface $product
     * @return string
     */
    private function getPromoRoundel(ProductInterface $product) : string
    {
        $html = "";

        $price = $product->getPrice();
        $finalPrice = $product->getFinalPrice();
        if ($price > $finalPrice) {
            $discount = (int)((1 - (($finalPrice) / $price)) * 100);
            $html = "<div class='discount-roundel'>{$discount}%</div>";

        }

        return $html;
    }

    /**
     * @param string $countryCode
     * @return array
     */
    public function getRegions(string $countryCode = 'BR'): array
    {
        return $this->country
                ->loadByCode($countryCode)
                ->getRegions()
                ->loadData()
                ->toArray()['items'] ?? [];
    }

    /**
     * @return string
     */
    public function getNewsletterPrivacyPolicyText(): string
    {
        return $this->scopeConfig->getValue(self::NEWSLETTER_PRIVACY_POLICY_TEXT_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    /**
     * @return bool
     */
    public function getNewsletterPrivacyPolicyStatus(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::NEWSLETTER_PRIVACY_POLICY_ACTIVE_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
