<?php


namespace TheITNerd\UX\Setup\Patch\Data;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Eav\Api\Data\AttributeSetSearchResultsInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface as PatchRevertableInterfaceAlias;

/**
 * Class CustomerAddressStreetAttributes
 * @package TheITNerd\UX\Setup\Patch\Data
 */
class CustomerAddressStreetAttributes implements DataPatchInterface, PatchRevertableInterfaceAlias
{
    public const ROUNDEL_ATTRIBUTE_GROUP = 'Product Roundel';

    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $eavSetupFactory;

    /**
     * ProductRoundelAttributes constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritDoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->updateAttribute(
            AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
            'street',
            'multiline_count',
            4,
            null
        );

        $this->moduleDataSetup->getConnection()->endSetup();

    }

    /**
     * {@inheritDoc}
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        foreach ($this->attributes as $attribute) {
            $eavSetup->removeAttribute(Product::ENTITY, $attribute['code']);
        }

        foreach ($this->getAttributeSetList() as $attributeSet) {
            $eavSetup->removeAttributeGroup(
                Product::ENTITY,
                $attributeSet->getId(),
                self::ROUNDEL_ATTRIBUTE_GROUP
            );
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return AttributeSetSearchResultsInterface
     * @throws Exception
     */
    private function getAttributeSetList(): AttributeSetSearchResultsInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        try {
            $productAttributeSet = $this->productAttributeSet->getList($searchCriteria);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        return $productAttributeSet;
    }
}
