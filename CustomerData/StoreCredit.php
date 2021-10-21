<?php

namespace TheITNerd\UX\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\CustomerBalance\Model\Balance;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

/**
 * Class RewardPoints
 * @package TheITNerd\UX\CustomerData
 */
class StoreCredit implements SectionSourceInterface
{
    /**
     * @var Balance
     */
    private Balance $balance;

    /**
     * @var CurrentCustomer
     */
    private CurrentCustomer $currentCustomer;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var PriceHelper
     */
    private PriceHelper $priceHelper;


    /**
     * @param Balance $balance
     * @param CurrentCustomer $currentCustomer
     * @param StoreManagerInterface $storeManager
     * @param PriceHelper $priceHelper
     */
    public function __construct(
        Balance $balance,
        CurrentCustomer $currentCustomer,
        StoreManagerInterface $storeManager,
        PriceHelper $priceHelper
    )
    {
        $this->balance = $balance;
        $this->currentCustomer = $currentCustomer;
        $this->storeManager = $storeManager;
        $this->priceHelper = $priceHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        if (!$this->currentCustomer->getCustomerId()) {
            return [];
        }

        try {
            $this->balance->setCustomerId($this->currentCustomer->getCustomerId());
            $this->balance->setWebsiteId($this->storeManager->getWebsite()->getId());
            $balance = $this->balance->loadByCustomer();
        } catch (\Exception $e) {
            //Here we have not found store credit for this session
            return [
                'error' => $e->getMessage()
            ];
        }

        return [
            'balance' => $this->priceHelper->currency($balance->getAmount(), true, false)
        ];
    }
}
