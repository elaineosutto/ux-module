<?php


namespace TheITNerd\UX\Api;

/**
 * Interface CalculateCarriersRatesInterface
 * @package TheITNerd\UX\Api
 */
interface CalculateCarriersRatesInterface
{
    /**
     * Calculate carriers rates for a given product
     * @return array
     */
    public function calculateCarriersRates(): array;
}
