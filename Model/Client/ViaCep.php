<?php


namespace TheITNerd\UX\Model\Client;

use Exception;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Exception\NotFoundException;
use TheITNerd\UX\Helper\Cache;
use TheITNerd\UX\Model\Cache\Viacep as ViacepCache;

/**
 * Class ViaCep
 * @package TheITNerd\UX\Model\Client
 */
class ViaCep
{
    public const COUNTRY_ID = "BR";

    /**
     * @var string
     */
    protected string $apiBaseEndpoint = "https://viacep.com.br/ws/";

    /**
     * @var array|string[]
     */
    protected array $apiEndpoints = [
        'get_address_by_zipcode' => '{zip}/json'
    ];

    /**
     * @var RegionFactory
     */
    private RegionFactory $regionFactory;

    /**
     * @var Cache
     */
    private Cache $cache;

    /**
     * ViaCep constructor.
     * @param RegionFactory $regionFactory
     * @param Cache $cache
     */
    public function __construct(
        RegionFactory $regionFactory,
        Cache $cache
    )
    {
        $this->regionFactory = $regionFactory;
        $this->cache = $cache;
    }

    /**
     * @param string $zipcode
     * @return array
     * @throws NotFoundException
     */
    public function getAddressByZipcode(string $zipcode): array
    {
        $zipcode = preg_replace('/\D/', '', $zipcode);

        $endpoint = str_replace('{zip}', $zipcode, $this->apiEndpoints['get_address_by_zipcode']);

        $initialData = $this->makeRequest("{$this->apiBaseEndpoint}{$endpoint}");

        return [
            'region_id' => $this->findRegion($initialData['uf']),
            'zipcode' => $initialData['cep'],
            'street' => $initialData['logradouro'],
            'complement' => $initialData['complemento'],
            'neighborhood' => $initialData['bairro'],
            'city' => $initialData['localidade'],
            'region' => $initialData['uf'],
            'ibge' => $initialData['ibge'],
            'gia' => $initialData['gia'],
            'ddd' => $initialData['ddd'],
            'siafi' => $initialData['siafi'],
            'country_code' => self::COUNTRY_ID,
        ];
    }

    /**
     * @param string $region
     * @param string $city
     * @param string $street
     * @return array
     */
    public function getZIPCodeByAddress(string $region, string $city, string $street): array
    {
        return $this->makeRequest("{$this->apiBaseEndpoint}{$region}/{$city}/{$street}/json/");
    }

    /**
     * @param $endpoint
     * @param string $type
     * @return mixed
     */
    protected function makeRequest($endpoint, string $type = 'GET')
    {

        $key = ViacepCache::TYPE_IDENTIFIER."_{$endpoint}_{$type}";
        if($data = $this->cache->load($key)) {
            return $data;
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $type,
        ));

        $response = json_decode(curl_exec($curl), true);

        curl_close($curl);

        $this->cache->save($key, $response, [ViacepCache::CACHE_TAG]);

        return $response;
    }

    /**
     * @param string $uf
     * @return mixed
     * @throws NotFoundException
     */
    protected function findRegion(string $uf)
    {
        try {
            $region = $this->regionFactory->create();
            return $region->loadByCode($uf, self::COUNTRY_ID)->getId();
        } catch (Exception $e) {
            throw new NotFoundException(__('Region not found'));
        }

    }

}
