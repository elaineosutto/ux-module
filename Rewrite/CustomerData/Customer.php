<?php

namespace TheITNerd\UX\Rewrite\CustomerData;


use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Helper\View;

/**
 * Class Customer
 * @package TheITNerd\UX\Rewrite\CustomerData
 */
class Customer extends \Magento\Customer\CustomerData\Customer
{
    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var View
     */
    private $customerViewHelper;

    /**
     * @var GroupRepositoryInterface
     */
    private GroupRepositoryInterface $groupRepository;

    /**
     * @param CurrentCustomer $currentCustomer
     * @param View $customerViewHelper
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        CurrentCustomer $currentCustomer,
        View $customerViewHelper,
        GroupRepositoryInterface $groupRepository
    )
    {
        $this->currentCustomer = $currentCustomer;
        $this->customerViewHelper = $customerViewHelper;
        $this->groupRepository = $groupRepository;

        parent::__construct($currentCustomer, $customerViewHelper);
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        if (!$this->currentCustomer->getCustomerId()) {
            return [];
        }

        $customer = $this->currentCustomer->getCustomer();
        return [
            'fullname' => $this->customerViewHelper->getCustomerName($customer),
            'firstname' => $customer->getFirstname(),
            'email' => $customer->getEmail(),
            'email_hash' => md5(strtolower(trim($customer->getEmail()))),
            'group' => $this->groupRepository->getById($customer->getGroupId())->getCode(),
            'websiteId' => $customer->getWebsiteId(),
        ];
    }
}
