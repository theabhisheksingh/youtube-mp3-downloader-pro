<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Import image library
require_once __DIR__ . '/../core/Image.php';

// Get runtime error
$thumbFailed = false;

// Get video IDs
$videos = uniqueX\Backend::getValue('shareThumb');
$videos = uniqueX\YouTube::search(is_string($videos) ? $videos : false);
$videos =
    is_array($videos) && isset($videos['videos']) &&
    is_array($videos['videos']) && count($videos['videos']) > 0
        ? $videos['videos'] : false;

// Check GD library
if (extension_loaded('GD')) {
    // Check videos
    if (is_array($videos)) {
        // Get videos list
        $vIDs = array();
        $vDetails = array();
        foreach ($videos as $video) {
            $vIDs[] = $video['id'];
            $vDetails[$video['id']] = $video;
        }
        // Get videos with raw thumbnails data (init)
        $videoThumbs = array();
        // Multiple videos
        if (count($vIDs) > 3) {
            // Get raw data of video thumbnails
            $perfectThumb = array();
            $thumbs = array();
            // Grab thumbails
            for ($i = 0; $i < 4; $i++) {
                if (is_string($thumb = uniqueX\General::getThumbnail($vIDs[$i]))) {
                    $thumbs[$vIDs[$i]] = $thumb;
                    // Update perfect thumbnail
                    if (count($perfectThumb) == 0) {
                        $perfectThumb[$vIDs[$i]] = $thumb;
                    }
                }
            }
            // Check thumbnails
            if (count($thumbs) == 4) {
                $videoThumbs = $thumbs;
            } elseif (count($perfectThumb) != 0) {
                $videoThumbs = $perfectThumb;
            }
        } elseif (is_string($thumb = uniqueX\General::getThumbnail($vIDs[0], true))) {
            $videoThumbs[$vIDs[0]] = $thumb;
        }
        // Thumbnail dimensions
        $thumbWidth = 470;
        $thumbHeight = 246;
        // Start process
        if (count($videoThumbs) == 4) {
            // Four thumbnails
            $videoIDs = array_keys($videoThumbs);
            // Create thumbnail
            $img = new uniqueX\Image($thumbWidth, $thumbHeight);
            // Prepare thumbnail
            $img->box(array(
                'width' => $thumbWidth,
                'height' => $thumbHeight,
                'background' => 'black'
            ));
            // Add video thumbnails
            $img->box(array(
                'x' => 0,
                'y' => 0,
                'width' => $thumbWidth / 2,
                'height' => $thumbHeight / 2,
                'background' => imagecreatefromstring($videoThumbs[$videoIDs[0]])
            ));
            $img->box(array(
                'x' => $thumbWidth / 2,
                'y' => 0,
                'width' => $thumbWidth / 2,
                'height' => $thumbHeight / 2,
                'background' => imagecreatefromstring($videoThumbs[$videoIDs[1]])
            ));
            $img->box(array(
                'x' => 0,
                'y' => $thumbHeight / 2,
                'width' => $thumbWidth / 2,
                'height' => $thumbHeight / 2,
                'background' => imagecreatefromstring($videoThumbs[$videoIDs[2]])
            ));
            $img->box(array(
                'x' => $thumbWidth / 2,
                'y' => $thumbHeight / 2,
                'width' => $thumbWidth / 2,
                'height' => $thumbHeight / 2,
                'background' => imagecreatefromstring($videoThumbs[$videoIDs[3]])
            ));
            // Website watermark
            $waterMark = $img->message(array(
                'x' => "({$thumbWidth}-w)-2",
                'y' => "({$thumbHeight}-h)-2",
                'text' => $_SERVER['HTTP_HOST'],
                'color' => '#004b79',
                'font' => 'SourceCodePro-Medium',
                'size' => 8
            ), true);
            $img->box(array(
                'x' => $thumbWidth - $waterMark['area']['width'] - 4,
                'y' => $thumbHeight - $waterMark['area']['height'] - 4,
                'width' => $waterMark['area']['width'] + 4,
                'height' => $waterMark['area']['height'] + 4,
                'background' => 'white'
            ));
            $img->message(array(
                'x' => "({$thumbWidth}-w)-2",
                'y' => "({$thumbHeight}-h)-2",
                'text' => $_SERVER['HTTP_HOST'],
                'color' => '#004b79',
                'font' => 'SourceCodePro-Medium',
                'size' => 8
            ), true);
            // Play icon shadow
            $img->box(array(
                'x' => ($thumbWidth - 132) / 2,
                'y' => ($thumbHeight - 132) / 2,
                'width' => 132,
                'height' => 132,
                'background' => imagecreatefromstring(file_get_contents(__DIR__ . '/../core/resources/play_dark.png')),
                'opacity' => 75
            ));
            // Play icon
            $img->box(array(
                'x' => ($thumbWidth - 128) / 2,
                'y' => ($thumbHeight - 128) / 2,
                'width' => 128,
                'height' => 128,
                'background' => imagecreatefromstring(file_get_contents(__DIR__ . '/../core/resources/play.png')),
                'opacity' => 10
            ));
            // Display thumbnail
            $img->display();
        } elseif (count($videoThumbs) == 1) {
            // Single thumbnail
            $videoIDs = array_keys($videoThumbs);
            $videoDetails = $vDetails[$videoIDs[0]];
            $videoThumbnail = $videoThumbs[$videoIDs[0]];
            // Create thumbnail
            $img = new uniqueX\Image($thumbWidth, $thumbHeight);
            // Set video thumb
            $img->box(array(
                'width' => $thumbWidth,
                'height' => $thumbHeight,
                'background' => imagecreatefromstring($videoThumbnail)
            ));
            // Play icon shadow
            $img->box(array(
                'x' => ($thumbWidth - 68) / 2,
                'y' => (($thumbHeight - 50) - 68) / 2,
                'width' => 68,
                'height' => 68,
                'background' => imagecreatefromstring(file_get_contents(__DIR__ . '/../core/resources/play_dark.png')),
                'opacity' => 75
            ));
            // Play icon
            $img->box(array(
                'x' => ($thumbWidth - 64) / 2,
                'y' => (($thumbHeight - 50) - 64) / 2,
                'width' => 64,
                'height' => 64,
                'background' => imagecreatefromstring(file_get_contents(__DIR__ . '/../core/resources/play.png')),
                'opacity' => 10
            ));
            // HD feature
            if ($videoDetails['features']['HD']) {
                $img->box(array(
                    'x' => $thumbWidth - (42 + 10),
                    'y' => 10,
                    'width' => 42,
                    'height' => 42,
                    'background' => imagecreatefromstring(file_get_contents(__DIR__ . '/../core/resources/hd.png')),
                    'opacity' => 10
                ));
            }
            // 3D feature
            if ($videoDetails['features']['3D']) {
                $img->box(array(
                    'x' => $thumbWidth - (84 + 20),
                    'y' => 10,
                    'width' => 42,
                    'height' => 42,
                    'background' => imagecreatefromstring(file_get_contents(__DIR__ . '/../core/resources/3d.png')),
                    'opacity' => 10
                ));
            }
            // Footer
            $img->box(array(
                'y' => $thumbHeight - 50,
                'width' => 470,
                'height' => 50,
                'background' => 'white',
                'opacity' => 0
            ));
            // Video details
            $videoOwner = $videoDetails['publish']['owner'];
            $videoOwner = strlen($videoOwner) > 4 && substr($videoOwner, -4) == 'VEVO'
                ? substr($videoOwner, 0, -4) : $videoOwner;
            $videoOwner = strlen($videoOwner) > 13 ? substr($videoOwner, 0, 13) . '.' : $videoOwner;
            $img->message(array(
                'x' => 10,
                'y' => $thumbHeight - 41,
                'text' => "   {$videoOwner}",
                'color' => '#0079c1',
                'font' => 'SourceCodePro-Medium',
                'size' => 10
            ));
            $img->message(array(
                'x' => 10,
                'y' => $thumbHeight - 41,
                'text' => 'by ' . str_repeat(' ', strlen($videoOwner)) . ' on',
                'color' => '#6a737b',
                'font' => 'SourceCodePro-Medium',
                'size' => 10
            ));
            $img->message(array(
                'x' => 10,
                'y' => $thumbHeight - 41,
                'text' => '   ' . str_repeat(' ', strlen($videoOwner)) . '    ' . $videoDetails['publish']['date'],
                'color' => '#7d3f98',
                'font' => 'SourceCodePro-Medium',
                'size' => 10
            ));
            // Website watermark
            $img->message(array(
                'x' => "{$thumbWidth}-(w+8)",
                'y' => $thumbHeight - 41,
                'text' => $_SERVER['HTTP_HOST'],
                'color' => '#004b79',
                'font' => 'SourceCodePro-Medium',
                'size' => 8
            ));
            // Video stats
            $videoViews = number_format($videoDetails['stats']['views']);
            $videoLikes = number_format($videoDetails['stats']['likes']);
            $videoDislikes = number_format($videoDetails['stats']['dislikes']);
            // Video views
            $img->message(array(
                'x' => 10,
                'y' => $thumbHeight - 19,
                'text' => json_decode('"&#xF26C;"'),
                'color' => '#0085c3',
                'font' => 'FontAwesome',
                'size' => 12
            ));
            $viewsArea = $img->message(array(
                'x' => 32,
                'y' => $thumbHeight - 16,
                'text' => $videoViews,
                'color' => '#0085c3',
                'font' => 'SourceCodePro-Regular',
                'size' => 10
            ), true);
            // Video likes
            $img->message(array(
                'x' => 32 + $viewsArea['area']['width'] + 13,
                'y' => $thumbHeight - 21,
                'text' => json_decode('"&#xF087;"'),
                'color' => '#237f52',
                'font' => 'FontAwesome',
                'size' => 12
            ));
            $likesArea = $img->message(array(
                'x' => 32 + $viewsArea['area']['width'] + 13 + 20,
                'y' => $thumbHeight - 16,
                'text' => $videoLikes,
                'color' => '#237f52',
                'font' => 'SourceCodePro-Regular',
                'size' => 10
            ), true);
            // Video dislikes
            $img->message(array(
                'x' => 32 + $viewsArea['area']['width'] + 13 + 20 + $likesArea['area']['width'] + 13,
                'y' => $thumbHeight - 20,
                'text' => json_decode('"&#xF088;"'),
                'color' => '#f53794',
                'font' => 'FontAwesome',
                'size' => 12
            ));
            $img->message(array(
                'x' => 32 + $viewsArea['area']['width'] + 13 + 20 + $likesArea['area']['width'] + 13 + 20,
                'y' => $thumbHeight - 16,
                'text' => $videoDislikes,
                'color' => '#f53794',
                'font' => 'SourceCodePro-Regular',
                'size' => 10
            ));
            // Video rating
            foreach (uniqueX\General::printStars(
                $thumbWidth - 102,
                $thumbHeight - 21,
                $videoDetails['stats']['rating']
            ) as $star) {
                $img->message($star);
            }
            // Display final thumbnail
            $img->display();
        } else {
            $thumbFailed = true;
        }
    } else {
        $thumbFailed = true;
    }
} else {
    $thumbFailed = true;
}

// Check runtime error
if ($thumbFailed) {
    if (is_array($videos)) {
        header('HTTP/1.1 301 Moved Permanently');
        header("Location: {$videos[0]['thumbnail']}");
    } else {
        header('HTTP/1.0 404 Not Found');
        echo 'File not found';
    }
}
