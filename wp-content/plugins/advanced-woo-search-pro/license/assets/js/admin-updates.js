jQuery(document).ready(function ($) {
    'use strict';

    var $licenseBtn = $('#activate-license');
    var $licenseForm = $('[data-license-form]');
    var $licenseFormErrorText = $licenseForm.find('.license-error');
    var $licenseInput = $licenseForm.find('input[type="text"]');

    var $updateBtn = $('#update-plugin');
    var $updateBtnContainer = $updateBtn.closest('td');

    var $refreshPluginInfo = $('#refresh-plugin-info');
    var $refreshPluginInfoContainer = $refreshPluginInfo.closest('td');

    $licenseBtn.on( 'click', function(e) {

        e.preventDefault();

        var licenseNumber = $licenseInput.val().trim();

        if ( licenseNumber ) {

            var sendData = {
                action: 'wpunit-aws-ajax-actions',
                type: 'verify-license',
                license: licenseNumber,
                _ajax_nonce: aws_vars.ajax_nonce
            };

            var self = $(this);
            var isActive = self.data('is-active');

            console.log(isActive);

            if ( isActive && isActive === 'active' ) {
                sendData.type = 'deactivate-license';
                var proceedDeactivation = confirm("By deactivating your license you will no longer receive any plugin updates, but will enable activating the license on another site. Are you sure you want to proceed?");
                if ( ! proceedDeactivation ) {
                    return;
                }
            }

            $licenseInput.attr('disabled','disabled');
            $licenseBtn.attr('disabled','disabled');
            $licenseForm.addClass('aws-processing');
            $licenseForm.removeClass('valid');
            $licenseForm.removeClass('invalid');

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: sendData,
                dataType: "json",
                success: function (response) {

                    $licenseBtn.removeAttr('disabled');
                    $licenseForm.removeClass('aws-processing');

                    if ( response.data.type === 'valid' ) {

                        $licenseForm.addClass('valid');
                        self.data('is-active', 'active');
                        self.text('Deactivate License');
                        $licenseInput.val( $licenseInput.val().replace(/[\w\W]/gi, '*') );

                    } else if( response.data.type === 'invalid' ) {

                        $licenseInput.removeAttr('disabled');
                        $licenseForm.addClass('invalid');
                        self.data('is-active', 'inactive');
                        self.text('Activate License');
                        $licenseFormErrorText.text( response.data.text );

                    } else if( response.data.type === 'deactivated' ) {

                        $licenseInput.removeAttr('disabled');
                        self.data('is-active', 'inactive');
                        self.text('Activate License');
                        $licenseInput.val('');

                    }

                }
            });

        } else {
            alert('License field is empty.');
        }

    });

    $updateBtn.on( 'click', function(e) {

        e.preventDefault();

        $updateBtnContainer.addClass('aws-processing');

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'wpunit-aws-ajax-actions',
                type: 'clear-cache',
                _ajax_nonce: aws_vars.ajax_nonce
            },
            dataType: "json",
            complete: function () {
                window.location.href = $updateBtn.attr('href');
            }
        });

    });

    $refreshPluginInfo.on( 'click', function(e) {

        e.preventDefault();

        $refreshPluginInfoContainer.addClass('aws-processing');

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'wpunit-aws-ajax-actions',
                type: 'refresh-plugin-info',
                _ajax_nonce: aws_vars.ajax_nonce
            },
            dataType: "json",
            complete: function () {
                location.reload();
            }
        });

    });

});