function VikCalNextMonth() {
    var month = jQuery("#VCMonth").val();
    var year = jQuery("#VCYear").val();

    jQuery.post(
        ajaxurl,
        {
            'action': 'VikCalChangeMonth',
            'month':   month,
            'year': year,
            'changeMonth': 'next',
        },
        function(response){
            jQuery("#VikCal").html(response);
        }
    );
}

function VikCalPreviousMonth() {
    var month = jQuery("#VCMonth").val();
    var year = jQuery("#VCYear").val();

    jQuery.post(
        ajaxurl,
        {
            'action': 'VikCalChangeMonth',
            'month':   month,
            'year': year,
            'changeMonth': 'prev',
        },
        function(response){
            jQuery("#VikCal").html(response);
        }
    );
}