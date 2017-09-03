<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Get ad codes (init)
$adCodes = array();

// Process ad codes
foreach (array('mass-advt-mp3above', 'mass-advt-mp3below', 'mass-advt-mp4above', 'mass-advt-mp4below') as $ad) {
    $adCodes[$ad] = isset($config[$ad]) && is_string($config[$ad]) && strlen(trim($config[$ad])) > 0
        ? str_replace(array('+', '/', '='), array('.', '-', '_'), base64_encode($config[$ad])) : null;
}

// Javascript mime type
header('Content-Type: application/javascript; charset=UTF-8');

// Print javascript code
echo <<<JS
window.advt = {
    mp3Above: '{$adCodes['mass-advt-mp3above']}',
    mp3Below: '{$adCodes['mass-advt-mp3below']}',
    mp4Above: '{$adCodes['mass-advt-mp4above']}',
    mp4Below: '{$adCodes['mass-advt-mp4below']}'
};
(function($){
    $(function() {
        $(document)
            .on('click', '.v-dl-mp3', function() {
                var vdl = $(this).closest('[data-vid]').find('.v-music'), mediaItems =  vdl.find('.media-items');
                if(window.advt.mp3Above.length > 0 && vdl.find('.dlad-mp3-above').length == 0) {
                    mediaItems.before($('<div>').addClass('dlad dlad-above dlad-mp3-above').html($('<iframe>')
                    .attr('src', window.siteConf.siteLink + 'adframe?adCode=' + window.advt.mp3Above)));
                }
                if(window.advt.mp3Below.length > 0 && vdl.find('.dlad-mp3-below').length == 0) {
                    mediaItems.after($('<div>').addClass('dlad dlad-below dlad-mp3-below').html($('<iframe>')
                    .attr('src', window.siteConf.siteLink + 'adframe?adCode=' + window.advt.mp3Below)));
                }
            })
            .on('click', '.v-dl-more', function() {
                var vdl = $(this).closest('[data-vid]').find('.v-download'), mediaItems =  vdl.find('.media-items');
                if(window.advt.mp4Above.length > 0 && vdl.find('.dlad-mp4-above').length == 0) {
                    mediaItems.before($('<div>').addClass('dlad dlad-above dlad-mp4-above').html($('<iframe>')
                    .attr('src', window.siteConf.siteLink + 'adframe?adCode=' + window.advt.mp4Above)));
                }
                if(window.advt.mp4Below.length > 0 && vdl.find('.dlad-mp4-below').length == 0) {
                    mediaItems.after($('<div>').addClass('dlad dlad-below dlad-mp4-below').html($('<iframe>')
                    .attr('src', window.siteConf.siteLink + 'adframe?adCode=' + window.advt.mp4Below)));
                }
            });
    });
})(window.jQuery);
JS;
