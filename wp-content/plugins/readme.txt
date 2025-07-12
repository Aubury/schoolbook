=== Morkva Liqpay Extended ===
Contributors: bandido, dpmine
Plugin Name: Morkva Liqpay Extended
Tags: LiqPay, Ликпей, Лікпей
Tested up to: 6.8
Stable tag: 0.8.2
WC tested up to: 9.8.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Платіжний модуль LiqPay з callback.

== Description ==

Функціонал плагіну:


* можливість додати спосіб оплати Liqpay

* можливість додати опис способу доставки

* можливість додати ключі API

* callback при success i non-success трансакціях (ідповідно статус замовлення буде або processing або cancelled)

* тестовий режим і поля вводу тестових ключів

* валюта лише гривня

* інтерфейс чекауту лише український


Потрібна підтримка чи додатковий функціонал? support@morkva.co.ua

= 0.8.2 =
* WP 6.8 - сумісний
* WooCommerce 9.8 - сумісний

= 0.8.1 =
* [new] додали налаштування Hold

= 0.8.0 =
* [new] додали запис параметрів rrn_debit та authcode_debit для формування чеків
* [fix] виправили deprecated-функції
* перевірили сумісність з WooCommerce 9.6

= 0.7.2 =
* [new] додали налаштування статуса замовлення після успішної оплати

= 0.7.1 =
* WP 6.7 - сумісний
* WooCommerce 9.4 - сумісний

= 0.7.0 =
* [ui] невеликі зміни в інтерфейсі
* [new] додали лого Лікпей до налаштувань

= 0.6.1 =
* WP 6.6 - сумісний

= 0.6.0 =
* [new] додали підтримку Checkout Blocks

= 0.5.2 =
* WooCommerce 8.8 - сумісний
* WP 6.5 - сумісний

= 0.5.1 =
* [new] виправили callback

= 0.5.0 =
* [new] додали запис sender_card_type

= 0.4.2 =
* [hotfix] виправили помилку callback

= 0.4.1 =
* [hotfix] виправили тестовий режим

= 0.4.0 =
* [new] додати тестовий режим лише для адмінів

= 0.3.3 =
* [new] додали збереження полів callback LiqPay

= 0.3.2 =
* [new] додали збереження полів sender_card_mask2 та sender_card_type

= 0.3.1 =
* [fix] виправили сумістність HPOS

= 0.3.0 =
* [new] додали підтримку High-Performance Order Storage (HPOS)

= 0.2.0 =
* [new] додали функціонал отримання і запису вартості замовлення в грн

= 0.0.2 =
* перевірено сумісність з WordPress 6.3