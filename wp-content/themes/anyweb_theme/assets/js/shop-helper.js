function getCookie(name) {
    let matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}



const savedFilters = getCookie('products_filters');
const countElementBlock = document.querySelector('.countelementblock');
const countElement = document.querySelector('#countelement');
let bxFilterText = document.querySelector('.bx-filter-text');
const pagination = document.querySelector('.woocommerce-pagination');
const step_pagination = 12;

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
function applyFiltersRaw(filters, {push = true, reload = false} = {}) {
    // если вдруг прилетела строка — парсим
    if (typeof filters === 'string') {
        try { filters = JSON.parse(filters); } catch(e) { return; }
    }

    const params = new URLSearchParams();

    for (const [key, val] of Object.entries(filters)) {
        if (val == null) continue;
        const arr = Array.isArray(val) ? val : [val];
        arr.filter(v => v != null && String(v).trim() !== '')
            .forEach(v => params.append(key, v));
    }

    const newUrl = location.pathname + (params.toString() ? '?' + params.toString() : '');
    push ? history.pushState({}, '', newUrl) : history.replaceState({}, '', newUrl);
    if (reload) location.href = newUrl;
    get_count_products();
}


/* ========================= Примеры использования ========================= */

// 1) Ваш объект из скрина:


let currentFilters = null;
if (savedFilters && savedFilters !== 'null') {
    try {
        currentFilters = JSON.parse(savedFilters);
    } catch(e) {
        currentFilters = null;
    }
}

if (currentFilters) {
    applyFiltersRaw(currentFilters, { push: true, reload: false });
}

function restoreFiltersFromCookie() {
    const saved = getCookie('products_filters');
    if (!saved || saved === 'null') return;

    try {
        const filters = JSON.parse(saved);
        if (filters && Object.keys(filters).length) {
            applyFiltersRaw(filters, { push: true, reload: false });
        }
    } catch(e) {}
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

function get_count_products() {

    let cookie_products = JSON.parse(getCookie('products'));
    let total = cookie_products.length;

   if (total > 0) {
       countElementBlock.classList.remove('hidden');
       bxFilterText.classList.remove("hidden-non");
       countElement.classList.remove('hidden');
       countElement.innerHTML = total;
   }

    if (total < step_pagination) {
        pagination.style.display('none');
    } else {
        pagination.style.display('block');
    }

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







