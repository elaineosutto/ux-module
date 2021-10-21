<?php /** @noinspection PhpUndefinedMethodInspection */

namespace TheITNerd\UX\Model\Api;


use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\QuoteFactory;
use TheITNerd\UX\Api\CalculateCarriersRatesInterface;
use TheITNerd\UX\Model\Client\ViaCep;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

/**
 * Class CalculateCarriersRates
 * @package TheITNerd\UX\Model\Api
 */
class CalculateCarriersRates implements CalculateCarriersRatesInterface
{

    /**
     * @var ProductInterface
     */
    protected $product;
    /**
     * @var ViaCep
     */
    private ViaCep $viaCepClient;
    /**
     * @var Http
     */
    private Http $request;
    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;
    /**
     * @var QuoteFactory
     */
    private QuoteFactory $quoteFactory;
    /**
     * @var TotalsCollector
     */
    private TotalsCollector $totalsCollector;

    /**
     * @var PriceHelper
     */
    private PriceHelper $priceHelper;

    /**
     * ZipSearch constructor.
     * @param Http $request
     * @param ViaCep $viaCepClient
     * @param ProductRepository $productRepository
     * @param QuoteFactory $quoteFactory
     * @param TotalsCollector $totalsCollector
     * @param PriceHelper $priceHelper
     */
    public function __construct(
        Http $request,
        ViaCep $viaCepClient,
        ProductRepository $productRepository,
        QuoteFactory $quoteFactory,
        TotalsCollector $totalsCollector,
        PriceHelper $priceHelper
    )
    {
        $this->viaCepClient = $viaCepClient;
        $this->request = $request;
        $this->priceHelper = $priceHelper;

        $this->productRepository = $productRepository;
        $this->quoteFactory = $quoteFactory;
        $this->totalsCollector = $totalsCollector;
    }

    /**
     * ${inheritdoc}
     */
    public function searchAddress(): array
    {
        return [$this->viaCepClient->getAddressByZipcode($this->request->getParam('zipcode'))];
    }


    /**
     * ${inheritdoc}
     */
    public function calculateCarriersRates(): array
    {

        if(
            !$this->request->has('product')
            || !$this->request->has('postcode')
        ) {
            throw new LocalizedException(__('Please inform the product and postcode'));
        }

        $product = $this->getProduct();

        $quote = $this->quoteFactory->create();
        $quote->setCurrency();

        $options = $this->getProductOptions();

        $quote->addProduct($product, $options);

        $quote->getShippingAddress()->setCountryId(ViaCep::COUNTRY_ID);
        $quote->getShippingAddress()->setPostcode($this->request->getParam('postcode'));

        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true);

        $this->totalsCollector->collectAddressTotals($quote, $shippingAddress);

        $shippingRates = $shippingAddress->getGroupedAllShippingRates();

        $output = [];

        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $title = $rate->getMethodTitle();

                if ($title) {
                    $output[] = [
                        'title' => $title,
                        'price' => $this->priceHelper->currency($rate->getPrice(), true, false)
                    ];
                }
            }
        }

        return $output;

    }

    /**
     * @return ProductInterface|mixed|null
     * @throws NoSuchEntityException
     */
    protected function getProduct()
    {
        if (!$this->product) {
            $this->product = $this->productRepository->getById($this->request->getParam('product'));
        }

        return $this->product;
    }

    /**
     * @return DataObject
     * @throws NoSuchEntityException
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function getProductOptions(): DataObject
    {
        $options = new DataObject;
        $options->setProduct($this->getProduct()->getId());
        $options->setQty($this->request->getParam('qty'));

        if (!strcmp($this->getProduct()->getTypeId(), 'configurable')) {
            $options->setSuperAttribute($this->request->getParam('super_attribute'));
        } else if (!strcmp($this->getProduct()->getTypeId(), 'bundle')) {
            $options->setBundleOption($this->request->getParam('bundle_option'));
            $options->setBundleOptionQty($this->request->getParam('bundle_option_qty'));
        }

        return $options;

    }
}
