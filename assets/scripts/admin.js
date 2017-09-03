(function ($) {
    /* jshint strict: false */
    /* global confirm, Cookies, alert */
    $(function () {
        var ytgRes = $('#ytgRes');
        // Get engines download progress
        function engineDowlnloadProgress(pointer, engineName, callback) {
            if (typeof pointer === 'string' &&
                typeof engineName === 'string' &&
                typeof callback === 'function' &&
                pointer in window && typeof window[pointer] === 'boolean' && window[pointer]
            ) {
                $.ajax({url: './store/' + engineName + '.txt'}).always(function (response, status) {
                    if (typeof response === 'string' && status === 'success') {
                        var progress = parseInt(response, 10);
                        progress = progress >= 0 && progress <= 100 ? progress : 0;
                        callback(progress);
                        setTimeout(function () {
                            engineDowlnloadProgress(pointer, engineName, callback);
                        }, 500);
                    }
                });
            }
        }

        // PHP runner
        $('[name="phpResult"]').on('load', function () {
            $(this).css('height', (this.contentWindow.document.body.scrollHeight + 30) + 'px');
        });

        // Start process on demand
        $(document)
            .on('click', '#debugMode', function () {
                window.location.href = '?debug=toggle&token=' + $('[data-token]').attr('data-token');
            })
            .on('click', 'li[data-clid]', function () {
                var cLayers = $(this).closest('.cLayers'), clid = $(this).attr('data-clid');
                cLayers.find('[data-clid]').removeClass('active');
                cLayers.find('[data-clid="' + clid + '"]').addClass('active');
            })
            .on('click', '.abHead .fa', function () {
                var abox = $(this).closest('.abox');
                if (abox.hasClass('show-help')) {
                    abox.removeClass('show-help');
                } else {
                    abox.addClass('show-help');
                }
            })
            .on('click', '#ytCheck', function () {
                var videoID = $('#ytInput').val().trim(), ytReport = ytgRes.find('#ytReport');
                ytgRes.addClass('show-me');
                if (videoID.length === 11) {
                    ytgRes.addClass('loading').removeClass('report');
                    $.ajax({
                        url: '$ajax-admin',
                        type: 'POST',
                        data: {purpose: 'troubleshoot', action: 'ytgrab', video: videoID},
                        dataType: 'json'
                    }).always(function (response, status) {
                        ytgRes.find('#ytVideo').html(videoID);
                        ytgRes.removeClass('loading').addClass('report');
                        if (status === 'success' && typeof response === 'object' && response !== null) {
                            if (response.status) {
                                ytReport.text('Congratulations, Your server can grab this YouTube video links perfectly.');
                            } else {
                                ytReport.text('Sorry, ' + response.error);
                            }
                        } else {
                            ytReport.text('Failed to connect server');
                        }
                    });
                } else {
                    ytgRes.removeClass('loading').addClass('report');
                    ytReport.text('Invalid YouTube video ID');
                }
            })
            .on('click', '[data-act]', function () {
                var act = $(this).attr('data-act'),
                    actX = act.split(':'),
                    actE = $('[data-engine="' + actX[0] + '"]'),
                    actY = actX[0] + '-' + window.serverInfo.name + '-' + window.serverInfo.type + 'bit';
                actY += window.serverInfo.name === 'win' ? '.exe' : '';
                if (actE.hasClass('display')) {
                    return false;
                }
                if (actX[1].indexOf('install') > -1) {
                    window[actY] = true;
                    actE.addClass('progress');
                    engineDowlnloadProgress(actY, actY, function (progress) {
                        if (progress > 0 && actE.hasClass('progress')) {
                            actE.find('.ersText').text('Installing ' + actX[0] + ' engine: ' + progress + '%');
                            actE.find('.erProgress').css('display', 'block');
                            actE.find('.erpPos').css('width', progress + '%');
                        }
                    });
                }
                actE.removeClass('success failed').addClass('display');
                actE.find('.ersText').text('Processing, please wait...');
                // Connect server
                $.ajax({
                    url: '$ajax-admin',
                    type: 'POST',
                    data: {
                        purpose: 'troubleshoot',
                        action: 'engine',
                        process: act,
                        osName: window.serverInfo.name,
                        osType: window.serverInfo.type
                    },
                    dataType: 'json'
                }).always(function (response, status) {
                    delete window[actY];
                    actE.removeClass('progress').addClass('failed');
                    actE.find('.erProgress').css('display', 'none');
                    if (status === 'success' && typeof response === 'object' && response !== null) {
                        if (response.status) {
                            actE.removeClass('failed').addClass('success');
                            actE.find('.ersText').text(response.message);
                        } else {
                            actE.find('.ersText').text(response.error);
                        }
                    } else {
                        actE.find('.ersText').text('Failed to communicate with server');
                    }
                });
            })
            .on('click', '#logOut', function () {
                if (confirm('Are you sure to logout?')) {
                    window.location.href = $(this).attr('data-logout');
                }
            })
            .on('click', '[data-admin-go]', function () {
                window.location.href = $(this).attr('data-admin-go');
            })
            .on('click', '#updateConf', function () {
                // Prepare dynamic form
                var form = $('<form>');
                form.attr({action: '$admin', method: 'post'});
                // Append data
                $('[data-conf]').each(function () {
                    form.append($('<input>').attr({
                        type: 'hidden',
                        name: $(this).attr('data-conf'),
                        value: $(this).val()
                    }));
                });
                // Submit form
                $('body').append(form);
                form.submit();
            })
            .on('click', '#clearCache', function () {
                Cookies.remove('templateID');
                Cookies.remove('templateFlavor');
                alert('Template preview cache successfully cleared');
            })
            .on('click', '[data-template-act]', function () {
                // Get template data
                var template = $(this).closest('[data-template-id]'),
                    templateID = template.attr('data-template-id'),
                    templateFlavor = template.find('.tFlavors .active').attr('data-template-flavor');
                // Optimize template data
                templateFlavor = typeof templateFlavor === 'string' ? templateFlavor : 'default';
                // Start process
                switch ($(this).attr('data-template-act')) {
                    case 'active':
                        window.location.href =
                            '?template=' + templateID +
                            '&flavor=' + templateFlavor +
                            '&token=' + $('html').attr('data-token');
                        break;
                    case 'preview':
                        var purchaseView = template.attr('data-template-purchase-view');
                        if (typeof purchaseView === 'string' && purchaseView.length > 0) {
                            window.open(purchaseView.replace('{{template}}', templateID).replace('{{flavor}}', templateFlavor));
                        } else {
                            Cookies.set('templateID', templateID);
                            Cookies.set('templateFlavor', templateFlavor);
                            window.open('.');
                        }
                        break;
                    case 'buy-now':
                        window.open(template.attr('data-template-purchase-link'));
                        break;
                }
            })
            .on('click', '[data-template-flavor]', function () {
                $(this).parent().find('[data-template-flavor]').removeClass('active');
                $(this).addClass('active');
            });
        // Alert on exit
        window.onbeforeunload = function () {
            if ($('.engine-report').hasClass('progress')) {
                return "Engines are installing please wait some time.";
            }
        };
    });
})(window.jQuery);
