<?php

namespace TheITNerd\UX\Model\Api;

use Exception;
use Magento\Framework\App\Request\Http;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Psr\Log\LoggerInterface;
use TheITNerd\UX\Api\MailInterface;
use TheITNerd\UX\Api\SendMailInterface;

class SendMail implements SendMailInterface
{

    /**
     * @var MailInterface
     */
    private MailInterface $mail;

    /**
     * @var Http
     */
    private Http $request;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    /**
     * @param UploaderFactory $uploaderFactory
     * @param MailInterface $mail
     * @param Http $request
     * @param LoggerInterface $logger
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        MailInterface                               $mail,
        Http                                        $request,
        LoggerInterface                             $logger,
        ManagerInterface $messageManager
    )
    {

        $this->mail = $mail;
        $this->request = $request;
        $this->logger = $logger;
        $this->messageManager = $messageManager;

    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function sendMail(): array
    {
        try {
            $this->sendEmail($this->getParams());

            $response = ['success' => true, 'message' => __('Your email was sent successfully, we will reply as soon as possible.')];
        } catch (LocalizedException $e) {
            $this->logger->warning($e);
            $response = ['success' => true, 'message' => __($e->getMessage())];
        } catch (Exception $e) {
            $this->logger->critical($e);
            $response = ['success' => true, 'message' => __($e->getMessage())];
        }

        if (!$response['success']) {
            $this->messageManager->addErrorMessage($response['message']);
        } else {
            $this->messageManager->addSuccessMessage($response['message']);
        }

        return [$response];
    }

    /**
     * @param array $post
     * @return $this
     */
    private function sendEmail(array $post): self
    {
        $attachments = [];

        foreach ($this->request->getFiles() as $file) {
            $attachments[] = $file;
        }

        $post['attachments'] = $attachments;

        $this->mail->send(
            $post['email'],
            [
                'data' => new DataObject($post)
            ]
        );

        return $this;
    }

    private function getParams(): array
    {
        if (trim($this->request->getParam('name')) === '') {
            throw new LocalizedException(__('Enter the Name and try again.'));
        }

        if (trim($this->request->getParam('phone')) === '') {
            throw new LocalizedException(__('Enter the Name and try again.'));
        }

        if (trim($this->request->getParam('subject')) === '') {
            throw new LocalizedException(__('Enter the Name and try again.'));
        }

        if (trim($this->request->getParam('message')) === '') {
            throw new LocalizedException(__('Enter the comment and try again.'));
        }
        if (false === strpos($this->request->getParam('email'), '@')) {
            throw new LocalizedException(__('The email address is invalid. Verify the email address and try again.'));
        }
        if (trim($this->request->getParam('hideit')) !== '') {
            throw new LocalizedException(__('This should be sent...'));
        }

        return $this->request->getParams();
    }
}
