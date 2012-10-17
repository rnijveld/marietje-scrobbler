(function ($, window, document) {
    $(document).ready(function () {
        var interval = 10000;
        var scrobbles = $('#scrobbles');
        var updateUrl = scrobbles.data('url');

        var noord = $('#np-noord');
        var zuid = $('#np-zuid');

        var remover = $('#remove_scrobble');
        var ignorer = $('#track_ignore');

        var lastScrobble = 0;

        var doRemove = function (scrobble, tr) {
            $.post(remover.data('url'), {
                'artist': scrobble.artist,
                'track': scrobble.title,
                'timestamp': scrobble.start
            }, function () {
                tr.fadeOut('fast', function () {
                    tr.remove();
                });
            });
        };

        var update = function () {

            $.getJSON(updateUrl, {since: lastScrobble}, function (data) {
                if (!data.zk) {
                    zuid.find('.artist').text('');
                    zuid.find('.title').text('');
                    zuid.find('img').attr('src', '/img/stopped.jpg');
                } else {
                    zuid.find('.artist').text(data.zk.artist);
                    zuid.find('.title').text(data.zk.title);
                    zuid.find('img').attr('src', data.zk.image ? data.zk.image : '/img/unknown.jpg');
                }

                if (!data.nk) {
                    noord.find('.artist').text('');
                    noord.find('.title').text('');
                    noord.find('img').attr('src', '/img/stopped.jpg');
                } else {
                    noord.find('.artist').text(data.nk.artist);
                    noord.find('.title').text(data.nk.title);
                    noord.find('img').attr('src', data.nk.image ? data.nk.image : '/img/unknown.jpg');
                }

                $.each(data.scrobbles, function (k, scrobble) {
                    lastScrobble = scrobble.sent;
                    var dt = new Date(scrobble.sent * 1000);
                    var time = '' + (dt.getHours() < 10 ? '0' + dt.getHours() : dt.getHours()) + ':' +
                        (dt.getMinutes() < 10 ? '0' + dt.getMinutes() : dt.getMinutes());
                    var tr = $('<tr/>', {
                            'data-timestamp': scrobble.start
                        })
                        .append($('<td/>').text(scrobble.artist))
                        .append($('<td/>').text(scrobble.title))
                        .append($('<td/>').text(time))
                        .append($('<td/>').append(
                            $('<a/>', {
                                'class': 'btn btn-warning btn-small',
                                'title': 'Remove scrobble',
                                'href': '#remove_scrobble',
                                'data-toggle': 'modal'
                            }).append($('<span/>').addClass('icon-trash icon-white')).click(function () {
                                var click = function () {
                                    doRemove(scrobble, tr);
                                    remover.modal('hide');
                                };

                                var hide = function () {
                                    remover.off('hide', hide);
                                    $('#remove_remove').off('click', click);
                                };
                                $('#remove_remove').on('click', click);
                                remover.on('hide', hide);
                            })
                        ).append(document.createTextNode(' ')).append(
                            $('<a/>', {
                                'class': 'btn btn-danger btn-small',
                                'title': 'Ignore track',
                                'href': '#track_ignore',
                                'data-toggle': 'modal'
                            }).append($('<span/>').addClass('icon-remove icon-white')).click(function () {
                                var clickArtist = function () {
                                    $.post(ignorer.data('url'), {
                                        'artist': scrobble.artist
                                    });
                                    doRemove(scrobble, tr);
                                    ignorer.modal('hide');
                                };

                                var clickTrack = function () {
                                    $.post(ignorer.data('url'), {
                                        'artist': scrobble.artist,
                                        'track': scrobble.title
                                    });
                                    doRemove(scrobble, tr);
                                    ignorer.modal('hide');
                                };

                                var hide = function () {
                                    ignorer.off('hide', hide);
                                    $('#ignore_artist').off('click', clickArtist);
                                    $('#ignore_track').off('click', clickTrack);
                                };

                                $('#ignore_artist').on('click', clickArtist);
                                $('#ignore_track').on('click', clickTrack);
                                ignorer.on('hide', hide);
                            })
                        )
                    );
                    scrobbles.find('tbody').prepend(tr);
                });
            });
        };
        setInterval(update, interval);
        update();
    });
}(jQuery, window, document));
