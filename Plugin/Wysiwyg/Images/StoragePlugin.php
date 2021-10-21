<?php


namespace TheITNerd\UX\Plugin\Wysiwyg\Images;

use Magento\Cms\Model\Wysiwyg\Images\Storage;
use TheITNerd\UX\Helper\Image as ImageHelper;

/**
 * Class StoragePlugin
 * @package TheITNerd\UX\Plugin\Wysiwyg\Images
 */
class StoragePlugin
{
    /**
     * @var ImageHelper
     */
    private ImageHelper $imageHelper;

    /**
     * StoragePlugin constructor.
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        ImageHelper $imageHelper
    )
    {
        $this->imageHelper = $imageHelper;
    }

    /**
     * Skip resizing vector images
     *
     * @param Storage $storage
     * @param callable $proceed
     * @param $source
     * @param bool $keepRatio
     * @return mixed
     */
    public function aroundResizeFile(Storage $storage, callable $proceed, $source, $keepRatio = true)
    {
        if ($this->imageHelper->isVectorImage($source)) {
            return $source;
        }

        return $proceed($source, $keepRatio);
    }

    /**
     * Return original file path as thumbnail for vector images
     *
     * @param Storage $storage
     * @param callable $proceed
     * @param $filePath
     * @param false $checkFile
     * @return mixed
     */
    public function aroundGetThumbnailPath(Storage $storage, callable $proceed, $filePath, $checkFile = false)
    {
        if ($this->imageHelper->isVectorImage($filePath)) {
            return $filePath;
        }

        return $proceed($filePath, $checkFile);
    }
}
