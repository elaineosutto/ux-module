<?php


namespace TheITNerd\UX\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Image
 * @package TheITNerd\UX\Helper
 */
class Image extends AbstractHelper
{
    public const VECTOR_EXTENSIONS = [
        'xml',
        'svg',
        'svg+xml'
    ];

    /**
     * Check if the file is a vector image
     *
     * @param $file
     * @return bool
     */
    public function isVectorImage($file)
    {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        return in_array($extension, $this->getVectorExtensions(), true);
    }

    /**
     * et vector images extensions
     *
     * @return array
     */
    public function getVectorExtensions()
    {
        return self::VECTOR_EXTENSIONS;
    }
}
