<?php


namespace TheITNerd\UX\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\Data\AttributeSetSearchResultsInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface as PatchRevertableInterfaceAlias;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Zend_Validate_Exception;
use Exception;

/**
 * Class ProductRoundelAttributes
 * @package TheITNerd\UX\Setup\Patch\Data
 */
class ProductRoundelAttributes implements DataPatchInterface, PatchRevertableInterfaceAlias
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
     * @var array|string[][]
     */
    private array $attributes = [
        [
            'label' => 'Roundel Text',
            'code' => 'roundel_text',
            'type' => 'varchar'
        ],
        [
            'label' => 'Roundel Color',
            'code' => 'roundel_color',
            'type' => 'varchar'
        ],
        [
            'label' => 'Roundel BG Color',
            'code' => 'roundel_bg_color',
            'type' => 'varchar'
        ]
    ];

    /**
     * @var AttributeSetRepositoryInterface
     */
    private AttributeSetRepositoryInterface $productAttributeSet;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * ProductRoundelAttributes constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeSetRepositoryInterface $productAttributeGroup
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        AttributeSetRepositoryInterface $productAttributeGroup,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->productAttributeSet = $productAttributeGroup;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        foreach ($this->getAttributeSetList() as $attributeSet) {
            $eavSetup->addAttributeGroup(
                Product::ENTITY,
                $attributeSet->getId(),
                self::ROUNDEL_ATTRIBUTE_GROUP
            );
        }


        foreach ($this->attributes as $attribute) {

            $eavSetup->addAttribute(
                Product::ENTITY,
                $attribute['code'],
                [
                    'type' => $attribute['type'],
                    'label' => $attribute['label'],
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'input' => 'text',
                    'frontend' => '',
                    'required' => false,
                    'backend' => '',
                    'default' => null,
                    'visible' => true,
                    'user_defined' => false,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'unique' => false,
                    'used_in_product_listing' => true,
                    'apply_to' => 'simple,grouped,configurable,virtual,bundle',
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'option' => '',
                    'group' => self::ROUNDEL_ATTRIBUTE_GROUP,
                ]
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
}
