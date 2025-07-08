jQuery(function($){
    var $input = $('#sc-city-search'),
        $tbody = $('#sc-cities-table tbody'),
        timer;

    $input.on('input', function(){
        clearTimeout(timer);
        timer = setTimeout(function(){
            var term = $input.val();
            $.post(
                scCitiesAjax.ajax_url,
                {
                    action: 'sc_search_cities',
                    nonce:  scCitiesAjax.nonce,
                    term:   term
                },
                function(res){
                    if (res.success) {
                        $tbody.empty();
                        if (res.data.length) {
                            res.data.forEach(function(row){
                                $tbody.append(
                                    '<tr>'
                                    + '<td style="border:1px solid #ddd;padding:8px;">'+ row.country +'</td>'
                                    + '<td style="border:1px solid #ddd;padding:8px;">'+ row.city +'</td>'
                                    + '<td style="border:1px solid #ddd;padding:8px;">'+ row.temperature +'</td>'
                                    +'</tr>'
                                );
                            });
                        } else {
                            $tbody.append('<tr><td colspan="3" style="padding:8px;">Ничего не найдено.</td></tr>');
                        }
                    }
                }
            );
        }, 300);
    });
});
