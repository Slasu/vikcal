jQuery(document).ready(function() {
    jQuery('.vikcal--colorfield').wpColorPicker();
});

jQuery(document).ready(function() {
    jQuery('#VikCalDate').datepicker({
        dateFormat : 'dd-mm-yy'
    });
});

//Based on the https://jeroensormani.com/how-to-include-the-wordpress-media-selector-in-your-plugin/
//and changed to meet the needs
jQuery( document ).ready( function( $ ) {

    jQuery('.VCMediaSelectorButton').on('click', function( event ){
        event.preventDefault();
        var selectedImageObjId = $(this).attr('id');

        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Select a image to upload',
            button: {
                text: 'Use this image',
            },
            multiple: false
        });

        file_frame.on( 'select', function() {
            attachment = file_frame.state().get('selection').first().toJSON();

            var a = $( '#'+selectedImageObjId ).parent();
            $( '.vc--image__preview', a).attr( 'src', attachment.url ).css( 'width', 'auto' );
            $( '#'+selectedImageObjId ).siblings( '.vc--image' ).val( attachment.id );
        });

        file_frame.open();
    });

});