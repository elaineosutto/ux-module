<?php


namespace TheITNerd\UX\Plugin\Design\Backend;

use Magento\Theme\Model\Design\Backend\Image;
use TheITNerd\UX\Helper\Image as ImageHelper;

class ImagePlugin
{
    /**
     * @var ImageHelper
     */
    private ImageHelper $imageHelper;

    /**
     * ImagePlugin constructor.
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        ImageHelper $imageHelper
    ) {
        $this->imageHelper = $imageHelper;
    }

    /**
     * Extend allowed extensions for theme files (logo, favicon, etc.)
     *
     * @param Image $subject
     * @param $extensions
     * @return array
     */
    public function afterGetAllowedExtensions(Image $subject, $extensions)
    {
        return array_merge($extensions, $this->imageHelper->getVectorExtensions());
    }
}
