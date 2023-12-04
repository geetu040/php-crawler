<?php

require "config.php";
require "crawler-utils.php";

// ======> PARAMS

// $url = "https://nust.edu.pk/news/finding-innovative-creative-solutions-fics-23-concludes-at-nust/";
// $url = "https://github.com/geetu040";
// $url = "https://www.google.com/search?q=cat";

// $depth = 6;

$requestData = json_decode(file_get_contents('php://input'), true);
$url = $requestData['url'];
$depth = $requestData['depth'];

// ======> CLEANING OUTPUT DIRECTORY

$files = glob($output_dir . '*');
foreach ($files as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}

logMessage("Cleaned output directory: $output_dir");

// ======> ROBOTS.TXT

$baseUrl = getBaseUrl($url);
$disallowedUrls = getDisallowedLinksFromRobotsTxt($baseUrl);

logMessage("Loaded robots.txt and disallowed URLs");

// ======> PREPARING FOR OPERATION

$orig_url = $url;
$urls_to_scrap = [$url];        # urls to be fetched are pushed here
$completed_urls = [];

while ($depth > 0 && !empty($urls_to_scrap)) {

    // ======> LOADING PAGE
    $current_url = pop_highest_priority_url($urls_to_scrap, $orig_url);
    if (in_array($current_url, $completed_urls)) {
        continue;
    }
    $htmlDom = scrapeUrl($current_url);
    if ($htmlDom == FALSE) {
        logMessage("Failed to load page: $current_url");
        continue;
    } else {
        logMessage("Loaded page successfully: $current_url");
    }

    saveDom(
        $htmlDom, // DOM Object
        "loaded_pages/$depth.html"    // path to save
    );

    // ======> EXTRACTING NEW URLS

    $more_urls = getAllHrefsFromAnchorTags($htmlDom);
    $more_urls_filtered = [];
    foreach ($more_urls as $url) {

        // Remove URLs starting with "#"
        if (strpos($url, '#') === 0) {
            continue;
        }

        // Add base URL to URLs starting with "/"
        if (strpos($url, '/') === 0) {
            $url = rtrim(getBaseUrl($current_url), '/') . $url;
        }

        // Validate URL pattern
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            continue;
        }

        // Remove Already Scrapped URL
        if (in_array($url, $completed_urls)) {
            continue;
        }

        // Remove Disallowed URLs
        if (!isUrlAllowed($url, $disallowedUrls)) {
            continue;
        }

        $more_urls_filtered[] = $url;
    }

    // ======> PAGE SUCCESSFUL, UPDATING URLS

    $more_urls_filtered = array_unique($more_urls_filtered);
    $urls_to_scrap = array_merge($urls_to_scrap, $more_urls_filtered);
    $depth--;
    $completed_urls[] = $current_url;

    logMessage("Remaining depth: $depth");
}

// Close the log file
fclose($logFile);

// JSON RESPONSE
$response = [
    'success' => true,
    'message' => 'Operation completed successfully'
];
header('Content-Type: application/json');
echo json_encode($response);

?>
