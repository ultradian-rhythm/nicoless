<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

/**
 * Pagemap SSG
 * Copyright 2025, Nico Less (https://nicoless.de)
 * Version 20250715
 */

$assets = [
    'template/standards.css' => null,
    'template/main.css' => null,
    'template/modules.css' => null,
    'template/mobile.css' => null,
    'template/pagemap.js' => null,
];

$feed = [
    'title' => 'Recommended Music — Nico Less',
    'description' => 'Recommendations for slow and melancholic music, between Ambient and Experimental. New recommendations once a week.',
    'language' => 'en-US',
    'link' => 'https://nicoless.de',
    'ttl' => '1440',
    'file' => 'rss.xml',

    'categories' => [
        'recommendations' => 'Recommendations',
    ],
];

/**
 * Helpers
 */

function parseTemplate(object $data): string {
    global $assets;

    $template = file_get_contents("./template/$data->template.html");

    if (file_exists("$data->path/component.css")) {
        $template = preg_replace(
            '/(<head.*?>.*?)(<link\b)/is',
            '$1<link rel="stylesheet" href="component.css">$2',
            $template
        );
    }

    if (isset($data->teaser)) {
        $data->component = str_replace(
            '%teaser%',
            implode(PHP_EOL, getSubpages($data->teaser)),
            $data->component
        );
    }

    $contents = [
        '%robots%' => $data->robots ?? 'index, follow',
        '%title%' => $data->title,
        '%description%' => $data->description,
        '%component%' => $data->component,
    ];

    foreach ($assets as $filepath => $cachepath) {
        $template = str_replace($filepath, $cachepath, $template);
    }

    return str_replace(
        array_keys($contents),
        array_values($contents),
        $template
    );
}

function getComponents(string $path, string|null $parent = null): array {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() == 'component.json') {
            $contents = file_get_contents($file->getPathname());
            $data = json_decode($contents);

            $data->path = $file->getPath();
            $data->component = file_get_contents("$data->path/component.html");

            if ($parent && ($data->parent ?? null) != $parent) {
                continue;
            }
            
            $components[] = $data;
        }
    }

    return $components ?? [];
}

function getSubpages(object $teaser): array {
    $components = getComponents($teaser->path, $teaser->path);
    $template = file_get_contents("./template/components/$teaser->template.html");

    usort($components, function($a, $b) {
        return strtotime($b->date ?? '') <=> strtotime($a->date ?? '');
    });

    foreach ($components as $component) {
        if ($component->date ?? false) {
            $dateformat = (new IntlDateFormatter('en_US', 0, 0, null, null, $teaser->dateFormat))->format(new DateTime($component->date));
        }

        if ($component->tags ?? false) {
            $tags = array_map('trim', explode(',', $component->tags));
            $tags = implode(PHP_EOL, array_map(fn($tag) => "<li>$tag</li>", $tags));
        }

        $contents = [
            '%title%' => $component->title,
            '%description%' => $component->description,
            '%image%' => "/$component->path/cover.webp",
            '%path%' => $component->path,
            '%date%' => $component->date ?? null,
            '%dateformat%' => $dateformat ?? null,
            '%tags%' => $tags ?? null,
        ];
    
        $subpages[] = str_replace(
            array_keys($contents),
            array_values($contents),
            $template
        );
    }

    return $subpages ?? [];
}

/**
 * Build assets
 */

array_map('unlink', array_filter(glob('template/cache/*'), 'is_file'));

foreach ($assets as $filepath => &$cachepath) {
    $filemtime = filemtime($filepath);
    $pathinfo = pathinfo($filepath);
    $cachepath = "template/cache/$pathinfo[filename].$filemtime.$pathinfo[extension]";

    copy($filepath, $cachepath);
}

/**
 * Build site and feed
 */

$components = getComponents('./');

$rss = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"></rss>');
$channel = $rss->addChild('channel');
$channel->addChild('title', htmlspecialchars($feed['title']));
$channel->addChild('link', htmlspecialchars($feed['link']));
$channel->addChild('description', htmlspecialchars($feed['description']));
$channel->addChild('language', htmlspecialchars($feed['language']));
$channel->addChild('lastBuildDate', date(DATE_RSS));
$channel->addChild('ttl', $feed['ttl']);

foreach ($components as $component) {
    // create static page
    $contents = parseTemplate($component);
    file_put_contents("$component->path/index.html", $contents);

    // add feed entry
    if (in_array($component->parent ?? null, array_keys($feed['categories']))) {
        $path = trim($feed['link'], '/') . trim($component->path, '.');

        $item = $channel->addChild('item');
        $item->addChild('title', htmlspecialchars($component->title));
        $item->addChild('description', htmlspecialchars($component->description));
        $item->addChild('pubDate', date(DATE_RSS, strtotime($component->date)));
        $item->addChild('link', htmlspecialchars($path));
        $item->addChild('category', htmlspecialchars($feed['categories'][$component->parent]));

        $image = is_file("$component->path/cover.webp") ? "$path/cover.webp" : false;

        if ($image) {
            $enclosure = $item->addChild('enclosure');
            $enclosure->addAttribute('url', $image);
            $enclosure->addAttribute('length', filesize("$component->path/cover.webp"));
            $enclosure->addAttribute('type', 'image/webp');
        }
    }
}

file_put_contents($feed['file'], $rss->asXML());

/**
 * Generate preview
 */

$page = trim(substr($_SERVER['REQUEST_URI'], strlen($_SERVER['SCRIPT_NAME'])), '/');
$html = file_get_contents(($page ? "./$page/" : '') . 'index.html');
$html = preg_replace('/href="component\.css"/i', 'href="/' . $page . '/component.css"', $html);
$html = preg_replace('/src="(?!http|\/)([^"]+)"/i', 'src="/' . $page . '/$1"', $html);

exit($html);

?>