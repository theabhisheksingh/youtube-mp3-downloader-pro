(function ($) {
    /* jshint strict: false */
    /* global console, alert */
    $(function () {
        $(document)
            .on('click', '#ajaxResult .fa-close', function () {
                $('#ajaxResult').removeClass('loading success failed');
            })
            .on('click', '.del-dmca', function () {
                var dmcaRecord = $(this).closest('[data-dmca]').attr('data-dmca');
                if (typeof dmcaRecord === 'string') {
                    $.ajax({
                        url: '$ajax-dmca',
                        type: 'POST',
                        dataType: 'json',
                        data: {action: 'delDMCA', record: dmcaRecord}
                    });
                    $(this).closest('[data-dmca]').remove();
                }
            })
            .on('click', '#addDMCA', function () {
                var dmcaValue = $('#dmcaValue').val().trim(),
                    dmcaNote = $('#dmcaNote').val().trim();
                // Start process
                if (dmcaValue.length >= 1) {
                    var ajaxRes = $('#ajaxResult');
                    ajaxRes.removeClass('success failed').addClass('loading');
                    // Start process
                    $.ajax({
                        url: '$ajax-dmca',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'addDMCA',
                            value: dmcaValue,
                            type: $('input[name=decordType]:checked').val(),
                            note: dmcaNote
                        }
                    }).always(function (response, status) {
                        ajaxRes.removeClass('loading').addClass('failed');
                        if (typeof response === 'object' && response !== null && status === 'success') {
                            if (response.status) {
                                ajaxRes.removeClass('failed').addClass('success');
                                $('#dmcaList').prepend($('<tr>').attr('data-dmca', response.record.value)
                                        .append($('<td>').text(response.record.type))
                                        .append($('<td>').text(response.record.value))
                                        .append($('<td>').text(response.record.time))
                                        .append($('<td>').html($('<div>').addClass('rNote').text(response.record.note)))
                                        .append($('<td>').html($('<ul>').html($('<li>')
                                            .html($('<span>').addClass('fa fa-close del-dmca')))))
                                );
                            } else {
                                ajaxRes.find('.noTXT').text(response.error);
                            }
                        } else {
                            ajaxRes.find('.noTXT').text('Invalid data recieved from server');
                        }
                    });
                } else {
                    alert('Enter valid video/channel link');
                }
            });
    });
})(window.jQuery);
