<?php

function exitGracefully($message) {
	die($message);
}

function getBaseUrl($url) {
    // Parse the URL
    $parsedUrl = parse_url($url);

    // Check if the scheme and host components are set
    if (isset($parsedUrl['scheme']) && isset($parsedUrl['host'])) {
        // Build and return the base URL
        return $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
    }

    // If the scheme or host is not set, return an error or handle it accordingly
    exitGracefully("scheme or host name not set");
}

function getDisallowedLinksFromRobotsTxt($baseUrl) {
    // Construct the robots.txt URL
    $robotsTxtUrl = rtrim($baseUrl, '/') . '/robots.txt';

    // Initialize cURL session
    $ch = curl_init($robotsTxtUrl);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute cURL session and get the content of robots.txt
    $robotsTxtContent = curl_exec($ch);

    // Close cURL session
    curl_close($ch);

    // Check if robots.txt content is retrieved successfully
    if ($robotsTxtContent === false) {
        // Handle the error, e.g., by returning an empty array
        return [];
    }

    // Parse robots.txt content to extract disallowed links
    $disallowedLinks = [];
    $lines = explode("\n", $robotsTxtContent);

    foreach ($lines as $line) {
        $line = trim($line);

        // Check if the line starts with "Disallow:"
        if (strpos($line, 'Disallow:') === 0) {
            // Extract the disallowed path
            $disallowedPath = trim(substr($line, strlen('Disallow:')));

            // Construct the full disallowed link
            $disallowedLink = rtrim($baseUrl, '/') . $disallowedPath;

            // Add the disallowed link to the array
            $disallowedLinks[] = $disallowedLink;
        }
    }

    return $disallowedLinks;
}

function scrapeUrl($url) {
    // Initialize cURL session
    $ch = curl_init($url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute cURL session and get the HTML content
    $htmlContent = curl_exec($ch);

    // Check if cURL request was successful
    if ($htmlContent === false) {
        // Handle the error, e.g., by returning false
        return false;
    }

    // Check if the page contains a "PAGE NOT FOUND" message or has a 404 status
    if (trim($htmlContent) == "" || stripos($htmlContent, 'PAGE NOT FOUND') !== false || curl_getinfo($ch, CURLINFO_HTTP_CODE) == 404) {
        // Handle the "PAGE NOT FOUND" case, e.g., by returning false
        return false;
    }

    // Close cURL session
    curl_close($ch);

    // Create a DOMDocument object
    $dom = new DOMDocument();

    // Load the HTML content into the DOMDocument
    libxml_use_internal_errors(true); // Suppress warnings
    $dom->loadHTML($htmlContent);
    libxml_use_internal_errors(false); // Re-enable warnings

    // Return the DOMDocument
    return $dom;
}


function saveDom($dom, $filePath) {
    // Save HTML content to a file
    $htmlContent = $dom->saveHTML();
    file_put_contents($filePath, $htmlContent);
}

function getAllHrefsFromAnchorTags($dom) {
    $hrefs = array();

    // Get all anchor tags
    $anchorTags = $dom->getElementsByTagName('a');

    // Loop through the anchor tags and extract href attributes
    foreach ($anchorTags as $anchor) {
        // Get the href attribute
        $href = $anchor->getAttribute('href');

        // Add the href value to the array
        $hrefs[] = $href;
    }

    return $hrefs;
}

function basicClean($more_urls, $base_url) {
    $filtered_urls = array();

    foreach ($more_urls as $url) {
        // Remove URLs starting with "#"
        if (strpos($url, '#') === 0) {
            continue; // Skip this URL
        }

        // Add base URL to URLs starting with "/"
        if (strpos($url, '/') === 0) {
            $url = rtrim($base_url, '/') . $url;
        }

        // Validate URL pattern
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            continue; // Skip this URL
        }

        // Add the valid and modified URL to the filtered array
        $filtered_urls[] = $url;
    }

    return $filtered_urls;
}

function isUrlAllowed($url, $disallowedLinks) {
    // Iterate through the disallowed links
    foreach ($disallowedLinks as $disallowedLink) {
        // Convert wildcard characters to regex pattern
        $regexPattern = str_replace(['*', '/'], ['.*', '\/'], $disallowedLink);

        // Check if the given URL matches the disallowed link pattern
        if (preg_match('/^' . $regexPattern . '$/', $url)) {
            // If there's a match, the URL is disallowed
            return false;
        }
    }

    // If no match is found, the URL is allowed
    return true;
}

function filterUrls($more_urls, $prior_urls, $disallowedUrls) {
    $filteredUrls = [];

    foreach ($more_urls as $url) {
        if (isUrlAllowed($url, $disallowedUrls) && !in_array($url, $prior_urls)) {
            $filteredUrls[] = $url;
        }
    }

    return $filteredUrls;
}

function pop_highest_priority_url(&$urls, $target_url) {
    // Initialize an array to store Jaccard similarity scores for each URL
    $scores = [];

    // Convert URLs to sets of characters
    $targetSet = array_flip(str_split($target_url));

    // Calculate Jaccard similarity scores for each URL
    foreach ($urls as $url) {
        $urlSet = array_flip(str_split($url));

        // Calculate Jaccard similarity coefficient
        $intersection = count(array_intersect_key($targetSet, $urlSet));
        $union = count($targetSet) + count($urlSet) - $intersection;

        // Jaccard similarity score
        $jaccardSimilarity = $intersection / $union;

        // Store the Jaccard similarity score
        $scores[$url] = $jaccardSimilarity;
    }

    // Find the URL with the highest Jaccard similarity score
    $maxScoreUrl = array_search(max($scores), $scores);

    // Remove the URL with the highest score from the array
    $key = array_search($maxScoreUrl, $urls);
    if ($key !== false) {
        array_splice($urls, $key, 1);
    }

    // Return the chosen URL
    return $maxScoreUrl;
}



?>