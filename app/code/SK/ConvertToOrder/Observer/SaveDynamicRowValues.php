<?php

namespace SK\ConvertToOrder\Observer;

use SK\ConvertToOrder\Ui\DataProvider\Product\Form\Modifier\DynamicRowAttribute;
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
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $observer->getEvent()->getDataObject();
        $wholeRequest = $this->request->getPost();
        $post = $wholeRequest['product'];
        
        if (empty($post)) {
            $post = !empty($wholeRequest['variables']['product']) ? $wholeRequest['variables']['product'] : [];
        }

        //compatible with
        $compatible_with = isset(
            $post['compatible_with']
        ) ? $post['compatible_with'] : [];

         echo '<pre>---------------';
        print_r($compatible_with);
        die;
        // print_r($post['compatible_with']);
        if(!empty($compatible_with)) {
            $newBundleCompatibleWith = $this->getBundleProducts($post);
            print_r($newBundleCompatibleWith);

            $newBundleCompatibleWith = array_merge($post['compatible_with']['dynamic_row'], $newBundleCompatibleWith);
          
            $filteredCompatibleWithData['dynamic_row'] = $this->filterCompatibleWithData($newBundleCompatibleWith);

            // Print the filtered array
            //  echo "<pre>";
            //  print_r($filteredCompatibleWithData);
            //  echo "</pre>";
            // die;

            if (is_array($filteredCompatibleWithData)) {
                $product->setCompatibleWith($this->serializer->serialize($filteredCompatibleWithData));
            }

        }
        
        
        
        
        // if (is_array($compatible_with)) {
        //     $product->setCompatibleWith($this->serializer->serialize($compatible_with));
        // }
        
        // heatsink condition
        $heatsink_condition = isset(
            $post['heatsink_condition']
        ) ? $post['heatsink_condition'] : [];

        if (is_array($heatsink_condition)) {
            $product->setHeatsinkCondition($this->serializer->serialize($heatsink_condition));
        }
    }

    public function getBundleProducts($post) {
        try{
            //echo '==================================';
            $searchCriteria = $this->searchCriteriaBuilder
                            ->addFilter('type_id', Type::TYPE_BUNDLE) // Only bundle products
                            ->addFilter('bundle_category', $post['bundle_into'] , 'IN') // Filter by custom attribute
                            ->create();

            $bundleProducts = $this->productRepository->getList($searchCriteria)->getItems();

            
            //Loop Through Results
            $newBundleCompatibleWith = [];
            foreach ($bundleProducts as $bundleProduct) {
                $bundleOptions = $bundleProduct->getExtensionAttributes()->getBundleProductOptions();
                //print_r($bundleOptions);
                //echo "Product ID: " . $bundleProduct->getId() . " | Name: " . $bundleProduct->getName() . "\n";

                foreach($bundleOptions as $bundleOption) {
                    if(strtolower($bundleOption['title']) == strtolower($post['product_type'])) {
                        //echo "Bundle option " . $post['product_type'] . " found " . "\n";

                        //$post['compatible_with']['dynamic_row'][] = [
                        $newBundleCompatibleWith[] = [
                            'record_id' => isset($post['compatible_with']['dynamic_row']) ? count($post['compatible_with']['dynamic_row']) : 0,
                            'bundle_product' => $bundleProduct->getSku(),
                            'bundle_option' => $bundleOption['option_id'],
                            'initialize' => 'true'
                        ];
                    }else {
                        //echo "Bundle option " . $post['product_type'] . " not found " . "\n";
                    }
                }
            }

            return $newBundleCompatibleWith;
            
        }catch(Magento\Framework\Exception\LocalizedException $e) {
            echo 'fdgfdg'.$e->getMessage();die;
        }
    }

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
}
