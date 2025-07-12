<?php

/**
 * Setup
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

/**
 * Helpers
 */

function parseTemplate(object $data): string {
    $template = file_get_contents("./templates/$data->template.html");

    if (file_exists("$data->path/component.css")) {
        $template = preg_replace(
            '/(<head.*?>.*?)(<link\b)/is',
            '$1<link href="component.css" rel="stylesheet">$2',
            $template
        );
    }

    $contents = [
        '%title%' => $data->title,
        '%description%' => $data->description,
        '%component%' => $data->component,
    ];

    return str_replace(
        array_keys($contents),
        array_values($contents),
        $template
    );
}

function getComponents(string $path): array {
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
            
            $components[] = $data;
        }
    }

    return $components ?? [];
}

/**
 * Generator
 */

$components = getComponents('./');

foreach ($components as $component) {
    $contents = parseTemplate($component);
    file_put_contents("$component->path/index.html", $contents);
}

/**
 * Preview
 */

$page = filter_input(INPUT_GET, 'preview');
$html = file_get_contents(($page ? "./$page/" : '') . 'index.html');
$html = preg_replace('/href="component\.css"/i', 'href="./' . $page . '/component.css"', $html);
$html = preg_replace('/src="(?!http|\/)([^"]+)"/i', 'src="./' . $page . '/$1"', $html);

exit($html);

?>