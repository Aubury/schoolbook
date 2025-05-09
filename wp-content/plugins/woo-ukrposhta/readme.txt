=== Woo Ukrposhta ===
Contributors: bandido, olegkovalyov
Tags: ukrposhta, delivery, укрпошта, укрпочта, міжнародна, доставка
Requires at least: 5.0
Tested up to: 6.6
Requires PHP: 7.0
Stable tag: 1.17.7
WC tested up to: 8.8
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Створюйте експрес-накладні автоматично, на сторінці замовлення. 10% знижка  на відправлення, створені онлайн за допомогою API Ukrposhta. Менше часу витрачайте на відправку.
Плагін також додає спосіб доставки "на відділення Укрпошти". При оформленні замовлення, покупці вводять потрібні поля.

== Опис ==
Для отримання ключа API потрібно зв'язатись з менеджером у вашому регіоні за <a href="https://ukrposhta.ua/kontakti-menedzheriv-po-roboti-z-korporativnimi-kliyentami/">Посиланням</a>. Процедура отримання ключа займає деякий час, тому що з ключем у вас може з'явитися можливість створювати міжнародні відправлення, а це потребує деякого часу на оформлення.
Плагін додає варіант доставки Укрпошти у вибір способів доставки на сторінці оформлення замовлення Woocommerce. Після активації плагіну спосіб доставки потрібно активувати в настройках доставки Woocommerce.
Також на сторінці самого замовлення в адмінпанелі можна сформувати нове відправлення та отримати номер накладної.
Відповідно, для роботи цього плагіна, потрібен плагін Woocommerce.



== Інструкція ==

1. Встановіть і активуйте плагін

2. На сторінці налаштувань плагіна, введіть ключі УкрПошти

3. Заповніть форму настройок плагіну

4. Перейдіть на сторінку замовлення та згенеруйте відправлення


== Відеоогляд ==

<iframe width="847" height="480" src="https://www.youtube.com/embed/OfrHW7BspY0" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>


== Підтримка ==

Якщо виникла помилка при встановленні чи використанні плагіна - пишіть на hello@morkva.co.ua або в нашу групу в Facebook https://www.facebook.com/groups/morkvasupport/.



== Frequently Asked Questions ==

= В якому форматі вводити номер телефону одержувача? =

Вводьте в форматі 0987654321

= Чи потрібно мені вводити дані відправника кожного разу? =

Дані відправника ви вводите один раз.

= Чи потрібно мені вводити дані отримувача кожного разу? =

Так, проте про-версія плагіну використовує дані з форми замовлення.

= Чи рахує плагін вартість доставки? =

Ні, плагін лише формує накладну. Планується в майбутніх версіях

= Чи зберігається номер накладної? =

Так, номер накладної зберігається в замовленні.

= Чи можна змінити платника? =

Так.

= Чи можна змінити оголошену вартість? =

Так.




== Screenshots ==

1. Створення накладної. Для переходу на цю сторінку натисніть кнопку "Створити накладну" на сторінці замовлення
2. Сторінка настройок
3. Сторінка замовлень з індикаторами служб доставок
4. Сторінка з накладними
5. Сторінка замовлення

== Що нового? ==

= 1.17.7 =
* WP 6.6 - сумісний

= 1.17.6 =
* [fix] виправили вивід метабокса аресної доставки

= 1.17.5 =
* WooCommerce 8.8 - сумісний
* WP 6.5 - сумісний

= 1.17.4 =
* [hotfix] виправили помилку створення накладної

= 1.17.3 =
* [hotfix] виправили помилку відсутністі поля країна

= 1.17.2 =
* [hotfix] виправили вагу у формі створення ТТН

= 1.17.1 =
* [fix] виправили сумістність HPOS
* [new] додали По-батькові для Відправника

= 1.17.0 =
* [dev] синхронізували код з Про-версією
* перевірили сумісність з WordPress 6.4
* перевірили сумісність з WooCommerce 8.2

= 1.16.18 =
* [dev] оновлений freemius sdk до v2.4.3

= 1.6.17 =
* [fix] виправлена логіка плагіну, коли на сайті видалене поле Країна зі сторінки Checkout
* [new] доданий переклад для плейсхолдерів Індекс відділення та Населений пункт плагіну

= 1.6.15 =
* [new] змінений алгоритм друку стікера

= 1.6.14 =
* [new] прибрана валідація назви міста за введеним індексом відділення

= 1.6.11 =
* [new] виправлені дрібні помилки

= 1.6.10 =
* [new] змінений алгоритм створення накладної

= 1.6.9 =
* [new] Розширена валідація полів Індекс відділення та Населений пункт для доставки на Відділення. В цих полях можна застосовувати апостроф та букви будь-якого регістру для відправлень по Україні.

= 1.6.8 =
* [fix] Виправлена валідація полів Індекс відділення та Населений пункт

= 1.6.7 =
* [fix] Доопрацьована валідація полів Прізвище форми створення відправлення

= 1.6.6 =
* [fix] Виправлені дрібні помилки

= 1.6.5 =
* Перевірена підтримка WordPress 5.6

= 1.6.2 =
* [fix] оптимізований вивід останніх створених накладних на сторінці Мої накладні

= 1.6.0 =
* [new] перероблений функціонал розрахунку вартості доставки і створення накладних як по Україні, так і за кордон
* [new] адресна доставка тепер додається окремим способом доставки через меню WooCommerce - Налаштування - Доставка
* [fix] в 'Заявлену цінність' більше не включається вартість доставки

= 1.5.12 =
* [new] оновлений розрахунок вартості доставки на відділення (по Україні)

= 1.5.11 =
* [new] додана можливість друку стікерів відправлень по Україні зі сторінки 'Редагувати замовлення'

= 1.5.10 =
* [new] додана можливість створення замовлення з Відправником - Фізична особа-підприємець (ФОП)

= 1.5.9 =
* [new] змінено правило валідації поштового коду відправлення

= 1.5.8 =
* [new] додана перевірка нульової ваги при створенні накладних

= 1.5.7 =
* [fix] виправлений розрахунок міжнародних відправлень

= 1.5.5 =
* [new] додана можливість створювати Відправника юридичну особу
* [new] змінений вигляд сторінки Налаштування
* [new] друк форми 103а для поштових відправлень EXPRESS та STANDARD від двох відправлень

= 1.5.3 =
* [new] міжнародні відправленя можна створювати для всіх типів упаковки

= 1.5.0 =
* [new] додана можливість створення міжнародних відправлень
* [fix] виправлена валідація на кирилічні символи для полів при створенні відправлень по Україні
* [fix] в таблиці на сторінці Мої відправлення Укрпошти сформовані відправлення виводяться в звороній хронологінчній послідовності

= 1.4.17 =
* [new] поле Населений пункт отримувача на сторінці Оформлення замовлення тепер можна вводити українською, російською та англійською мовами
* [fix] додана назва Адреса для відповідного поля при адресній доставці

= 1.4.16 =
* [new] додана групова дія Друкувати

= 1.4.15 =
* [new] одиниці виміру ваги відповідають налаштуваням у WooCommerce
* [new] валідація поля Населений пункт по поштовому індексу
* [new] валідація поля Індекс Відділення за регулярним виразом

= 1.4.14 =
* [new] додана часткова валідація полів Індекс Відділення та Населений пункт

= 1.4.13 =
* [new] додані англійські та російські переклади полів для сторінки Checkout
* [fix] виправлення деяких недоліків

= 1.4.12 =
* [new] додана можливість міняти назву способу доставки

= 0.5.4 =
* [new] базова багатомовність

= 0.5.2 =
* [fix] сумісність wordpress 5.3
* [fix] тепер в кожній створеній накладній огляд буде дозволено

= 0.5 =
* [fix] сумісність woocommerce 3.7
* [fix] виправлення помилок


= 0.4 =
* [fix] замінено короткі теги php на довгі
* [fix] виправлення помилок

= 0.3 =
* [new] генеруються накладні укрпошти. для повного використання можливостей плагіну потірбно підписати договір з Укрпоштою

= 0.2 =
* [new] додано підтримку системи freemius

= 0.1 =
* [new] спосіб доставки Укрпошта
