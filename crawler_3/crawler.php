<?php declare(strict_types=1);

require 'vendor/autoload.php';

use \Spatie\Crawler\Crawler;
use \Spatie\Crawler\CrawlObservers\CrawlObserver;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

$crawlObserver = new class extends CrawlObserver {

    /**
     * Called when the crawler will crawl the url.
     * 
     * @param \Psr\Http\Message\UriInterface $url
     */
    public function willCrawl(UriInterface $url): void
    {
    }

    /**
     * Called when the crawler has crawled the given url successfully.
     * 
     * @param \Psr\Http\Message\UriInterface $url
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \Psr\Http\Message\UriInterface|null $foundOnUrl
     */
    public function crawled(
        UriInterface $url,
        ResponseInterface $response,
        ?UriInterface $foundOnUrl = null
    ): void {
        echo "Crawling: ".$url->__toString().PHP_EOL;
    }

    /**
     * Called when the crawler had a problem crawling the given url.
     * 
     * @param \Psr\Http\Message\UriInterface $url
     * @param \GuzzleHttp\Exception\RequestException $requestException
     * @param \Psr\Http\Message\UriInterface|null $foundOnUrl
     */
    public function crawlFailed(
        UriInterface $url,
        RequestException $requestException,
        ?UriInterface $foundOnUrl = null
    ): void {
        echo "Failed to crawl: ".$url->__toString().PHP_EOL;
    }

    /**
     * Called when the crawl has ended.
     */
    public function finishedCrawling(): void {
        echo "Crawling Done!".PHP_EOL;
    }

};

Crawler::create()
    ->setCrawlObserver($crawlObserver) // hooks into the crawl process
    ->setConcurrency(2) // crawl 2 URLs at once
    ->setCurrentCrawlLimit(10) // process 10 URLs
    ->setMaximumResponseSize(1024 * 1024 * 2) // only crawl files less than 2 MB
    ->setMaximumDepth(2) // limits the crawl depth to 2
    ->setDelayBetweenRequests(500) // delay new requests by 500ms
    ->setParseableMimeTypes(['text/html', 'text/plain']) // only crawl text and html files
    ->startCrawling('https://www.imdb.com'); // kickstart crawler
