function getCookie(name) {
    let matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

function setCookie(name, value, options) {
    options = options || {};
    var expires = options.expires;
    if (typeof expires == "number" && expires) {
        var d = new Date();
        d.setTime(d.getTime() + expires * 1000);
        expires = options.expires = d;
    }
    if (expires && expires.toUTCString) {
        options.expires = expires.toUTCString();
    }
    value = encodeURIComponent(value);
    var updatedCookie = name + "=" + value;
    for (var propName in options) {
        updatedCookie += "; " + propName;
        var propValue = options[propName];
        if (propValue !== true) {
            updatedCookie += "=" + propValue;
        }
    }
    document.cookie = updatedCookie;
}

function changeSidebar() {
    const sidebar = document.querySelector('.sidebar');

    if (!sidebar) {
        return;
    }

    sidebar.classList.toggle(
        'filters-modal',
        window.innerWidth <= 1030
    );
}

changeSidebar();

window.addEventListener('resize', changeSidebar);

new MutationObserver(changeSidebar).observe(document.body, {
    childList: true,
    subtree: true
});

///////////////// FILTERS /////////////////////////////////

const savedFilters = getCookie('products_filters');
const countElementBlock = document.querySelector('.countElementBlock');


const countElement = document.querySelector('#countElement');


const bxFilterText = document.querySelector('.delete-filter-text');
const pagination = document.querySelector('.woocommerce-pagination');
const loadMoreButton = document.querySelector('#load-more');
const step_pagination = 12;

const del_filter= document.querySelector("#del_filter");
const bx_filter_text = document.querySelector('.delete-filter-text');

const h2_title = document.querySelector('.catalog-title');
const product_item_container = document.querySelector('.products');

let filter_chbx= document.querySelectorAll(".filter-chbx");

const mobileResultCount = document.querySelector(
    '.mobile-result-count__number'
)

const filter_count_bubble = document.querySelector(
    '.filter-sorting-holder .bubble'
);


/**
 * Применяет фильтры к URL.
 * @param {Object} filters
 * @param {Object} options
 *  - push: boolean (default true)      // pushState (true) или replaceState (false)
 *  - reload: boolean                   // перезагрузить страницу новым URL
 *  - keepParams: string[]              // какие сторонние параметры сохранить (например, ['utm_source','utm_medium'])
 *  - dropParams: string[]              // какие сторонние параметры удалить
 *  - ... остальные опции см. filtersToParams
 */
function applyFiltersRaw(
    filters,
    {
        push = true,
        reload = false,
        preservePage = false
    } = {}
) {
    if (typeof filters === 'string') {
        try {
            filters = JSON.parse(filters);
        } catch (e) {
            return;
        }
    }

    // Запоминаем страницу из текущего URL.
    const currentParams = new URLSearchParams(
        window.location.search
    );

    const productPage = currentParams.get('product-page');

    const params = new URLSearchParams();

    for (const [key, val] of Object.entries(filters)) {
        if (val == null) {
            continue;
        }

        const values = Array.isArray(val) ? val : [val];

        values
            .filter(value => {
                return value != null &&
                    String(value).trim() !== '';
            })
            .forEach(value => {
                params.append(key, value);
            });
    }

    // При первоначальном восстановлении не удаляем страницу.
    if (preservePage && productPage) {
        params.set('product-page', productPage);
    } else {
        params.delete('product-page');
    }

    const newUrl =
        location.pathname +
        (params.toString() ? '?' + params.toString() : '');

    if (push) {
        history.pushState({}, '', newUrl);
    } else {
        history.replaceState({}, '', newUrl);
    }

    if (reload) {
        location.href = newUrl;
    }

    get_count_products();
}

let currentFilters = null;

if (savedFilters && savedFilters !== 'null') {
    try {
        currentFilters = JSON.parse(savedFilters);
    } catch(e) {
        currentFilters = null;
    }
}

if (currentFilters) {
    applyFiltersRaw(currentFilters, {
        push: false,
        reload: false,
        preservePage: true
    });

    updateSelectedFiltersCount(currentFilters);
}

function restoreFiltersFromCookie() {
    const saved = getCookie('products_filters');

    if (!saved || saved === 'null') {
        return;
    }

    try {
        const filters = JSON.parse(saved);

        if (filters && Object.keys(filters).length) {
            applyFiltersRaw(filters, {
                push: false,
                reload: false,
                preservePage: true
            });
        }
    } catch (e) {
        console.error(e);
    }
}

function restoreFiltersFromCookieToUrl() {
    const raw = getCookie('products_filters');
    if (!raw || raw === 'null') return false;
    let filters;
    try { filters = JSON.parse(decodeURIComponent(raw)); } catch(e) { return false; }

    if (!filters || !Object.keys(filters).length) return false;

    // Собираем плоский URL из объекта (RAW-режим)
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([k, v]) => {
        const arr = Array.isArray(v) ? v : [v];
        arr.filter(Boolean).forEach(val => params.append(k, val));
    });
    const newUrl = location.pathname + '?' + params.toString();
    history.replaceState({}, '', newUrl); // без лишнего шага в истории

    const currentParams = new URLSearchParams(
        window.location.search
    );

    const productPage = currentParams.get('product-page');

    if (productPage) {
        params.set('product-page', productPage);
    }

    return true;
}

function refreshCountFromDataset() {
    const el = document.getElementById('sb-result-count');
    if (!el) return;
    get_count_products();
}

function isShopUrl() {
    const p = window.location.pathname.toLowerCase();
    return /\/(shop|product-category|catalog)(\/|$)/.test(p)
}

function isShopPage() {
    const c = document.body.classList;
    return (
        c.contains('post-type-archive-product') ||  // магазин (архив товаров)
        c.contains('tax-product_cat') ||            // категория товара
        c.contains('tax-product_tag') ||            // метка товара
        Array.from(c).some(cls => cls.startsWith('tax-pa_')) // архивы атрибутов
    );
}

function updatePaginationVisibility(totalProducts) {
    const pagination = document.querySelector(
        '.woocommerce-pagination'
    );

    if (!pagination) {
        return;
    }

    pagination.classList.toggle(
        'pagination-hidden',
        Number(totalProducts) <= step_pagination
    );
}

function getSelectedFiltersCount(filters) {
    if (!filters || typeof filters !== 'object') {
        return 0;
    }

    return Object.entries(filters).reduce(
        function (count, [filterName, values]) {
            // Сортировку не считаем фильтром.
            if (filterName === 'sort') {
                return count;
            }

            const filterValues = Array.isArray(values)
                ? values
                : [values];

            const selectedValues = filterValues.filter(
                function (value) {
                    return (
                        value !== null &&
                        value !== undefined &&
                        String(value).trim() !== ''
                    );
                }
            );

            return count + selectedValues.length;
        },
        0
    );
}

function updateSelectedFiltersCount(filters) {
    const count = getSelectedFiltersCount(filters);

    if (!filter_count_bubble) {
        return count;
    }

    filter_count_bubble.textContent = count;

    return count;
}

function get_count_products() {

    let cookie_products = JSON.parse(getCookie('products'));
    let total = cookie_products.length;
    const selectedFilters = document.querySelectorAll(
        '.filter-chbx[type="checkbox"]:checked'
    );

   if (total > 0 && selectedFilters.length !== 0) {
       countElementBlock.classList.remove('hidden');
       countElement.classList.remove('hidden');
       countElement.innerHTML = total;
       bx_filter_text.classList.remove('hidden-non');
   }

    if (mobileResultCount) {
        mobileResultCount.textContent = total;
    }

    updatePaginationVisibility(total);
}

function detectShopPage() {
    return isShopPage() || isShopUrl();
}

window.addEventListener('popstate', () => {
    if (detectShopPage()) {
        console.log('Это магазин/каталог товаров - popstate');
        restoreFiltersFromCookie();
        get_count_products();
    }
});

window.addEventListener('pageshow', (e) => {
    if ((e.persisted || performance.getEntriesByType('navigation')[0]?.type === 'back_forward') && detectShopPage()) {
        console.log('Это магазин/каталог товаров - pageshow');
        restoreFiltersFromCookie();
        get_count_products();
    }
});

$('.filter-parameters-box-title').on('click', function () {
    $(this).hasClass('opened') ? $(this).removeClass('opened') : $(this).addClass('opened');
})

// Показать/скрыть значения фильтра после первых трёх.
$(document).on('click', '.filter-show-all', function () {
    const $button = $(this);
    const $filterBox = $button.closest('.filter-parameters-box');
    const willExpand = !$filterBox.hasClass('is-expanded');

    $filterBox.toggleClass('is-expanded', willExpand);
    $button.attr('aria-expanded', String(willExpand));
    $button
        .find('.filter-show-all__text')
        .text(willExpand ? 'Згорнути' : 'Дивитися всі');
});

$(document).on('click', '.filter-btn', function() {
    if($(this).hasClass('active')) {
        $(this).removeClass('active');
        $(this).parents('.catalog-section-holder').find('.filter-holder').removeClass('opened-filter');
        $('body').removeClass('filter-is-open');
    } else {
        $(this).addClass('active');
        $(this).parents('.catalog-section-holder').find('.filter-holder').addClass('opened-filter');
        $('body').addClass('filter-is-open');
    }
});

$(document).on('click', '.close-filter', function() {
    if($(this).parents('.catalog-section-holder').find('.filter-btn').hasClass('active')) {
        $(this).parents('.catalog-section-holder').find('.filter-btn').removeClass('active');
        $(this).parents('.catalog-section-holder').find('.filter-holder').removeClass('opened-filter');
        $('body').removeClass('filter-is-open');
    } else {
        $(this).parents('.catalog-section-holder').find('.filter-btn').addClass('active');
        $(this).parents('.catalog-section-holder').find('.filter-holder').addClass('opened-filter');
        $('body').addClass('filter-is-open');
    }
});

function updateFilterResultBlock() {
    const selectedFilters = document.querySelectorAll(
        '.filter-chbx[type="checkbox"]:checked'
    );

    if ( selectedFilters.length === 0 ) {
        countElementBlock.classList.add('hidden');
    } else {
        countElementBlock.classList.remove('hidden');
    }
}

//==================== DELET FILTER =======================//

if(del_filter){
    del_filter.addEventListener('click', function(){
        event.preventDefault();

        filter_chbx.forEach(element => {
            if (element.checked) {
                element.checked = false;
            }
        });

        // Убираем визуальное выделение.
        document
            .querySelectorAll('.label-container.active')
            .forEach(function (element) {
                element.classList.remove('active');
            });

        setCookie('products_filters', null);

        arr = {};
        arr = {
            sort: ['news']
        };

        jQuery(document).ready( function( jQuery ){
            var jqXHR = {
                action:'sjax',
                nonce_code: soJsLet.nonce,
                category: h2_title.dataset.termslug,
                filters: JSON.stringify(arr),
                flag: 'delete_filter',
            }

            jQuery.post( soJsLet.ajaxurl, jqXHR, function( response){
                bx_filter_text.classList.add("hidden-non");
                let backResponse = JSON.parse(response);
                countElementBlock.classList.add("hidden");
                product_item_container.innerHTML = backResponse[1];
                setCookie('products', backResponse[2]);

                /*
                 * Удаляем возможную вторую cookie,
                 * созданную для корня сайта.
                 */
                setCookie('products_filters', '', {
                    expires: -1,
                    path: '/'
                });

                setCookie('products', '', {
                    expires: -1
                });

                setCookie('products', '', {
                    expires: -1,
                    path: '/'
                });

                const products = JSON.parse(backResponse[2]);
                const totalProducts = products.length;

                const totalPages = Math.max(
                    1,
                    Math.ceil(totalProducts / step_pagination)
                );


                if (mobileResultCount) {
                    mobileResultCount.textContent = totalProducts;
                }

                // Возвращаем пагинацию на первую страницу.
                updatePagination(1, totalPages);

                const $loadMoreButton = jQuery('#load-more');

                updatePaginationVisibility(totalProducts);

                updateSelectedFiltersCount(arr);

                // Создаём чистый адрес категории.
                const cleanUrl = new URL(window.location.href);

                cleanUrl.search = '';

                cleanUrl.pathname = cleanUrl.pathname.replace(
                    /\/page\/\d+\/?$/,
                    '/'
                );

                /*
                 * Загружаем чистую страницу.
                 * WordPress заново сформирует товары,
                 * фильтры и пагинацию.
                 */
                window.location.replace(cleanUrl.toString());
            });
        });

    })
}

//==================== ADD FILTER =======================//

function resetCatalogPage() {
    const url = new URL(window.location.href);

    // Если номер записан как параметр.
    url.searchParams.delete('product-page');

    // На случай адреса вида /page/4/.
    url.pathname = url.pathname.replace(
        /\/page\/\d+\/?$/,
        '/'
    );

    window.history.replaceState(
        { catalogPage: 1 },
        '',
        url.toString()
    );
}

var arr = {};

if(filter_chbx){
    filter_chbx.forEach(element => {

        arr[element.dataset.attribute_label] = [];

        element.addEventListener('change', function (evt) {
            var el = evt.target;
            filter_chbx.forEach(element => {
                if (element.checked) {
                    var mayak = true

                    if (arr[element.dataset.attribute_label]!='') {
                        arr[element.dataset.attribute_label].forEach(ele => {
                            if(ele == element.value){
                                mayak = false
                            }
                        });

                        if(mayak) {
                            if(element.name == 'sorting'){
                                arr[element.dataset.attribute_label][0] = element.value;
                            }else{
                                arr[element.dataset.attribute_label].push( element.value)
                            }
                            mayak = true
                        }

                    } else {
                        if(element.name == 'sorting'){
                            arr[element.dataset.attribute_label][0] = element.value;
                        } else {
                            arr[element.dataset.attribute_label].push( element.value);
                        }
                    }
                }
            });

            if (el.checked) {

            } else {
                for (let index = 0 ; index < arr[element.dataset.attribute_label].length; index++) {
                    if(arr[element.dataset.attribute_label][index] == el.value){
                        arr[element.dataset.attribute_label].splice(index, 1)
                    }
                }
            }

            console.log(arr);
            updateFilterResultBlock();
            setCookie('products_filters', JSON.stringify(arr));

            jQuery(document).ready( function( jQuery ){
                var jqXHR = {
                    action:'sjax',
                    nonce_code: soJsLet.nonce,
                    filters: JSON.stringify(arr),
                    category: h2_title.dataset.termslug,
                }
                jQuery.post( soJsLet.ajaxurl, jqXHR, function( response ){

                    let backResponse = JSON.parse(response);

                    countElement.innerHTML = backResponse[0];

                    bx_filter_text.classList.remove("hidden-non");

                    product_item_container.innerHTML = backResponse[1];

                    setCookie('products', backResponse[2]);

                    const filteredProducts = JSON.parse(backResponse[2]);
                    const totalProducts = filteredProducts.length;
                    const totalPages = Math.max(
                        1,
                        Math.ceil(totalProducts / step_pagination)
                    );


                    if (mobileResultCount) {
                        mobileResultCount.textContent = totalProducts;
                    }

                    // После изменения фильтра всегда возвращаемся на страницу 1.
                    resetCatalogPage();

                    // Перестраиваем пагинацию под новое количество товаров.
                    updatePagination(1, totalPages);
                    // При 12 товарах и меньше скроет весь nav.
                    updatePaginationVisibility(totalProducts);

                    updateSelectedFiltersCount(arr);

                    const $loadMore = $('#load-more');

                    if (totalProducts <= step_pagination) {
                        $loadMore
                            .addClass('disabled')
                            .prop('disabled', true);
                    } else {
                        $loadMore
                            .removeClass('disabled loading')
                            .prop('disabled', false)
                            .attr('data-current-page', 1)
                            .attr('data-max-pages', totalPages)
                            .attr('data-next-page', getPageUrl(2));
                    }

                } );
            } );

        }, false);
    });
}

updateFilterResultBlock();

///////////////// PAGINATION //////////////////////////////

if (getCookie('products') !== undefined) {
    var cookie_products = JSON.parse(getCookie('products'));

    var ul_products = $('.products');
    var list_products = ul_products.find('.product-item-list-col-1');
    var show_now = 12;
    var button = $( '#load-more' );
    var	products_on_page = $('.product-item-list-col-1').length;
    var	maxPages = cookie_products.length;

    console.log('cookie_products = ' + cookie_products.length);

    if (products_on_page < maxPages)  {
        button.click( function( event ) {
            event.preventDefault();
            cookie_products = JSON.parse(getCookie('products'));
            products_on_page = $('.product-item-list-col-1').length,
                maxPages = cookie_products.length;
            var new_products = [];

            for (var i = products_on_page; i < (products_on_page + show_now); i++) {
                new_products.push(cookie_products[i]);
            }

            var data = {
                action:'more_products',
                nonce_code: add_more_object.nonce,
                products: new_products,
            }

            $.ajax({
                url : add_more_object.url, // обработчик
                data: data,
                type : 'POST', // тип запроса
                success : function( request, xhr, status, error, data ){
                    if (xhr === "success") {
                        let backResponse = JSON.parse(request);
                        ul_products.append(backResponse);
                        if ($('.product-item-list-col-1').length === maxPages) {
                            button
                                .addClass('loading')
                                .prop('disabled', true);
                        }

                        const loadedProducts = ul_products
                            .find('.product-item-list-col-1')
                            .length;

                        const totalProducts = cookie_products.length;
                        const currentPage = Math.ceil(
                            loadedProducts / show_now
                        );
                        const totalPages = Math.ceil(
                            totalProducts / show_now
                        );

                        updatePagination(
                            currentPage,
                            totalPages
                        );

                        const currentPageUrl = getPageUrl(currentPage);

                        window.history.replaceState(
                            { page: currentPage },
                            '',
                            currentPageUrl
                        );

                    }
                },
                error : function (error) {
                    console.log(error);
                }
            });

        } );
    }
}

function getPageUrl(page) {
    const url = new URL(window.location.href);
    const pageBase = button.attr('data-page-base');

    // Для первой страницы удаляем номер из URL.
    if (page === 1) {
        url.searchParams.delete('product-page');

        url.pathname = url.pathname
            .replace(/\/page\/\d+\/?$/, '/');

        return url.toString();
    }

    if (pageBase && pageBase.includes('%#%')) {
        return pageBase.replace('%#%', page);
    }

    url.pathname = url.pathname
        .replace(/\/page\/\d+\/?$/, '/')
        .replace(/\/+$/, '');

    url.pathname += '/page/' + page + '/';

    return url.toString();
}

function updatePagination(currentPage, totalPages) {
    const $numbers = $(
        '.woocommerce-pagination .page-numbers-container'
    );

    if (!$numbers.length) {
        return;
    }

    const visiblePages = [];

    for (let page = 1; page <= totalPages; page++) {
        if (
            page === 1 ||
            page === totalPages ||
            Math.abs(page - currentPage) <= 1
        ) {
            visiblePages.push(page);
        }
    }

    let html = '';
    let previousPage = 0;

    visiblePages.forEach(function (page) {
        if (
            previousPage &&
            page - previousPage > 1
        ) {
            html +=
                '<li>' +
                '<span class="page-numbers dots">…</span>' +
                '</li>';
        }

        if (page === currentPage) {
            html +=
                '<li>' +
                '<span aria-current="page" ' +
                'class="page-numbers current">' +
                page +
                '</span>' +
                '</li>';
        } else {
            html +=
                '<li>' +
                '<a class="page-numbers" href="' +
                getPageUrl(page) +
                '">' +
                page +
                '</a>' +
                '</li>';
        }

        previousPage = page;
    });

    $numbers.html(html);

    button
        .attr('data-current-page', currentPage)
        .attr('data-max-pages', totalPages);

    updatePaginationArrows(
        currentPage,
        totalPages
    );

    // Записываем текущую страницу в адрес браузера.
    const currentPageUrl = getPageUrl(currentPage);

    window.history.replaceState(
        { catalogPage: currentPage },
        '',
        currentPageUrl
    );
}

function updatePaginationArrows(
    currentPage,
    totalPages
) {
    const $pagination = $('.woocommerce-pagination');

    const $prev = $pagination.find('.prev.page-arrow');
    const $next = $pagination.find('.next.page-arrow');

    if (currentPage > 1) {
        $prev
            .removeClass('disabled')
            .attr('aria-disabled', 'false')
            .attr('href', getPageUrl(currentPage - 1));
    }

    if (currentPage < totalPages) {
        $next
            .removeClass('disabled')
            .attr('aria-disabled', 'false')
            .attr('href', getPageUrl(currentPage + 1));
    } else {
        $next
            .addClass('disabled')
            .attr('aria-disabled', 'true')
            .removeAttr('href');
    }
}

////////////////////////// SORTING //////////////////////////////////////

$(document).on('click', '.sorting-select__button', function (event) {
    event.preventDefault();
    event.stopPropagation();

    const $button = $(this);
    const $sorting = $button.closest('.sorting');
    const isOpened = $sorting.hasClass('opened');

    $('.sorting').not($sorting).removeClass('opened');
    $('.sorting-select__button')
        .not($button)
        .attr('aria-expanded', 'false');

    $sorting.toggleClass('opened', !isOpened);
    $button.attr('aria-expanded', String(!isOpened));
});

$(document).on('click', '.sorting-list label', function () {
    const $label = $(this);
    const $sorting = $label.closest('.sorting');
    const text = $label.data('sorting-text');

    $sorting
        .find('.sorting-select__value')
        .text(text);

    $sorting
        .find('.label-container')
        .removeClass('active');

    $label
        .closest('.label-container')
        .addClass('active');

    $sorting.removeClass('opened');

    $sorting
        .find('.sorting-select__button')
        .attr('aria-expanded', 'false');

    /*
     * Ничего дополнительно запускать не нужно:
     * label переключит связанный radio, после чего сработает
     * существующий обработчик change для .filter-chbx.
     */
});

$(document).on('click', function (event) {
    if (!$(event.target).closest('.sorting').length) {
        $('.sorting').removeClass('opened');

        $('.sorting-select__button')
            .attr('aria-expanded', 'false');
    }
});

var radioButtons = document.querySelectorAll('input[type="radio"]');

radioButtons.forEach(function(radioButton) {
    radioButton.addEventListener('change', function() {
        // Удаляем класс checked у всех label-container
        document.querySelectorAll('.label-container ').forEach(function(labelContainer) {
            labelContainer.classList.remove('active');
        });

        // Проверяем, соотетствует ли label рдиокнопке, которя была выбрана
        var labelForRadio = document.querySelector('label[for="' + this.id + '"]');
        if (labelForRadio) {
            labelForRadio.parentNode.classList.add('active');
            selectedValue = this.value;
            // console.log("Выбрано значение: " + selectedValue)
        }
    });
});

function restoreSortingSelect() {
    const $checked = $('input[name="sorting"]:checked');

    if (!$checked.length) {
        return;
    }

    const inputId = $checked.attr('id');
    const $label = $('.sorting-list label[for="' + inputId + '"]');

    if (!$label.length) {
        return;
    }

    $('.sorting-select__value').text(
        $label.data('sorting-text')
    );

    $('.sorting-list .label-container').removeClass('active');

    $label
        .closest('.label-container')
        .addClass('active');
}

$(restoreSortingSelect);






