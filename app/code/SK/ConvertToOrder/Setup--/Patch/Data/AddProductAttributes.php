<?php

declare(strict_types=1);

namespace SK\ConvertToOrder\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Product;

class AddProductAttributes implements DataPatchInterface
{
    private $moduleDataSetup;
    private $eavSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        // ✅ Create a new product attribute group (tab)
        $groupName = 'Assign Product to Bundles';
        $attributeCode = 'compatible_with';
        
        $eavSetup->removeAttribute(Product::ENTITY, $attributeCode);
        // Product Compatible With
        $eavSetup->addAttribute(
            Product::ENTITY,
            $attributeCode,
            [
                'group'         => $groupName,
                'type' => 'text',
                'input' => 'text',
                'label' => 'Compatible With',
                'visible' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend' => '',
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'is_html_allowed_on_front' => true,
                'required'      => false,
                'apply_to' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
            ]
        );

        // TDP Attribute for CPU
        $eavSetup->removeAttribute(Product::ENTITY, 'tdp');
        $eavSetup->addAttribute(
            Product::ENTITY,
            'tdp',
            [
                'group'         => 'General',
                'type' => 'int',
                'input' => 'text',
                'label' => 'TDP (Wattage)',
                'visible' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
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

        // Heatsink Performance Attribute
        $eavSetup->removeAttribute(Product::ENTITY, 'heatsink_performance');
        $eavSetup->addAttribute(
            Product::ENTITY,
            'heatsink_performance',
            [
                'group'         => 'General',
                'type' => 'text',
                'input' => 'select',
                'label' => 'Heatsink Performance',
                'visible' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'source' => \SK\ConvertToOrder\Model\Attribute\Source\AvailableHeatsinkPerformance::class,
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

        // Heatsink Condition Attribute
        $eavSetup->removeAttribute(Product::ENTITY, 'heatsink_condition');
        $eavSetup->addAttribute(
            Product::ENTITY,
            'heatsink_condition',
            [
                'group'         => 'Apply condition for Heatsink Selection',
                'type' => 'text',
                'input' => 'text',
                'label' => 'Heatsink Condition',
                'visible' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'is_used_in_grid'               => false,
                'is_visible_in_grid'            => false,
                'is_filterable_in_grid'         => false,
                'visible'                       => true,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'is_html_allowed_on_front' => true,
                'required'      => false,
                'apply_to' => \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
            ]
        );
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
