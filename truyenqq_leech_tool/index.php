<?php
require 'vendor/autoload.php'; // Assuming you are using Composer for Guzzle and other libraries

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DomCrawler\Crawler;

$user_agent_list = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36",
    "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36",
    "Mozilla/5.0 (iPad; CPU OS 15_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/104.0.5112.99 Mobile/15E148 Safari/604.1",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.3",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36 Edg/124.0.0",
    "Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Mobile Safari/537.3"
];

function onechapter($web, $headers, $output_dir) {
    $client = new Client(['headers' => $headers]);
    try {
        $res = $client->get($web);
        $html_content = (string) $res->getBody();
        $crawler = new Crawler($html_content);
        $h1_tag = $crawler->filter('h1.detail-title.txt-primary')->first();
        if ($h1_tag->count() > 0) {
            $h1_text = preg_replace('/\s+/', ' ', trim($h1_tag->text()));
            echo $h1_text . PHP_EOL;
        }
        $img_links = [];
        $crawler->filter('div.page-chapter')->each(function (Crawler $node) use (&$img_links) {
            $node->filter('img')->each(function (Crawler $imgNode) use (&$img_links) {
                $img_links[] = $imgNode->attr('data-original');
            });
        });
        $folder = $output_dir === '' ? __DIR__ . "/downloads/$h1_text" : "$output_dir/$h1_text";
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }
        foreach ($img_links as $index => $link) {
            echo $link . PHP_EOL;
            $file = "$folder/image_$index.jpg";
            $response = $client->get($link);
            file_put_contents($file, $response->getBody());
        }
        sleep(1);
        echo "Xong." . PHP_EOL;
    } catch (RequestException $e) {
        echo "Error: " . $e->getMessage() . PHP_EOL;
    }
}

function allchapters($web, $headers, $domain) {
    $client = new Client(['headers' => $headers]);
    try {
        $res = $client->get($web);
        $html_content = (string) $res->getBody();
        $crawler = new Crawler($html_content);
        $chapters = [];
        $crawler->filter('div.works-chapter-item')->each(function (Crawler $node) use (&$chapters, $domain) {
            $node->filter('a')->each(function (Crawler $linkNode) use (&$chapters, $domain) {
                $chapters[] = $domain . $linkNode->attr('href');
            });
        });
        $chapters = array_reverse($chapters);
        print_r($chapters);
        $h1_tag = $crawler->filter('h1[itemprop="name"]')->first();
        if ($h1_tag->count() > 0) {
            $title = preg_replace('/\s+/', ' ', trim($h1_tag->text()));
            echo $title . PHP_EOL;
        }
        $output_dir = __DIR__ . "/downloads/$title";
        foreach ($chapters as $link) {
            onechapter($link, $headers, $output_dir);
        }
    } catch (RequestException $e) {
        echo "Error: " . $e->getMessage() . PHP_EOL;
    }
}

$web = readline("Nhập đường link của truyện: ");
echo "**!** Tool còn nhiều hạn chế, và mình sẽ luôn cố gắng cập nhật để bắt kịp với trang web." . PHP_EOL;
sleep(5);
echo "Running..." . PHP_EOL;
$referer = 'https://' . explode('/', $web)[2] . '/';
$domain = 'https://' . explode('/', $web)[2];
echo "Server: $referer" . PHP_EOL;
$headers = [
    'Connection' => 'keep-alive',
    'Cache-Control' => 'max-age=0',
    'Upgrade-Insecure-Requests' => '1',
    'User-Agent' => $user_agent_list[array_rand($user_agent_list)],
    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
    'Accept-Encoding' => 'gzip, deflate',
    'Accept-Language' => 'en-US,en;q=0.9,fr;q=0.8',
    'referer' => $referer
];
if (strpos($web, 'chap') !== false) {
    echo "Có vẻ đây là link của 1 chap đơn. Tiến hành tải..." . PHP_EOL;
    $output_dir = '';
    onechapter($web, $headers, $output_dir);
} else {
    echo "Có vẻ như đây là đường link của cả một truyện. Tiến hành tải tất cả chương mà truyện hiện có..." . PHP_EOL;
    allchapters($web, $headers, $domain);
}
?>

