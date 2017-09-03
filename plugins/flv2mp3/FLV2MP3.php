<?php

/**
 * Product: FLV to MP3 Converter - PHP Script
 * Product URI: http://codecanyon.net/item/flv-to-mp3-converter-php-script/11590945
 * Description: Convert FLV video to MP3 audio with pure PHP script
 * Version: 1.1 (Aug 30th, 2015)
 * Author: uniqueX
 * Author URI: http://codecanyon.net/user/uniqueX
 */

namespace uniqueX;

/**
 * Class FLV2MP3
 * Convert FLV video MP3 audio in milliseconds with pure PHP script
 * @package uniqueX
 */

class FLV2MP3
{
    /**
     * Convert FLV video to MP3 audio with pure PHP script
     *
     * @param string $input FLV file path or link
     * @param string $output Output MP3 file path
     * @param string|callback $progress Get current status of conversion progress
     * @return int|string If success returns output file size as number. Else error message as string
     */

    final public static function convert($input, $output = '', $progress = '')
    {
        error_reporting(E_ALL);
        // No time limit for converting FLV to MP3
        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        }
        // Detect run time error (init)
        $runtime_error = null;
        // Get output audio binary size (init)
        $audio_size = 0;
        // Check for mp3 stream (init)
        $mp3_stream = false;
        // Validate input file name
        if (is_string($input)) {
            // Decide output filename
            $output = $output == null ? (self::fileName($input) . '.mp3') : $output;
            // Check for print audio output into STDOUT
            $stdout = false;
            if (preg_match('/stdout\:?(.+)?/i', $output, $matches)) {
                // Get download as name
                $name = isset($matches[1]) ? self::fileName($matches[1]) : null;
                // Update stdout value
                $stdout = strlen($name) > 0 ? $name : true;
            }
            // Provide real time progress report
            $need_progress = is_callable($progress);
            // Detect video duration (init)
            $video_duration = -1;
            // Enable realtime datatransfer as mp3 audio chunks
            $flush_support = is_string($stdout) ? (function_exists('ob_flush') && function_exists('flush')) : false;
            // Try to open input file
            $istream = @fopen($input, 'r');
            // Audo detect output file name
            if ($stdout == '_auto_') {
                // Detect file name via http headers
                if (function_exists('stream_get_meta_data')) {
                    $meta = @stream_get_meta_data($istream);
                    // Check for headers
                    if (is_array($meta) && isset($meta['wrapper_data']) && is_array($meta['wrapper_data'])) {
                        // Get response headers
                        $responseHeaders =
                            isset($meta['wrapper_data']['headers']) &&
                            is_array($meta['wrapper_data']['headers'])
                                ? $meta['wrapper_data']['headers']
                                : $meta['wrapper_data'];
                        // Scan response headers
                        foreach ($responseHeaders as $header) {
                            // Scan each header for detecting filename
                            if (preg_match('/^content\\-disposition[^\\"]+\\"([^\\"]+)\\"/i', $header, $matches)) {
                                $stdout = self::fileName($matches[1], $stdout);
                                break;
                            }
                        }
                    }
                }
            }
            // Only for YouTube
            if ($stdout == '_auto_') {
                parse_str(parse_url($input, PHP_URL_QUERY), $params);
                if (is_array($params) && isset($params['title'])) {
                    $stdout = $params['title'];
                }
            }
            // Define output file name by input filename
            if ($stdout == '_auto_') {
                $stdout = self::fileName($input);
            }
            // Validate input stream
            if (is_resource($istream)) {
                // Try to write output file
                $ostream = $stdout === false ? @fopen($output, 'w') : 'stdout';
                // Validate output stream
                if (is_resource($ostream) || $ostream === 'stdout') {
                    // Read init data from video file
                    if (substr(fread($istream, 13), 0, 4) == "FLV\x01") {
                        // Define ASCII table
                        $ascii = array();
                        for ($a = 0; $a <= 255; $a++) {
                            $ascii[pack('C', $a)] = $a;
                        }
                        // Count parsed media packets
                        $packs = 0;
                        // Detect mp3 audio packet (init)
                        $aPack = false;
                        // Get first media packet header
                        $mHead = fread($istream, 4);
                        // Media packet type
                        $mType = $mHead[0];
                        // Media packet size
                        $mSize =
                            ($ascii[$mHead[1]] * 65536) +
                            ($ascii[$mHead[2]] * 256) +
                            ($ascii[$mHead[3]]) + 15;
                        // Parse media packets one by one
                        while (!feof($istream)) {
                            // Update parsed packets count
                            $packs++;
                            // Stop when MP3 audio not available on FLV video
                            if (!$aPack && $packs >= 10) {
                                break;
                            }
                            // Get media packet
                            $mPack = self::readExactly($istream, $mSize);
                            // Check for is audio packet
                            if ($mType == "\x08") {
                                // Checking for is mp3 stream
                                if (!$mp3_stream) {
                                    // Get audio codec ID
                                    $aCodec = $ascii[$mPack[7]] >> 4;
                                    // Check for is audio codec is mp3 codec
                                    if ($aCodec == 2 || $aCodec == 14) {
                                        $mp3_stream = true;
                                    }
                                }
                                // Final checkpost for mp3 codec
                                if ($mp3_stream) {
                                    // MP3 audio packet is available
                                    if (!$aPack) {
                                        $aPack = true;
                                        // Provide some important headers before sending mp3 audio as chunks
                                        if ($ostream == 'stdout' && $stdout != '_init_' && function_exists('header')) {
                                            // Detect download as name
                                            $download_as = is_string($stdout) ? $stdout : 'audio';
                                            // Send headers to browser
                                            header('Content-Type: application/octet-stream');
                                            header("Content-Disposition: attachment; filename=\"{$download_as}.mp3\"");
                                            if ($audio_size > 0) {
                                                header("Content-Length: {$audio_size}");
                                            }
                                            header('Connection: Close');
                                            // Enable flush
                                            if ($flush_support) {
                                                if (function_exists('apache_setenv')) {
                                                    @apache_setenv('no-gzip', 1);
                                                }
                                                if (function_exists('ini_set')) {
                                                    @ini_set('zlib.output_compression', false);
                                                    @ini_set('implicit_flush', true);
                                                }
                                                if (function_exists('ob_implicit_flush') &&
                                                    function_exists('ob_end_flush')
                                                ) {
                                                    @ob_implicit_flush(true);
                                                    @ob_end_flush();
                                                }
                                            }
                                        }
                                    }
                                    // Stop convert on init process
                                    if ($ostream == 'stdout' && $stdout == '_init_') {
                                        break;
                                    }
                                    // Get current duration position
                                    $current_position =
                                        ($ascii[$mPack[3]] * 16777216) +
                                        ($ascii[$mPack[0]] * 65536) +
                                        ($ascii[$mPack[1]] * 256) +
                                        ($ascii[$mPack[2]]);
                                    // Provide current progress of convert process
                                    if ($need_progress) {
                                        // Calculate conversion percentage by total duration and current position
                                        $position = $video_duration > 0
                                            ? floor($current_position / ($video_duration / 100))
                                            : -1;
                                        $progress($position);
                                    }
                                    // Print audio stream as mp3 chunk
                                    $pack = substr($mPack, 8, $mSize - 16);
                                    if ($ostream == 'stdout') {
                                        if ($stdout != '_init_') {
                                            echo $pack;
                                        }
                                    } else {
                                        fwrite($ostream, $pack);
                                    }
                                    // Provide mp3 audio as chunks
                                    if ($flush_support) {
                                        @ob_flush();
                                        @flush();
                                    }
                                }
                            } elseif ($mType == "\x12") {
                                // Get some inital details from meta pack
                                $duration = self::parseMeta($mPack, 'duration');
                                $audiocodecid = self::parseMeta($mPack, 'audiocodecid');
                                $audiodatarate = self::parseMeta($mPack, 'audiodatarate');
                                // Get video time in milliseconds
                                $video_duration = intval($duration) * 1000;
                                // Get audio bitrate in bps
                                $audio_bitrate = intval($audiodatarate) * 1000;
                                // Calculate output audio file size
                                $audio_size = floor($video_duration * (($audio_bitrate / 1000) / 8));
                                // Check for is audio stream is mp3 stream
                                $mp3_stream = ($audiocodecid << 4) == 32;
                            }
                            // Parse next media packet header
                            $i = $mSize - 4;
                            if (isset($mPack[$i])) {
                                // Get next media packet type
                                $mType = $mPack[$i];
                                // Get next media packet size
                                $mSize =
                                    ($ascii[$mPack[++$i]] * 65536) +
                                    ($ascii[$mPack[++$i]] * 256) +
                                    ($ascii[$mPack[++$i]]) + 15;
                            }
                        }
                        if (!$aPack) {
                            $runtime_error = 'MP3 codec is not available in this flv video';
                        }
                    } else {
                        $runtime_error = 'This is not a flv video';
                    }
                    // Close output file
                    if ($ostream != 'stdout') {
                        fclose($ostream);
                    }
                } else {
                    $runtime_error = 'Failed to write output file';
                }
                // Close video file
                fclose($istream);
            } else {
                $runtime_error = 'Failed to open input file';
            }
        } else {
            $runtime_error = 'Invalid input params';
        }

        // Prepare return value
        $return = $audio_size < 1 || !$mp3_stream
            ? (is_string($runtime_error) ? $runtime_error : 'Unknown error')
            : $audio_size;

        return $return;
    }

    /**
     * Read some raw binary data from file
     *
     * @param resource $FilePointer File resource for reading bytes as raw data
     * @param int $length How many bytes to read
     * @return string returns raw binary data of file
     */

    private static function readExactly($FilePointer, $length)
    {
        $return = '';
        if (is_numeric($length) && $length >= 0) {
            while ($length != 0) {
                // Read file binary data
                $BinData = fread($FilePointer, $length);
                $return .= $BinData;
                // Calculate remaining binary data
                $BinSize = strlen($BinData);
                $length = $BinSize == 0 ? 0 : $length - $BinSize;
            }
        }
        return $return;
    }

    /**
     * Get value by key from flv video meta data
     *
     * @param string $meta FLV video meta data
     * @param string $key Key video meta key
     * @return int Returns value of key as number
     */

    private static function parseMeta($meta, $key)
    {
        $return = -1;
        // Validate input params
        if (is_string($meta) && is_string($key)) {
            // Optimize key value
            $key .= "\x00";
            // Search key in meta data
            $pos = strpos($meta, $key);
            if (is_numeric($pos)) {
                // Get raw value of key
                $raw = substr($meta, $pos + strlen($key), 8);
                // Optimize raw value
                if (pack('s', 1) == pack('v', 1)) {
                    $raw = strrev($raw);
                }
                // Decode raw value data
                $val = unpack('d', $raw);
                $val = is_array($val) ? array_values($val) : array(-1);
            }
            // Optimize return value
            $return = isset($val[0]) ? $val[0] : $return;
        }
        return $return;
    }

    /**
     * Convert file path into file name without extension
     *
     * @param string $path Input file path as string
     * @param string $fallback Fallback value on failed to detect file name
     * @return string Returns smart filename without extension
     */
    private static function fileName($path, $fallback = '')
    {
        $return = $fallback;
        // Validate input path
        if (is_string($path)) {
            // Get filename of input path
            $name = @pathinfo($path, PATHINFO_FILENAME);
            // Optimize filename
            if (is_string($name) && preg_match('/^([^\\?]+)/i', $name, $macthes)) {
                $return = $macthes[1];
            }
        }
        return $return;
    }
}
