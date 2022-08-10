<?php declare(strict_types=1);

require 'vendor/autoload.php';

use \RoachPHP\Http\Response;
use \RoachPHP\Spider\BasicSpider;
use \RoachPHP\Roach;

class ImdbSpider extends BasicSpider {
    /**
     * @var string[]
     */
    public array $startUrls = [
        'https://imdb.com'
    ];
  
    /**
     * How many requests are allowed to be sent concurrently.
     */
    public int $concurrency = 2;
  
    /**
     * The delay (in seconds) between requests.
     */
    public int $requestDelay = 5;

    public function parse(Response $response): \Generator {
        // $anchors = $response->filter('a');
        $links = $response->filter('a')->links();
        foreach ($links as $link) {
            $url = $link->getUri();
            yield $this->item([
                'URL' => $url,
            ]);
            // yield $this->request('GET', $url);
        }
    }
}

Roach::startSpider(ImdbSpider::class);


// $items = Roach::collectSpider(ImdbSpider::class);
// print_r($items);