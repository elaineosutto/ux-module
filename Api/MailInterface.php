<?php

namespace TheITNerd\UX\Api;

/**
 * Email from contact Form
 */
interface MailInterface
{
    /**
     * @param string $replyTo
     * @param array $variables
     */
    public function send(string $replyTo, array $variables): void;
}
