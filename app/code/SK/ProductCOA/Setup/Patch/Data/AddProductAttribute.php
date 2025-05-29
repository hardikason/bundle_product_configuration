<?php

declare(strict_types=1);

namespace SK\ProductCOA\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;

//use Magento\Catalog\Model\Product\Attribute\Backend\Image;

class AddProductAttribute implements DataPatchInterface
{
    private $eavSetupFactory;
    private $eavConfig;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create();

        $eavSetup->removeAttribute(Product::ENTITY, 'authentication_photo');
        $eavSetup->addAttribute(
            Product::ENTITY,
            'authentication_photo',
            [
                'type' => 'varchar',
                'label' => 'Authentication Photo',
                'input' => 'text',
                'frontend' => \Magento\Catalog\Model\Product\Attribute\Frontend\Image::class,
                'required' => false,
                'sort_order' => 100,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'user_defined' => true,
                'group' => 'Authentication Photo',
                'used_in_product_listing' => true,
                'visible_on_front' => true,
            ]
        );

        $attributeSetId = $eavSetup->getAttributeSetId(Product::ENTITY, 'Default'); // or any other set
        $attributeGroupId = $eavSetup->getAttributeGroupId(Product::ENTITY, $attributeSetId, 'Authentication Photo');

        $eavSetup->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            $attributeGroupId,
            'authentication_photo',
            100
        );

        // Optional: make attribute usable in admin forms
        // $attribute = $this->eavConfig->getAttribute(Product::ENTITY, 'authentication_photo');
        // $attribute->setData(
        //     'used_in_forms',
        //     ['adminhtml_catalog_product']
        // );
        // $attribute->save();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }
}
