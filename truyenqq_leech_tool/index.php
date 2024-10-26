<?php
$user_agent_list = array(
   "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36",
   "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36",
   "Mozilla/5.0 (iPad; CPU OS 15_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/104.0.5112.99 Mobile/15E148 Safari/604.1",
   "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.3",
   "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36 Edg/124.0.0",
   "Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Mobile Safari/537.3"
);

function onechapter($web, $headers, $output_dir) {
    $res = file_get_contents($web, false, stream_context_create(array(
        'http' => array(
            'header' => implode("\r\n", $headers)
        )
    )));
    $soup = new DOMDocument();
    $soup->loadHTML($res);
    $h1_tag = $soup->getElementsByTagName("h1")->item(0);
    if ($h1_tag) {
        $h1_text = trim(preg_replace('/\s+/', ' ', $h1_tag->textContent));
        echo $h1_text . "\n";
    }
    $img_links = array();
    $pages = $soup->getElementsByTagName("div");
    foreach ($pages as $page) {
        if ($page->getAttribute("class") == "page-chapter") {
            $imgs = $page->getElementsByTagName("img");
            foreach ($imgs as $img) {
                $img_links[] = $img->getAttribute("data-original");
            }
        }
    }
    if ($output_dir == '') {
        $folder = __DIR__ . "/downloads/" . $h1_text;
    } else {
        $folder = $output_dir . "/" . $h1_text;
    }
    if (!file_exists($folder)) {
        mkdir($folder, 0755, true);
    }
    foreach ($img_links as $index => $link) {
        echo $link . "\n";
        $file = $folder . "/image_" . $index . ".jpg";
        $response = file_get_contents($link, false, stream_context_create(array(
            'http' => array(
                'header' => implode("\r\n", $headers)
            )
        )));
        file_put_contents($file, $response);
    }
    sleep(1);
    echo "Done.\n";
}

function allchapters($web, $headers, $domain) {
    $res = file_get_contents($web, false, stream_context_create(array(
        'http' => array(
            'header' => implode("\r\n", $headers)
        )
    )));
    $soup = new DOMDocument();
    $soup->loadHTML($res);
    $chapters = array();
    $chapter_items = $soup->getElementsByTagName("div");
    foreach ($chapter_items as $item) {
        if ($item->getAttribute("class") == "works-chapter-item") {
            $links = $item->getElementsByTagName("a");
            foreach ($links as $link) {
                $chapters[] = $domain . $link->getAttribute("href");
            }
        }
    }
    $chapters = array_reverse($chapters);
    echo implode("\n", $chapters) . "\n";
    $h1_tag = $soup->getElementsByTagName("h1")->item(0);
    if ($h1_tag) {
        $title = trim(preg_replace('/\s+/', ' ', $h1_tag->getAttribute("itemprop")));
        echo $title . "\n";
    }
    $output_dir = __DIR__ . "/downloads/" . $title;
    foreach ($chapters as $link) {
        onechapter($link, $headers, $output_dir);
    }
}

$web = trim(fgets(STDIN));
echo "**!** Tool has some limitations, and I will always try to update to keep up with the website.\n";
sleep(5);
echo "Running...\n";
$referer = "https://" . explode("/", $web)[2] . "/";
$domain = "https://" . explode("/", $web)[2];
echo "Server: " . $referer . "\n";
$headers = array(
    'Connection: keep-alive',
    'Cache-Control: max-age=0',
    'Upgrade-Insecure-Requests: 1',
    'User-Agent: ' . $user_agent_list[array_rand($user_agent_list)],
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
    'Accept-Encoding: gzip, deflate',
    'Accept-Language: en-US,en;q=0.9,fr;q=0.8',
    'referer: ' . $referer
);
if (strpos($web, "chap") !== false) {
    echo "Looks like this is a link to a single chapter. Proceeding to download...\n";
    $output_dir = '';
    onechapter($web, $headers, $output_dir);
} else {
    echo "Looks like this is a link to a full story. Proceeding to download all chapters...\n";
    allchapters($web, $headers, $domain);
}