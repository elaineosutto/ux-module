<?php


namespace TheITNerd\UX\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use TheITNerd\UX\Helper\Image as ImageHelper;

/**
 * Class AssetFactory
 * @package TheITNerd\UX\Model
 */
class AssetFactory extends AssetInterfaceFactory
{
    /**
     * @var AssetInterfaceFactory
     */
    private AssetInterfaceFactory $assetFactory;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var ImageHelper
     */
    private ImageHelper $imageHelper;

    /**
     * AssetFactory constructor.
     * @param AssetInterfaceFactory $assetFactory
     * @param Filesystem $filesystem
     * @param ImageHelper $imageHelper
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        AssetInterfaceFactory $assetFactory,
        Filesystem $filesystem,
        ImageHelper $imageHelper,
        ObjectManagerInterface $objectManager,
        $instanceName = '\\Magento\\MediaGalleryApi\\Api\\Data\\AssetInterface'
    )
    {
        $this->assetFactory = $assetFactory;
        $this->filesystem = $filesystem;
        $this->imageHelper = $imageHelper;

        parent::__construct($objectManager, $instanceName);
    }

    /**
     * Set height and width for SVG images when saving to DB
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data = [])
    {
        if ((empty($data['width']) || empty($data['height']))
            && isset($data['path'])
            && $this->imageHelper->isVectorImage($data['path'])
        ) {
            $absolutePath = $this->getMediaDirectory()->getAbsolutePath($data['path']);
            $width = 300;
            $height = 150;

            $svg = simplexml_load_string(file_get_contents($absolutePath));
            if (!empty($svg['width']) && !empty($svg['height'])) {
                $width = (int)$svg['width'];
                $height = (int)$svg['height'];
            }

            $data['width'] = $width;
            $data['height'] = $height;
        }

        return $this->assetFactory->create($data);
    }

    /**
     * Retrieve media directory instance with read access
     *
     * @return ReadInterface
     */
    private function getMediaDirectory()
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
    }
}
