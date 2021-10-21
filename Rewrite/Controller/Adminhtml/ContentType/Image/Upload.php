<?php


namespace TheITNerd\UX\Rewrite\Controller\Adminhtml\ContentType\Image;


use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Upload extends \Magento\PageBuilder\Controller\Adminhtml\ContentType\Image\Upload
{
    public const UPLOAD_DIR = 'wysiwyg';

    public const ADMIN_RESOURCE = 'Magento_Backend::content';

    /**
     * @var Filesystem\DirectoryList
     * @deprecad use $mediaDirectory instead
     */
    private Filesystem\DirectoryList $directoryList;

    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * @var UploaderFactory
     */
    private UploaderFactory $uploaderFactory;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Images
     */
    private Images $cmsWysiwygImages;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param StoreManagerInterface $storeManager
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem\DirectoryList $directoryList
     * @param Images $cmsWysiwygImages
     * @param Filesystem|null $filesystem
     * @throws FileSystemException
     */
    public function __construct(
        Context                  $context,
        JsonFactory              $resultJsonFactory,
        StoreManagerInterface    $storeManager,
        UploaderFactory          $uploaderFactory,
        Filesystem\DirectoryList $directoryList,
        Images                   $cmsWysiwygImages,
        Filesystem               $filesystem = null
    )
    {

        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->uploaderFactory = $uploaderFactory;
        $this->directoryList = $directoryList;
        $this->cmsWysiwygImages = $cmsWysiwygImages;
        $filesystem = $filesystem ?? ObjectManager::getInstance()->create(Filesystem::class);
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);

        parent::__construct($context, $resultJsonFactory, $storeManager, $uploaderFactory, $directoryList, $cmsWysiwygImages, $filesystem);
    }

    /**
     * Allow users to upload images to the folder structure
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $fieldName = $this->getRequest()->getParam('param_name');
        $fileUploader = $this->uploaderFactory->create(['fileId' => $fieldName]);

        // Set our parameters
        $fileUploader->setFilesDispersion(false);
        $fileUploader->setAllowRenameFiles(true);
        $fileUploader->setAllowedExtensions(['jpeg', 'jpg', 'png', 'gif', 'svg']);
        $fileUploader->setAllowCreateFolders(true);

        try {
            if (!$fileUploader->checkMimeType(['image/png', 'image/jpeg', 'image/gif', 'image/svg', 'image/xml', 'image/svg+xml'])) {
                throw new LocalizedException(__('File validation failed.'));
            }

            $result = $fileUploader->save($this->getUploadDir());
            $baseUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            $result['id'] = $this->cmsWysiwygImages->idEncode($result['file']);
            $result['url'] = $baseUrl . $this->getFilePath(self::UPLOAD_DIR, $result['file']);
        } catch (Exception $e) {
            $result = [
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }
        return $this->resultJsonFactory->create()->setData($result);
    }

    /**
     * Return the upload directory
     *
     * @return string
     */
    private function getUploadDir()
    {
        return $this->mediaDirectory->getAbsolutePath(self::UPLOAD_DIR);
    }

    /**
     * Retrieve path
     *
     * @param string $path
     * @param string $imageName
     * @return string
     */
    private function getFilePath($path, $imageName)
    {
        return rtrim($path, '/') . '/' . ltrim($imageName, '/');
    }
}
