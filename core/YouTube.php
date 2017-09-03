<?php



namespace uniqueX;

include "Decryptor.php";

class YouTube

{

    private static $sandBox = array();

    public static function search($query, $maxResults = 1, $idsOnly = false, $getDescription = true)

    {
		//die($query);
		$return = false;
		
		// Detect query from youtube or dailymotion or facebook
		
		$isOtherSite = false;
		
		if(preg_match('#dailymotion.com/video/([a-z0-9]+)#',$query,$m)){
			include_once(dirname(__FILE__).DIRECTORY_SEPARATOR ."dailymotion.class.php");
			$Dailymotion = new Dailymotion();
			$videoTemp = $Dailymotion -> videoInfo($m[1]);
			if($videoTemp !== false){
				$isOtherSite = true;
				$query = $videoTemp['title'];
			}
		}   
		if(preg_match('#facebook.com/(.+)/videos/([0-9]+)#',$query,$m)){
			include_once(dirname(__FILE__).DIRECTORY_SEPARATOR ."facebook.class.php");
			$Facebook = new Facebook();
			$videoTemp = $Facebook -> videoInfo($m[2]);
			if($videoTemp !== false){
				$isOtherSite = true;
				$query = $videoTemp['title'];
			}
		}       

        // Detect Google API Key

        $YouTubeKey = General::youtubeKey();

        if (!is_string($YouTubeKey)) {

            return false;

        }

        // Get configuration

        $config = Backend::getValue('config');

        $config = is_array($config) ? $config : array();

        // Validate query type

        if (is_string($query = trim($query)) && strlen($query) > 0) {

            // At first detect direct query

            $directAction = @unserialize(base64_decode($query)); // @ is imp

            $directAction = is_array($directAction) && isset($directAction['type']) ? $directAction : null;

            // Detect spcial things on query (init)

            $special = array(

                'videos' => array(),

                'action' => array('type' => null, 'value' => null),

                'patterns' => array(

                    'user' => '/user\/([a-z0-9\-\_]+)/i',

                    'channel' => '/channel\/(UC[a-z0-9\-\_]+)/i',

                    'playlist' => '/list\=(PL[a-z0-9\-\_]+)/i',

                    '(channel)' => '/\((UC[a-z0-9\-\_]+)\)/i',

                )

            );

            // Check direct query

            if ($directAction == null) {

                // Detect video IDs on query

                $videoIDs = preg_match_all('/([a-z0-9\_\-]{11})/i', $query, $qM) ? $qM[1] : array();

                $special['videos'] = self::videosInfo($videoIDs, $getDescription, $YouTubeKey);

                // Scan for special things

                foreach ($special['patterns'] as $type => $pattern) {

                    // Detect value on query

                    if (preg_match($pattern, $query, $matches)) {

                        switch ($type) {

                            case 'playlist':

                                $special['action'] = array('type' => 'playlist', 'value' => $matches[1]);

                                break;

                            case 'user':

                                if (is_string($channel = self::getChannel($matches[1], $YouTubeKey))) {

                                    $special['action'] = array('type' => 'channel', 'value' => $channel);

                                }

                                break;

                            case 'channel':

                                $special['action'] = array('type' => 'channel', 'value' => $matches[1]);

                                break;

                            case '(channel)':

                                $special['action'] = array('type' => 'channel', 'value' => $matches[1]);

                                break;

                        }

                    }

                }

                // Get related videos

                if ($maxResults > 1 && count($special['videos']) == 1 && $special['action']['type'] != 'playlist') {

                    $special['action'] = array('type' => 'related', 'value' => $special['videos'][0]['id']);

                }

                // Search videos

                if (count($special['videos']) == 0 && $special['action']['type'] == null) {

                    $special['action'] = array('type' => 'search', 'value' => $query);

                }

                // Update directAction configuration

                $directAction = array(

                    'next' => null,

                    'type' => $special['action']['type'],

                    'value' => $special['action']['value']

                );

            }

            // Get video IDs on demand

            list($videoIDs, $defaultRegion) = array(array(), 'US');

            // Prepare request params (init)

            $params = array('key' => $YouTubeKey);

            // Safe saerch

            if (isset($config['safe-search']) &&

                is_string($config['safe-search']) &&

                strlen($config['safe-search']) > 0

            ) {

                $params['safeSearch'] = $config['safe-search'];

            }

            // Set cursor of result position

            if (isset($directAction['next'])) {

                $params['pageToken'] = $directAction['next'];

            }

            // Attach max results config

            $params['maxResults'] = $maxResults > 1 && count($special['videos']) > 0 ? $maxResults - 1 : $maxResults;

            // Attach main key

            $mainKeys = array(

                'search' => 'q',

                'channel' => 'channelId',

                'related' => 'relatedToVideoId',

                'playlist' => 'playlistId'

            );

            if (in_array($directAction['type'], array_keys($mainKeys))) {

                $params[$mainKeys[$directAction['type']]] = $directAction['value'];

            }

            // Start process on demand

            if (in_array($directAction['type'], array('channel', 'related', 'search'))) {

                $params['part'] = 'id';

                $params['type'] = 'video';

                $params['fields'] = 'nextPageToken,items(id(videoId))';

                $params['regionCode'] = $defaultRegion;

                if ($directAction['type'] == 'channel') {

                    $params['order'] = 'viewCount';

                }

                // Pack request params

                $request = http_build_query($params);

                // Send request

                if (is_string($response = self::http("https://www.googleapis.com/youtube/v3/search?{$request}"))) {

                    // Decode response data

                    $resData = self::object2Array(json_decode($response));

                    // Get cursor of next page

                    $directAction['next'] = isset($resData['nextPageToken']) ? $resData['nextPageToken'] : null;

                    // Detect items on response

                    if (isset($resData['items']) && count($resData['items']) > 0) {

                        foreach ($resData['items'] as $item) {

                            if (is_array($item) && isset($item['id'], $item['id']['videoId'])) {

                                $videoIDs[] = $item['id']['videoId'];

                            }

                        }

                    }

                }

            } elseif ($directAction['type'] == 'playlist') {

                $params['part'] = 'snippet';

                $params['fields'] = 'nextPageToken,items(snippet(resourceId(videoId)))';

                // Pack request params

                $request = http_build_query($params);

                // Send request

                $target = "https://www.googleapis.com/youtube/v3/playlistItems?{$request}";

                if (is_string($response = self::http($target))) {

                    // Decode response data

                    $resData = self::object2Array(json_decode($response));

                    // Get cursor of next page

                    $directAction['next'] = isset($resData['nextPageToken']) ? $resData['nextPageToken'] : null;

                    // Detect items on response

                    if (isset($resData['items']) && count($resData['items']) > 0) {

                        foreach ($resData['items'] as $item) {

                            if (is_array($item) && isset($item['snippet'], $item['snippet']['resourceId'])) {

                                $videoIDs[] = $item['snippet']['resourceId']['videoId'];

                            }

                        }

                    }

                }

            }

            // Get details of videos

            if ($idsOnly) {

                $specialVideos = array();

                foreach ($special['videos'] as $video) {

                    $specialVideos[] = $video['id'];

                }

                // Combine all videos

                $allVideos = array_values(array_unique(array_merge($specialVideos, $videoIDs)));

            } else {

                $videoInfo = self::videosInfo($videoIDs, $getDescription, $YouTubeKey);

                // Combine all videos

                $allVideos = array_merge($special['videos'], $videoInfo);

                // Remove existing videos

                $uniqueIDs = array();

                foreach ($allVideos as $pos => $dat) {

                    if (in_array($dat['id'], $uniqueIDs)) {

                        unset($allVideos[$pos]);

                    } else {

                        $uniqueIDs[] = $dat['id'];

                    }

                }

                $allVideos = array_values($allVideos);

            }

            // Dump final result
			
			
			if($isOtherSite){
				$tallVideos = $allVideos;
				$allVideos = array();
				$allVideos[] = $videoTemp;
				foreach($tallVideos as $k => $video){
					$video['site'] = "youtube";
					$allVideos[] = $video;
				}
				//$allVideos = array_unshift($allVideos,$videoTemp);
			}

            $return = array(

                'videos' => $allVideos,

				'directDownload' => isset($config['direct-download']) && $config['direct-download'] == 'yes' ? true : false,
				
                'directAction' => is_string($directAction['next']) ? base64_encode(serialize($directAction)) : false

            );
			//die(print_r($return));

        }



        return $return;

    }

	
	public static function getCharts($countries,$charts,$chartsFile){
		$YouTubeKey = General::youtubeKey();
		$config = Backend::getValue('config');
		$config = is_array($config) ? $config : array();
		if(is_string($YouTubeKey) && count($countries) > 0) {
			$nCharts = array();
			$params = array('key' => $YouTubeKey);

            // Safe search
			
            if (isset($config['safe-search']) &&

                is_string($config['safe-search']) &&

                strlen($config['safe-search']) > 0

            ) {
                $params['safeSearch'] = $config['safe-search'];
            }
			
            $params['maxResults'] = 50;
			$params['part'] = 'id';
            $params['chart'] = "mostPopular";
            $params['order'] = "viewCount";
			$params['regionCode'] = $regionCode;
			
			foreach($charts as $regionCode => $value)
			{
				$lCode = strtolower($regionCode);
				$params['regionCode'] = strtoupper($regionCode);
				$request = http_build_query($params);			
				if (is_string($response = self::http("https://www.googleapis.com/youtube/v3/videos?{$request}"))) {
					$resData = self::object2Array(json_decode($response));
					if (isset($resData['items']) && count($resData['items']) > 0) {
						$nCharts[$lCode] = "";
						foreach($resData['items'] as $index => $item)
							$nCharts[$lCode] .= $item["id"];
					}
				}
					
			}	
			if(count($nCharts) > 0)
			{
				$json = json_encode($nCharts, JSON_PRETTY_PRINT);
				$charts = $nCharts;
				file_put_contents($chartsFile, $json);
			}
		}
		return $charts;
	}


    public static function charts()

    {

        // Charts files

        list($countriesFile, $chartsFile) = array(__DIR__ . '/../charts/countries.json', __DIR__ . '/../charts/charts.json');

        // Get charts data

        $countries = self::object2Array(json_decode(file_get_contents($countriesFile)));

        $charts = self::object2Array(json_decode(file_get_contents($chartsFile)));

        // Update charts data
		
		//$charts = self::getCharts($countries,$charts,$chartsFile);

        if (is_writable($chartsFile) && filemtime($chartsFile) < time() - (86400 * 1)) {
			$charts = self::getCharts($countries,$charts,$chartsFile);
            
			//file_put_contents($chartsFile, file_get_contents('http://files.uniquex.co/charts.json'));

        }

        // Dump data

        return array('countries' => $countries, 'list' => $charts);

    }



    public static function links($videoID, $needSizes = false, $onlyFLV = false)

    {
		if(preg_match("#dailymotion_([a-z0-9]+)#",$videoID,$m)){
			include_once(dirname(__FILE__).DIRECTORY_SEPARATOR ."dailymotion.class.php");
			$Dailymotion = new Dailymotion();
			$videoID = $m[1];
			return $Dailymotion -> links($videoID, $needSizes, false);
		}
		if(preg_match("#facebook_([a-z0-9]+)#",$videoID,$m)){
			include_once(dirname(__FILE__).DIRECTORY_SEPARATOR ."facebook.class.php");
			$Facebook = new Facebook();
			$videoID = $m[1];
			return $Facebook -> links($videoID, $needSizes, false);
		}
        $return = 'Invalid video ID';

        // Get YouTube API key

        $YouTubeKey = General::youtubeKey();

        // Simple validation of youtube video ID

        if (is_string($YouTubeKey) && is_string($videoID) && preg_match('/^[a-z0-9\-\_]{11}$/i', $videoID)) {

            // Check for YouTube video

            $videoInfo = self::videosInfo(array($videoID), false, $YouTubeKey);

            if (is_array($videoInfo) && isset($videoInfo[0]) && $videoInfo[0]['id'] == $videoID) {

                // Check for DMCA notice

                if (class_exists('uniquex\DMCA') && is_array($dmcaRecords = DMCA::getDMCA())) {

                    $notice = false;

                    $vi = $videoInfo[0];

                    foreach ($dmcaRecords as $record) {

                        if ($record[1] == $vi['id'] || $record[1] == $vi['publish']['user']) {

                            $notice = true;

                            break;

                        }

                    }

                    if ($notice) {

                        return 'Video DMCA protected';

                    }

                }

            } else {

                return 'Video not available';

            }

            // Get youtube iTags information

            $iTags = self::itags();

            // Download webpage from youtube server

            if (is_string($webPage = self::http("https://www.youtube.com/embed/{$videoID}"))) {
				//die($webPage);
                // Detect html5 player link

                $playerLink = null;

                $playerPattern = '/"assets":.+?"js":\s*("[^"]+")/';
					
				//preg_match($playerPattern, $webPage, $matches);
				//$_player = json_decode($matches[1]);
				//die(print_r($_player));
				//die(print_r($matches));
                if (preg_match($playerPattern, $webPage, $matches) &&

                    is_string($_player = json_decode($matches[1])) && strlen($_player) >= 1

                ) {
					
					
				
                    $playerLink = substr($_player, 0, 2) == '//' ? "http:{$_player}" : $_player;
					//edited by shravan to fix player link url
					//$playerLink = 'http://s.ytimg.com'.$playerLink;
					$playerLink = 'https://www.youtube.com'.$playerLink;
					//die($playerLink);
					
                }

                // Detect sts value

                $sts = null;

                if (preg_match('/"sts"\s*:\s*(\d+)/i', $webPage, $matches)) {

                    $sts = $matches[1];

                }

                // Get video links and details [Here X = Error (Unknown)]

                $videoDetails = 'X';

                // Detect player SDA rule if need (init)

                $playerRule = null;
				
				 // Detect youtube swf player
				$playerID = "";
				if(preg_match('#swfbin\\\\/player-([A-Za-z0-9\-_]+)#',$webPage,$m)){
					$playerID = $m[1];
				}
				//die($playerID);
				
                // Checking for safe filename of download media file

                $downloadAs = null;

                // Checking parse processing status

                $good = false;

                // Try to get get videos list

                foreach (array('embedded', 'detailpage', 'vevo') as $elKey) {

                    $query = http_build_query(array(

                        'c' => 'web',

                        'el' => $elKey,

                        'hl' => 'en_US',

                        'sts' => $sts,

                        'cver' => 'html5',

                        'eurl' => "https://youtube.googleapis.com/v/{$videoID}",

                        'html5' => '1',

                        'iframe' => '1',

                        'authuser' => '1',

                        'video_id' => $videoID,

                    ));

                    // Download video info

                    if (is_string($videoData = self::http("https://www.youtube.com/get_video_info?{$query}"))) {

                        parse_str($videoData, $videoData);

                        if (is_array($videoData) && isset($videoData['token'])) {

                            $videoDetails = $videoData;

                            break;

                        } elseif (is_array($videoData) &&

                            isset($videoData['status']) &&

                            $videoData['status'] == 'fail'

                        ) {

                            $videoDetails = isset($videoData['reason']) ? $videoData['reason'] : 'X';

                        }

                    }

                }

                // Validate video details

                if (is_array($videoDetails)) {

                    // Check for is rental video

                    if (!(isset($videoDetails['ypc_video_rental_bar_text']) && !isset($videoDetails['author']))) {

                        if (isset($videoDetails['title'])) {

                            $good = true;

                            // Update media link download as name [Safename for Win, Unix, etc...]

                            $specialChars = "\x00\x21\x22\x24\x25\x2a\x2f\x3a\x3c\x3e\x3f\x5c\x7c";

                            $downloadAs = str_replace(str_split($specialChars), '_', $videoDetails['title']);

                        } else {

                            $return = 'Video details are not available [Err: 0x01]';

                        }

                    } else {

                        $return = 'Rental videos are not support';

                    }

                } else {

                    // Checking for unavailable message

                    $return = is_string($videoDetails) && $videoDetails != 'X' ?

                        $videoDetails : 'Video details are not available [Err: 0x02]';

                }

                // Check for perfect video details

                if ($good) {

                    // Default media link data

                    $defaultMedia = array(

                        'extension' => null,

                        'type' => null,

                        'size' => null,

                        'itag' => null,

                        'link' => null,

                        'dash' => false,

                        'video' => array(

                            '3d' => false,

                            'codec' => null,

                            'width' => null,

                            'height' => null,

                            'bitrate' => null,

                            'framerate' => null

                        ),

                        'audio' => array(

                            'codec' => null,

                            'bitrate' => null,

                            'frequency' => null

                        )

                    );

                    // Final media links (init)

                    $finalLinks = array();

                    // Get raw media links

                    $rawMediaLinks = null;

                    // Get media links

                    $mediaLinks = array();

                    // Detect media links

                    if (isset($videoDetails['adaptive_fmts'])) {

                        $rawMediaLinks .= $videoDetails['adaptive_fmts'];

                    }

                    if (isset($videoDetails['url_encoded_fmt_stream_map'])) {

                        $rawMediaLinks .= ",{$videoDetails['url_encoded_fmt_stream_map']}";

                    }

                    if (is_string($rawMediaLinks) && strpos($rawMediaLinks, ',') !== false) {

                        foreach (explode(',', $rawMediaLinks) as $link) {

                            parse_str($link, $link);

                            $mediaLinks[] = $link;

                        }

                    }

                    // Checking for more video links

                    $needMore = true;

                    if ($onlyFLV) {

                        foreach ($mediaLinks as $mLink) {

                            if ($mLink['itag'] == '5') {

                                $needMore = false;

                                break;

                            }

                        }

                    }

                    // Get more video links

                    if ($needMore) {

                        if (is_string($videoPage = self::http("https://www.youtube.com/watch?v={$videoID}"))) {

                            $rawMediaLinks = null;

                            $adaptive = '/\"adaptive\_fmts\"\:\s*\"([^\"]+)/i';

                            $stream = '/\"url\_encoded\_fmt\_stream\_map\"\:\s*\"([^\"]+)/i';

                            if (preg_match($adaptive, $videoPage, $matches) &&

                                is_string($JsonOut = json_decode("\"{$matches[1]}\""))

                            ) {

                                $rawMediaLinks .= $JsonOut;

                            }

                            if (preg_match($stream, $videoPage, $matches) &&

                                strpos($matches[1], 's=') === false &&

                                is_string($JsonOut = json_decode("\"{$matches[1]}\""))

                            ) {

                                $rawMediaLinks .= ',' . $JsonOut;

                            }

                            if (is_string($rawMediaLinks) && strpos($rawMediaLinks, ',') !== false) {

                                foreach (explode(',', $rawMediaLinks) as $link) {

                                    parse_str($link, $link);

                                    $mediaLinks[] = $link;

                                }

                            }

                        }

                    }

                    // Check media links

                    if (count($mediaLinks) >= 1) {
						
						// if($_SERVER['REMOTE_ADDR'] == "113.189.173.233")
						// print_r($mediaLinks);

                        // Scan and optimize all media links

                        foreach ($mediaLinks as $mlink) {							
							

                            // Validate iTag

                            if (isset($mlink['itag']) && $mlink['itag'] != '_rtmp' && isset($mlink['url'])) {

                                // Adjust some important keys

                                parse_str(parse_url($mlink['url'], PHP_URL_QUERY), $mQuery);

                                // Update media query details

                                $mQuery['title'] = $downloadAs;

                                //$mQuery['keepalive'] = 'no';
								
                                $mQuery['keepalive'] = 'yes';

                                $mQuery['ratebypass'] = 'yes';

                                // Update media link

                                if (is_array($Ex = explode('?', $mlink['url']))) {

                                    $mlink['url'] = "{$Ex[0]}?" . http_build_query($mQuery);

                                }

                                // Adjust signature

                                if (isset($mlink['sig'])) {

                                    $mlink['url'] .= "&signature={$mlink['sig']}";

                                }

                                // Decrypt secret signature
                                //**************************** Edited by krakas 17.06.2016 *************************************

                                if (isset($mlink['s'])) {

                                    // Get player SDA rule

                                    if ($playerRule == null && $playerRule !== false && $playerLink != null) {

                                        $playerRule = self::getSDARule($playerLink);

                                    }

                                    // Check player SDA rule

                                    if (is_string($playerRule)) {// Decrypt signature

                                        $signature = self::decryptSignature($mlink['s'], $playerRule);
										if (is_string($signature)) {

                                            $mlink['url'] .= "&signature={$signature}";

                                        }

                                    }


                                    // Decrypt signature using decryptor
                                    //$signature = Decryptor::decryptSig($mlink['s'],$playerID);

                                    //$mlink['url'] .= "&signature={$signature}";
									

                                }
								
								

                                //*****************************************************************************************************

                                // Detect signature on video link

                                if (strpos($mlink['url'], '&signature=') !== false) {

                                    // Get default media link data

                                    $mediaLink = $defaultMedia;



                                    // Update itag value

                                    $mediaLink['itag'] = intval($mlink['itag']);



                                    // Update media link

                                    $mediaLink['link'] = $mlink['url'];



                                    // Detect media link size

                                    if (isset($mlink['clen'])) {

                                        $mediaLink['size'] = self::getNumber($mlink['clen']);

                                    }



                                    // Get details of media link with itag

                                    $iTagV = array();

                                    if (isset($iTags[$mlink['itag']])) {

                                        $iTagInf = $iTags[$mlink['itag']];

                                        // Update media extension

                                        $mediaLink['extension'] = $iTagInf['extension'];

                                        // Update iTag video details

                                        if (isset($iTagInf['video'])) {

                                            $iTagV = $iTagInf['video'];

                                        }

                                        // Check for is DASH media

                                        if (isset($iTagInf['dash']) &&

                                            in_array($iTagInf['dash'], array('video', 'audio'))

                                        ) {

                                            // Detect media type

                                            $mediaLink['type'] = $iTagInf['dash'];

                                            // DASH media

                                            $mediaLink['dash'] = true;

                                            switch ($iTagInf['dash']) {

                                                case 'video':

                                                    // Audio stream is not availabe

                                                    $mediaLink['audio'] = false;

                                                    break;

                                                case 'audio':

                                                    // Video stream is not availabe

                                                    $mediaLink['video'] = false;

                                                    // Audio bitrate & quality

                                                    $Bitrate = isset($mlink['bitrate'])

                                                        ? self::getNumber($mlink['bitrate'])

                                                        : (isset($iTagInf['audio']) &&

                                                        isset($iTagInf['audio']['bitrate'])

                                                            ? $iTagInf['audio']['bitrate'] : null);

                                                    // Update bitrate

                                                    if ($Bitrate != null) {

                                                        $mediaLink['audio']['bitrate'] = self::getNumber($Bitrate);

                                                    }

                                                    // Audio frequency

                                                    if (isset($iTagInf['audio']) &&

                                                        isset($iTagInf['audio']['frequency'])

                                                    ) {

                                                        // Frequency

                                                        $mediaLink['audio']['frequency'] =

                                                            self::getNumber($iTagInf['audio']['frequency']);

                                                    }

                                                    break;

                                            }

                                        } else {

                                            $mediaLink['type'] = 'video';

                                        }

                                    }



                                    // Update media video stream details

                                    if ($mediaLink['video'] !== false) {

                                        // Check for is 3D video

                                        if (isset($iTagV['3d']) && $iTagV['3d']) {

                                            $mediaLink['video']['3d'] = true;

                                        }

                                        // Width & Height

                                        if (isset($mlink['size'])) {

                                            list($width, $height) = explode('x', $mlink['size']);

                                            $mediaLink['video']['width'] = intval($width);

                                            $mediaLink['video']['height'] = intval($height);

                                        } elseif (isset($iTagV['height'])) {

                                            // Get dimensions from iTag info

                                            $mediaLink['video']['height'] = $iTagV['height'];

                                            // Get width of video

                                            if (isset($iTagV['width'])) {

                                                $mediaLink['video']['width'] = $iTagV['width'];

                                            } else {

                                                $mediaLink['video']['width'] = ceil(($iTagV['height'] / 9) * 16);

                                            }

                                        }

                                        // Video bitrate

                                        $vBitrate = isset($mlink['bitrate'])

                                            ? self::getNumber($mlink['bitrate'])

                                            : (isset($iTagV['bitrate']) ? $iTagV['bitrate'] : null);

                                        if ($vBitrate != null) {

                                            $mediaLink['video']['bitrate'] = self::getNumber($vBitrate);

                                        }

                                        // Video FrameRate

                                        $mediaLink['video']['framerate'] = isset($mlink['fps'])

                                            ? self::getNumber($mlink['fps'])

                                            : (isset($iTagV['framerate']) ? $iTagV['framerate'] : null);

                                    }



                                    // Get media extension and codecs

                                    $typePattern = '/^([a-z0-9\-\_\/]+)(\;\s*codecs\="(?P<codecs>[^"]+)")?/i';

                                    if (isset($mlink['type']) && is_string($mlink['type']) &&

                                        preg_match($typePattern, $mlink['type'], $matches)

                                    ) {

                                        // Check media type

                                        if ($mediaLink['type'] == null) {

                                            $mediaLink['type'] = stripos($matches['type'], 'audio') !== false

                                                ? 'audio' : 'video';

                                        }

                                        // Check media file extension

                                        if ($mediaLink['extension'] == null) {

                                            if (stripos($matches['type'], 'mp4')) {

                                                $mediaLink['extension'] = $mediaLink['type'] == 'video' ? 'mp4' : 'm4a';

                                            } elseif (stripos($matches['type'], 'webm')) {

                                                $mediaLink['extension'] = 'webm';

                                            } elseif (stripos($matches['type'], 'flv')) {

                                                $mediaLink['extension'] = 'flv';

                                            } elseif (stripos($matches['type'], '3gp')) {

                                                $mediaLink['extension'] = '3gp';

                                            }

                                        }

                                        // Update codec details

                                        if (isset($matches['codecs'])) {

                                            $codecs = explode(',', $matches['codecs']);

                                            // Check for is DASH media

                                            if (!$mediaLink['dash'] && count($codecs) == 1) {

                                                $mediaLink['dash'] = true;

                                            }

                                            // Media stream codecs

                                            if ($mediaLink['type'] == 'video') {

                                                // Update video codec

                                                if (is_array($mediaLink['video']) && isset($codecs[0])) {

                                                    $vCodec = explode('.', trim($codecs[0]));

                                                    $mediaLink['video']['codec'] = $vCodec[0];

                                                }

                                                // Update audio codec

                                                if (is_array($mediaLink['audio']) && isset($codecs[1])) {

                                                    $aCodec = explode('.', trim($codecs[1]));

                                                    $mediaLink['audio']['codec'] = $aCodec[0];

                                                }

                                            } else {

                                                // Update audio codec

                                                if (is_array($mediaLink['audio']) && isset($codecs[0])) {

                                                    $vCodec = explode('.', trim($codecs[0]));

                                                    $mediaLink['audio']['codec'] = $vCodec[0];

                                                }

                                            }

                                        }

                                    }

                                    // Add media link details to final links

                                    $finalLinks[$mediaLink['itag']] = $mediaLink;

                                }

                            }

                        }

                    }

                    // Remove useless blocks on DASH media

                    foreach ($finalLinks as $iTag => $data) {

                        if ($data['dash']) {

                            // Detect remove block

                            $rBlock = $data['type'] == 'video' ? 'audio' : 'video';

                            // Remove useless block

                            $finalLinks[$iTag][$rBlock] = false;

                        }

                        // Remove media block if extension not available

                        if ($data['extension'] == null) {

                            unset($finalLinks[$iTag]);

                        }

                    }

                    // Get DASH media links

                    if (!$onlyFLV && isset($videoDetails['dashmpd']) && function_exists('simplexml_load_string')) {

                        $dmfLink = $videoDetails['dashmpd'];

                        // Optimize DASH manifest link

                        /******************************************* Edited by krakas 17.06.2016 **************************************************/

                        if (preg_match('/\/s\/([a-z0-9\.]+)/i', $dmfLink, $matches) && is_string($playerLink)) {

                            // Get player SDA rule

                            if ($playerRule == null && $playerRule !== false && $playerLink != null) {

                                $playerRule = self::getSDARule($playerLink);
							}

                            // Validate player SDA rule

                            if (is_string($playerRule)) {

                                // Decode signature

                                $newSignature = self::decryptSignature($matches[1], $playerRule);
								         
                                // Decrypt signature using Decryptor
                                //$newSignature = Decryptor::decryptSig($matches[1],$playerID);

                                if (is_string($newSignature)) {

                                    // Update DASH manifest link

                                    $dmfLink = str_replace($matches[0], "/signature/{$newSignature}", $dmfLink);

                                }

                            }

                        }

                        // ***************************************************************************************************************************

                        // Download DMF data

                        if (is_string($manifestData = self::http($dmfLink))) {

                            // Get total DASH media links (init)

                            $mediaList = array();

                            // Parse XML manifest data

                            if (is_object($manifestXML = @simplexml_load_string($manifestData))) {

                                // Scan for media links

                                foreach ($manifestXML->{'Period'}->{'AdaptationSet'} as $vBlock) {

                                    // Get attributes

                                    $attributes = self::xmlAttributes($vBlock);

                                    // Get media type and extension

                                    list($mType, $mExtension) = explode('/', $attributes['mimeType']);

                                    // Scan for media links

                                    foreach ($vBlock->{'Representation'} as $Media) {

                                        // Get default media link details

                                        $mediaLink = $defaultMedia;

                                        // This is DASH media link

                                        $mediaLink['dash'] = true;

                                        // Get attributes

                                        $attributes = self::xmlAttributes($Media);

                                        // Optimize video link

                                        $mLink = (string)$Media->{'BaseURL'};

                                        // Adjust some important keys

                                        parse_str(parse_url($mLink, PHP_URL_QUERY), $mQuery);

                                        // Update media query details

                                        $mQuery['title'] = $downloadAs;

                                       //$mQuery['keepalive'] = 'no';
										
                                        $mQuery['keepalive'] = 'yes';

                                        $mQuery['ratebypass'] = 'yes';

                                        // Update media link

                                        if (is_array($Ex = explode('?', $mLink))) {

                                            $mLink = "{$Ex[0]}?" . http_build_query($mQuery);

                                        }

                                        // Get media size

                                        $mSize = self::xmlAttributes($Media->{'BaseURL'}, 'yt');

                                        $mSize = self::getNumber($mSize['contentLength']);

                                        // Optimize media link details

                                        $mediaLink['itag'] = intval($attributes['id']);

                                        $mediaLink['link'] = $mLink;

                                        $mediaLink['type'] = $mType;

                                        $mediaLink['size'] = $mSize;

                                        $mediaLink['extension'] = $mType == 'audio' && $mExtension == 'mp4'

                                            ? 'm4a' : $mExtension;

                                        // Media type details

                                        if ($mType == 'video') {

                                            // Audio stream is not found

                                            $mediaLink['audio'] = false;

                                            // Video codec

                                            $mediaLink['video']['codec'] = $attributes['codecs'];

                                            // Video width and height

                                            $mediaLink['video']['width'] = intval($attributes['width']);

                                            $mediaLink['video']['height'] = intval($attributes['height']);

                                            // Video bitrate

                                            $mediaLink['video']['bitrate'] = self::getNumber($attributes['bandwidth']);

                                            // Video frame rate

                                            $mediaLink['video']['framerate'] = intval($attributes['frameRate']);

                                        } else {

                                            // Video stream is not found

                                            $mediaLink['video'] = false;

                                            // Audio codec

                                            $mediaLink['audio']['codec'] = $attributes['codecs'];

                                            // Audio bitrate

                                            $mediaLink['audio']['bitrate'] = self::getNumber($attributes['bandwidth']);

                                            // Audio frequency

                                            $mediaLink['audio']['frequency'] =

                                                self::getNumber($attributes['audioSamplingRate']);

                                        }

                                        // Update media link

                                        $mediaList[] = $mediaLink;

                                    }

                                }

                            }

                            // Check for media links

                            if (count($mediaList) >= 1) {

                                foreach ($mediaList as $DMFMedia) {

                                    // Update final links

                                    $finalLinks[$DMFMedia['itag']] = $DMFMedia;

                                }

                            }

                        }

                    }

                    $needSizes = true;

                    // Optimize media link file sizes

                    if ($needSizes) {

                        foreach ($finalLinks as $iTag => $data) {

                            if ($data['size'] == null) {

                                // Get media size from headers

                                $mediaSize = 0;

                                if (is_array($mHeaders = self::http($data['link'], true))) {

                                    foreach (array_reverse($mHeaders) as $H) {

                                        if (preg_match('/^Content\-Length\:\s*(.*)/i', $H, $M)) {

                                            $mediaSize = self::getNumber($M[1]);

                                        } elseif (preg_match('/HTTP\//i', $H)) {

                                            break;

                                        }

                                    }

                                }

                                $finalLinks[$iTag]['size'] = $mediaSize;

                                // Check media link file size

                                /*if ($mediaSize > 0) {

                                    // Update media link size

                                } else {

                                    // Remove media link from final links

                                    unset($finalLinks[$iTag]);

                                }*/

                            }

                        }

                    }

                    // Final checkout of videos list

                    $return = count($finalLinks) >= 1

                        ? array_values($finalLinks)

                        : 'Video links are not available';
					//die(print_r($return));
                }

            } else {

                $return = 'Failed to download video webpage';

            }

        }

        // Add video to recent videos

        if (is_array($return)) {

            // Create recent videos file

            $recent = __DIR__ . '/../store/.recent';

            if (!is_file($recent)) {

                file_put_contents(__DIR__ . '/../store/.recent', '');

            }

            // Update recent videos data

            $rData = file_get_contents($recent);

            if (!(is_string($rData) && strpos($rData, $videoID) !== false) &&

                is_resource($rFile = fopen($recent, 'a+'))

            ) {

                // Append data

                fwrite($rFile, $videoID);

                // Close recent file

                fclose($rFile);

            }

            // Optimize recent videos file data

            if (strlen($rData) > 77000) {

                file_put_contents($recent, substr($rData, 55000));

            }

        }



        return $return;

    }



    public static function youtubeAPI($apiKey)

    {

        $return = 'Failed to connect YouTube API server (timeout/unknown)';

        // Check API key

        if (is_string($apiKey)) {

            // Prepare request params for getting information of video

            $params = array(

                'key' => $apiKey,

                'id' => 'e-ORhEE9VVg',

                'part' => 'snippet,statistics,contentDetails',

                'fields' => 'items(id)'

            );

            // Pack request params

            $request = http_build_query($params);

            // Send HTTP request and get response

            if (is_string($response = YouTube::http("https://www.googleapis.com/youtube/v3/videos?{$request}"))) {

                // Decode json data

                if (is_array($data = YouTube::object2Array(json_decode($response)))) {

                    if (isset($data['items'])) {

                        $return = true;

                    } elseif (isset($data['error'])) {

                        $return = implode(", ", array_values($data['error']['errors'][0]));

                    } else {

                        $return = 'Unknown error from YouTube API server';

                    }

                } else {

                    $return = 'Invalid data received from YouTube API server';

                }

            }

        }

        return $return;

    }


	//**************************** Added by trongtd1988 31.03.2017 *************************************

	
    private static function getChannel($userID, $youtubeKey)

    {

        $return = false;

        if (is_string($userID) && is_string($youtubeKey)) {

            // Prepare request params

            $params = array(

                'part' => 'id',

                'forUsername' => $userID,

                'fields' => 'items(id)',

                'key' => $youtubeKey

            );

            $request = http_build_query($params);

            // Send request to YouTube API server

            if (is_string($response = self::http("https://www.googleapis.com/youtube/v3/channels?{$request}"))) {

                // Decode response data

                $resData = json_decode($response);

                $channel = $resData->items[0]->id;

                if (is_string($channel)) {

                    $return = $channel;

                }

            }

        }

        return $return;

    }



    private static function itags()

    {

        return self::object2Array(json_decode(file_get_contents(__DIR__ . '/resources/itags.json')));

    }



    private static function xmlAttributes($xmlNode, $nameSpace = null)

    {

        $attributes = (array)(is_string($nameSpace) ?

            $xmlNode->{'attributes'}($nameSpace, true) : $xmlNode->{'attributes'}());

        return $attributes['@attributes'];

    }



      private static function videosInfo($videoIDs, $getDescription, $youtubeKey)

      {

          $return = array();

          // Check for video Ids

          if (is_array($videoIDs) && count($videoIDs) > 0) {

              // Prepare request params for getting information of video

              $params = array(

                  'id' => implode(',', $videoIDs),

                  'key' => $youtubeKey,

                  'part' => 'snippet,statistics,contentDetails',

                  'fields' => 'items(id,snippet(publishedAt,channelId,title,' .

                      ($getDescription ? 'description,' : null) . 'channelTitle),' .

                      'contentDetails(duration,dimension,definition),statistics(viewCount,likeCount,dislikeCount))'

              );

              // Pack request params

              $request = http_build_query($params);

              // Send HTTP request

              if (is_string($remote = self::http("https://www.googleapis.com/youtube/v3/videos?{$request}")) &&

                  is_array($items = self::object2Array(json_decode($remote))) &&

                  isset($items['items']) && count($items['items']) > 0

              ) {

                  // Prepare video information

                  foreach ($items['items'] as $videoInfo) {

                      // Get video information

                      $info = self::infoMaker($videoInfo);

                      // Validate and update videos info

                      if (is_array($info)) {

                          $return[] = $info;

                      }

                  }

              }

          }

          return $return;

      }



    private static function infoMaker($info)

    {

        $return = false;

        // Validate input

        if (is_array($info) && isset($info['id'])) {

            // Video information (init)

            $return = array(

                'id' => null,

                'title' => null,

                'description' => null,

                'thumbnail' => null,

                'webpage' => null,

                'duration' => null,

                'publish' => array(

                    'date' => null,

                    'user' => null,

                    'owner' => null,

                ),

                'stats' => array(

                    'views' => 0,

                    'likes' => 0,

                    'dislikes' => 0,

                    'rating' => 0,

                ),

                'features' => array(

                    'HD' => false,

                    '3D' => false

                )

            );

            // Video ID

            $return['id'] = $info['id'];

            // Thumbnail

            $return['thumbnail'] = "https://img.youtube.com/vi/{$info['id']}/mqdefault.jpg";

            // Webpage

            $return['webpage'] = "https://www.youtube.com/watch?v={$info['id']}";

            // Duration and features

            if (isset($info['contentDetails'])) {

                if (isset($info['contentDetails']['duration'])) {

                    $return['duration'] = self::parseDuration($info['contentDetails']['duration']);

                }

                if (isset($info['contentDetails']['definition']) &&

                    strpos($info['contentDetails']['definition'], 'hd') !== false

                ) {

                    $return['features']['HD'] = true;

                }

                if (isset($info['contentDetails']['dimension']) &&

                    strpos($info['contentDetails']['dimension'], '3d') !== false

                ) {

                    $return['features']['3D'] = true;

                }

            }

            // Details

            if (isset($info['snippet'])) {

                $s = $info['snippet'];

                // Video title

                if (isset($s['title'])) {

                    $return['title'] = $s['title'];

                }

                // Video description

                if (isset($s['description'])) {

                    $return['description'] = $s['description'];

                }

                // Pulish date

                if (isset($s['publishedAt']) &&

                    is_numeric($ts = strtotime($s['publishedAt'])) && $ts > 0 &&

                    is_string($Date = gmdate('M jS, Y', $ts))

                ) {

                    $return['publish']['date'] = $Date;

                }

                // Publish user ID

                if (isset($s['channelId'])) {

                    $return['publish']['user'] = $s['channelId'];

                }

                // Publish user name

                if (isset($s['channelTitle'])) {

                    $return['publish']['owner'] = $s['channelTitle'];

                }

            }

            // Statistics

            if (isset($info['statistics'])) {

                $s = $info['statistics'];

                // Views

                if (isset($s['viewCount'])) {

                    $return['stats']['views'] = self::getNumber($s['viewCount']);

                }

                // Likes

                if (isset($s['likeCount'])) {

                    $return['stats']['likes'] = self::getNumber($s['likeCount']);

                }

                // Dislikes

                if (isset($s['dislikeCount'])) {

                    $return['stats']['dislikes'] = self::getNumber($s['dislikeCount']);

                }

                // Calculate rating

                if ($return['stats']['likes'] + $return['stats']['dislikes'] > 0) {

                    $return['stats']['rating'] =

                        round(

                            $return['stats']['likes'] /

                            (($return['stats']['likes'] + $return['stats']['dislikes']) / 10)

                        ) / 2;

                }

            }

        }

        return $return;

    }



    private static function http($Link, $headersOnly = false)

    {

        $return = null;

        if (is_string($Link)) {

            // Get configuration

            $config = Backend::getValue('config');

            $config = is_array($config) ? $config : array();

            // Start process

            $gZipHeader = false;

            // Default UserAgent string

            $UA =

                'Mozilla/5.0 (Windows NT 6.3; WOW64) ' .

                'AppleWebKit/537.36 (KHTML, like Gecko) ' .

                'Chrome/40.0.2214.115 Safari/537.36';

            // Prepare headers for HTTP request

            $Headers = array(

                'Referer' => $Link,

                'Accept-Language' => 'en-US,en;q=0.8,te;q=0.6',

                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',

                'User-Agent' => $UA,

            );

            if (function_exists('gzencode') && function_exists('gzdecode')) {

                $gZipHeader = true;

                $Headers['Accept-Encoding'] = 'gzip';

            }

            $Headers['Connection'] = 'Close';

            // Pack all headers

            $HeadPack = '';

            foreach ($Headers as $K => $V) {

                $HeadPack .= "{$K}: {$V}\r\n";

            }

            // Create a stream

            $options = array(

                'http' => array(

                    'method' => 'GET',

                    'header' => $HeadPack,

                    'timeout' => 10,

                    'max_redirects' => 10,

                    'ignore_errors' => true,

                    'follow_location' => 1,

                    'protocol_version' => '1.0',

                )

            );

            // Force IP version

            if (isset($config['force-ipv4']) && $config['force-ipv4'] == 'yes') {

                $options['socket'] = array('bindto' => '192.168.0.102:0');

            }

            // Pack options

            $context = stream_context_create($options);

            // Open HTTP connection

            if (is_resource($http = fopen($Link, 'r', null, $context))) {

                // Get response headers

                list($meta, $headers) = array(stream_get_meta_data($http), array());

                if (is_array($meta) && isset($meta['wrapper_data']) && is_array($meta['wrapper_data'])) {

                    $headers = $meta['wrapper_data'];

                    if (isset($headers['headers']) && is_array($headers['headers'])) {

                        $headers = $headers['headers'];

                    }

                }

                // Get headers from CURL

                if (count($headers) == 0 && function_exists('curl_version')) {

                    // Prepare CURL connection

                    $curl = curl_init();

                    curl_setopt($curl, CURLOPT_URL, $Link);

                    curl_setopt($curl, CURLOPT_HEADER, true);

                    curl_setopt($curl, CURLOPT_NOBODY, true);

                    // Enable gZip

                    if ($gZipHeader) {

                        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');

                    }

                    // Force IP version

                    if (isset($config['force-ipv4']) &&

                        $config['force-ipv4'] == 'yes' &&

                        defined('CURLOPT_IPRESOLVE')

                    ) {

                        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

                    }

                    // Set content range

                    if (isset($_SERVER['HTTP_RANGE'])) {

                        curl_setopt($curl, CURLOPT_RANGE, str_replace('bytes=', '', $_SERVER['HTTP_RANGE']));

                    }

                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                    curl_setopt($curl, CURLOPT_HTTPHEADER, $Headers);

                    @curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

                    // Get headers

                    $headers = explode("\n", str_replace("\r\n", "\n", curl_exec($curl)));

                }

                if ($headersOnly) {

                    // Get headers only

                    $return = $headers;

                } else {

                    // Download content

                    $content = '';

                    while (!feof($http)) {

                        $content .= fread($http, 1024 * 8);

                    }

                    // Decode gZiped content

                    $originalContent = $content;

                    $content = $gZipHeader ? @gzdecode($content) : null;

                    $content = is_string($content) ? $content : $originalContent;

                    // Check content

                    if (strlen($content) >= 1) {

                        $return = $content;

                    }

                }

                // Close HTTP connection

                fclose($http);

            }

        }

        return $return;

    }



    private static function getSDARule($playerLink)

    {
		$SWF_DIR = __DIR__ ."/swfs/";
        $return = false;

        // Check player link

        if (is_string($playerLink)) {

            // Search on cache

            if (in_array($playerLink, self::$sandBox)) {

                $return = self::$sandBox[$playerLink];

            } else {
				$playerID = "";
				if(preg_match('#/player-([A-Za-z0-9\-_]+)#',$playerLink,$m)){
					$playerID = $m[1];
				}
				$is_nlink = true;
				if(preg_match('#([a-z]{2})_([A-Z]{2})-([A-Za-z0-9\-_]+)#',$playerLink,$m)){
					$playerID = $m[3];
					$is_nlink = false;					
				}
				if(preg_match('/([a-zA-Z0-9\-_=]+)/',$playerID,$m))
					$playerID = $m[1];
				if($is_nlink)
					$playerLink = "https://www.youtube.com/yts/jsbin/player-$playerID/en_US/base.js";
				else
					$playerLink = "https://www.youtube.com/yts/jsbin/player-en_US-$playerID/base.js";
				
				///die($playerID);
				if($playerID && file_exists($SWF_DIR .$playerID))
				{
					$dscript = @file_get_contents($SWF_DIR .$playerID);
					if(!empty($dscript) && preg_match('/([R|S|W]{1})(\d+)/', $dscript, $m)){
						self::$sandBox[$playerLink] = $dscript;
						$return = $dscript;
					}
				}
				//die($playerLink);
                // Get player signature
				$dscript = self::getDecrypt($playerLink);
				if(!empty($dscript)){
					self::$sandBox[$playerLink] = $dscript;
					$return = $dscript;
					if($playerID){
						@file_put_contents($SWF_DIR .$playerID,$dscript);
					}
				}                

            }

        }



        // Dump player signature

        return $return;

    }
	/*
	  Created by: trongtd1988
	  On: 28.06.2016

	*/
	private static function getDecrypt($playerLink){
		$get_file = @file_get_contents($playerLink);
		$defunc = "";
		$de = "";
		if(preg_match("#\"signature\",([\$a-zA-Z0-9]+)\([a-zA-Z]+\)#Us",$get_file,$ms)){
			$defunc = $ms[1];
		}
		if(preg_match("#".str_replace('$','\$',$defunc)."=function\(a\){(.*)};#Us",$get_file,$ms)){
			$de = $ms[1];
		}
		$ffuncs = array();
		$mfunc = "";
		if(preg_match_all("#([\$a-zA-Z0-9]+).([a-zA-Z0-9^join]+)\((.*)\)#Us",$de,$ms)){
			//print_r($ms);
			//$de = $ms[1];
			foreach($ms[2] as $k => $val)
			{			
				if( $val!="split" &&  $val != "join")
				{
					$ffuncs[] = $val;
					$mfunc = $ms[1][$k];
				}
			}
		}
		//print_r($ffuncs);
		//print_r($mfunc);
		//echo "defunc $defunc\r\n";
		//echo "de $de\r\n";
		$dfuncs = array();
		if(preg_match("#".str_replace('$','\$',$mfunc)."={([a-zA-Z0-9]+):function\([a-z,]*\){(.*)},\s*([a-zA-Z0-9]+):function\([a-z,]*\){(.*)},\s*([a-zA-Z0-9]+):function\([a-z,]*\){(.*)}}#Us",$get_file,$ms)){
			//print_r($ms);
			$decl = $mfunc;
			$i = 1;
			$dlen = count($ms);
			while($i < $dlen){
				$func = $ms[$i];
				if(stristr($ms[$i+1],".length"))
					$dfuncs[$func] = "W";
				if(stristr($ms[$i+1],".reverse"))
					$dfuncs[$func] = "R";
				if(stristr($ms[$i+1],".splice"))
					$dfuncs[$func] = "S";
				$i = $i + 2;
			}	
		}
		$dscript = "";
		if($de && count($dfuncs) > 0){
			$dlines = explode(";",$de);
			foreach($dlines as $k => $val){
				if(preg_match("/".str_replace('$','\$',$decl).".([a-zA-Z0-9]+)\(a(|,[0-9]+)\)/",$val,$m))
				{
					$func = $dfuncs[$m[1]];
					$dscript .= $func.($m[2] ? ltrim($m[2],",") : 0);
				}		
			}
		}		
		//die($dscript);
		return $dscript;
	}

    private static function decryptSignature($encryptedSig, $algorithm)

    {

        $output = false;

        // Validate pattern of SDA rule

        if (is_string($encryptedSig) && is_string($algorithm) &&

            preg_match_all('/([R|S|W]{1})(\d+)/', $algorithm, $matches)

        ) {

            // Apply each SDA rule on encrypted signature

            foreach ($matches[1] as $pos => $cond) {

                $size = $matches[2][$pos];

                switch ($cond) {

                    case 'R':

                        // Reverse EncSig (Encrypted Signature)

                        $encryptedSig = strrev($encryptedSig);

                        break;

                    case 'S':

                        // Splice EncSig

                        $encryptedSig = substr($encryptedSig, $size);

                        break;

                    case 'W':

                        // Swap first char and nth char on EncSig

                        $sigArray = str_split($encryptedSig);

                        $zeroChar = $sigArray[0];

                        // Replace positions

                        $sigArray[0] = @$sigArray[$size];

                        $sigArray[$size] = $zeroChar;

                        // Join signature

                        $encryptedSig = implode('', $sigArray);

                        break;

                }

            }

            // Finally dump decrypted signature :)

            $output = $encryptedSig;

        }



        return $output;

    }



    private static function getNumber($data)

    {

        return floatval(number_format(floatval($data), 0, '.', ''));

    }



    private static function parseDuration($duration)

    {

        $return = 0;

        // Validate duration

        if (is_string($duration)) {

            // Optimize duration

            $newDuration = '';

            foreach (explode('T', strtoupper($duration)) as $i => $s) {

                $newDuration .= $i == 0 ? $s : str_replace('M', 'I', $s);

            }

            // Parse duration

            if (preg_match_all('/([0-9]+)([a-z]+)/i', $newDuration, $M)) {

                foreach (array_combine($M[2], $M[1]) as $Type => $number) {

                    // Optimize number

                    $number = intval($number);

                    // Process duration

                    switch ($Type) {

                        case 'Y':

                            $return += $number * 86400 * 365;

                            break;

                        case 'M':

                            $return += $number * 86400 * 30;

                            break;

                        case 'W':

                            $return += $number * 86400 * 7;

                            break;

                        case 'D':

                            $return += $number * 86400;

                            break;

                        case 'H':

                            $return += $number * 3600;

                            break;

                        case 'I':

                            $return += $number * 60;

                            break;

                        case 'S':

                            $return += $number;

                            break;

                    }

                }

            }

        }

        return $return;

    }



    private static function object2Array($data)

    {

        // Checking data type

        if (is_array($data) || is_object($data)) {

            $output = array();

            // Convert object to array in recursive method

            foreach ($data as $key => $value) {

                $output[$key] = self::object2Array($value);

            }

            // Update data

            $data = $output;

        }

        return $data;

    }

}
