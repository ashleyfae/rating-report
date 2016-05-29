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

            $('#novelist-reset-tab-button').click(function (e) {
                Rating_Report.resetSettings(e);
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
            
        }
        
    };
    
    Rating_Report.init();

})(jQuery);