<?php


namespace TheITNerd\UX\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Cache
 * @package TheITNerd\UX\Helper
 */
class Cache extends AbstractHelper
{
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * Data constructor.
     * @param Context $context
     * @param SerializerInterface $serializer
     * @param CacheInterface $cache
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        SerializerInterface $serializer,
        CacheInterface $cache,
        StoreManagerInterface $storeManager
    )
    {
        $this->serializer = $serializer;
        $this->cache = $cache;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @param string $key
     * @param string $scope
     * @return array|bool|float|int|string|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function load(string $key, string $scope = 'global')
    {

        $key = $this->addScopeToCacheKey($key, $scope);

        if ($cache = $this->cache->load($key)) {
            return $this->serializer->unserialize($cache);
        }

        return null;
    }

    /**
     * @param string $key
     * @param string $scope
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function addScopeToCacheKey(string $key, string $scope): string
    {
        switch ($scope) {
            case 'website':
                return "{$key}_website_{$this->storeManager->getWebsite()->getId()}";
                break;
            case 'store':
                return "{$key}_store_{$this->storeManager->getStore()->getId()}";
                break;
        }

        return $key;
    }

    /**
     * @param string $key
     * @param $data
     * @param array $tags
     * @param int $ttl
     * @param string $scope
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function save(string $key, $data, array $tags = [], int $ttl = 86400, string $scope = 'global')
    {
        $key = $this->addScopeToCacheKey($key, $scope);
        return $this->cache->save(
            $this->serializer->serialize($data),
            $key,
            $tags,
            $ttl
        );
    }


}
