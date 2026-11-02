/**
 * Weather Effect - Image Preview Handler (Adapted for Free version)
 * Adds live image previews next to all dropdown selectors
 */
jQuery(document).ready(function ($) {
    // Plugin base URL - set by PHP inline script
    var pluginUrl = typeof AWPLIFE_WE_PLUGIN_PATH !== 'undefined' ? AWPLIFE_WE_PLUGIN_PATH : '';

    // Configuration for all dropdowns with image previews
    var previewConfig = {
        // Christmas Effects
        'christmas_ball': { folder: 'christmas' },
        'christmas_bell': { folder: 'christmas' },
        'christmas_candy': { folder: 'christmas' },
        'christmas_gift': { folder: 'christmas' },
        'christmas_snowman': { folder: 'christmas' },
        'christmas_snow_flake': { folder: 'christmas' },

        // Halloween Effects
        'halloween_ghost': { folder: 'halloween' },
        'halloween_bats': { folder: 'halloween' },
        'halloween_moon': { folder: 'halloween' },
        'halloween_pumpkin': { folder: 'halloween' },
        'halloween_web': { folder: 'halloween' },
        'halloween_witch': { folder: 'halloween' },

        // Valentine Effects
        'valentine_rose': { folder: 'valentine' },
        'valentine_balloon': { folder: 'valentine' },
        'valentine_heart': { folder: 'valentine' },

        // Rain Effects
        'rain_umbrella': { folder: 'rain' },
        'rain_drops': { folder: 'rain' },
        'rain_cloud': { folder: 'rain' },

        // Summer Effects
        'summer_drink': { folder: 'summer' },
        'summer_sun': { folder: 'summer' },

        // New Year Effects
        'newyear_balloon': { folder: 'newyear' },
        'new_year_sticker': { folder: 'newyear' },

        // Thanksgiving Effects
        'thanksgiving_turkey': { folder: 'thanksgiving' },

        // Spring Effects
        'spring_leaf': { folder: 'spring' },

        // Autumn Effects
        'autumn_leaf': { folder: 'autumn' }
    };

    // Style for preview images
    var previewStyle = 'width: 50px; height: 50px; margin-left: 15px; vertical-align: middle; object-fit: contain; background: #f1f5f9; border-radius: 8px; padding: 5px; border: 1px solid #e2e8f0;';

    // Initialize previews for all configured dropdowns
    $.each(previewConfig, function (selectId, config) {
        var $select = $('#' + selectId);
        if ($select.length) {
            // Get current value
            var currentValue = $select.val();
            var imageUrl = getImageUrl(config.folder, currentValue);

            // Create preview image if it doesn't exist
            var previewId = selectId + '_preview';
            if ($('#' + previewId).length === 0) {
                // Wrap select in a flex container if not already wrapped
                if (!$select.parent().hasClass('wep-select-preview-wrap')) {
                    $select.wrap('<span class="wep-select-preview-wrap" style="display: inline-flex; align-items: center; gap: 10px;"></span>');
                }

                // snowman3 in free version is named Snowman3.png on disk
                var displayedSrc = imageUrl;
                if (currentValue === 'snowman3') {
                    displayedSrc = getImageUrl(config.folder, 'Snowman3');
                }

                var $preview = $('<img>', {
                    id: previewId,
                    src: displayedSrc,
                    style: previewStyle,
                    class: 'wep-dropdown-preview'
                });

                // Insert after select but inside the wrapper
                $select.after($preview);
            }

            // Update on change
            $select.on('change', function () {
                var newValue = $(this).val();
                var correctedValue = newValue;
                if (newValue === 'snowman3') {
                    correctedValue = 'Snowman3';
                }
                var newImageUrl = getImageUrl(config.folder, correctedValue);
                $('#' + previewId).attr('src', newImageUrl);
            });
        }
    });

    // Helper function to build image URL
    function getImageUrl(folder, value) {
        return pluginUrl + 'assets/images/' + folder + '/' + value + '.png';
    }
});
