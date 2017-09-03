<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

if (!uniqueX\Backend::getValue('adminStatus')) {
    exit;
}

// Communicate JSON format
header('Content-Type: application/json; charset=UTF-8');

// Define defualt output
$output = array('status' => false, 'error' => 'Unkown error');

// Start process on demand
switch (uniqueX\General::requestParam('action')) {
    case 'addDMCA':
        // Get request data from HTTP
        $value = uniqueX\General::requestParam('value');
        $type = uniqueX\General::requestParam('type');
        $note = uniqueX\General::requestParam('note');
        // Check request details
        if (strlen($value) > 0 && strlen($type) > 0 && in_array($type, array('video', 'channel', 'word'))) {
            // Search for query
            $result = null;
            if ($type == 'word' || (is_array($result = uniqueX\YouTube::search($value)) &&
                    isset($result['videos']) && is_array($result['videos']) && count($result['videos']) > 0)
            ) {
                if ($type == 'word') {
                    $finalObject = strtolower($value);
                    // Adjust note
                    $note = $note == null ? "Block word: {$value}" : $note;
                } else {
                    $video = $result['videos'][0];
                    // Final object
                    $finalObject = $type == 'video' ? $video['id'] : $video['publish']['user'];
                    // Prepare note
                    if ($note == null) {
                        $note = $type == 'video' ? $video['title'] : $video['publish']['owner'];
                    }
                }
                // Check for DMCA records is exist
                $exist = false;
                if (is_array($dmca = uniqueX\DMCA::getDMCA())) {
                    foreach ($dmca as $rec) {
                        if ($rec[1] == $finalObject) {
                            $exist = true;
                            break;
                        }
                    }
                }
                if (!$exist) {
                    // Add to DMCA records
                    if (uniqueX\DMCA::addDMCA($type, $finalObject, $note)) {
                        $output = array('status' => true, 'record' => array(
                            'type' => ucfirst($type),
                            'value' => $finalObject,
                            'time' => date("M jS, Y h:i:s a"),
                            'note' => $note
                        ));
                    } else {
                        $output['error'] = 'Failed to update DMCA records. Please contact developer';
                    }
                } else {
                    $output['error'] = 'DMCA record already exist';
                }
            } else {
                $output['error'] = 'Videos (or) Channels are not matched with this query';
            }
        } else {
            $output['error'] = 'Invalid HTTP request';
        }
        break;
    case 'delDMCA':
        uniqueX\DMCA::delDMCA(uniqueX\General::requestParam('record'));
        break;
}

// Print output in JSON format
echo json_encode($output);
