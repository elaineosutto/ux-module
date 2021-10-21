<?php

namespace TheITNerd\UX\Model\Mail;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config extends \Magento\Contact\Model\Config
{
    /**
     * Email template config path
     */
    public const XML_PATH_WIDGET_EMAIL_TEMPLATE = 'contact/email/email_template_widget';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;

        parent::__construct($scopeConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function emailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_WIDGET_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

}
