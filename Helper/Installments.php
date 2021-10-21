<?php
namespace TheITNerd\UX\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Directory\Model\Country;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class Installments
 * @package TheITNerd\UX\Helper
 */
class Installments extends AbstractHelper
{

    public const INSTALLMENTS_ACTIVE_CONFIG_PATH = 'theitnerd_catalog/installments/active';
    public const INSTALLMENTS_MINIMUM_INSTALLMENT_CONFIG_PATH = 'theitnerd_catalog/installments/minimum_installment';
    public const INSTALLMENTS_MAXIMUM_INSTALLMENTS_CONFIG_PATH = 'theitnerd_catalog/installments/maximum_installments';
    public const INSTALLMENTS_INTEREST_CONFIG_PATH = 'theitnerd_catalog/installments/interest';
    public const INSTALLMENTS_NO_INTEREST_INSTALLMENTS_CONFIG_PATH = 'theitnerd_catalog/installments/no_interest_installments';

    /**
     * @param string $path
     * @return mixed
     */
    protected function getConfig(string $path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param float $value
     * @return array
     */
    public function getInstallments(float $value) : array
    {
        $data = [];

        if((bool)$this->getConfig(self::INSTALLMENTS_ACTIVE_CONFIG_PATH)) {
            $minimumInstallment = (float)$this->getConfig(self::INSTALLMENTS_MINIMUM_INSTALLMENT_CONFIG_PATH);
            $maximumInstallments = (float)$this->getConfig(self::INSTALLMENTS_MAXIMUM_INSTALLMENTS_CONFIG_PATH);
            $interest = (float)$this->getConfig(self::INSTALLMENTS_INTEREST_CONFIG_PATH);
            $noInterestInstallments = (float)$this->getConfig(self::INSTALLMENTS_NO_INTEREST_INSTALLMENTS_CONFIG_PATH);

            //check maximum installments based on minimum installment value
            $minimumInstallments = floor($value / $minimumInstallment);
            if($minimumInstallments < $maximumInstallments) {
                $maximumInstallments = $minimumInstallments;
            }

            //Check maximum no interest installments based on minimum installment value
            if($minimumInstallments < $noInterestInstallments) {
                $noInterestInstallments = $minimumInstallments;
            }

            //Do installments without interests
            if($noInterestInstallments > 1) {
                for($i = 2; $i <= $noInterestInstallments; $i++) {
                    $data[] = [
                        'installment' => $i,
                        'value' => $value/$i,
                        'total' => $value,
                        'has_interest' => false
                    ];
                }
            }

            //Do installments with interests
            for($i = ($noInterestInstallments+1); $i <= $maximumInstallments; $i++) {
                $total = $value * (1 + ($interest/100)) ** $i;
                $data[] = [
                    'installment' => $i,
                    'value' => $total / $i,
                    'total' => $total,
                    'has_interest' => true
                ];
            }

        }

        return $data;
    }
}
