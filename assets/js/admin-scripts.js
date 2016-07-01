(function ($) {

    var Rating_Report = {

        /**
         * Initialize
         */
        init: function () {

            // Repeatable fields.
            $('.rating-report-add-link').relCopy();

            // Initialize colour picker.
            $('.rating-report-color-picker').wpColorPicker();

            // Initialize reset tab.
            $('#novelist-reset-tab-button').click(function (e) {
                Rating_Report.resetSettings(e);
            });

            // Image upload
            $('.rating-report-upload-image-button').click(function (e) {
                Rating_Report.addImage($(this), e);
            });

            // Image remove
            $('.rating-report-remove-image-button').click(function (e) {
                Rating_Report.removeImage($(this), e);
            });

        },

        /**
         * Reset settings to their defaults
         * @param e
         * @returns {boolean}
         */
        resetSettings: function (e) {

            e.preventDefault();

            if (!confirm(RATING_REPORT.confirm_reset)) {
                return false;
            }

            var parentDiv = $(this).parent();

            parentDiv.append('<span id="rating-report-spinner" class="spinner is-active"></span>');

            var data = {
                'action': 'rating_report_restore_default_settings',
                'tab': $(this).data('current-tab'),
                'section': $(this).data('current-section')
            };

            $.post(ajaxurl, data, function (response) {
                $('#rating-report-spinner').remove();

                if (response.success == true) {
                    window.location.href = response.data;
                } else {
                    parentDiv.append(response.data);
                }
            });

        },

        /**
         * Add Media
         * @param element Object that was clicked on (our button)
         * @param e Click event
         */
        addImage: function (element, e) {
            e.preventDefault();

            var ratingReportGalleryFrame;
            var imageIDField = $('#' + element.parent().data('value'));
            var imageSRCField = $('#' + element.parent().data('image'));

            element.nextAll('.rating-report-remove-image-button').show();

            // Create the media frame.
            ratingReportGalleryFrame = wp.media.frames.rating_report = wp.media({
                title: RATING_REPORT.gallery_title,
                button: {
                    text: RATING_REPORT.gallery_update
                },
                multiple: false,
                library: {type: 'image'}
            });

            // When an image is selected, run a callback.
            ratingReportGalleryFrame.on('select', function () {
                var attachment = ratingReportGalleryFrame.state().get('selection').first().toJSON();
                imageIDField.val(attachment.id);
                imageSRCField.attr('src', attachment.url).attr('width', attachment.width).attr('height', attachment.height).attr('srcset', attachment.url + ' ' + attachment.width + 'w').show();
            });

            // Finally, open the modal.
            ratingReportGalleryFrame.open();
        },

        /**
         * Remove Media
         * @param element Object that was clicked on (our button)
         * @param e Click event
         */
        removeImage: function (element, e) {
            e.preventDefault();

            var imageIDField = $('#' + element.parent().data('value'));
            var imageSRCField = $('#' + element.parent().data('image'));

            imageIDField.val('');
            imageSRCField.hide();
            element.hide();
        }

    };

    Rating_Report.init();

})(jQuery);