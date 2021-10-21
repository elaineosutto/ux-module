<?php


namespace TheITNerd\UX\Plugin\File\Validator;

use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use TheITNerd\UX\Helper\Image as ImageHelper;

class NotProtectedExtensionPlugin
{
    /**
     * @var ImageHelper
     */
    private ImageHelper $imageHelper;

    /**
     * NotProtectedExtensionPlugin constructor.
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        ImageHelper $imageHelper
    )
    {
        $this->imageHelper = $imageHelper;
    }

    /**
     * Remove vector images from protected extensions list
     *
     * @param NotProtectedExtension $subject
     * @param $result
     * @return mixed
     */
    public function afterGetProtectedFileExtensions(NotProtectedExtension $subject, $result)
    {
        $vectorExtensions = $this->imageHelper->getVectorExtensions();

        foreach (array_keys($result) as $extension) {
            if (in_array($extension, $vectorExtensions, true)) {
                unset($result[$extension]);
            }
        }

        return $result;
    }
}
