(function ($) {
    /* jshint strict: false */
    /* global Cookies, addthis, YT */
    $(function () {
        if (typeof window._lib !== 'object') {
            window._lib = {};
        }
        var library = window._lib;
        library.music = {
            elm: $('#musicPlayer'),
            map: undefined,
            ready: function () {
                return typeof library.music.map !== 'undefined';
            },
            open: function (videoID) {
                $('html').addClass('music-goto');
                library.music.elm.addClass('musicShow');
                // reset timer
                $('#musicTimer').text('00:00/00:00');
                // Reset music progress
                var mProgress = $('#musicProgress');
                mProgress.find('.mpBuffer').addClass('show-me');
                mProgress.find('.mpLoad').css('width', 0);
                mProgress.find('.mpPlay').css('width', 0);

                library.music.elm.find('.musicPlay .fa').removeClass('fa-repeat fa-pause').addClass('fa-play');

                library.music.map = new YT.Player('audioPlayer', {
                    height: '100',
                    width: '90',
                    videoId: videoID,
                    suggestedQuality: 'small',
                    events: {
                        onReady: library.music.events.ready,
                        onError: library.music.events.error,
                        onStateChange: library.music.events.stateChange
                    }
                });

                library.music.track = setInterval(function () {
                    if (typeof library.music.map === 'object' && 'getDuration' in library.music.map) {
                        library.music.events.progress({
                            current: library.music.map.getCurrentTime(),
                            total: library.music.map.getDuration(),
                            buffer: parseInt(library.music.map.getVideoLoadedFraction() * 100, 10)
                        });
                    }
                }, 250);

            },
            close: function () {
                clearInterval(library.music.track);
                if (library.music.ready()) {
                    $('html').removeClass('music-goto');
                    library.music.map.destroy();
                    library.music.elm.removeClass('musicShow');
                    library.music.map = undefined;
                }
            },
            prev: function () {
                // Close current video
                var current = $('.video-mplay.playing-music');
                if (current.length !== 0) {
                    current.click();
                } else {
                    current = $('#videos').find('.videos').first().find('.video-mplay');
                }
                // Detect previous video
                var finalVideo, currentVideo = current.closest('[data-vid]'), previousVideo = currentVideo.prev();
                if (previousVideo.length !== 0) {
                    finalVideo = previousVideo;
                } else {
                    finalVideo = currentVideo.parent().find('[data-vid]').last();
                }
                // Play previous video
                finalVideo.find('.video-mplay').click();
                $('html, body').animate({scrollTop: finalVideo.offset().top - 5}, 300);
            },
            next: function () {
                // Close current video
                var current = $('.video-mplay.playing-music');
                if (current.length !== 0) {
                    current.click();
                } else {
                    current = $('#videos').find('.videos').first().find('.video-mplay');
                }
                // Detect next video
                var finalVideo, currentVideo = current.closest('[data-vid]'), nextVideo = currentVideo.next();
                if (nextVideo.length !== 0) {
                    finalVideo = nextVideo;
                } else {
                    finalVideo = currentVideo.parent().find('[data-vid]').first();
                }
                // Play next video
                finalVideo.find('.video-mplay').click();
                $('html, body').animate({scrollTop: finalVideo.offset().top - 5}, 300);
            },
            events: {
                ready: function (e) {
                    library.music.elm.removeClass('musicError');
                    if (e.target.isMuted()) {
                        e.target.unMute();
                    }
                    e.target.setVolume(parseInt(localStorage.getItem('musicVolume'), 10));
                    e.target.setPlaybackRate(parseFloat(localStorage.getItem('musicSpeed')));
                    library.music.elm.find('.musicPlay .fa').removeClass('fa-repeat fa-pause').addClass('fa-play');
                    e.target.seekTo(0);
                    e.target.playVideo();
                },
                error: function () {
                    library.music.elm.addClass('musicError');
                },
                stateChange: function (e) {
                    if (e.data === -1 || e.data === 3) {
                        library.music.elm.find('.mpBuffer').addClass('show-me');
                    } else {
                        library.music.elm.find('.mpBuffer').removeClass('show-me');
                    }
                    switch (e.data) {
                        case 0:
                            library.music.elm.find('.musicPlay .fa').removeClass('fa-play fa-pause').addClass('fa-repeat');
                            switch (localStorage.getItem('musicRepeat')) {
                                case 'one':
                                    library.music.map.playVideo();
                                    break;
                                case 'all':
                                    library.music.next();
                                    break;
                            }
                            break;
                        case 1:
                            library.music.elm.find('.musicPlay .fa').removeClass('fa-play fa-repeat').addClass('fa-pause');
                            break;
                        case 2:
                            library.music.elm.find('.musicPlay .fa').removeClass('fa-repeat fa-pause').addClass('fa-play');
                            break;
                    }
                },
                progress: function (report) {
                    var mProgress = $('#musicProgress');
                    // Update buffer
                    mProgress.find('.mpLoad').css('width', report.buffer + '%');
                    // Update progress bar
                    var progress = report.current / (report.total / 100);
                    progress = progress > 100 ? 100 : (progress < 0 ? 0 : progress);
                    mProgress.find('.mpPlay').css('width', progress + '%');
                    // Update timestamps
                    $('#musicTimer').text(library.HumanTime(report.current) + '/' + library.HumanTime(report.total));
                }
            }
        };
        // Handle click events
        $(document)
            .on('click', '.musicPrev', function () {
                library.music.prev();
            })
            .on('click', '.musicNext', function () {
                library.music.next();
            })
            .on('click', '.musicClose', function () {
                $('.video-mplay.playing-music').removeClass('playing-music');
                library.music.close();
            })
            .on('click', '.video-mplay', function () {
                var thisPlay = $(this).hasClass('playing-music');
                $('.video-mplay.playing-music').removeClass('playing-music');
                library.music.close();
                // Get video element
                var videoElm = $(this).closest('[data-vid]');
                // Get initial details
                var videoID = videoElm.attr('data-vid'),
                    videoTitle = videoElm.attr('data-vtitle');
                // Update music panel
                library.music.elm.removeClass('musicError');
                $('#musicTitle').html(videoTitle);
                var musicSpeed = localStorage.getItem('musicSpeed'),
                    musicRepeat = localStorage.getItem('musicRepeat'),
                    musicVolume = localStorage.getItem('musicVolume');
                // Optimize values
                musicSpeed = ['0.25', '0.5', '1.0', '1.5', '2.0'].indexOf(musicSpeed) > -1 ? musicSpeed : '1.0';
                musicRepeat = ['one', 'all', 'off'].indexOf(musicRepeat) > -1 ? musicRepeat : 'all';
                musicVolume = typeof musicVolume === 'string' ? parseInt(musicVolume, 10) : 100;
                musicVolume = musicVolume >= 1 && musicVolume <= 100 ? musicVolume : 100;
                // Update screen
                $('#musicVolumeSeek').find('.musicVolumePos').css('width', musicVolume + '%');
                $('#musicSpeedOptions').find('[data-music-speed="' + musicSpeed + '"]').click();
                $('#musicRepeatOptions').find('[data-music-repeat="' + musicRepeat + '"]').click();
                // Update options cache
                localStorage.setItem('musicSpeed', musicSpeed);
                localStorage.setItem('musicRepeat', musicRepeat);
                localStorage.setItem('musicVolume', musicVolume);
                // Play music
                if (!thisPlay) {
                    $(this).addClass('playing-music');
                    library.music.open(videoID);
                }
            })
            .on('click', '.musicPlay', function () {
                var span = $(this).find('span.fa');
                if (span.hasClass('fa-pause')) {
                    library.music.map.pauseVideo();
                } else {
                    library.music.map.playVideo();
                }
            })
            .on('click', '#musicRepeatOptions li', function () {
                $('#musicRepeatOptions').find('li').removeClass('active');
                $(this).addClass('active');
                // Store value
                localStorage.setItem('musicRepeat', $(this).attr('data-music-repeat'));
            })
            .on('click', '#musicSpeedOptions li', function () {
                $('#musicSpeedOptions').find('li').removeClass('active');
                $(this).addClass('active');
                // Set music speed
                if (typeof library.music.map === 'object' && 'setPlaybackRate' in library.music.map) {
                    library.music.map.setPlaybackRate(parseFloat($(this).attr('data-music-speed')));
                }
                // Store value
                localStorage.setItem('musicSpeed', $(this).attr('data-music-speed'));
            })
            .on('click', '#musicProgress', function (e) {
                var mousePos = e.clientX - $(this).offset().left,
                    totalPos = $(this).width();
                // Seek audio
                if (typeof library.music.map === 'object' && 'seekTo' in library.music.map) {
                    library.music.map.seekTo((mousePos / (totalPos / 100)) * (library.music.map.getDuration() / 100));
                }
            })
            .on('click', '#musicVolumeSeek', function (e) {
                var mousePos = e.clientX - $(this).offset().left,
                    totalPos = $(this).width(),
                    _percent = parseInt(mousePos / (totalPos / 100), 10);
                // Seek volume
                if (typeof library.music.map === 'object' && 'setVolume' in library.music.map) {
                    library.music.map.setVolume(_percent);
                }
                $(this).find('.musicVolumePos').css('width', _percent + '%');
                // Store value
                localStorage.setItem('musicVolume', _percent);
            });
    });
})(window.jQuery);
