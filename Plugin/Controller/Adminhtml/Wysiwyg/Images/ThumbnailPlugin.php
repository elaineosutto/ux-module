<?php


namespace TheITNerd\UX\Plugin\Controller\Adminhtml\Wysiwyg\Images;

use Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\Thumbnail;
use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\LocalizedException;
use TheITNerd\UX\Helper\Image as ImageHelper;

/**
 * Class ThumbnailPlugin
 * @package TheITNerd\UX\Plugin\Controller\Adminhtml\Wysiwyg\Images
 */
class ThumbnailPlugin
{
    /**
     * @var Images
     */
    private Images $wysiwygImages;

    /**
     * @var RawFactory
     */
    private RawFactory $resultRawFactory;

    /**
     * @var ImageHelper
     */
    private ImageHelper $imageHelper;

    /**
     * ThumbnailPlugin constructor.
     * @param Images $wysiwygImages
     * @param RawFactory $resultRawFactory
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        Images $wysiwygImages,
        RawFactory $resultRawFactory,
        ImageHelper $imageHelper
    ) {
        $this->wysiwygImages = $wysiwygImages;
        $this->resultRawFactory = $resultRawFactory;
        $this->imageHelper = $imageHelper;
    }

    /**
     * Handle vector images for media storage thumbnails
     *
     * @param Thumbnail $subject
     * @param callable $proceed
     * @return Raw
     */
    public function aroundExecute(Thumbnail $subject, callable $proceed)
    {
        try {
            $file = $subject->getRequest()->getParam('file');
            $file = $this->wysiwygImages->idDecode($file);
            $thumb = $subject->getStorage()->resizeOnTheFly($file);

            if (!$this->imageHelper->isVectorImage($thumb)) {
                throw new LocalizedException(__('This is not a vector image'));
            }

            /** @var Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setHeader('Content-Type', 'image/svg+xml');
            $resultRaw->setContents(file_get_contents($thumb));

            return $resultRaw;
        } catch (\Exception $e) {
            return $proceed();
        }
    }
}
