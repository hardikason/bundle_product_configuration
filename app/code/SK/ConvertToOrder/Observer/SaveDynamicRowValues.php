<?php

namespace SK\ConvertToOrder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Catalog\Model\Product\Type;

class SaveDynamicRowValues implements ObserverInterface
{
    /**
     * Dependency Initilization
     *
     * @param RequestInterface $request
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        protected RequestInterface $request,
        protected SerializerInterface $serializer,
        protected ProductRepositoryInterface $productRepository,
        protected SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    /**
     * Execute
     *
     * @param Observer $observer
     * @return this
     */
    public function execute(Observer $observer)
    {
        
        $product = $observer->getProduct();
        // Get old values (before update)
        $originalProductData = $product->getOrigData();

        
        echo '<pre>--------New  Product Data -------';
       

        // For simple product save
        if ($product->getTypeId() == Type::TYPE_SIMPLE) {
            
        
        
            //compatible with
            $compatible_with = isset(
                $product['compatible_with']
            ) ? $product['compatible_with'] : [];

            if(!empty($compatible_with) || $product['bundle_into'] != null) {
                
                $newBundleCompatibleWith = $this->getBundleProductsFromBundleIntoField($product);
                if(isset($product['compatible_with']['dynamic_row'])) {
                    $newBundleCompatibleWith = array_merge($product['compatible_with']['dynamic_row'], $newBundleCompatibleWith);
                }
                
                $filteredCompatibleWithData['dynamic_row'] = $this->filterCompatibleWithData($newBundleCompatibleWith);

                $currentBundleInto = explode(',', isset($product['bundle_into'])?$product['bundle_into']:'');
                $origionalBundleInto = explode(',', isset($originalProductData['bundle_into'])?$originalProductData['bundle_into']:'');
            
                $finalCompatibleWithResultData = $this->getRemovedItems($filteredCompatibleWithData, $currentBundleInto, $origionalBundleInto);
                //print_r($finalCompatibleWithResultData);die;
                
                if (is_array($finalCompatibleWithResultData)) {
                    $product->setCompatibleWith($this->serializer->serialize($finalCompatibleWithResultData));
                }

            }
            
            
            // heatsink condition
            $heatsink_condition = isset(
                $product['heatsink_condition']
            ) ? $product['heatsink_condition'] : [];

            if (is_array($heatsink_condition)) {
                $product->setHeatsinkCondition($this->serializer->serialize($heatsink_condition));
            }
        }

        // For bundle product save
        if ($product->getTypeId() == Type::TYPE_BUNDLE) {
            $newOptions = $product->getExtensionAttributes()->getBundleProductOptions();
            //print_r($newOptions);

            $bundlesNewOptionsSelection = [];
            foreach($newOptions as $option) {
                foreach($option['product_links'] as $productLinks) {
                    $bundlesNewOptionsSelection[] = [
                        'simple_product_sku' => $productLinks['sku'],
                        'bundle_product' => $productLinks['parent_product_id'],
                        'option_id' => $productLinks['option_id'],
                        'selection_id' => $productLinks['selection_id']
                    ];
                }
            }
            print_r($bundlesNewOptionsSelection);
            // Extract `selection_id` values
            $newSelectionIds = array_column($bundlesNewOptionsSelection, 'selection_id');
            

            $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
                $product->getTypeInstance(true)->getOptionsIds($product), $product);
            print_r($selectionCollection->getData());

            $bundlesRemovedOptionsSelection = [];
            foreach($selectionCollection as $option)
            {
                if(!in_array($option['selection_id'], $newSelectionIds)) {
                    $bundlesRemovedOptionsSelection[] = [
                        'simple_product_sku' => $option['sku'],
                        'bundle_product' => $option['parent_product_id'],
                        'option_id' => $option['option_id'],
                        'selection_id' => $option['selection_id']
                    ];
                }
            }

            print_r($bundlesRemovedOptionsSelection);

            $product->setData('bundle_removed_items', $bundlesRemovedOptionsSelection);

        }
    }
    
    public function getBundleProductsFromBundleIntoField($product) {
        try{
            
            $bundleProducts = $this->getBundleProducts($product['bundle_into']);
            
            //Loop Through Results
            $newBundleCompatibleWith = [];
            foreach ($bundleProducts as $bundleProduct) {
                $bundleOptions = $bundleProduct->getExtensionAttributes()->getBundleProductOptions();
                
                //echo "Product ID: " . $bundleProduct->getId() . " | Name: " . $bundleProduct->getName() . "\n";

                foreach($bundleOptions as $bundleOption) {
                    if(strtolower($bundleOption['title']) == strtolower($product['product_type'])) {
                        //echo "Bundle option " . $post['product_type'] . " found " . "\n";

                        //$post['compatible_with']['dynamic_row'][] = [
                        $newBundleCompatibleWith[] = [
                            'record_id' => isset($product['compatible_with']['dynamic_row']) ? count($product['compatible_with']['dynamic_row']) : 0,
                            'bundle_product' => $bundleProduct->getSku(),
                            'bundle_option' => $bundleOption['option_id'],
                            'initialize' => 'true',
                            'delete' => 0
                        ];
                    }
                }
            }

            return $newBundleCompatibleWith;
            
        } catch(\Magento\Framework\Exception\LocalizedException $e) {
            echo 'fdgfdg'.$e->getMessage();die;
        }
    }

    public function getBundleProducts($bundleIds) {
        try {
            $searchCriteria = $this->searchCriteriaBuilder
                        ->addFilter('type_id', Type::TYPE_BUNDLE) // Only bundle products
                        ->addFilter('bundle_category', $bundleIds , 'IN') // Filter by custom attribute
                        ->create();

            $bundleProducts = $this->productRepository->getList($searchCriteria)->getItems();

            return $bundleProducts;

        } catch(\Magento\Framework\Exception\LocalizedException $e) {
            echo $e->getMessage();die;
        }

    }

    // remove duplicate entries
    public function filterCompatibleWithData($data) {
        
        $unique = [];
        $filteredData = [];

        foreach ($data as $row) {
            $key = $row["bundle_product"] . "-" . $row["bundle_option"]; // Unique identifier

            if (!isset($unique[$key])) {
                $unique[$key] = true; // Store unique key
                $filteredData[] = $row; // Add unique row
            }
        }

        return $filteredData;
    }

    /**
     * Recursively finds differences between two multidimensional arrays
     */
    public function getRemovedItems($compatible_with, $currentBundleInto, $origionalBundleInto) {
        
        $removeBundleFromCW = [];
        if(!empty($origionalBundleInto) || !empty($currentBundleInto)) {
            $missingInArray2 = array_diff($origionalBundleInto, $currentBundleInto); // Items in array1 but not in array2
            $missingInArray1 = array_diff($currentBundleInto, $origionalBundleInto); // Items in array2 but not in array1

            $result = [
                'removed' => $missingInArray2,
                'added' => $missingInArray1
            ];

            echo "<pre>removed";
            
            if(count($result['removed']) > 0) {
                $removedBundleProducts = $this->getBundleProducts($result['removed']);

                foreach ($removedBundleProducts as $removedBundleProduct) {
                    $removeBundleFromCW[] = $removedBundleProduct->getSku();
                }
            }
        }

        // Print the filtered array
        echo "<pre>removed";
        print_r($removeBundleFromCW);
        echo "</pre>";
        
        // Convert `dynamic_row` array to key-based for easy comparison
        $updatedArray = [];
        foreach ($compatible_with['dynamic_row'] as $key => $row) {

            if($row['delete'] === true || in_array($row['bundle_product'], $removeBundleFromCW)) {
                
                $row['delete'] = true;
                
            }
            $updatedArray[] = $row;
        }
        $compatible_with['dynamic_row'] = $updatedArray;
        return $compatible_with;
    }

}
