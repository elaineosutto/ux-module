<?php


namespace TheITNerd\UX\Api;

/**
 * Interface SendMailInterface
 * @package TheITNerd\UX\Api
 */
interface SendMailInterface
{
    /**
     * Send a Contact Email
     * @return array
     */
    public function sendMail(): array;

}
