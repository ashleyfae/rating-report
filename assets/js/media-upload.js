jQuery(document).ready(function ($) {

    // Using this var to track which item on a page full of multiple upload buttons is currently being uploaded.
    var current_rating_reportupload = 0;

    // Define uploader settings
    var frame = wp.media({
        title: RATING_REPORT_MEDIA.text_title,
        multiple: false,
        library: {type: 'image'},
        button: {text: RATING_REPORT_MEDIA.text_button}
    });

    // Call this from the upload button to initiate the upload frame.
    rating_reportopen_uploader = function (id) {
        current_rating_reportupload = id;
        frame.open();
        return false;
    };

    // Handle results from media manager.
    frame.on('close', function () {
        var attachment = frame.state().get('selection').first().toJSON();
        rating_reportrender_image(attachment);
    });

    // Output selected image HTML.
    // This part could be totally rewritten for your own purposes to process the results!
    rating_reportrender_image = function (attachment) {

        // Generate IMG
        img_html = '<img src="' + attachment.url + '" ';
        img_html += 'width="' + attachment.width + '" ';
        img_html += 'height="' + attachment.height + '" ';
        if (attachment.alt != '') {
            img_html += 'alt="' + attachment.alt + '" ';
        }
        img_html += '>';

        $("#" + current_rating_reportupload + "_image").attr('src', attachment.url).attr('width', attachment.width).attr('height', attachment.height).attr('srcset', attachment.url + ' ' + attachment.width + 'w').show();
        $("#" + current_rating_reportupload).val(attachment.id);
        $("#" + current_rating_reportupload + "_remove").show();
    };

    rating_reportclear_uploader = function (current_rating_reportupload) {
        $("#" + current_rating_reportupload + "_image").attr('src', '').hide();
        $("#" + current_rating_reportupload).val('');
        $("#" + current_rating_reportupload + "_remove").hide();
        return false;
    }

});