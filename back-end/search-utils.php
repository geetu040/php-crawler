<?php

function parseHtmlFiles($outputDir)
{
    $domObjects = [];

    // Iterate through HTML files in the specified directory
    $htmlFiles = glob($outputDir . '/*.html');
    foreach ($htmlFiles as $htmlFile) {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true); // Suppress warnings for HTML5 elements
        $dom->loadHTMLFile($htmlFile);
        libxml_clear_errors();

        $domObjects[] = $dom;
    }

    return $domObjects;
}
function getTagTexts($elements) {
    $texts = [];
    foreach ($elements as $element) {
        $texts[] = $element->textContent;
    }
    return $texts;
}
function extractDom($doms) {

    $data = [];

    foreach ($doms as $dom) {

        // Retrieve text from HTML tags
        $titleElement = $dom->getElementsByTagName('title')->item(0);
        $headings = $dom->getElementsByTagName('h1');
        $paragraphs = $dom->getElementsByTagName('p');
        $spans = $dom->getElementsByTagName('span');
        $anchors = $dom->getElementsByTagName('a');
        $listItems = $dom->getElementsByTagName('li');
        $tableCells = $dom->getElementsByTagName('td');
        $labels = $dom->getElementsByTagName('label');
        $buttons = $dom->getElementsByTagName('button');

        $title = $titleElement ? $titleElement->textContent : 'No title found';
        $headingTexts = getTagTexts($headings);
        $paragraphTexts = getTagTexts($paragraphs);
        $spanTexts = getTagTexts($spans);
        $anchorTexts = getTagTexts($anchors);
        $listItemTexts = getTagTexts($listItems);
        $cellTexts = getTagTexts($tableCells);
        $labelTexts = getTagTexts($labels);
        $buttonTexts = getTagTexts($buttons);

        // $data = array_merge($data, $title);
        $data = array_merge($data, $headingTexts);
        $data = array_merge($data, $paragraphTexts);
        $data = array_merge($data, $spanTexts);
        $data = array_merge($data, $anchorTexts);
        $data = array_merge($data, $listItemTexts);
        $data = array_merge($data, $cellTexts);
        $data = array_merge($data, $labelTexts);
        $data = array_merge($data, $buttonTexts);

        // $data[] = [
        //     'title' => $title,
        //     'headings' => $headingTexts,
        //     'paragraphs' => $paragraphTexts,
        //     'spans' => $spanTexts,
        //     'anchors' => $anchorTexts,
        //     'listItems' => $listItemTexts,
        //     'tableCells' => $cellTexts,
        //     'labels' => $labelTexts,
        //     'buttons' => $buttonTexts,
        // ];

    }

    // Trim and remove empty strings
    $data = array_filter(array_map('trim', $data), function($value) {
        return $value !== '';
    });

    return $data;

}

function cosineSimilarity($vectorA, $vectorB) {
    $dotProduct = 0;
    $magnitudeA = 0;
    $magnitudeB = 0;

    foreach ($vectorA as $key => $value) {
        // Check if the key exists in the second vector
        if (isset($vectorB[$key])) {
            $dotProduct += $value * $vectorB[$key];
        }

        $magnitudeA += pow($value, 2);
    }

    foreach ($vectorB as $value) {
        $magnitudeB += pow($value, 2);
    }

    $magnitudeA = sqrt($magnitudeA);
    $magnitudeB = sqrt($magnitudeB);

    if ($magnitudeA == 0 || $magnitudeB == 0) {
        return 0; // to handle division by zero
    }

    return $dotProduct / ($magnitudeA * $magnitudeB);
}

function getTopN($documents, $query, $n) {
    $queryVector = array_count_values(str_split($query));

    $documentVectors = array();
    foreach ($documents as $document) {
        $documentVector = array_count_values(str_split($document));
        $documentVectors[$document] = $documentVector;
    }

    $scores = array();

    // Calculate cosine similarity score for each document
    foreach ($documentVectors as $document => $documentVector) {
        $score = cosineSimilarity($queryVector, $documentVector);
        $scores[$document] = $score;
    }

    // Sort documents based on scores in descending order
    arsort($scores);

    // Get top N documents
    $topN = array_slice($scores, 0, $n, true);

    return $topN;
}

function makeOne($relevant) {
    // Concatenate all keys of the associative array
    $result = implode('. ', array_keys($relevant));

    // Return the concatenated string
    return $result;
}

function highlight($answer, $query) {
    // Convert both answer and query to lowercase for case-insensitive matching
    $lowercaseAnswer = strtolower($answer);
    $lowercaseQuery = strtolower($query);

    // Use preg_replace_callback to preserve original casing in the output
    $highlightedAnswer = preg_replace_callback(
        "/$lowercaseQuery/",
        function ($match) use ($query) {
            return '<u><i>' . substr($match[0], 0, strlen($query)) . '</i></u>' . substr($match[0], strlen($query));
        },
        $answer
    );

    // Return the highlighted answer with original casing
    return $highlightedAnswer;
}

?>