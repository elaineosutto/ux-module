<?php


namespace TheITNerd\UX\Api;

/**
 * Interface ZipSearchInterface
 * @package TheITNerd\UX\Api
 */
interface ZipSearchInterface
{
    /**
     * Search for an address from a zip code (Brazil only)
     * @return array
     */
    public function searchAddress(): array;

    /**
     * Search for a zip code from an address (Brazil only)
     * @return array
     */
    public function searchZIPCode(): array;
}
