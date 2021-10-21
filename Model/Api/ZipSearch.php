<?php
namespace TheITNerd\UX\Model\Api;


use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use TheITNerd\UX\Api\ZipSearchInterface;
use TheITNerd\UX\Model\Client\ViaCep;

class ZipSearch implements ZipSearchInterface
{

    /**
     * @var ViaCep
     */
    private ViaCep $viaCepClient;

    /**
     * @var Http
     */
    private Http $request;

    /**
     * ZipSearch constructor.
     * @param Http $request
     * @param ViaCep $viaCepClient
     */
    public function __construct(
        Http $request,
        ViaCep $viaCepClient
    )
    {
        $this->viaCepClient = $viaCepClient;
        $this->request = $request;
    }

    /**
     * ${inheritdoc}
     */
    public function searchAddress(): array
    {

        if(!$this->request->has('postcode')) {
            throw new LocalizedException(__('Please inform the postcode'));
        }

        return [$this->viaCepClient->getAddressByZipcode($this->request->getParam('postcode'))];
    }

    /**
     * ${inheritdoc}
     */
    public function searchZIPCode(): array
    {
        if(
            !$this->request->has('region')
            || !$this->request->has('city')
            || !$this->request->has('street')
        ) {
            throw new LocalizedException(__('Please inform the region, city and street'));
        }

        return $this->viaCepClient->getZIPCodeByAddress($this->request->getParam('region'), $this->request->getParam('city'), $this->request->getParam('street'));
    }
}
