$(function () {

    let oldPosition = 0;
    let newPosition = 0;
    let table = $('table.status');

    table.sortable({
        axis: 'y',
        cursor: 'move',
        items: 'tr.status-sort',
        placeholder: 'status-placeholder',
        start: function(event, ui) {
            let elements = table.sortable('toArray');
            oldPosition = elements.indexOf(ui.item.attr('id'));
        },
        stop: function(event, ui) {
            let elements = table.sortable('toArray');
            newPosition = elements.indexOf(ui.item.attr('id'));


            $.ajax({
                type: 'POST',
                url: table.data('update-position-url'),
                dataType: 'json',
                data: {
                    status: ui.item.data('status-id'),
                    oldpos: oldPosition,
                    newpos: newPosition
                },
                success: function(data, status, xhr) {
                    if (data != null) {
                        ui.item.children('td').first().html(newPosition + '.');
                        ui.item.addClass('sort-success');

                        for (let i = 0 ; i < data.length ; i++) {
                            $('#status-' + data[i].status_id).children('td').first()
                                .html((parseInt(data[i].position) + 1) + '.');
                        }

                        setTimeout(function() {
                            ui.item.removeClass('sort-success');
                        }, 2000);
                    } else {
                        table.sortable('cancel');
                        ui.item.addClass('sort-failed');

                        setTimeout(function() {
                            ui.item.removeClass('sort-failed');
                        }, 2000);
                    }
                },
                error: function(xhr, status, message) {
                    table.sortable('cancel');
                    ui.item.addClass('sort-failed');
                    alert(message);
                    setTimeout(function() {
                        ui.item.removeClass('sort-failed');
                    }, 2000);
                }
            });
        }
    });

});
