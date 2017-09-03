<?php

namespace uniqueX;

class Cache
{
    public static function getLinks($videoID)
    {
        $return = false;
        // Clear old cache files
        self::clearCache();
        // validate video ID
        if (is_string($videoID)) {
            // Detect video links on cache
            $cacheLinks = self::readCache($videoID);
            // Check for is cached links are working?
            if (self::cacheStatus($cacheLinks)) {
                $return = $cacheLinks;
            } else {
                // Get new video links
                $freshLinks = YouTube::links($videoID, true);
                // Store video links to cache
                if (is_array($freshLinks)) {
                    self::writeCache($videoID, $freshLinks);
                }
                // Dump output
                $return = $freshLinks;
            }
        }

        return $return;
    }

    private static function readCache($videoID)
    {
        $return = false;
        // Get cache file
        $cacheFile = self::cacheFile();
        clearstatcache();
        // validate video ID
        if (is_string($videoID) && is_file($cacheFile)) {
            // Open cache file
            if (is_resource($cache = fopen($cacheFile, 'r'))) {
				while(! feof($cache))  {
					$line = fgets($cache);
					// if($_SERVER['REMOTE_ADDR'] == "113.179.124.101")
						// echo $line."\r\n";
					$line = trim($line);
					$vars = @explode("[serialize]",$line);
					$dataID = $vars[0];
					if(count($vars) > 1)
						$data = $vars[1];
					else
						$data = "";
					$videoLinks = @unserialize($data);
					if ($dataID == $videoID) {
                        $return = $videoLinks;
						break;
                    }
			    }
				// while (($line = fgets($cache)) !== false) {
					// $line = str_replace("\n", "", $line);
					// if($_SERVER['REMOTE_ADDR'] == "113.179.124.101")
						// echo $line."\r\n";
					
					// list($dataID, $data) = explode("[serialize]",$line);
					// $videoLinks = @unserialize($data);
					// if ($dataID == $videoID) {
                        // $return = $videoLinks;
						// break;
                    // }
				// }
				
				fclose($cache);                
            }
        }
		
        return $return;
    }

    private static function writeCache($videoID, $videoLinks)
    {
        $return = false;
        // Validate input params
        if (is_string($videoID) && is_array($videoLinks)) {
            // Pack video links
            $packLinks = serialize($videoLinks);
            // Write video links to cache
            if (is_resource($cache = fopen(self::cacheFile(), 'a+'))) {
                fwrite($cache, $videoID);
                //fwrite($cache, pack('H*', str_pad(dechex(strlen($packLinks)), 6, '0', STR_PAD_LEFT)));
                fwrite($cache,"[serialize]");
                fwrite($cache, $packLinks);
                fwrite($cache, "\n");
                // Close cache file
                fclose($cache);
            }
        }

        return $return;
    }

    private static function cacheStatus($cacheLinks)
    {
        $return = false;
        // Validate input params
        if (is_array($cacheLinks)) {
            // Validate one media link
            $headers = array_reverse(get_headers($cacheLinks[0]['link']));
            if (is_array($headers)) {
                foreach ($headers as $header) {
                    if (preg_match('/^content\-length\:\s*([0-9]+)/i', $header, $matches)) {
                        $return = intval($matches[1]) > 0;
                        break;
                    } elseif (substr($header, 0, 5) == 'HTTP/') {
                        break;
                    }
                }
            }
        }
        return $return;
    }

    private static function clearCache()
    {
        // Get cache files
        $cacheFile = realpath(self::cacheFile());
        $cacheFiles = scandir(__DIR__ . '/../store/');
        // Try to remove old cache files
        foreach ($cacheFiles as $file) {
            if (realpath(__DIR__ . "/../store/{$file}") != $cacheFile &&
                preg_match('/^[0-9a-f]{32}\.cache$/i', $file)
            ) {
                unlink(__DIR__ . "/../store/{$file}");
            }
        }
    }

    private static function cacheFile()
    {
        return __DIR__ . '/../store/' . md5(date('YmdH')) . '.cache';
    }
}
