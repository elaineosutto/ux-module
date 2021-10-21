<?php
namespace TheITNerd\UX\Plugin;


use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\View\Asset\Repository;

/**
 * Class LazyResponse
 * @package TheITNerd\UX\Plugin
 */
class LazyResponse
{
    /**
     * @var string
     */
    protected string $placeholder = '';
    /**
     * @var Repository
     */
    private Repository $assetsRepository;
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * LazyResponse constructor.
     * @param RequestInterface $request
     * @param Repository $assetsRepository
     */
    public function __construct(
        RequestInterface $request,
        Repository $assetsRepository
    )
    {
        $this->assetsRepository = $assetsRepository;
        $this->request = $request;
    }

    /**
     * @param Http $response
     * @return Http
     */
    public function beforeSendResponse(Http $response): Http
    {
        $this->placeholder = $this->assetsRepository->getUrl('TheITNerd_UX::images/loader.gif');

        $body = $response->getBody();

        if (strpos($body, '<img') === false) {
            return $response;
        }

        if ($this->request->isXMLHttpRequest()) {
            $contentType = $response->getHeader('Content-Type');
            if ($contentType && $contentType->getMediaType() === 'application/json') {
                return $response;
            }
        }

        $this->addCssPreload($body)
            ->addJsPreload($body)
            ->addLazyLoad($body);

        $response->setBody($body);

        return $response;
    }

    /**
     * @param string $body
     * @return $this
     */
    private function addCssPreload(string &$body): self
    {


        preg_match_all('/href="(.+\.css)"/', $body, $matches, PREG_SET_ORDER);

        $preloads = [];

        foreach(array_column($matches, 1) as $style) {
            $preloads[] = '<link rel="preload" href="'.$style.'" as="style">';
        }

        $body = str_replace('<title>', implode("\n", $preloads)."\n<title>", $body);

        return $this;
    }

    /**
     * @param string $body
     * @return $this
     */
    private function addJsPreload(string &$body): self
    {


        preg_match_all('/src="(.+\.js)"/', $body, $matches, PREG_SET_ORDER);

        $preloads = [];

        foreach(array_column($matches, 1) as $script) {
            $preloads[] = '<link rel="preload" href="'.$script.'" as="script">';
        }

        $body = str_replace('<title>', implode("\n", $preloads)."\n<title>", $body);

        return $this;
    }

    /**
     * @param string $body
     */
    private function addLazyLoad(string &$body): void
    {
        $placeholder = $this->placeholder;

        $body = preg_replace_callback_array(
            [
                '/<img([^>]+?)width=[\'"]?([^\'"\s>]+)[\'"]([^>]+?)height=[\'"]?([^\'"\s>]+)[\'"]?([^>]*)>/' => function ($match) use ($placeholder) {
                    return $this->addLazyLoadImage($match[0], $placeholder);
                },
                '/<img([^>]+?)height=[\'"]?([^\'"\s>]+)[\'"]([^>]+?)width=[\'"]?([^\'"\s>]+)[\'"]?([^>]*)>/' => function ($match) use ($placeholder) {
                    return $this->addLazyLoadImage($match[0], $placeholder);
                },
                '/<img .*>/' => function ($match) use ($placeholder) {
                    return $this->addLazyLoadImage($match[0], $placeholder);
                }
            ],
            $body
        );
    }

    /**
     * @param $content
     * @param $placeholder
     * @return array|string|string[]|null
     */
    private function addLazyLoadImage($content, $placeholder)
    {
        return preg_replace_callback(
            '/<img\s*.*?(?:class="(.*?)")?([^>]*)>/',
            static function ($match) use ($placeholder) {
                if (stripos($match[0], ' data-srcset=') !== false) {
                    return $match[0];
                }

                if (stripos($match[0], ' class=') !== false) {
                    $lazy = str_replace([' class="', " class='"], [' class="lazyload ', " class='lazyload "], $match[0]);
                } else {
                    $lazy = str_replace('<img ', '<img class="lazyload" ', $match[0]);
                }

                return str_replace([' src="', " src='"], [' src="' . $placeholder . '" data-srcset="', " src='" . $placeholder . "' data-srcset='"], $lazy);
            },
            $content
        );
    }
}
