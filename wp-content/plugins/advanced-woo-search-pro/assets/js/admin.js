jQuery(document).ready(function ($) {
    'use strict';

    // Tooltips
    function awsInitTipTip() {

        $( '.aws-tip' ).tipTip( {
            'attribute': 'data-tip',
            'fadeIn': 50,
            'fadeOut': 50,
            'delay': 50,
        } );

    }

    awsInitTipTip();

    // Settings page ajax tabs
    var $tabsBtns = $('.aws-tabs .aws-nav-tab');
    var $sectionsBtns = $('.aws-admin-sections a');

    // Tabs
    $tabsBtns.on( 'click', function(e) {

        e.preventDefault();

        var tabName = $(this).data('tab-name');

        $('.aws-nav-tab').removeClass('aws-nav-tab-active');

        $(this).addClass('aws-nav-tab-active');

        // if tab has sections - reset to first active
        var $currentTab = $('[data-tab="'+ tabName +'"]');

        $('[data-tab]').hide();
        $currentTab.fadeIn();

        // Rebuild select2 if needed
        if ( $currentTab.find('.select2-container').length > 0 && $currentTab.find('.select2-container').is(':visible') ) {
            aws_init_select2();
        }

        var newUrl = updateQueryStringParameter(window.location.href, 'tab', tabName);
        window.history.pushState({ path: newUrl }, '', newUrl);

    });

    // Sections tabs
    $sectionsBtns.on( 'click', function(e) {

        e.preventDefault();

        var sectionName = $(this).data('section-name');
        var $currentTab = $(this).closest('[data-tab]');

        $currentTab.find('.aws-admin-sections a').removeClass('aws-active');

        $(this).addClass('aws-active');

        $currentTab.find('[data-section]').hide();
        $currentTab.find('[data-section="'+sectionName+'"]').not('[data-aws-hidden]').fadeIn();

        // Rebuild select2 if needed
        if ( $currentTab.find('.select2-container').length > 0 && $currentTab.find('.select2-container').is(':visible') ) {
            aws_init_select2();
        }

    });

    function updateQueryStringParameter(uri, key, value) {
        var re = new RegExp('([?&])' + key + '=.*?(&|#|$)', 'i');

        // If value is missing or empty string -> remove param
        if (value === undefined || value === null || value === '') {
            if (uri.match(re)) {
                return uri.replace(re, '$1').replace(/[?&]$/, ''); // clean trailing ? or &
            }
            return uri;
        }

        if (uri.match(re)) {
            return uri.replace(re, '$1' + key + '=' + value + '$2');
        } else {
            var hash = '';
            if (uri.indexOf('#') !== -1) {
                hash = uri.replace(/.*#/, '#');
                uri = uri.replace(/#.*/, '');
            }
            var separator = uri.indexOf('?') !== -1 ? '&' : '?';
            return uri + separator + key + '=' + value + hash;
        }
    }

    // Options dependencies toggler
    $(document).on( 'change', '#aws_form [data-dependencies] input, #aws_form [data-dependencies] select', function ( e ) {

        var $currentTable = $(this).closest('table');
        var option_name = $(this).closest('[data-option]').data('option');
        var dependencies = $(this).closest('[data-dependencies]').data('dependencies');
        var newValue = $(this).val();

        if ( $(this).hasClass('aws-toggler') ) {
            var newValue = $(this).is(':checked') ? 'true' : 'false';
        }

        if ( dependencies && typeof dependencies === 'object' ) {

            var optionsToHide = dependencies;
            if ( dependencies.hasOwnProperty(newValue) ) {

                $.each(dependencies[newValue], function(index, value) {
                    $currentTable.find('[data-option="'+ value +'"]').removeAttr('data-aws-hidden').show().find('.aws-row-name').addClass('aws-opt-highlight');
                });

                optionsToHide = Object.fromEntries(
                    Object.entries(dependencies).filter(([key]) => key !== newValue)
                );

                setTimeout(function() {
                    $currentTable.find('.aws-opt-highlight').removeClass('aws-opt-highlight');
                }, 700);

                aws_init_select2();

            }

            $.each(optionsToHide, function(index, value) {
                $.each(value, function(i, opt_to_hide) {
                    $currentTable.find('[data-option="'+ opt_to_hide +'"]').attr('data-aws-hidden', 'true').hide();
                });
            });

        }

    } );

    // Terms sources table
    var select2Data = {};

    function awsGetSelect2Id( $select ) {

        let selectId = $select.attr('id');
        if (!selectId) {
            selectId = $select.attr('name') || $select.data('instance-id');
        }
        if ( ! selectId ) {
            // Generate unique ID if none exists
            selectId = 'select2_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            $select.attr('data-instance-id', selectId);
        }

        return selectId;

    }

    function awsGetExistingTerms($container) {
        const existingTerms = new Set();

        // Find all existing items in the terms table within the same container
        $container.find('.aws-terms-table-item').each(function() {
            const termValue = $(this).data('term');
            if (termValue) {
                existingTerms.add(termValue.toString());
            }
        });

        return existingTerms;
    }

    function awsCallSelec2ForTerms($select) {

        let select2Id = awsGetSelect2Id($select);

        $select.select2({
            minimumResultsForSearch: 15,
            placeholder: 'Select item',
            ajax: {
                url: aws_vars.ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    // Only make AJAX request if data is not loaded yet
                    if ( ! select2Data[select2Id].isDataLoaded ) {
                        return {
                            action: 'aws-termsSelect',
                            term: $select.data('ajax'),
                            optionId: $select.data('option'),
                            search: params.term,
                            instanceId: aws_vars.instance,
                            _ajax_nonce: aws_vars.ajax_nonce
                        };
                    }
                    // Return null to prevent AJAX call when data is already cached
                    return null;
                },
                processResults: function(data, params) {
                    // If this is the initial load, cache the data
                    if (!select2Data[select2Id].isDataLoaded && data) {
                        // Initialize disabledItems from server data if items come pre-disabled
                        if (data.results) {
                            data.results.forEach(item => {
                                if (item.disabled === true) {
                                    select2Data[select2Id].disabledItems.add(item.id);
                                }
                            });
                        }
                        select2Data[select2Id].cachedData = data;
                        select2Data[select2Id].isDataLoaded = true;
                    }

                    // Use cached data for all subsequent requests
                    const cachedData = select2Data[select2Id].cachedData;
                    if (!cachedData || !cachedData.results) {
                        return { results: [] };
                    }

                    // Filter results based on search term
                    let filteredResults = cachedData.results;
                    if (params.term && params.term.trim() !== '') {
                        const searchTerm = params.term.toLowerCase();
                        filteredResults = cachedData.results.filter(item =>
                            item.text && item.text.toLowerCase().includes(searchTerm)
                        );
                    }

                    // Apply disabled state to filtered results (both server-side and client-side disabled items)
                    const processedResults = filteredResults.map(item => ({
                        ...item,
                        disabled: select2Data[select2Id].disabledItems.has(item.id) || item.disabled === true
                    }));

                    return {
                        results: processedResults,
                        pagination: {
                            more: false // No pagination needed for cached data
                        }
                    };
                },
                transport: function(params, success, failure) {
                    // If data is already loaded, use cached data with search filtering
                    if (select2Data[select2Id].isDataLoaded && select2Data[select2Id].cachedData) {
                        // Extract search term from params
                        const searchTerm = params.data && params.data.search ? params.data.search.toLowerCase() : '';

                        let filteredResults = select2Data[select2Id].cachedData.results;

                        // Apply search filter if search term exists
                        if (searchTerm && searchTerm.trim() !== '') {
                            filteredResults = select2Data[select2Id].cachedData.results.filter(item =>
                                item.text && item.text.toLowerCase().includes(searchTerm)
                            );
                        }

                        // Apply disabled state to filtered results (both server-side and client-side disabled items)
                        const processedResults = filteredResults.map(item => ({
                            ...item,
                            disabled: select2Data[select2Id].disabledItems.has(item.id) || item.disabled === true
                        }));

                        const processedData = {
                            ...select2Data[select2Id].cachedData,
                            results: processedResults
                        };

                        success(processedData);
                        return;
                    }

                    // Only make AJAX call for initial data load
                    return $.ajax(params).done(success).fail(failure);
                }
            }
        });

    }

    // Initialize Select2 on page load
    $('.aws-terms-sources-select.aws-select2').each(function () {

        let $select = $(this);
        let $container = $select.closest('td'); // Find the container (td) for this select

        let select2Id = awsGetSelect2Id( $select );

        // Initialize select2Data for this instance
        if ( typeof select2Data[select2Id] === "undefined" ) {
            select2Data[select2Id] = {
                isDataLoaded: false,
                cachedData: null,
                disabledItems: new Set()
            };
        }

        // Get existing terms from the table and add them to disabled items
        const existingTerms = awsGetExistingTerms($container);
        existingTerms.forEach(termId => {
            select2Data[select2Id].disabledItems.add(termId);
        });

        awsCallSelec2ForTerms( $select );

    });

    function awsEnableDisableSelect2ItemById(itemId, disable, $select) {

        let select2Id = awsGetSelect2Id($select);

        if (disable) {
            select2Data[select2Id].disabledItems.add(itemId);
        } else {
            select2Data[select2Id].disabledItems.delete(itemId);
        }

        // Refresh all select2 instances to reflect the change
        $('.aws-terms-sources-select.aws-select2').each(function() {
            const $currentSelect = $(this);
            const currentSelectId = awsGetSelect2Id($currentSelect);

            // Only update if this select uses the same data source
            if (currentSelectId === select2Id ||
                (select2Data[currentSelectId] && select2Data[currentSelectId].cachedData)) {

                // Get current value to preserve selection
                const currentVal = $currentSelect.val();

                // Trigger a refresh of the dropdown
                $currentSelect.select2('close');

                // Clear current results to force refresh
                if ($currentSelect.data('select2')) {
                    $currentSelect.data('select2').$results.empty();
                }

                // Restore selection if it's still valid (not disabled)
                if (currentVal && !select2Data[select2Id].disabledItems.has(currentVal)) {
                    $currentSelect.val(currentVal).trigger('change');
                } else if (currentVal && select2Data[select2Id].disabledItems.has(currentVal)) {
                    // Clear selection if the selected item is now disabled
                    $currentSelect.val(null).trigger('change');
                }
            }
        });
    }

    // Handle item removal
    $(document).on('click', '.aws-terms-table-item [data-delete]', function(e) {
        e.preventDefault();

        const container = $(this).closest('td');
        const $item = $(this).closest('.aws-terms-table-item');
        const val = $item.data('term');

        var $select2 = $(this).closest('td').find('.aws-terms-sources-select.aws-select2');

        // disable all 'search in' sources if subfield is for 'index_sources' option
        if ( $select2.attr('name').indexOf("index_sources") !== -1 ) {
            if ( confirm( aws_vars.index_disable_text ) ) {
                disableIndexField( $select2.data('field'), val );
            } else {
                return;
            }
        }

        $item.remove();

        awsEnableDisableSelect2ItemById(val, false, $select2);

        // Update counter
        container.closest('.aws-table-sources-item').find('[data-item-count]').text( container.find('.aws-terms-table-item[data-term]').length );

    });

    // Handle item addition
    $(document).on('click', '.aws-terms-sources-group [data-add]', function(e) {
        e.preventDefault();

        const container = $(this).closest('td');
        const $group = container.find('.aws-terms-sources-group');
        const $select = $group.find('.aws-terms-sources-select');

        const val = $select.val();
        const label = $select.find('option:selected').text();
        const template = container.find('#awsTermsTableItemTemplate').html();

        var $select2 = $(this).closest('td').find('.aws-terms-sources-select.aws-select2');

        if (typeof val !== 'undefined' && val && typeof label !== 'undefined' && label) {

            // check is field need to be enabled for index
            if ( awsIsNeedToEnableIndex( val, $select2  ) ) {

                awsEnableDisableSelect2ItemById(val, true, $select2);

                // Clear selection
                $select.val(null).trigger('change');

                // Create new table item
                const newItem = template.replace(/\%val/gi, val).replace(/\%label/gi, label);
                container.find('.aws-terms-table').append(newItem);

                // Update counter
                container.closest('.aws-table-sources-item').find('[data-item-count]').text( container.find('.aws-terms-table-item[data-term]').length );

            }

        }

    });

    // If search field is not in index - ask and add it
    $(document).on('change', '.aws-table-sources-item .aws-name input[name*="search_in"][name*="[value]"]', function(e) {
        if ( $(this).closest('.aws-name').find('[data-index-disabled]').length > 0 ) {
            if ( confirm( aws_vars.index_text ) ) {
                // ajax to enable index
                enableIndexField( $(this).data('field') );
                $(this).closest('.aws-name').find('[data-index-disabled]').remove();
            } else {
                $(this).prop('checked', false);
            }
        }
    });

    // If index disabled - disable appropriate search source
    $(document).on('change', '.aws-table-sources-item .aws-name input[name*="index_sources"][name*="[value]"]', function(e) {
        if ( ! $(this).is(':checked') ) {
            if ( confirm( aws_vars.index_disable_text ) ) {
                disableIndexField( $(this).data('field') );
            } else {
                $(this).prop('checked', true);
            }
        }
    });

    // Edit source tables items
    var editButton = $('.aws-table-sources .aws-actions [data-edit]');
    editButton.on( 'click', function(e){
        e.preventDefault();
        var isActive = $(this).closest('.aws-table-sources-item').hasClass('on-edit');
        $('.aws-table-sources .aws-table-sources-item').removeClass('on-edit');
        if ( ! isActive ) {
            $(this).closest('.aws-table-sources-item').addClass('on-edit');
        }
    } );

    // Edit dynamic table items
    $(document).on( 'click', '.aws-dynamic-table-item-actions [data-edit], .aws-dynamic-table-item-name a', function(e){
        e.preventDefault();
        var tableItem = $(this).closest('.aws-dynamic-table-item');
        var isActive = tableItem.hasClass('on-edit');
        $('.aws-dynamic-table .aws-dynamic-table-item').removeClass('on-edit');
        if ( ! isActive ) {
            tableItem.addClass('on-edit');
            aws_init_select2();
        }
    } );

    // Remove item from dynamic table
    $(document).on( 'click', '.aws-dynamic-table-item-actions [data-delete]', function(e){
        e.preventDefault();

        var self = $(this);
        var mainTable = self.closest('.aws-dynamic-table');
        var tableItem = self.closest('.aws-dynamic-table-item');

        if ( confirm( "Are you sure want to delete this filter?" ) ) {

            self.addClass('loading');

            tableItem.find('.aws-dynamic-table-item-name a').addClass('aws-opt-highlight-remove');

            setTimeout(function() {

                tableItem.remove();
                self.removeClass('loading');

                if ( mainTable.find('.aws-dynamic-table-item').length === 0 ) {
                    mainTable.addClass('aws-dynamic-table-empty');
                }

            }, 1000);

        }

    } );

    function awsGetUniqueIdForDynamicTable( tableName ) {

        var idsList = new Array();
        var uniqueId = 2;

        $('[data-table-name="'+ tableName +'"]').find('[name]').each(function() {
            var name = $(this).attr('name');
            if (name && name.indexOf(tableName + '[') === 0) {

                const regex = new RegExp(`${tableName}\\[([^\\]]+)\\]\\[[^\\]]+\\]`);
                const match = name.match(regex);
                const id = match ? parseInt( match[1] ) : null;

                if  (id && ! idsList.includes( id ) ) {
                    idsList.push( id )
                }

            }
        });

        for (var i = 2; i <= 999; i++) {
            if ( ! idsList.includes( i ) ) {
                uniqueId = i;
                break;
            }
        }

        return uniqueId;

    }

    // Add new item for dynamic table
    $('.aws-dynamic-table-btn [data-add]').on('click', function(e) {

        e.preventDefault();

        var self = $(this);

        self.addClass('aws-loading');

        var optionName = self.data('add');
        var $currentTable = $('[data-table-name="'+ optionName +'"]');

        // update id
        var filter_id =  awsGetUniqueIdForDynamicTable(optionName);
        
        var template = $('[data-dynamic-table-template="'+ optionName +'"]').html();
        template = template.replace( /\{unque_id\}/gi, filter_id );
        template = template.replace(/aws-input-name/gi, 'name');

        setTimeout(function() {

            $currentTable.find( '.aws-dynamic-table-body' ).append( template ).find('.aws-dynamic-table-item:last .aws-dynamic-table-item-name a').addClass('aws-opt-highlight');

            // Fix tooltips
            $currentTable.find( '.aws-dynamic-table-item:last [data-tip-text]' ).each(function() {
                var elem = $(this);
                var text = $(this).data('tip-text');
                elem.attr('data-tip', text);
            });
            awsInitTipTip();

            // Fix select2
            $currentTable.find('.aws-dynamic-table-item:last .select2.select2-container').remove();
            aws_init_select2();

            self.removeClass('aws-loading');

            $currentTable.removeClass('aws-dynamic-table-empty');

        }, 500);

        setTimeout(function() {
            $currentTable.find('.aws-dynamic-table-item-name .aws-opt-highlight').removeClass('aws-opt-highlight');
        }, 1000);

    });

    // Live heading change for dynamic table items
    $(document).on('keyup input', '.aws-dynamic-table-item-settings input[name*="[item_name]"]', function(e) {

        e.preventDefault();

        var self = $(this);
        var newVal = self.val();

        self.closest('.aws-dynamic-table-item').find('.aws-dynamic-table-item-name a').text( newVal );

    });
    
    // Sotable for dynamic table items
    $('.aws-dynamic-table-body').sortable({
        axis: "y",
        items: ".aws-dynamic-table-item",
    }).disableSelection();

    function awsIsNeedToEnableIndex( val, $select2 ) {

        let moveForward = true;

        let select2Id = awsGetSelect2Id($select2);

        let itemData = select2Data[select2Id].cachedData.results.find(item => item.id === val);

        if ( itemData && typeof itemData.index !== 'undefined' && ! itemData.index ) {

            if ( confirm( aws_vars.index_text ) ) {

                // ajax to enable index
                enableIndexField( $select2.data('field'), val );

            } else {
                moveForward = false;
            }

        }

        return moveForward;

    }

    // enable needed index fields
    function enableIndexField( field, subField ) {

        var data = {
            action: 'aws-indexEnable',
            field: field,
            _ajax_nonce: aws_vars.ajax_nonce
        };

        if ( typeof subField !== 'undefined' ) {
            data.subField = subField;
        }

        $.ajax({
            type: 'POST',
            url: aws_vars.ajaxurl,
            data: data,
            dataType: "json",
            success: function (data) {
            }
        });

    }

    // enable needed index fields
    function disableIndexField( field, subField ) {

        var data = {
            action: 'aws-indexDisabled',
            field: field,
            _ajax_nonce: aws_vars.ajax_nonce
        };

        if ( typeof subField !== 'undefined' ) {
            data.subField = subField;
        }

        $.ajax({
            type: 'POST',
            url: aws_vars.ajaxurl,
            data: data,
            dataType: "json",
            success: function (data) {
            }
        });

    }

    // Select2 cached data
    var select2CachedData = {};

    // Select2 init
    function aws_init_select2() {

        $('.aws-rules-table select.aws-select2').select2({
            minimumResultsForSearch: 15
        });

        // Select2 init for main plugin settings page
        $('.aws-select2-main').select2({
            minimumResultsForSearch: 20
        });

        var awsSelect2Ajax = $('select.aws-select2-ajax');

        if ( awsSelect2Ajax.length > 0 ) {
            awsSelect2Ajax.each(function( index ) {

                var ajaxAction = $(this).data('ajax');
                var ajaxCallback = $(this).data('ajax-callback') || '';
                var ajaxCallbackParam = $(this).data('ajax-callback-param') || '';
                var placeholder = $(this).data('placeholder') || '';
                var minimumInputLength = $(this).data('input') || 0;

                if ( minimumInputLength === 0 ) {

                    let select2Id = awsGetSelect2Id( $(this) );

                    // Initialize select2CachedData for this instance
                    if ( typeof select2CachedData[select2Id] === "undefined" ) {
                        select2CachedData[select2Id] = {
                            isDataLoaded: false,
                            cachedData: null
                        };
                    }

                    $(this).select2({
                        ajax: {
                            type: 'POST',
                            delay: 250,
                            url: aws_vars.ajaxurl,
                            dataType: "json",
                            data: function(params) {
                                // Only make AJAX request if data is not loaded yet
                                if ( ! select2CachedData[select2Id].isDataLoaded ) {
                                    return {
                                        search: params.term,
                                        action: ajaxAction,
                                        callback: ajaxCallback,
                                        param: ajaxCallbackParam,
                                        _ajax_nonce: aws_vars.ajax_nonce
                                    };
                                }
                                // Return null to prevent AJAX call when data is already cached
                                return null;
                            },
                            processResults: function(data, params) {
                                // If this is the initial load, cache the data
                                if (!select2CachedData[select2Id].isDataLoaded && data) {
                                    select2CachedData[select2Id].cachedData = data;
                                    select2CachedData[select2Id].isDataLoaded = true;
                                }

                                // Use cached data for all subsequent requests
                                const cachedData = select2CachedData[select2Id].cachedData;
                                if (!cachedData || !cachedData.results) {
                                    return { results: [] };
                                }

                                // Filter results based on search term
                                let filteredResults = cachedData.results;
                                if (params.term && params.term.trim() !== '') {
                                    const searchTerm = params.term.toLowerCase();
                                    filteredResults = cachedData.results.filter(item =>
                                        item.text && item.text.toLowerCase().includes(searchTerm)
                                    );
                                }

                                return {
                                    results: filteredResults,
                                    pagination: {
                                        more: false // No pagination needed for cached data
                                    }
                                };

                            },
                            transport: function(params, success, failure) {
                                // If data is already loaded, use cached data with search filtering
                                if (select2CachedData[select2Id].isDataLoaded && select2CachedData[select2Id].cachedData) {
                                    // Extract search term from params
                                    const searchTerm = params.data && params.data.search ? params.data.search.toLowerCase() : '';

                                    let filteredResults = select2CachedData[select2Id].cachedData.results;

                                    // Apply search filter if search term exists
                                    if (searchTerm && searchTerm.trim() !== '') {
                                        filteredResults = select2CachedData[select2Id].cachedData.results.filter(item =>
                                            item.text && item.text.toLowerCase().includes(searchTerm)
                                        );
                                    }

                                    const processedData = {
                                        ...select2CachedData[select2Id].cachedData,
                                        results: filteredResults
                                    };

                                    success(processedData);
                                    return;
                                }

                                // Only make AJAX call for initial data load
                                return $.ajax(params).done(success).fail(failure);
                            }
                        },
                        placeholder: placeholder,
                        minimumResultsForSearch: 15,
                        minimumInputLength: parseInt( minimumInputLength ),
                    });

                } else {

                    $(this).select2({
                        ajax: {
                            type: 'POST',
                            delay: 250,
                            url: aws_vars.ajaxurl,
                            dataType: "json",
                            data: function (params) {
                                return {
                                    search: params.term,
                                    action: ajaxAction,
                                    callback: ajaxCallback,
                                    param: ajaxCallbackParam,
                                    _ajax_nonce: aws_vars.ajax_nonce
                                };
                            },
                        },
                        placeholder: placeholder,
                        minimumInputLength: parseInt( minimumInputLength ),
                    });

                }

            });
        }

    }

    aws_init_select2();

    // Advanced admin filters

    var awsUniqueID = function() {
        return Math.random().toString(36).substr(2, 11);
    };

    var awsGetRuleTemplate = function( groupID, ruleID) {

        var template = $(this).closest('.aws-rules').find('#awsRulesTemplate').html();

        if ( typeof groupID !== 'undefined' ) {
            template = template.replace( /\[group_(.+?)\]/gi, '[group_'+groupID+']' );
        }

        if ( typeof ruleID !== 'undefined' ) {
            template = template.replace( /\[rule_(.+?)\]/gi, '[rule_'+ruleID+']' );
            template = template.replace( /data-aws-rule="(.+?)"/gi, 'data-aws-rule="'+ruleID+'"' );
        }

        return template;

    };

    $(document).on( 'click', '[data-aws-remove-rule]', function(e) {
        e.preventDefault();
        var $table = $(this).closest('.aws-rules-table');
        var $container = $(this).closest('.aws-rules');
        $(this).closest('[data-aws-rule]').remove();

        if ( $table.find('[data-aws-rule]').length < 1 ) {
            $table.remove();
        }

        if ($container.find('[data-aws-rule]').length < 1 ) {
            $container.addClass('aws-rules-empty');
        }

    });


    $(document).on( 'click', '[data-aws-add-rule]', function(e) {
        e.preventDefault();

        var groupID = $(this).closest('.aws-rules-table').data('aws-group');
        var ruleID = awsUniqueID();
        var rulesTemplate = awsGetRuleTemplate.call(this, groupID, ruleID);

        $(this).closest('.aws-rules-table').find( '.aws-rule' ).last().after( rulesTemplate );
        $(this).closest('.aws-rules').removeClass('aws-rules-empty');
        aws_init_select2();

    });


    $(document).on( 'click', '[data-aws-add-group]', function(e) {
        e.preventDefault();

        var groupID = awsUniqueID();
        var rulesTemplate = awsGetRuleTemplate.call(this, groupID);

        rulesTemplate = '<table class="aws-rules-table" data-aws-group="' + groupID + '"><tbody>' + rulesTemplate + '</tbody></table>';
        $(this).closest('.aws-rules').find('.aws-rules-table').last().after( rulesTemplate );
        $(this).closest('.aws-rules').removeClass('aws-rules-empty');
        aws_init_select2();

    });

    $(document).on( 'click', '[data-aws-add-first-filter]', function(e) {
        e.preventDefault();

        var groupID = awsUniqueID();
        var rulesTemplate = awsGetRuleTemplate.call(this, groupID);

        rulesTemplate = '<table class="aws-rules-table" data-aws-group="' + groupID + '"><tbody>' + rulesTemplate + '</tbody></table>';
        $(this).closest('.aws-rules').prepend( rulesTemplate );
        $(this).closest('.aws-rules').removeClass('aws-rules-empty');
        aws_init_select2();

    });

    $(document).on('change', '[data-aws-param]', function(evt, params) {

        var newParam = this.value;
        var ruleGroup = $(this).closest('[data-aws-rule]');

        var section = ruleGroup.data('aws-filter-section');

        var ruleOperator = ruleGroup.find('[data-aws-operator]');
        var ruleValues = ruleGroup.find('[data-aws-value]');
        var ruleParams = ruleGroup.find('[data-aws-param]');
        var ruleSuboptions = ruleGroup.find('[data-aws-suboption]');

        var ruleInputName = ruleGroup.data('input-name');
        var ruleID = ruleGroup.data('aws-rule');
        var groupID = $(this).closest('[data-aws-group]').data('aws-group');

        ruleGroup.addClass('aws-pending');

        if ( ruleSuboptions.length ) {
            ruleSuboptions.remove();
            ruleGroup.find('.select2-container').remove();
        }

        $.ajax({
            type: 'POST',
            url: aws_vars.ajaxurl,
            dataType: "json",
            data: {
                action: 'aws-getRuleGroup',
                name: newParam,
                inputName: ruleInputName,
                section: section,
                ruleID: ruleID,
                groupID: groupID,
                _ajax_nonce: aws_vars.ajax_nonce
            },
            success: function (response) {
                if ( response ) {

                    ruleGroup.removeClass('adv');

                    if ( typeof response.data.aoperators !== 'undefined' ) {
                        ruleOperator.html( response.data.aoperators );
                    }

                    if ( typeof response.data.avalues !== 'undefined' ) {
                        ruleValues.html( response.data.avalues );
                    }

                    if ( typeof response.data.asuboptions !== 'undefined' ) {
                        ruleParams.after( response.data.asuboptions );
                        ruleGroup.addClass('adv');
                    }

                    ruleGroup.removeClass('aws-pending');

                    aws_init_select2();

                }
            }
        });

    });

    $(document).on('change', '[data-aws-suboption]', function(evt, params) {

        var suboptionParam = this.value;
        var ruleGroup = $(this).closest('[data-aws-rule]');

        var section = ruleGroup.data('aws-filter-section');

        var ruleParam = ruleGroup.find('[data-aws-param] option:selected').val();
        var ruleValues = ruleGroup.find('[data-aws-value]');

        var ruleInputName = ruleGroup.data('input-name');
        var ruleID = ruleGroup.data('aws-rule');
        var groupID = $(this).closest('[data-aws-group]').data('aws-group');

        ruleGroup.addClass('aws-pending');

        $.ajax({
            type: 'POST',
            url: aws_vars.ajaxurl,
            dataType: "json",
            data: {
                action: 'aws-getSuboptionValues',
                param: ruleParam,
                suboption: suboptionParam,
                section: section,
                ruleID: ruleID,
                groupID: groupID,
                inputName: ruleInputName,
                _ajax_nonce: aws_vars.ajax_nonce
            },
            success: function (response) {
                if ( response ) {
                    ruleValues.html( response.data );
                    ruleGroup.removeClass('aws-pending');
                    aws_init_select2();
                }
            }
        });

    });


    // Image upload
    $('.image-upload-btn').click(function(e) {

        e.preventDefault();

        var container = $(this).closest('td');
        var size = $(this).data('size');
        var custom_uploader;

        //If the uploader object has already been created, reopen the dialog
        if (custom_uploader) {
            custom_uploader.open();
            return;
        }

        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false,
            type : 'image'
        });

        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader.on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            //console.log(attachment);

            var image_size = attachment.sizes['full'];

            if ( attachment.sizes[size] ) {
                image_size = attachment.sizes[size];
            } else if ( attachment.sizes['woocommerce_gallery_thumbnail'] ) {
                image_size = attachment.sizes['woocommerce_gallery_thumbnail'];
            } else if ( attachment.sizes['woocommerce_thumbnail'] ) {
                image_size = attachment.sizes['woocommerce_thumbnail'];
            }

            var image_src = image_size.url;

            container.find('.image-hidden-input').val(image_src);
            container.find('.image-preview').attr('src', image_src ).addClass('full');
        });

        //Open the uploader dialog
        custom_uploader.open();

    });


    $('.image-remove-btn').click(function(e) {
        e.preventDefault();

        var container = $(this).closest('td');

        container.find('img').attr('src', '').removeClass('full');
        container.find('.image-hidden-input').val('');

    });

    // Rename instance
    $('.aws-instance-name').on( 'click', function(e) {

        var self = $(this);

        var name = self.text();
        var newName = prompt( 'Type new name for this search form', name );
        var instanceId = self.data('id');

        if ( newName && ( name !== newName ) ) {

            $.ajax({
                type: 'POST',
                url: aws_vars.ajaxurl,
                data: {
                    action: 'aws-renameForm',
                    id: instanceId,
                    name: newName,
                    _ajax_nonce: aws_vars.ajax_nonce
                },
                dataType: "json",
                success: function (data) {
                    self.text(newName);
                    $('input[name="search_instance"]').val(newName);
                }
            });

        }

    });

    // Make instance main
    $('.aws-table.aws-form-instances .featured').on( 'click', function(e) {

        e.preventDefault();

        var self = $(this);
        var instanceId = self.data('id');
        var enabled = '0';

        if ( self.hasClass('is-featured') ) {
            enabled = '1';
            $('.aws-table.aws-form-instances .featured').removeClass('is-featured')
        } else {
            $('.aws-table.aws-form-instances .featured').removeClass('is-featured')
            self.addClass('is-featured');
        }

        $.ajax({
            type: 'POST',
            url: aws_vars.ajaxurl,
            data: {
                action: 'aws-makeMainForm',
                id: instanceId,
                enabled: enabled,
                _ajax_nonce: aws_vars.ajax_nonce
            },
            dataType: "json",
            success: function (data) {
            }
        });

    });

    // Copy instance
    $('.aws-table.aws-form-instances .aws-actions .copy').on( 'click', function(e) {

        e.preventDefault();

        var self = $(this);
        var instanceId = self.data('id');

        self.addClass('loading');

        $.ajax({
            type: 'POST',
            url: aws_vars.ajaxurl,
            data: {
                action: 'aws-copyForm',
                id: instanceId,
                _ajax_nonce: aws_vars.ajax_nonce
            },
            dataType: "json",
            success: function (data) {
                location.reload();
            }
        });

    });

    // Remove instance
    $('.aws-table.aws-form-instances .aws-actions .delete').on( 'click', function(e) {

        e.preventDefault();

        var self = $(this);
        var instanceId = self.data('id');

        if ( confirm( "Are you sure want to delete this search form?" ) ) {

            self.addClass('loading');

            $.ajax({
                type: 'POST',
                url: aws_vars.ajaxurl,
                data: {
                    action: 'aws-deleteForm',
                    id: instanceId,
                    _ajax_nonce: aws_vars.ajax_nonce
                },
                dataType: "json",
                success: function (data) {
                    location.reload();
                }
            });

        }

    });

    // Add instance
    $('.aws-insert-instance-btn').on( 'click', function(e) {

        e.preventDefault();
        e.stopPropagation();

        $(this).addClass('aws-loading');

        $.ajax({
            type: 'POST',
            url: aws_vars.ajaxurl,
            data: {
                action: 'aws-addForm',
                _ajax_nonce: aws_vars.ajax_nonce
            },
            dataType: "json",
            success: function (data) {
                location.reload();
            }
        });

    });

    // Clear cache
    $('#aws-clear-cache .button').on( 'click', function(e) {

        e.preventDefault();

        var $clearCacheBlock = $(this).closest('#aws-clear-cache');

        $clearCacheBlock.addClass('aws-loading');

        $.ajax({
            type: 'POST',
            url: aws_vars.ajaxurl,
            data: {
                action: 'aws-clear-cache',
                _ajax_nonce: aws_vars.ajax_nonce
            },
            dataType: "json",
            success: function (data) {
                $clearCacheBlock.removeClass('aws-loading');
                alert('Cache cleared!');
            }
        });

    });

    // Reindex table
    var $reindexBlock = $('#aws-reindex');
    var $reindexBtn = $('#aws-reindex .button');
    var $reindexProgress = $('#aws-reindex .reindex-progress');
    var $reindexCount = $('#aws-reindex-count strong');
    var syncStatus;
    var processed;
    var toProcess;
    var processedP;
    var syncData = false;

    // Reindex table
    $reindexBtn.on( 'click', function(e) {

        e.preventDefault();

        syncStatus = 'sync';
        processed  = 0;
        toProcess  = 0;
        processedP = 0;

        $reindexBlock.addClass('loading');
        $reindexProgress.html ( processedP + '%' );

        sync('start');

    });


    function sync( data ) {

        $.ajax({
            type: 'POST',
            url: aws_vars.ajaxurl,
            data: {
                action: 'aws-reindex',
                data: data,
                _ajax_nonce: aws_vars.ajax_nonce
            },
            dataType: "json",
            timeout:0,
            success: function (response) {
                if ( 'sync' !== syncStatus ) {
                    return;
                }

                toProcess = response.data.found_posts;
                processed = response.data.offset;

                processedP = Math.floor( processed / toProcess * 100 );
                if ( processedP > 100 ) {
                    processedP = 100;
                }

                syncData = response.data;

                if ( 0 === response.data.offset && ! response.data.start ) {

                    // Sync finished
                    syncStatus = 'finished';

                    console.log( response.data );
                    console.log( "Reindex finished!" );

                    $reindexBlock.removeClass('loading');

                    $reindexCount.text( response.data.found_posts );

                } else {

                    console.log( response.data );

                    $reindexProgress.html ( processedP + '%' );

                    // We are starting a sync
                    syncStatus = 'sync';

                    sync( response.data );
                }

            },
            error : function( jqXHR, textStatus, errorThrown ) {
                console.log( "Request failed: " + textStatus );

                if ( textStatus == 'timeout' || jqXHR.status == 504 ) {
                    console.log( 'timeout' );
                    if ( syncData ) {
                        setTimeout(function() { sync( syncData ); }, 1000);
                    }
                } else if ( textStatus == 'error') {
                    if ( syncData ) {

                        if ( 0 !== syncData.offset && ! syncData.start ) {
                            setTimeout(function() { sync( syncData ); }, 3000);
                        }

                    }
                }

            },
            complete: function ( jqXHR, textStatus ) {
            }
        });

    }

    // Dismiss welcome notice

    $( '.aws-welcome-notice.is-dismissible' ).on('click', '.notice-dismiss', function ( event ) {

        $.ajax({
            type: 'POST',
            url: aws_vars.ajaxurl,
            data: {
                action: 'aws-hideWelcomeNotice',
                _ajax_nonce: aws_vars.ajax_nonce
            },
            dataType: "json",
            success: function (data) {
            }
        });

    });

});