# PHP Crawler

![Front-end Look](/assets/front-end.png)

## Front-end

This HTML file implements a simple web crawler with a user interface. The web page allows users to input a URL and set the crawling depth. Upon clicking the "Crawl" button, the page sends a request to the server-side script ('back-end/crawler.php') via a POST request, fetching and processing data.

### Features

- **User-friendly Interface:** The HTML document provides a clean and responsive user interface with input fields for URL and crawling depth, accompanied by a "Crawl" button.
  
- **Progress Bar:** A progress bar visually indicates the loading status of content. It dynamically updates as the crawler fetches data from the server.

- **Search Functionality:** Users can input a search term, triggering a search via a POST request to 'back-end/search.php'. The results are displayed below the search input.

### Usage

1. Open the HTML file in a web browser.
2. Enter a valid URL and set the desired crawling depth.
3. Click the "Crawl" button to initiate the web crawling process. The progress bar will update accordingly.
4. Optionally, enter a search term and click the "Search" button to see the results.

Note: Ensure that the server-side scripts ('back-end/crawler.php' and 'back-end/search.php') are correctly configured and accessible for the functionality to work as expected.

## Back-end

# Web Crawler Backend

This backend code implements a web crawler that extracts information from web pages and provides search functionality. The code is divided into several files:

### `completion.php`

This script counts the number of HTML files in the `loaded_pages/` directory and returns the count as a JSON response. It is used to track the progress of the web crawling process.

### `config.php`

This file contains configuration parameters and a function (`logMessage`) to log messages to a file (`scraping_log.txt`). It sets up essential file paths and initializes a log file for tracking the crawler's activities.

### `crawler-utils.php`

A collection of utility functions used by the main crawler script (`crawler.php`). Functions include those for gracefully exiting the script (`exitGracefully`), getting the base URL from a given URL (`getBaseUrl`), fetching disallowed links from `robots.txt` (`getDisallowedLinksFromRobotsTxt`), scraping a URL (`scrapeUrl`), saving a DOM object to a file (`saveDom`), and more.

### `crawler.php`

The primary web crawler script that initiates the crawling process. It loads pages, extracts new URLs, filters them based on various criteria, and continues crawling to the specified depth. The script logs activities to `scraping_log.txt` and provides a JSON response upon completion.

### `scraping_log.txt`

A log file that records the activities and status of the web crawler, including information about cleaned directories, loaded pages, and any failures encountered during the crawling process.

### `search-utils.php`

Utility functions for processing HTML files obtained during crawling. Functions include parsing HTML files (`parseHtmlFiles`), extracting text from various HTML tags (`extractDom`), calculating cosine similarity between vectors (`cosineSimilarity`), and retrieving the top N documents based on a query (`getTopN`).

### `search.php`

A script that handles search functionality. It reads a JSON input containing a search query, extracts relevant information from previously crawled pages, and returns a highlighted response that matches the query.

Ensure that the backend scripts are properly configured, and the necessary dependencies are installed for successful execution.
