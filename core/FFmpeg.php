<?php

namespace uniqueX;

class FFmpeg
{
    public static function convert($input, $name, $quality = 320, $start = 0, $end = 0)
    {
        $return = false;
        // Get config
        global $config;
        // Validate input params
        if (is_string($input) && is_string($name)) {
            // Optimize quality value
            $quality = intval($quality);
            $quality = in_array($quality, array(64, 128, 192, 256, 320)) ? $quality : 320;
            // Get FFmpeg engine path
            if (is_string($FFmpeg = self::getEngine('ffmpeg')) && is_string($cURL = self::getEngine('curl'))) {
                // Check for execute permission
                if (self::execPermission()) {
                    // Detect debug config
                    $curlDebug = isset($config['curl-debug']) && $config['curl-debug'] == 'yes';
                    $ffmpegDebug = isset($config['ffmpeg-debug']) && $config['ffmpeg-debug'] == 'yes';
                    // Prepare execute command
                    $command = "\"{$cURL}\" -k -L \"{$input}\"" . ($curlDebug && !$ffmpegDebug ? " 2>&1" : null);
                    if (!$curlDebug) {
						
						$start_arg = "";
						$end_arg = "";
						if($start) 
							$start_arg = " -ss ".escapeshellarg($start);
						if($end) 
							$end_arg = " -t ".escapeshellarg($end);
						
                        $command .= " | \"{$FFmpeg}\" -i pipe:0{$start_arg}{$end_arg} -b:a {$quality}k -f mp3 pipe:1";
                        if ($ffmpegDebug) {
                            $command .= " 2>&1";
                        }
						
                    }
					// Execute process
                    $descriptorspec = array(1 => array('pipe', 'w'));
                    if (is_resource($process = proc_open($command, $descriptorspec, $pipes))) {
                        // Enable realtime datatransfer as mp3 audio chunks
                        $flushSupport = function_exists('ob_flush') && function_exists('flush');
                        // Provide output mp3 size
                        parse_str(parse_url($input, PHP_URL_QUERY), $iParams);
                        if (isset($iParams['dur'])) {
                            $duration = intval($iParams['dur']);
							
							if($end && $start)
								 $duration = $end - $start;
                            
							if ($duration > 0) {
                                header('Content-Length: ' . ($duration * ($quality / 8) * 1000));
                            }
                        }
                        // Send some important headers
                        header('Content-Type: application/octet-stream');
                        header('Content-Disposition: attachment; filename="' . str_replace('"', '', $name) . '"');
                        header('Connection: Close');
                        // Activate flush
                        if ($flushSupport) {
                            if (function_exists('apache_setenv')) {
                                apache_setenv('no-gzip', 1);
                            }
                            if (function_exists('ini_set')) {
                                ini_set('zlib.output_compression', false);
                                ini_set('implicit_flush', true);
                            }
                            if (function_exists('ob_implicit_flush') && function_exists('ob_end_flush')) {
                                ob_implicit_flush(true);
                                ob_end_flush();
                            }
                        }
                        // Get MP3 buffer
                        while (!feof($pipes[1])) {
                            $buffer = fread($pipes[1], 1024 * 16);
                            // Print MP3 buffer
                            echo $buffer;
                            // Provide mp3 audio as chunks
                            if ($flushSupport) {
                                @ob_flush();
                                @flush();
                            }
                        }
                        // Close pipes
                        fclose($pipes[1]);
                        // Close process
                        proc_close($process);
                    } else {
                        $return = 'Failed to execute engines';
                    }
                } else {
                    $return = 'Execute permission is not available';
                }
            } else {
                $return = 'Engines are not found';
            }
        } else {
            $return = 'Invalid input file';
        }
        return $return;
    }

    private static function execPermission()
    {
        $return = false;
        // Execute simple command
        if (function_exists('proc_open') &&
            is_resource($process = proc_open('echo Hello', array(1 => array('pipe', 'w')), $pipes))
        ) {
            // Validate output value
            $return = strpos(stream_get_contents($pipes[1]), 'Hello') !== false;
            fclose($pipes[1]);
            // Close process
            proc_close($process);
        }
        return $return;
    }

    private static function getEngine($engine)
    {
        $return = false;
        // Get config
        global $config;
        // Validate input params
        if (in_array($engine, array('ffmpeg', 'curl'))) {
            // Native cURL
            if ($engine == 'curl' && isset($config['curl-engine']) && $config['curl-engine'] == 'system') {
                return 'curl';
            }
            // Get operating system details
            list($osName, $osType) = array(php_uname('s'), php_uname('m'));
            // Scan OS details
            if (stripos($osName, 'window') !== false) {
                $osName = 'win';
            } elseif (stripos($osName, 'linux') !== false) {
                $osName = 'linux';
            }
            $engineBIT = strpos($osType, '64') !== false ? '64' : '32';
            // Validate OS
            if (in_array($osName, array('win', 'linux', 'mac'))) {
                $engineName = "{$engine}-{$osName}-{$engineBIT}bit";
                if ($osName == 'win') {
                    $engineName .= '.exe';
                }
                // Get engine path
                if (is_string($enginePath = realpath(__DIR__ . "/../store/{$engineName}"))) {
                    if (!is_executable($enginePath)) {
                        chmod($enginePath, 0777);
                    }
                    clearstatcache();
                    if (is_executable($enginePath)) {
                        $return = $enginePath;
                    }
                }
            }
        }

        return $return;
    }
}
