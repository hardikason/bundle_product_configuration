<?php
namespace SK\ProductCOA\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Filesystem\Io\File as IOFile;

class AuthenticationPhotoDataProvider extends AbstractModifier
{
    /**
     * Constructor function
     *
     * @param UrlInterface $urlBuilder
     * @param LocatorInterface $locator
     * @param IOFile $ioFile
     */
    public function __construct(
        protected UrlInterface $urlBuilder,
        protected LocatorInterface $locator,
        protected IOFile $ioFile
    ) {
    }

    /**
     * Function to send authentication photo data in form
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();

        $productId = $product->getId();

        $attribute = $product->getCustomAttribute('authentication_photo');
        $value = $attribute ? $attribute->getValue() : null;
        
        if ($value) {
            $fileInfo = $this->ioFile->getPathInfo($value);
            $basename = $fileInfo['basename'];

            $data[$productId]['product']['authentication_photo'] = [
                [
                    'name' => $basename,
                    'url' => $this->getMediaUrl() . $value,
                    'type' => 'image/jpeg',
                ]
            ];
        }

        return $data;
    }

    /**
     * Modify Meta function
     *
     * @param array $meta
     * @return void
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Get media url
     *
     * @return string
     */
    protected function getMediaUrl()
    {
        return $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]);
    }
}
