<?php

declare(strict_types=1);

namespace SK\ConvertToOrder\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Product;

class AddBundleCategoryProductAttributes implements DataPatchInterface
{
    /**
     * Constructor function
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        protected ModuleDataSetupInterface $moduleDataSetup,
        protected EavSetupFactory $eavSetupFactory
    ) {
    }

    /**
     * Add Product Attributes
     *
     * @return void
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        // Create a new product attribute group (tab)
        $bundleCategoryattributeCode = 'bundle_category';
        
        // Heatsink Performance Attribute
        $eavSetup->removeAttribute(Product::ENTITY, $bundleCategoryattributeCode);
        $eavSetup->addAttribute(
            Product::ENTITY,
            $bundleCategoryattributeCode,
            [
                'group'         => 'General',
                'type' => 'varchar',
                'input' => 'select',
                'label' => 'Bundle Category',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'source' => \SK\ConvertToOrder\Model\Attribute\Source\BundleCategories::class,
                'backend' => '',
                'is_used_in_grid'               => true,
                'is_visible_in_grid'            => true,
                'is_filterable_in_grid'         => true,
                'visible'                       => true,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'is_html_allowed_on_front' => true,
                'required'      => true,
                'apply_to' => \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
            ]
        );


        // Attribute
        $bundleIntoAttributeCode = 'bundle_into';
        $eavSetup->removeAttribute(Product::ENTITY, $bundleIntoAttributeCode);
        $eavSetup->addAttribute(
            Product::ENTITY,
            $bundleIntoAttributeCode,
            [
                'group'         => 'General',
                'type' => 'text',
                'input' => 'multiselect',
                'label' => 'Add to Bundles Categories',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'source' => \SK\ConvertToOrder\Model\Attribute\Source\BundleCategories::class,
                'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
                'is_used_in_grid'               => true,
                'is_visible_in_grid'            => true,
                'is_filterable_in_grid'         => true,
                'visible'                       => true,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'is_html_allowed_on_front' => true,
                'required'      => false,
                'apply_to' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
            ]
        );

        // Attribute
        $productTypeAttributeCode = 'product_type';
        $eavSetup->removeAttribute(Product::ENTITY, $productTypeAttributeCode);
        $eavSetup->addAttribute(
            Product::ENTITY,
            $productTypeAttributeCode,
            [
                'group'         => 'General',
                'type' => 'varchar',
                'input' => 'select',
                'label' => 'Product Type',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'source' => \SK\ConvertToOrder\Model\Attribute\Source\ProductTypes::class,
                'backend' => '',
                'visible' => true,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'is_html_allowed_on_front' => true,
                'required'      => false,
                'apply_to' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
            ]
        );
    }

    /**
     * FetDependencies function
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * GetAliases function
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}
