<?php
namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Pim\Bundle\ProductBundle\Entity\ProductAttribute;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;

/**
* Load products
*
* Execute with "php app/console doctrine:fixtures:load"
*
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*
*/
class LoadProductData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Get product manager
     * @return \Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager
     */
    protected function getProductManager()
    {
        return $this->container->get('product_manager');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $nbProducts = 25;
        $batchSize = 500;

        // get scopes
        $scopeEcommerce = $this->getReference('channel.ecommerce');
        $scopeMobile    = $this->getReference('channel.mobile');

        // force in english because product is translatable
        $locale = $this->getReference('language.en_US');
        $this->getProductManager()->setLocale($locale->getCode());

        // get currency
        $currencyUSD = $this->getReference('currency.USD');

        // get attributes by reference
        $attName        = $this->getReference('product-attribute.name');
        $attDate        = $this->getReference('product-attribute.releaseDate');
        $attDescription = $this->getReference('product-attribute.shortDescription');
        $attSize        = $this->getReference('product-attribute.size');
        $attColor       = $this->getReference('product-attribute.color');
        $attPrice       = $this->getReference('product-attribute.price');

        // get attribute color options
        $optColors = $this->getProductManager()->getAttributeOptionRepository()->findBy(
            array('attribute' => $attColor->getAttribute())
        );
        $colors = array();
        foreach ($optColors as $option) {
            $colors[]= $option;
        }

        // get attribute size options
        $optSizes = $this->getProductManager()->getAttributeOptionRepository()->findBy(
            array('attribute' => $attSize->getAttribute())
        );
        $sizes = array();
        foreach ($optSizes as $option) {
            $sizes[]= $option;
        }

        $descriptions = array('my long description', 'my other description');
        for ($ind= 0; $ind < $nbProducts; $ind++) {

            // sku
            $prodSku = 'sku-'.$ind;
            $product = $this->getProductManager()->createFlexible();
            $product->setSku($prodSku);

            // name
            $names = array('en_US' => 'my product name', 'fr_FR' => 'mon nom de produit', 'de_DE' => 'produkt namen');
            foreach ($names as $locale => $data) {
                $value = $this->getProductManager()->createFlexibleValue();
                $value->setAttribute($attName->getAttribute());
                $value->setLocale($locale);
                $value->setData($data.' '.$ind);
                $product->addValue($value);
            }

            // description
            $locales = array('en_US', 'fr_FR', 'de_DE');
            $scopes = array('ecommerce', 'mobile');
            foreach ($locales as $locale) {
                foreach ($scopes as $scope) {
                    $value = $this->getProductManager()->createFlexibleValue();
                    $value->setLocale($locale);
                    $value->setScope($scope);
                    $value->setAttribute($attDescription->getAttribute());
                    $product->addValue($value);
                    $value->setData('description ('.$locale.') ('.$scope.') '.$ind);
                }
            }

            // size
            $value = $this->getProductManager()->createFlexibleValue();
            $value->setAttribute($attSize->getAttribute());
            $firstSizeOpt = $sizes[rand(0, count($sizes)-1)];
            $value->addOption($firstSizeOpt);
            $product->addValue($value);

            // color
            $value = $this->getProductManager()->createFlexibleValue();
            $value->setAttribute($attColor->getAttribute());
            $firstColorOpt = $colors[rand(0, count($colors)-1)];
            $value->addOption($firstColorOpt);
            $secondColorOpt = $colors[rand(0, count($colors)-1)];
            if ($firstColorOpt->getId() != $secondColorOpt->getId()) {
                $value->addOption($secondColorOpt);
            }
            $product->addValue($value);

            // price
            $value = $this->getProductManager()->createFlexibleValue();
            $value->setAttribute($attPrice->getAttribute());
            $value->setData(rand(5, 100));
            $value->setCurrency('USD');
            $product->addValue($value);

            $this->getProductManager()->getStorageManager()->persist($product);

            if (($ind % $batchSize) == 0) {
                $this->getProductManager()->getStorageManager()->flush();
                // detaches all products and values from doctrine
                $this->getProductManager()->getStorageManager()->clear('Acme\Bundle\DemoFlexibleEntityBundle\Entity\Product');
                $this->getProductManager()->getStorageManager()->clear('Acme\Bundle\DemoFlexibleEntityBundle\Entity\ProductValue');
            }
        }

        $this->getProductManager()->getStorageManager()->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 3;
    }
}
