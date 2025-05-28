<?php
namespace SK\ProductCOA\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;

class AuthenticationPhotoDataProvider extends AbstractModifier
{
    protected $urlBuilder;
    protected $locator;

    public function __construct(
        UrlInterface $urlBuilder,
        LocatorInterface $locator
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->locator = $locator;
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
            $data[$productId]['product']['authentication_photo'] = [
                [
                    'name' => basename($value),
                    'url' => '/pub/media/authentication-photo/image/' . basename($value),
                    'type' => 'image/jpeg',
                ]
            ];
        }

        return $data;
    }

    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    protected function getMediaUrl()
    {
        return $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]);
    }
}
