<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Stop process if not admin
if (!uniqueX\Backend::getValue('adminStatus')) {
    exit;
}

// Communicate in JSON format
header('Content-Type: application/json; charset=UTF-8');

// Define default output
$output = array('status' => false, 'error' => 'Unknown error');

// Start process on demand
switch (uniqueX\General::requestParam('purpose')) {
    case 'troubleshoot':
        switch (uniqueX\General::requestParam('action')) {
            case 'ytgrab':
                $status = uniqueX\YouTube::links(uniqueX\General::requestParam('video'));
                if (is_array($status)) {
                    $output['status'] = array('status' => true);
                } else {
                    $output['error'] = is_string($status) ? $status : 'Failed to grab YouTube video links';
                }
                break;
            case 'engine':
                $process = explode(':', uniqueX\General::requestParam('process'));
                $osName = uniqueX\General::requestParam('osName');
                $osType = uniqueX\General::requestParam('osType');
                // Validate request params
                if (is_array($process) && count($process) == 2 &&
                    in_array($process[0], array('ffmpeg', 'curl')) &&
                    in_array($process[1], array('install', 're-install', 'exec-perm')) &&
                    in_array($osName, array('win', 'linux')) &&
                    in_array($osType, array('32', '64'))
                ) {
                    // Check store folder
                    $store = __DIR__ . '/../store';
                    if (is_dir($store)) {
                        $store = realpath($store);
                        // Check read & write permissions for store folder
                        if (is_readable($store) && is_writable($store)) {
                            // Get file name
                            $fileName = "{$process[0]}-{$osName}-{$osType}bit";
                            $fileName .= $osName == 'win' ? '.exe' : null;
                            // Start process
                            if (in_array($process[1], array('install', 're-install'))) {
                                // Remove if already found
                                if (is_file("{$store}/{$fileName}")) {
                                    unlink("{$store}/{$fileName}");
                                }
                                // Get remote file details
                                $rFiles = @json_decode(file_get_contents('http://files.uniquex.co/md5.json'));
                                // Start downloading engine
                                if (isset($rFiles->{$fileName})) {
                                    if (is_resource($local = fopen("{$store}/{$fileName}", 'w'))) {
                                        // Connect remote server
                                        $remote = fopen("http://files.uniquex.co/{$fileName}", 'r');
                                        if (is_resource($remote)) {
                                            $downloaded = 0;
                                            while (!feof($remote)) {
                                                // Download engine as packets
                                                $buffer = fread($remote, 1024 * 16);
                                                // Write engine file
                                                fwrite($local, $buffer);
                                                // Upload download size
                                                $downloaded += strlen($buffer);
                                                // Calculate download progress
                                                $progress = floor($downloaded / ($rFiles->{$fileName}->size / 100));
                                                @file_put_contents("{$store}/{$fileName}.txt", $progress);
                                            }
                                            // Close remote connection
                                            fclose($remote);
                                        }
                                        // Close local file
                                        fclose($local);
                                        // Downloading completed
                                        if (is_file("{$store}/{$fileName}.txt")) {
                                            @unlink("{$store}/{$fileName}.txt");
                                        }
                                        // Check MD5 HASH
                                        $localHash = md5(file_get_contents("{$store}/{$fileName}"));
                                        if ($localHash == $rFiles->{$fileName}->hash) {
                                            chmod("{$store}/{$fileName}", 0777);
                                            $output = array(
                                                'status' => true,
                                                'message' => ($process[0] == 'ffmpeg' ? 'FFmpeg' : 'CURL') .
                                                    ' engine installed successfully. Please reload this page.'
                                            );
                                        } else {
                                            // Downloaded engine corrupted :(
                                            unlink("{$store}/{$fileName}");
                                            $output = 'Downloaded engine file is corrupted :(';
                                        }
                                    } else {
                                        $output['error'] = 'Failed to download engine file. ' .
                                            'Please provide all perimissions for store folder. ' .
                                            'Read documentation to know ho to provide all ' .
                                            'permissions for store folder.';
                                    }
                                } else {
                                    $output['error'] = 'Failed to download this file. Contact developer. ' .
                                        'File name is "' . $fileName . '"';
                                }
                            } else {
                                if (is_file("{$store}/{$fileName}")) {
                                    // Set execute permission
                                    chmod("{$store}/{$fileName}", 0777);
                                    clearstatcache();
                                    // Check execute permission
                                    if (is_executable("{$store}/{$fileName}")) {
                                        $output = array(
                                            'status' => true,
                                            'message' => 'Action complted. Reload this page.'
                                        );
                                    } else {
                                        $output['error'] = 'Failed to set execute permission.';
                                    }
                                } else {
                                    $output['error'] = 'Engine file nout found.';
                                }
                            }
                        } else {
                            $output['error'] = 'Provide read and write permissions for store folder. ' .
                                'This folder found in product main folder.';
                        }
                    } else {
                        $output['error'] = 'Store folder not found. Create "store" folder in product main folder.';
                    }
                } else {
                    $output['error'] = 'Invalid request';
                }
                break;
        }
        break;
}

// Print output in JSON format
echo json_encode($output);
