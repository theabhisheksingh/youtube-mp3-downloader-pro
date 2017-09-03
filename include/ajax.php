<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// No timelimit for processing query
set_time_limit(0);

// Communicate with browsers in JSON format
header('Content-Type: application/json; charset=UTF-8');

// Define default output
$output = array('status' => false, 'error' => 'Invalid request');

// Start process on demand purpose
switch (uniqueX\General::requestParam('purpose')) {
    case 'search':
        // Get required params from http request
        $query = uniqueX\General::requestParam('query');
        $noExtra = uniqueX\General::requestParam('noExtra');
        // Validate search query
        if ($query != null) {
            // Get number videos to load as default
            $maxVideos = intval($config['max-videos']);
            $maxVideos = $maxVideos >= 1 && $maxVideos <= 50 ? $maxVideos : 10;
            $maxVideos = $noExtra == 'yes' ? 1 : $maxVideos;
            // Get list of videos
            $list = uniqueX\YouTube::search($query, $maxVideos, false, false);
            // Validate info
            if (is_array($list) && isset($list['videos']) && is_array($list['videos']) && count($list['videos']) > 0) {
                // Create secure tokens for downloading
                foreach ($list['videos'] as $pos => $data) {
                    $list['videos'][$pos]['token'] =
                        implode('-', str_split(strtoupper(md5(md5($data['id'] . uniqueX\Backend::installKey()))), 4));
                }
                // Dump final output
                $list['status'] = true;
                $output = $list;
            } else {
                $output['error'] = 'Videos are not available  [Err: 0x02]';
            }
        } else {
            $output['error'] = 'Videos are not available  [Err: 0x01]';
        }
        break;
    case 'getCharts':
        // Get charts data
        $charts = uniqueX\YouTube::charts();
        if (is_array($charts)) {
            $output = array('status' => true, 'charts' => $charts);
        } else {
            $output['error'] = 'Charts Not Available';
        }
        break;
    case 'getMore':
        // Get required params from http request
        $video = isset($_POST['v']) && is_string($_POST['v']) ? trim($_POST['v']) : null;
        $token = isset($_POST['t']) && is_string($_POST['t']) ? trim($_POST['t']) : null;
        // Validate video and token
        if (implode('-', str_split(strtoupper(md5(md5($video . uniqueX\Backend::installKey()))), 4)) == $token) {
            // Get links of YouTube video
            $allLinks = uniqueX\Cache::getLinks($video);            // Validate video links
            if (is_array($allLinks)) {
                $output = array('status' => true, 'links' => $allLinks);
            } else {
                $output['error'] = is_string($allLinks) ? $allLinks : 'Media links are not available';
            }
        } else {
            $output['error'] = 'Authentication error';
        }
        break;
    case 'latestSearches':
        // Get search term
        $searchTerm = isset($_POST['search']) && is_string($_POST['search']) ? trim($_POST['search']) : null;
        // Get latest searches
        $latestSearches = uniqueX\General::latestSearches(str_replace('-', ' ', $searchTerm));
        // Dump output
        if (is_array($latestSearches) && count($latestSearches) > 0) {
            $output = array('status' => true, 'latest' => $latestSearches);
        } else {
            $output['error'] = 'No latest searches';
        }
        break;
}

// Print output in JSON format
echo json_encode($output);