/**
 * Weather Effect - Admin UI Enhancements (Adapted for Free version)
 * Provides: Live Preview, Range Sliders Bubble Logic, Two-Column split screen
 */
(function ($) {
    'use strict';

    var WE_UI = {
        init: function () {
            this.initLivePreview();
            this.initRangeSliders();
            this.initColorPickerReload();
        },

        // Color picker changes reload preview
        initColorPickerReload: function () {
            var self = this;
            // The wpColorPicker callback handles updating color, we hook into it
            setTimeout(function() {
                var $colorField = $('#snow_falling_color');
                if ($colorField.length > 0 && typeof $colorField.wpColorPicker === 'function') {
                    var originalChange = $colorField.wpColorPicker('option', 'change');
                    $colorField.wpColorPicker('option', 'change', function(event, ui) {
                        if (typeof originalChange === 'function') {
                            originalChange(event, ui);
                        }
                        // Trigger color change preview update
                        setTimeout(function() {
                            self.refreshPreview();
                        }, 50);
                    });
                }
            }, 1000);
        },

        // ==========================================
        // LIVE PREVIEW PANEL
        // ==========================================
        initLivePreview: function () {
            // Create two-column layout with sticky preview on right
            if ($('.wep-two-column-layout').length === 0 && $('.snowfalls-page form').length > 0) {
                var $form = $('.snowfalls-page form');
                var $formContents = $form.children();

                // Create layout structure
                var $layout = $('<div class="wep-two-column-layout"></div>');
                var $settingsCol = $('<div class="wep-settings-column"></div>');
                var $previewCol = $('<div class="wep-preview-column"></div>');

                // Move form contents to settings column
                $settingsCol.append($formContents);

                // Build preview HTML
                var previewHTML =
                    '<div id="wep-live-preview" class="wep-preview-panel">' +
                    '<div class="wep-preview-header">' +
                    '<span class="wep-preview-title">Live Preview</span>' +
                    '<div class="wep-preview-controls">' +
                    '<input type="color" class="wep-preview-color-picker" title="Change Background Color" value="#302b63">' +
                    '<button type="button" class="wep-preview-refresh" title="Refresh Preview">↻</button>' +
                    '<button type="button" class="wep-preview-toggle" title="Toggle Preview">−</button>' +
                    '</div>' +
                    '</div>' +
                    '<div class="wep-preview-canvas-wrap">' +
                    '<canvas id="wep-admin-preview-canvas"></canvas>' +
                    '<div class="wep-preview-overlay">Click ↻ to preview</div>' +
                    '</div>' +
                    '</div>';

                $previewCol.append(previewHTML);

                // Assemble and add to form
                $layout.append($settingsCol);
                $layout.append($previewCol);
                $form.append($layout);
            }
            this.bindPreviewEvents();

            // Auto-initialize the Live Preview card on load
            this.refreshPreview();
        },

        bindPreviewEvents: function () {
            var self = this;
            var sliderDebounce = null;

            // Toggle preview visibility
            $(document).on('click', '.wep-preview-toggle', function () {
                var $canvasWrap = $('#wep-live-preview').find('.wep-preview-canvas-wrap');
                $canvasWrap.slideToggle(200);
                $(this).text($canvasWrap.is(':visible') ? '−' : '+');
            });

            $(document).on('click', '.wep-preview-refresh', function () {
                self.refreshPreview();
            });

            $(document).on('input change', '.wep-preview-color-picker', function () {
                self.refreshPreview();
            });

            // Listen to any input or select changes in the settings form
            $(document).on('change', '.snowfalls-page form input, .snowfalls-page form select', function () {
                self.refreshPreview();
            });

            // Listen to range sliders (instant preview update with debounce)
            $(document).on('input', '.range-slider__range', function () {
                if (sliderDebounce) clearTimeout(sliderDebounce);
                sliderDebounce = setTimeout(function () {
                    self.refreshPreview();
                }, 150);
            });
        },

        refreshPreview: function () {
            var self = this;
            var $canvas = $('#wep-admin-preview-canvas');
            var $overlay = $('.wep-preview-overlay');

            if (!$canvas.length) return;

            // Stop running animations
            if (this.previewAnimationId) {
                cancelAnimationFrame(this.previewAnimationId);
                this.previewAnimationId = null;
            }

            var canvas = $canvas[0];
            var ctx = canvas.getContext('2d');
            var width = $canvas.parent().width();
            var height = 200;

            canvas.width = width;
            canvas.height = height;

            // Gradient bg
            this.previewBgColor = $('.wep-preview-color-picker').val() || '#302b63';
            ctx.fillStyle = this.previewBgColor;
            ctx.fillRect(0, 0, width, height);

            var occasion = $('#weather_occasion').val();
            var enabled = $('input[name="enable_weather_effect"]:checked').val() === '1';

            if (!enabled) {
                $overlay.text('Effect is disabled').show();
                return;
            }

            $overlay.text('Loading...').show();

            var selectedImages = [];
            var isImageEffect = true;
            var occasionLabel = 'Weather Effect';

            // Check which checkboxes are checked and select values
            if (occasion === 'christmas_check') {
                occasionLabel = 'Christmas';
                var christmas_types = $('input[name="christmas_types"]:checked').val();
                if (christmas_types === 'snow_effect') {
                    if ($('#ball').is(':checked')) {
                        selectedImages.push('christmas/' + $('#christmas_ball').val() + '.png');
                    }
                    if ($('#bell').is(':checked')) {
                        selectedImages.push('christmas/' + $('#christmas_bell').val() + '.png');
                    }
                    if ($('#candy').is(':checked')) {
                        selectedImages.push('christmas/' + $('#christmas_candy').val() + '.png');
                    }
                    if ($('#gift').is(':checked')) {
                        selectedImages.push('christmas/' + $('#christmas_gift').val() + '.png');
                    }
                    if ($('#snowman').is(':checked')) {
                        var val = $('#christmas_snowman').val();
                        if (val === 'snowman3') val = 'Snowman3'; // Fix casing
                        selectedImages.push('christmas/' + val + '.png');
                    }
                    if ($('#snow_flake').is(':checked')) {
                        selectedImages.push('christmas/' + $('#christmas_snow_flake').val() + '.png');
                    }
                } else {
                    isImageEffect = false;
                }
            } else if (occasion === 'winter_check') {
                occasionLabel = 'Winter';
                isImageEffect = false;
            } else if (occasion === 'autumn_check') {
                occasionLabel = 'Autumn';
                if ($('#leaf').is(':checked')) {
                    selectedImages.push('autumn/' + $('#autumn_leaf').val() + '.png');
                }
            } else if (occasion === 'spring_check') {
                occasionLabel = 'Spring';
                if ($('#leaf_spring').is(':checked')) {
                    selectedImages.push('spring/' + $('#spring_leaf').val() + '.png');
                }
            } else if (occasion === 'summer_check') {
                occasionLabel = 'Summer';
                if ($('#drink').is(':checked')) {
                    selectedImages.push('summer/' + $('#summer_drink').val() + '.png');
                }
                if ($('#sun').is(':checked')) {
                    selectedImages.push('summer/' + $('#summer_sun').val() + '.png');
                }
            } else if (occasion === 'rainy_check') {
                occasionLabel = 'Rain';
                if ($('#umbrella').is(':checked')) {
                    selectedImages.push('rain/' + $('#rain_umbrella').val() + '.png');
                }
                if ($('#drop').is(':checked')) {
                    selectedImages.push('rain/' + $('#rain_drops').val() + '.png');
                }
                if ($('#cloud').is(':checked')) {
                    selectedImages.push('rain/' + $('#rain_cloud').val() + '.png');
                }
            } else if (occasion === 'halloween_check') {
                occasionLabel = 'Halloween';
                if ($('#ghost').is(':checked')) selectedImages.push('halloween/' + $('#halloween_ghost').val() + '.png');
                if ($('#bats').is(':checked')) selectedImages.push('halloween/' + $('#halloween_bats').val() + '.png');
                if ($('#moon').is(':checked')) selectedImages.push('halloween/' + $('#halloween_moon').val() + '.png');
                if ($('#pumpkin').is(':checked')) selectedImages.push('halloween/' + $('#halloween_pumpkin').val() + '.png');
                if ($('#web').is(':checked')) selectedImages.push('halloween/' + $('#halloween_web').val() + '.png');
                if ($('#witch').is(':checked')) selectedImages.push('halloween/' + $('#halloween_witch').val() + '.png');
            } else if (occasion === 'thanks_giving_check') {
                occasionLabel = 'Thanksgiving';
                if ($('#turkey').is(':checked')) selectedImages.push('thanksgiving/' + $('#thanksgiving_turkey').val() + '.png');
            } else if (occasion === 'valentine_check') {
                occasionLabel = 'Valentine';
                if ($('#rose').is(':checked')) selectedImages.push('valentine/' + $('#valentine_rose').val() + '.png');
                if ($('#balloon').is(':checked')) selectedImages.push('valentine/' + $('#valentine_balloon').val() + '.png');
                if ($('#heart').is(':checked')) selectedImages.push('valentine/' + $('#valentine_heart').val() + '.png');
            } else if (occasion === 'new_year_check') {
                occasionLabel = 'New Year';
                if ($('#balloon_new').is(':checked')) selectedImages.push('newyear/' + $('#newyear_balloon').val() + '.png');
                if ($('#sticker').is(':checked')) selectedImages.push('newyear/' + $('#new_year_sticker').val() + '.png');
            }

            var settings = this.getSliderSettings(occasion);

            if (isImageEffect) {
                if (selectedImages.length === 0) {
                    $overlay.text('No elements selected').show();
                    return;
                }
                $overlay.hide();
                this.loadImages(selectedImages, function (images) {
                    if (images.length === 0) {
                        $overlay.text('Could not load images').show();
                        return;
                    }
                    self.runPreviewAnimation(ctx, width, height, occasionLabel, images, settings);
                });
            } else {
                $overlay.hide();
                this.runShapePreviewAnimation(ctx, width, height, occasionLabel, settings);
            }
        },

        loadImages: function (imagePaths, callback) {
            var images = [];
            var loaded = 0;
            var total = imagePaths.length;

            if (total === 0) {
                callback([]);
                return;
            }

            var baseUrl = (typeof AWPLIFE_WE_PLUGIN_PATH !== 'undefined') ? AWPLIFE_WE_PLUGIN_PATH : ((typeof WE_PLUGIN_URL !== 'undefined') ? WE_PLUGIN_URL : '../wp-content/plugins/weather-effect/');

            imagePaths.forEach(function (path) {
                var img = new Image();
                img.onload = function () {
                    images.push(img);
                    loaded++;
                    if (loaded === total) callback(images);
                };
                img.onerror = function () {
                    loaded++;
                    if (loaded === total) callback(images);
                };
                img.src = baseUrl + 'assets/images/' + path;
            });
        },

        getSliderSettings: function (occasion) {
            var settings = {
                speed: 5,
                count: 15,
                minSize: 10,
                maxSize: 30
            };

            var prefix = '';
            if (occasion === 'christmas_check') {
                var christmas_types = $('input[name="christmas_types"]:checked').val();
                if (christmas_types === 'snow_effect') {
                    prefix = 'christmas_';
                } else {
                    return {
                        round: $('input[name="master_round"]:checked').val() === 'true',
                        roundType: $('input[name="round_type"]:checked').val() === 'true',
                        minSize: parseInt($('#min_size_round').val()) || 4,
                        maxSize: parseInt($('#max_size_round').val()) || 20,
                        shadows: $('input[name="master_shadows"]:checked').val() === 'true',
                        shadowType: $('input[name="shadow_type"]:checked').val() === 'true',
                        count: parseInt($('#flakes_shadow').val()) || 50,
                        speed: 3
                    };
                }
            } else if (occasion === 'winter_check') {
                var snow_types = $('input[name="snow_types"]:checked').val();
                if (snow_types === 'winter_snow') {
                    return {
                        round: true,
                        roundType: $('input[name="ice_cube"]:checked').val() === 'true',
                        minSize: parseInt($('#min_size_christmas').val()) || 1,
                        maxSize: parseInt($('#max_size_christmas').val()) || 5,
                        count: parseInt($('#flake_christmas').val()) || 50,
                        speed: 3
                    };
                } else {
                    return {
                        text: true,
                        minSize: parseInt($('#min_size_falling').val()) || 3,
                        maxSize: parseInt($('#max_size_falling').val()) || 30,
                        newOn: parseInt($('#snow_falling_time').val()) || 500,
                        color: $('#snow_falling_color').val() || '#ffffff',
                        count: 30,
                        speed: 3
                    };
                }
            } else if (occasion === 'autumn_check') {
                prefix = 'autumn_';
            } else if (occasion === 'spring_check') {
                prefix = 'spring_';
            } else if (occasion === 'summer_check') {
                prefix = 'summer_';
            } else if (occasion === 'rainy_check') {
                prefix = 'rain_';
            } else if (occasion === 'halloween_check') {
                prefix = 'halloween_';
            } else if (occasion === 'thanks_giving_check') {
                prefix = 'thanksgiving_';
            } else if (occasion === 'valentine_check') {
                prefix = 'valentine_';
            } else if (occasion === 'new_year_check') {
                prefix = 'newyear_';
            }

            if (prefix) {
                settings.speed = parseInt($('#' + prefix + 'speed').val()) || 5;
                settings.minSize = parseInt($('#' + prefix + 'min_size_leaf').val()) || 20;
                settings.maxSize = parseInt($('#' + prefix + 'max_size_leaf').val()) || 50;
                settings.count = parseInt($('#' + prefix + 'flakes_leaf').val()) || 5;
            }
            return settings;
        },

        previewAnimationId: null,
        previewParticles: [],

        runPreviewAnimation: function (ctx, width, height, label, images, settings) {
            var self = this;
            if (this.previewAnimationId) cancelAnimationFrame(this.previewAnimationId);

            var minSpeed = 0.5;
            var maxSpeed = settings.speed * 0.5;
            var count = Math.min(Math.max(Math.round(settings.count / 2), 3), 20);

            this.previewParticles = [];
            for (var i = 0; i < count; i++) {
                var size = settings.minSize + Math.random() * (settings.maxSize - settings.minSize);
                size = Math.min(Math.max(size * 0.5, 10), 40);
                var particleSpeed = minSpeed + Math.random() * (maxSpeed - minSpeed);
                this.previewParticles.push({
                    x: Math.random() * width,
                    y: Math.random() * height - height,
                    size: size,
                    speed: particleSpeed,
                    opacity: 0.8 + Math.random() * 0.2,
                    rotation: Math.random() * Math.PI * 2,
                    rotationSpeed: (Math.random() - 0.5) * 0.04,
                    sway: Math.random() * Math.PI * 2,
                    stepSize: 0.01 + Math.random() * 0.04,
                    image: images[Math.floor(Math.random() * images.length)]
                });
            }

            function animate() {
                ctx.fillStyle = self.previewBgColor || '#302b63';
                ctx.fillRect(0, 0, width, height);

                ctx.globalAlpha = 0.5;
                ctx.fillStyle = '#fff';
                ctx.font = '11px Arial';
                ctx.textAlign = 'center';
                ctx.fillText(label + ' Preview', width / 2, height - 10);

                self.previewParticles.forEach(function (p) {
                    p.y += p.speed;
                    p.sway += p.stepSize;
                    p.x += Math.cos(p.sway) * 0.8;
                    p.rotation += p.rotationSpeed;

                    if (p.y > height + p.size) {
                        p.y = -p.size;
                        p.x = Math.random() * width;
                        p.image = images[Math.floor(Math.random() * images.length)];
                    }

                    if (p.image) {
                        ctx.save();
                        ctx.globalAlpha = p.opacity;
                        ctx.translate(p.x, p.y);
                        ctx.rotate(p.rotation);
                        ctx.drawImage(p.image, -p.size / 2, -p.size / 2, p.size, p.size);
                        ctx.restore();
                    }
                });
                ctx.globalAlpha = 1;
                self.previewAnimationId = requestAnimationFrame(animate);
            }
            animate();
        },

        runShapePreviewAnimation: function (ctx, width, height, label, settings) {
            var self = this;
            if (this.previewAnimationId) cancelAnimationFrame(this.previewAnimationId);

            var count = settings.text ? 20 : Math.min(Math.max(Math.round(settings.count / 4), 10), 50);
            var minSpeed = 0.5;
            var maxSpeed = 2;

            this.previewParticles = [];
            for (var i = 0; i < count; i++) {
                var size = settings.minSize + Math.random() * (settings.maxSize - settings.minSize);
                size = Math.min(Math.max(size * 0.6, 2), 15);
                var particleSpeed = minSpeed + Math.random() * (maxSpeed - minSpeed);
                this.previewParticles.push({
                    x: Math.random() * width,
                    y: Math.random() * height - height,
                    size: size,
                    speed: particleSpeed,
                    opacity: 0.5 + Math.random() * 0.5,
                    sway: Math.random() * Math.PI * 2,
                    stepSize: 0.01 + Math.random() * 0.03
                });
            }

            function animate() {
                ctx.fillStyle = self.previewBgColor || '#302b63';
                ctx.fillRect(0, 0, width, height);

                ctx.globalAlpha = 0.5;
                ctx.fillStyle = '#fff';
                ctx.font = '11px Arial';
                ctx.textAlign = 'center';
                ctx.fillText(label + ' Preview', width / 2, height - 10);

                self.previewParticles.forEach(function (p) {
                    p.y += p.speed;
                    p.sway += p.stepSize;
                    p.x += Math.cos(p.sway) * 0.5;

                    if (p.y > height + p.size) {
                        p.y = -p.size;
                        p.x = Math.random() * width;
                    }

                    ctx.save();
                    ctx.globalAlpha = p.opacity;

                    if (settings.text) {
                        ctx.fillStyle = settings.color || '#ffffff';
                        ctx.font = Math.round(p.size * 1.5) + 'px Arial';
                        ctx.fillText('❄', p.x, p.y);
                    } else {
                        ctx.fillStyle = '#ffffff';
                        if (settings.shadows && settings.shadowType) {
                            ctx.shadowBlur = 4;
                            ctx.shadowColor = '#ffffff';
                        }

                        if (settings.round && settings.roundType) {
                            ctx.beginPath();
                            ctx.arc(p.x, p.y, p.size / 2, 0, Math.PI * 2);
                            ctx.fill();
                        } else {
                            ctx.fillRect(p.x - p.size / 2, p.y - p.size / 2, p.size, p.size);
                        }
                    }
                    ctx.restore();
                });
                ctx.globalAlpha = 1;
                self.previewAnimationId = requestAnimationFrame(animate);
            }
            animate();
        },

        // ==========================================
        // RANGE SLIDERS Bubble Logic
        // ==========================================
        initRangeSliders: function () {
            var self = this;

            $(document).on('input change', '.range-slider__range', function () {
                $(this).siblings('.range-slider__value').text(this.value);
                self.validateMinMaxSliders($(this));
            });

            // Initial load validation
            setTimeout(function () {
                self.validateAllMinMaxPairs();
            }, 500);
        },

        validateAllMinMaxPairs: function () {
            var self = this;
            $('.range-slider__range').each(function () {
                self.validateMinMaxSliders($(this));
            });
        },

        validateMinMaxSliders: function ($slider) {
            var name = $slider.attr('name') || '';
            var value = parseInt($slider.val()) || 0;
            var isValid = true;

            var $container = $slider.closest('.tab-content, #snow_winter, form');

            if (name.indexOf('min_size') !== -1) {
                var maxName = name.replace('min_size', 'max_size');
                var $maxSlider = $container.find('input[name="' + maxName + '"]');

                if ($maxSlider.length === 0) {
                    $maxSlider = $('input[name="' + maxName + '"]');
                }

                if ($maxSlider.length > 0) {
                    var maxValue = parseInt($maxSlider.val()) || 0;
                    if (value > maxValue) {
                        $slider.val(maxValue);
                        $slider.siblings('.range-slider__value').text(maxValue);
                        this.showSliderWarning($slider, 'Min size cannot exceed max size');
                        isValid = false;
                    }
                }
            } else if (name.indexOf('max_size') !== -1) {
                var minName = name.replace('max_size', 'min_size');
                var $minSlider = $container.find('input[name="' + minName + '"]');

                if ($minSlider.length === 0) {
                    $minSlider = $('input[name="' + minName + '"]');
                }

                if ($minSlider.length > 0) {
                    var minValue = parseInt($minSlider.val()) || 0;
                    if (value < minValue) {
                        $slider.val(minValue);
                        $slider.siblings('.range-slider__value').text(minValue);
                        this.showSliderWarning($slider, 'Max size cannot be less than min size');
                        isValid = false;
                    }
                }
            }

            if (isValid) {
                this.clearSliderWarning($slider);
            }
        },

        showSliderWarning: function ($slider, message) {
            var $warning = $slider.siblings('.wep-slider-warning');
            if ($warning.length === 0) {
                $warning = $('<span class="wep-slider-warning" style="color:#ff6b6b;font-size:11px;font-weight:bold;display:block;margin-top:3px;padding:3px 8px;background:#fff3f3;border-radius:3px;border:1px solid #ffcccc;"></span>');
                $slider.after($warning);
            }
            $warning.text('⚠ ' + message).show();
        },

        clearSliderWarning: function ($slider) {
            $slider.siblings('.wep-slider-warning').hide();
        }
    };

    $(document).ready(function () {
        WE_UI.init();
    });

    $(window).on('load', function () {
        setTimeout(function () {
            $('#wep-loading-overlay').fadeOut(300);
            $('.wep-admin-wrap').addClass('wep-loaded');
        }, 100);
    });

    setTimeout(function () {
        $('#wep-loading-overlay').fadeOut(300);
        $('.wep-admin-wrap').addClass('wep-loaded');
    }, 3000);

})(jQuery);
