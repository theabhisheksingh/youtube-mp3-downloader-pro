<?php

namespace uniqueX;

class Image
{
    private $sandbox = array();

    public function __construct($width, $height)
    {
        // Checking for GD library and FreeType library support
        $this->sandbox['support'] = (extension_loaded('gd') &&
            function_exists('gd_info') &&
            function_exists('imagettftext'));

        // Just define layers as array
        $this->sandbox['layers'] = array();

        // Adding names for some of most popular hex codes
        $this->sandbox['colors'] = array();
        if (is_file(__DIR__ . '/resources/colors.txt')) {
            $colors = file_get_contents(__DIR__ . '/resources/colors.txt');
            if (is_string($colors)) {
                foreach (explode(';', $colors) as $color) {
                    list($name, $value) = explode(':', $color);
                    $this->sandbox['colors'][$name] = $value;
                }
            }
        }

        // Set image dimensions
        $width = intval($width);
        $height = intval($height);
        if ($width >= 1 && $height >= 1) {
            $this->sandbox['dimensions'] = array(
                'width' => $width,
                'height' => $height
            );
        }
    }

    private function hex2rgb($hex_color_code)
    {
        $output = false;
        if (is_string($hex_color_code)) {
            // Checking for color name
            if (isset($this->sandbox['colors'][strtolower($hex_color_code)])) {
                $hex_color_code = $this->sandbox['colors'][strtolower($hex_color_code)];
            }
            // Remove # from color code
            $hex = is_string($hex_color_code) ? str_replace('#', null, $hex_color_code) : '';
            // Validate hex color code
            if (in_array(strlen($hex), array(3, 6)) && preg_match('/^[0-9a-f]+$/i', $hex)) {
                // Convert 3 letters hex code to 6 letters hex code
                if (strlen($hex) == 3) {
                    $hex = str_split($hex);
                    $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
                }
                // Hex color code RGB value
                $hex = str_split($hex, 2);
                $output = array(hexdec($hex[0]), hexdec($hex[1]), hexdec($hex[2]));
            }
        }

        return $output;
    }

    private function calculate($equation, $width = 0, $height = 0)
    {
        $output = 0;
        $equation = strtolower((string)$equation);
        $validate = '/^[0-9|\%|\^|\*|\(|\)|\-|\+|\.|\/|w|h|\s]+$/i';
        if (strlen($equation) >= 1 && preg_match($validate, $equation)) {
            $width = intval($width);
            $height = intval($height);
            $output = 0;
            $equation = str_replace(array('w', 'h'), array($width, $height), $equation);
            try {
                eval("\$output = {$equation};");
            } catch (\Exception $e) {
                // Nothing to do...
            }
            $output = intval($output);
        }

        return $output;
    }

    private function text_size($text, $font, $size, $angle)
    {
        $output = false;
        $poly = imagettfbbox($size, $angle, $font, $text);
        if ($poly !== false) {
            $minX = min(array($poly[0], $poly[2], $poly[4], $poly[6]));
            $maxX = max(array($poly[0], $poly[2], $poly[4], $poly[6]));
            $minY = min(array($poly[1], $poly[3], $poly[5], $poly[7]));
            $maxY = max(array($poly[1], $poly[3], $poly[5], $poly[7]));

            $output = array(
                'left' => abs($minX) - 1,
                'top' => abs($minY) - 1,
                'width' => $maxX - $minX,
                'height' => $maxY - $minY,
                'coordinates' => $poly
            );
        }

        return $output;
    }

    private function imageOpacity(&$image, $opacity)
    {
        if (!isset($opacity)) {
            return false;
        }
        $opacity /= 100;

        //get image width and height
        $w = imagesx($image);
        $h = imagesy($image);

        //turn alpha blending off
        imagealphablending($image, false);

        //find the most opaque pixel in the image (the one with the smallest alpha value)
        $minalpha = 127;
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $alpha = (imagecolorat($image, $x, $y) >> 24) & 0xFF;
                if ($alpha < $minalpha) {
                    $minalpha = $alpha;
                }
            }
        }

        //loop through image pixels and modify alpha for each
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                //get current alpha value (represents the TANSPARENCY!)
                $colorxy = imagecolorat($image, $x, $y);
                $alpha = ($colorxy >> 24) & 0xFF;
                //calculate new alpha
                if ($minalpha !== 127) {
                    $alpha = 127 + 127 * $opacity * ($alpha - 127) / (127 - $minalpha);
                } else {
                    $alpha += 127 * $opacity;
                }
                //get the color index with new alpha
                $alphacolorxy = imagecolorallocatealpha(
                    $image,
                    ($colorxy >> 16) & 0xFF,
                    ($colorxy >> 8) & 0xFF,
                    $colorxy & 0xFF,
                    $alpha
                );
                //set pixel with the new color + opacity
                if (!imagesetpixel($image, $x, $y, $alphacolorxy)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function box($config)
    {
        $output = false;
        // Validate input params
        if (is_array($config) && count($config) >= 1) {
            // Perfect configuration
            $conf = array();
            // Supported names
            $names = array('x', 'y', 'width', 'height', 'background', 'opacity', 'flip');
            // Auto currect errors in configuration
            foreach ($config as $name => $value) {
                if (is_string($name)) {
                    $name = str_replace(array(' ', '-', '_'), null, strtolower($name));
                    if (in_array($name, $names)) {
                        $conf[$name] = $value;
                    }
                }
            }

            // Check X coordinate
            $box_x = isset($conf['x']) ? intval($conf['x']) : 0;

            // Check Y coordinate
            $box_y = isset($conf['y']) ? intval($conf['y']) : 0;

            // Check width
            $box_w = isset($conf['width']) ? intval($conf['width']) : 0;

            // Check height
            $box_h = isset($conf['height']) ? intval($conf['height']) : 0;

            // Validate opacity
            $box_o = isset($conf['opacity']) ? intval($conf['opacity']) : 0;
            $box_o = $box_o >= 0 && $box_o <= 100 ? $box_o : 0;
            $box_o = ceil($box_o * 1.27);

            // Validate flip
            $box_f = array('vertical' => false, 'horizontal' => false);
            if (isset($conf['flip'])) {
                if (stripos($conf['flip'], 'v') !== false) {
                    $box_f['vertical'] = true;
                }
                if (stripos($conf['flip'], 'h') !== false) {
                    $box_f['horizontal'] = true;
                }
            }

            // Validate background
            $background = false;
            if (isset($conf['background'])) {
                // Check for color
                $color = $this->hex2rgb($conf['background']);
                if (is_array($color)) {
                    $background = $color;
                } elseif (is_string($conf['background']) && is_file($conf['background'])) {
                    $image = imagecreatefromstring(file_get_contents($conf['background']));
                    if ($image !== false) {
                        $background = $image;
                    }
                } elseif (is_resource($conf['background']) && get_resource_type($conf['background']) == 'gd') {
                    $background = $conf['background'];
                }
            }

            if ($box_w != 0 && $box_h != 0 && $background !== false) {
                $output = array(
                    'x' => $box_x,
                    'y' => $box_y,
                    'width' => $box_w,
                    'height' => $box_h,
                    'background' => $background,
                    'opacity' => $box_o,
                    'flip' => $box_f
                );
                $this->sandbox['layers'][] = array(
                    'type' => 'box',
                    'data' => $output
                );
            }
        }

        return $output;
    }

    public function message($config, $getArea = false)
    {
        $output = false;
        // Validate input params
        if (is_array($config) && count($config) >= 1) {
            // Perfect configuration
            $conf = array();
            // Supported names
            $names = array('x', 'y', 'text', 'size', 'color', 'font', 'opacity', 'angle', 'background');
            // Auto currect errors in configuration
            foreach ($config as $name => $value) {
                if (is_string($name)) {
                    $name = str_replace(array(' ', '-', '_'), null, strtolower($name));
                    if (in_array($name, $names)) {
                        $conf[$name] = $value;
                    }
                }
            }

            // Check X,Y coordinates
            $txt_x = isset($conf['x']) ? (string)$conf['x'] : '0';
            $txt_y = isset($conf['y']) ? (string)$conf['y'] : '0';

            // Text message
            $message = null;
            if (isset($conf['text']) && is_string($conf['text']) && strlen(trim($conf['text'])) >= 1) {
                $message = $conf['text'];
            }

            // Text size
            $txt_s = isset($conf['size']) ? intval($conf['size']) : 13;

            // Text color
            $txt_c = $this->hex2rgb(isset($conf['color']) ? $conf['color'] : 'black');

            // Text font
            $txt_f = null;
            if (isset($conf['font']) && is_string($conf['font'])) {
                if (is_file($conf['font'])) {
                    $txt_f = $conf['font'];
                } elseif (is_file(__DIR__ . "/resources/{$conf['font']}.ttf")) {
                    $txt_f = __DIR__ . "/resources/{$conf['font']}.ttf";
                }
            }

            // Set SourceCodePro regular font as default if input font not found
            if ($txt_f == null && is_file(__DIR__ . "/resources/SourceCodePro-Regular.ttf")) {
                $txt_f = __DIR__ . "/resources/SourceCodePro-Regular.ttf";
            }

            // Text opacity
            $txt_o = isset($conf['opacity']) ? intval($conf['opacity']) : 0;
            $txt_o = $txt_o >= 0 && $txt_o <= 100 ? $txt_o : 0;
            $txt_o = ceil($txt_o * 1.27);

            // Text angle
            $txt_a = isset($conf['angle']) ? intval($conf['angle']) % 360 : 0;

            // Validate text background
            $bg_color = null;
            $bg_opacity = 0;
            if (isset($conf['background']) && is_string($conf['background'])) {
                $bg = explode(',', trim($conf['background']));
                // Checking for background color
                $bg_c = $this->hex2rgb($bg[0]);
                if ($bg_c !== false) {
                    $bg_color = $bg_c;
                }
                // Checking for background opacity
                if (count($bg) == 2 && intval($bg[1]) != 0) {
                    $bg_o = intval($bg[1]);
                    $bg_opacity = $bg_o >= 0 && $bg_o <= 100 ? $bg_o : 0;
                }
            }

            if ($message != null && $txt_s != 0 && $txt_c != false && $txt_f != null) {
                $output = array(
                    'x' => $txt_x,
                    'y' => $txt_y,
                    'text' => $message,
                    'size' => $txt_s,
                    'color' => $txt_c,
                    'font' => $txt_f,
                    'opacity' => $txt_o,
                    'angle' => $txt_a,
                    'background' => array(
                        'color' => $bg_color,
                        'opacity' => ceil($bg_opacity * 1.27)
                    )
                );
                if ($getArea) {
                    $output['area'] = $this->text_size($message, $txt_f, $txt_s, $txt_a);
                }
                $this->sandbox['layers'][] = array(
                    'type' => 'message',
                    'data' => $output
                );
            }
        }

        return $output;
    }

    public function display($format = 'png', $quality = 100, $return = false)
    {
        $output = false;
        // Auto correct output format
        $format = is_string($format) ? strtolower($format) : 'png';
        $format = in_array($format, array('png', 'jpg', 'gif')) ? $format : 'png';
        // Qulaity validation
        $quality = intval($quality);
        $quality = $quality >= 0 && $quality <= 100 ? $quality : 100;
        // Return value force conversion
        $return = !!($return);

        if ($this->sandbox['support'] && isset($this->sandbox['dimensions'])) {
            if (!$return) {
                $output = true;
                header("Content-type: image/{$format}");
            }
            // Create image canvas
            $id = $this->sandbox['dimensions'];
            $image = imagecreatetruecolor($id['width'], $id['height']);

            if ($image !== false) {
                // Convert image into transparent
                $transparent = imagecolorallocatealpha($image, 255, 255, 255, $format == 'jpg' ? 0 : 127);
                imagealphablending($image, false);
                imagesavealpha($image, true);
                // Don't draw transparent rectangle if output type is GIF
                if ($format != 'gif') {
                    imagefilledrectangle($image, 0, 0, $id['width'], $id['height'], $transparent);
                }
                imagealphablending($image, true);
                $black = imagecolorallocate($image, 0, 0, 0);
                imagecolortransparent($image, $black);

                // Add layers on image
                if (count($this->sandbox['layers']) >= 1) {
                    foreach ($this->sandbox['layers'] as $l) {
                        $d = $l['data'];
                        switch ($l['type']) {
                            case 'box':
                                if (is_array($d['background'])) {
                                    $color = imagecolorallocatealpha(
                                        $image,
                                        $d['background'][0],
                                        $d['background'][1],
                                        $d['background'][2],
                                        $d['opacity']
                                    );
                                    imagefilledrectangle(
                                        $image,
                                        $d['x'],
                                        $d['y'],
                                        $d['x'] + $d['width'],
                                        $d['y'] + $d['height'],
                                        $color
                                    );
                                } else {
                                    // Add opacity to image
                                    $d['opacity'] = 100 - ceil($d['opacity'] / 1.27);
                                    if ($d['opacity'] != 100) {
                                        $this->imageOpacity($d['background'], $d['opacity']);
                                    }
                                    // Flip Image
                                    if ($d['flip']['vertical'] && $d['flip']['horizontal']) {
                                        imageflip($d['background'], IMG_FLIP_BOTH);
                                    } elseif ($d['flip']['vertical']) {
                                        imageflip($d['background'], IMG_FLIP_VERTICAL);
                                    } elseif ($d['flip']['horizontal']) {
                                        imageflip($d['background'], IMG_FLIP_HORIZONTAL);
                                    }
                                    // Place image layer on main image
                                    imagecopyresampled(
                                        $image,
                                        $d['background'],
                                        $d['x'],
                                        $d['y'],
                                        0,
                                        0,
                                        $d['width'],
                                        $d['height'],
                                        imagesx($d['background']),
                                        imagesy($d['background'])
                                    );
                                    imagedestroy($d['background']);
                                }
                                break;
                            case 'message':
                                // Get width, height, edges of text on image
                                $area = $this->text_size($d['text'], $d['font'], $d['size'], $d['angle']);
                                // Check for unexpected errors
                                if ($area !== false) {
                                    $message_x = $area['left'] +
                                        $this->calculate($d['x'], $area['width'], $area['height']);
                                    $message_y = $area['top'] +
                                        $this->calculate($d['y'], $area['width'], $area['height']);
                                    // Check for text background
                                    $bg = $d['background'];
                                    if (is_array($bg['color'])) {
                                        $background = imagecolorallocatealpha(
                                            $image,
                                            $bg['color'][0],
                                            $bg['color'][1],
                                            $bg['color'][2],
                                            $bg['opacity']
                                        );

                                        // Area coordinates
                                        $ac = $area['coordinates'];
                                        $points = array(
                                            $message_x + $ac[6],
                                            $message_y + $ac[7],
                                            $message_x + $ac[0],
                                            $message_y + $ac[1],
                                            $message_x + $ac[2],
                                            $message_y + $ac[3],
                                            $message_x + $ac[4],
                                            $message_y + $ac[5],
                                        );

                                        imagefilledpolygon($image, $points, 4, $background);
                                    }
                                    // Write text on image
                                    $color = imagecolorallocatealpha(
                                        $image,
                                        $d['color'][0],
                                        $d['color'][1],
                                        $d['color'][2],
                                        $d['opacity']
                                    );

                                    imagettftext(
                                        $image,
                                        $d['size'],
                                        $d['angle'],
                                        $message_x,
                                        $message_y,
                                        $color,
                                        $d['font'],
                                        $d['text']
                                    );
                                }
                                break;
                        }
                    }
                }

                // Dump output image
                if (!$return) {
                    switch ($format) {
                        case "png":
                            imagepng($image);
                            break;
                        case "jpg":
                            imagejpeg($image, null, $quality);
                            break;
                        case "gif":
                            imagegif($image);
                            break;
                    }
                    imagedestroy($image);
                } else {
                    $output = $image;
                }
            }
        }

        return $output;
    }
}
