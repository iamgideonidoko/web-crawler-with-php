<?php declare(strict_types=1);

require 'vendor/autoload.php';

use \PHPHtmlParser\Dom;
use \GuzzleHttp\Client;


class Crawler {
    /**
     * Store processed URLs
     */
    private array $processedUrls;
    /**
     * Store unprocessed URLs
     */
    private array $urlFrontier;
    /**
     * Crawl depth
     */
    private int $depth;

    /**
     * constructor
     * 
     * @param array $urls
     * @param array $depth
     *
     * @return void
     */
    public function __construct($urls = [], $depth = 3) {
        $this->processedUrls = [];
        $this->urlFrontier = $urls;
        $this->depth = $depth;
    }

    /**
     * Get HTML
     * 
     * @param string $url
     *
     * @return string
     */
    private function fetcher(string $url): string {
        $httpClient = new Client();
        $response = $httpClient->get($url);
        $html = (string) $response->getBody();
        return $html;
    }

    /**
     * Parse HTML into DOM
     * 
     * @param string $html
     *
     * @return Dom
     */
    private function parser(string $html): Dom {
        $dom = new Dom();
        $dom->loadStr($html);
        return $dom;
    }

    /**
     * Extract URLs and load them to the frontier if not processed
     * 
     * @param string $url
     * @param Dom  $dom
     *
     * @return Generator
     */
    private function urlExtractor(string $url, Dom $dom): Generator {
        $anchors = $dom->find('a');
        foreach ($anchors as $anchor) {
            $path = $anchor->getAttribute('href');
            if (isset($path) && gettype($path) == 'string' && str_starts_with($path, '/')) {
                $path = explode('?', trim($url, '/').'/'.trim($path, '/'))[0];
            }
            yield $path;
        }
    }

    /**
     * Detect if URL is processed and load them to the frontier if not
     * 
     * @param mixed $url
     *
     * @return Generator
     */
    private function urlDetector(mixed $url) {
        if (isset($url) && !in_array($url, $this->processedUrls) && !in_array($url, $this->urlFrontier)) {
            array_push($this->urlFrontier, $url);
        }
    }

    /**
     * Initiate a crawl work cycle
     * 
     * @param string $url
     *
     * @return void
     */
    private function crawl(string $url) {
        $html = $this->fetcher($url);
        $dom = $this->parser($html);
        $gen = $this->urlExtractor($url, $dom);
        $first = $gen->current();
        $this->urlDetector($first);
        $gen->next();
        while ($gen->valid()) {
            $this->urlDetector($gen->current());
            $gen->next();
        }
    }

    /**
     * Kickstart crawler
     *
     * @return void
     */
    public function run() {
        while (count($this->urlFrontier) > 0 && $this->depth > 0) {
            $url = array_pop($this->urlFrontier);
            $this->depth--;
            echo "Crawling: $url".PHP_EOL;
            try {
                $this->crawl($url);
            } catch(Exception $e) {
                echo "Failed to crawl: $url".PHP_EOL;
            } finally {
                array_push($this->processedUrls, $url);
            }
        }
    }

}

$newCrawler = new Crawler(['https://www.imdb.com'], 10);
$newCrawler->run();
