<?php

namespace TheITNerd\UX\Model\Mail;

use Laminas\Mime\Mime;
use Laminas\Mime\Part;
use Magento\Framework\App\Area;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use TheITNerd\UX\Api\MailInterface;

class Send implements MailInterface
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var TransportBuilder
     */
    private TransportBuilder $transportBuilder;

    /**
     * @var StateInterface
     */
    private StateInterface $state;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var File
     */
    private File $file;

    /**
     * @param Config $config
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $state
     * @param StoreManagerInterface $storeManager
     * @param File $file
     */
    public function __construct(
        Config                $config,
        TransportBuilder      $transportBuilder,
        StateInterface        $state,
        StoreManagerInterface $storeManager,
        File                  $file
    )
    {
        $this->config = $config;
        $this->transportBuilder = $transportBuilder;
        $this->state = $state;
        $this->storeManager = $storeManager;
        $this->file = $file;
    }

    public function send(string $replyTo, array $variables): void
    {
        $replyToName = !empty($variables['data']['name']) ? $variables['data']['name'] : null;
        if (!isset($variables['data']['subject'])) {
            $variables['data']['subject'] = __('Widget Contact Email');
        }

        $this->state->suspend();

        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($this->config->emailTemplate())
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId()
                    ]
                )
                ->setTemplateVars($variables)
                ->setFromByScope($this->config->emailSender())
                ->addTo($this->config->emailRecipient())
                ->setReplyTo($replyTo, $replyToName)
                ->getTransport();

            if ($variables['data']->getAttachments() !== null) {
                foreach ($variables['data']->getAttachments() as $a) {
                    $this->addAttachment($transport, $a);
                }
            }

            $transport->sendMessage();

        } finally {
            $this->state->resume();
        }

    }

    protected function addAttachment(TransportInterface $transport, array $file): self
    {
        $part = $this->createAttachment($file);
        $transport->getMessage()->getBody()->addPart($part);

        return $this;
    }

    /**
     * @param array $file
     * @return Part
     */
    protected function createAttachment(array $file): Part
    {
        $fileName = $file['name'];

        $attachment = new Part($this->file->read($file['tmp_name']));
        $attachment->disposition = Mime::TYPE_OCTETSTREAM;
        $attachment->encoding = Mime::ENCODING_BASE64;
        $attachment->filename = $fileName;

        return $attachment;
    }
}
