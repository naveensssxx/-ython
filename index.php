<?php

function getAwaasaanetaData($url = "https://www.awasaaneta.lk/") {
    /**
     * Fetches data from awasaaneta.lk and separates working and non-working links.
     */
    try {
        $response = file_get_contents($url);
        if ($response === false) {
            throw new Exception("Failed to fetch URL: " . $url);
        }
        $dom = new DOMDocument();
        @$dom->loadHTML($response); // Suppress warnings if HTML is malformed
        $links = [];
        $xpath = new DOMXPath($dom);
        $anchorTags = $xpath->query('//a[@href]');
        foreach ($anchorTags as $anchor) {
            $links[] = $anchor->getAttribute('href');
        }

        $workingLinks = [];
        $nonWorkingLinks = [];

        foreach ($links as $link) {
            // Clean up relative links and ensure they are absolute
            if (strpos($link, "http") !== 0) {
                if (strpos($link, "/") === 0) {
                    $link = rtrim($url, "/") . $link;
                } else {
                    $link = $url . $link;
                }
            }

            // Check if the link is an awasaaneta link
            if (preg_match("/awasaaneta\.lk/", $link)) {
                $opts = array('http' => array('timeout' => 5));
                $context = stream_context_create($opts);

                $linkResponse = @file_get_contents($link, false, $context);
                if ($linkResponse !== false) {
                    $workingLinks[] = $link;
                } else {
                    $nonWorkingLinks[] = $link;
                }
            }
        }

        return [$workingLinks, $nonWorkingLinks];

    } catch (Exception $e) {
        return [[], ["Error fetching data from awasaaneta.lk: " . $e->getMessage()]];
    }
}

function getGovernmentLinks($url = "https://www.awasaaneta.lk/") {
    /**
     * Fetches government links from awasaaneta.lk.
     */
    try {
        $response = file_get_contents($url);
        if ($response === false) {
            throw new Exception("Failed to fetch URL: " . $url);
        }
        $dom = new DOMDocument();
        @$dom->loadHTML($response);
        $links = [];
        $xpath = new DOMXPath($dom);
        $anchorTags = $xpath->query('//a[@href]');
        foreach ($anchorTags as $anchor) {
            $links[] = $anchor->getAttribute('href');
        }

        $governmentLinks = [];
        foreach ($links as $link) {
            if (strpos($link, "http") !== 0) {
                if (strpos($link, "/") === 0) {
                    $link = rtrim($url, "/") . $link;
                } else {
                    $link = $url . $link;
                }
            }
            // Add new government URL patterns here:
            if (preg_match("/\.gov\.lk|\.police\.lk|\.treasury\.gov\.lk|\.immigration\.gov\.lk|\.customs\.gov\.lk|\.gazette\.lk|\.health\.lk|\.army\.lk|\.education\.lk|\.parliament\.lk|\.agriculture\.gov\.lk|\.tourismmin\.gov\.lk|\.defence\.lk|\.icta\.lk/", $link)) {
                $governmentLinks[] = $link;
            }
        }

        return $governmentLinks;

    } catch (Exception $e) {
        return ["Error fetching government links from " . $url . ": " . $e->getMessage()];
    }
}

function getAllDomains($url = "https://www.awasaaneta.lk/") {
    /**
     * Fetches all domains (unique) from awasaaneta.lk and checks availability.
     */
    try {
        $response = file_get_contents($url);
        if ($response === false) {
            throw new Exception("Failed to fetch URL: " . $url);
        }
        $dom = new DOMDocument();
        @$dom->loadHTML($response);
        $links = [];
        $xpath = new DOMXPath($dom);
        $anchorTags = $xpath->query('//a[@href]');
        foreach ($anchorTags as $anchor) {
            $links[] = $anchor->getAttribute('href');
        }

        $domains = [];
        $domainAvailability = [];

        foreach ($links as $link) {
            if (strpos($link, "http") !== 0) {
                if (strpos($link, "/") === 0) {
                    $link = rtrim($url, "/") . $link;
                } else {
                    $link = $url . $link;
                }
            }
            if (preg_match("/https?:\/\/(?:www\.)?([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/", $link, $matches)) {
                $domains[] = $matches[1];
            }
        }

        $domains = array_unique($domains); // Remove duplicates

        foreach ($domains as $domain) {
            $ip = gethostbyname($domain);
            if ($ip != $domain) {
                $domainAvailability[$domain] = "Not Available";
            } else {
                $domainAvailability[$domain] = "Available";
            }
        }

        return $domainAvailability;

    } catch (Exception $e) {
        return ["Error fetching data from " . $url . ": " . $e->getMessage() => "Error"];
    }
}

// Example usage:
list($working, $nonWorking) = getAwaasaanetaData();
$governmentLinks = getGovernmentLinks();
$allDomains = getAllDomains();

echo "Working Links from awasaaneta.lk:\n";
foreach ($working as $link) {
    echo $link . "\n";
}

echo "\nNon-Working Links from awasaaneta.lk:\n";
foreach ($nonWorking as $link) {
    echo $link . "\n";
}

echo "\nGovernment Links from awasaaneta.lk:\n";
foreach ($governmentLinks as $link) {
    echo $link . "\n";
}

echo "\nAll Domains and Availability:\n";
foreach ($allDomains as $domain => $availability) {
    echo $domain . ": " . $availability . "\n";
}
?>