<?php

namespace SK\ConvertToOrder\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Text;
use SK\ConvertToOrder\Model\Attribute\Source\BundleProducts as BundleProducts;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollection;

use Magento\Framework\Stdlib\ArrayManager;

class DynamicRowAttribute extends AbstractModifier
{
    public const PRODUCT_ATTRIBUTE_CODE = 'compatible_with';
    public const FIELD_IS_DELETE = 'is_delete';
    public const FIELD_SORT_ORDER_NAME = 'sort_order';

    /**
     * Dependency Initilization
     *
     * @param LocatorInterface $locator
     * @param AttributeSetCollection $attributeSetCollection
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        private LocatorInterface $locator,
        protected AttributeSetCollection $attributeSetCollection,
        protected \Magento\Framework\Serialize\SerializerInterface $serializer,
        protected ArrayManager $arrayManager,
        protected BundleProducts $bundleProducts
    ) {
    }

    /**
     * Modify Data
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        $fieldCode = self::PRODUCT_ATTRIBUTE_CODE;

        $model = $this->locator->getProduct();
        $modelId = $model->getId();

        $highlightsData = $model->getCompatibleWith();

        if ($highlightsData) {
            $highlightsData = $this->serializer->unserialize($highlightsData, true);
            $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/' . $fieldCode;
            $data = $this->arrayManager->set($path, $data, $highlightsData);
        }

        
        return $data;
    }

    /**
     * Modify Meta
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $highlightsPath = $this->arrayManager->findPath(
            self::PRODUCT_ATTRIBUTE_CODE,
            $meta,
            null,
            'children'
        );

        

        if ($highlightsPath) {
            $meta = $this->arrayManager->merge(
                $highlightsPath,
                $meta,
                $this->initHighlightFieldStructure($meta, $highlightsPath)
            );
            $meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($highlightsPath, 0, -3)
                    . '/' . self::PRODUCT_ATTRIBUTE_CODE,
                $meta,
                $this->arrayManager->get($highlightsPath, $meta)
            );
            $meta = $this->arrayManager->remove(
                $this->arrayManager->slicePath($highlightsPath, 0, -2),
                $meta
            );
        }

        return $meta;
    }

    /**
     * Add Attribute Grid Config
     *
     * @param int $sortOrder
     * @return array
     */
    protected function addAttributeGridConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Attribute'),
                        'componentType' => DynamicRows::NAME,
                        'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows',
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'deleteValue' => '1',
                        'renderDefaultRecord' => false,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::FIELD_SORT_ORDER_NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                            ],
                        ],
                    ],
                    'children' => [
                        'attribute_type' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Select::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Attribute Type'),
                                        'enableLabel' => true,
                                        'dataScope' => 'attribute_type',
                                        'sortOrder' => 40,
                                        'validation' => [
                                            'required-entry' => true,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'attribute_lable' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Select::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Attribute'),
                                        'enableLabel' => true,
                                        'dataScope' => 'attribute_lable',
                                        'sortOrder' => 40,
                                        'validation' => [
                                            'required-entry' => true,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => Text::NAME,
                                        'label' => '',
                                        'sortOrder' => 50,
                                    ],
                                ],
                            ],
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * Get attraction highlights dynamic rows structure
     *
     * @param array $meta
     * @param string $highlightsPath
     * @return array
     */
    protected function initHighlightFieldStructure($meta, $highlightsPath)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'label' => __('Custom Dynamic Rows'),
                        'formElement' => 'container',  // **Important**
                        //'dataScope' => 'products_in_bundle',  // **Must match attribute code**
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
                        'sortOrder' =>
                        $this->arrayManager->get($highlightsPath . '/arguments/data/config/sortOrder', $meta),
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => [
                        'bundle_product' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Select::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Bundle Product'),
                                        'dataScope' => 'bundle_product',
                                        //'require' => '1',
                                        'options' => $this->bundleProducts->getAllOptions(),
                                        'validation' => ['required-entry' => true]
                                    ],
                                ],
                            ],
                        ],

                        'bundle_option_title' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Select::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Bundle Option Title'),
                                        'dataScope' => 'bundle_option_title',
                                        //'require' => '1',
                                        'options' => []
                                    ],
                                ],
                            ],
                        ],
                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => Text::NAME,
                                        'label' => '',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}