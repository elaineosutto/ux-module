<?php

namespace TheITNerd\UX\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Reward\Model\RewardFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class RewardPoints
 * @package TheITNerd\UX\CustomerData
 */
class RewardPoints implements SectionSourceInterface
{
    /**
     * @var CurrentCustomer
     */
    protected CurrentCustomer $currentCustomer;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var RewardFactory
     */
    private RewardFactory $modelFactory;


    /**
     * @param CurrentCustomer $currentCustomer
     * @param StoreManagerInterface $storeManager
     * @param RewardFactory $modelFactory
     */
    public function __construct(
        CurrentCustomer $currentCustomer,
        StoreManagerInterface $storeManager,
        RewardFactory $modelFactory
    )
    {
        $this->currentCustomer = $currentCustomer;
        $this->storeManager = $storeManager;
        $this->modelFactory = $modelFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        if (!$this->currentCustomer->getCustomerId()) {
            return [];
        }

        $websiteId = $this->storeManager->getWebsite()->getWebsiteId();
        $reward = $this->modelFactory->create();
        $reward->setCustomerId($this->currentCustomer->getCustomerId());
        $reward->setWebsiteId($websiteId);
        $reward->loadByCustomer();

        return [
            'points_balance' => $reward->getPointsBalance(),
            'currency_balance' => $reward->getFormatedCurrencyAmount()
        ];
    }
}
