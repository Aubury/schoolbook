<?php
/**
 * Registering callbacks for settings admin page
 *
 * @link        http://morkva.co.ua/
 * @since       1.0.0
 *
 * @package     morkvaup-plugin
 * @subpackage  morkvaup-plugin/includes
 */
/**
 * Registering callbacks for settings admin page
 *
 *
 * @package    morkvaup-plugin
 * @subpackage morkvaup-plugin/includes
 * @author     MORKVA <hello@morkva.co.ua>
 */
 class MUP_Plugin_Callbacks {
 	public function adminDashboard()
	{
		return require_once( "$this->plugin_path/templates/admin.php" );
	}

	public function adminInvoice()
	{
		return require_once( "$this->plugin_path/templates/invoice.php" );
	}

	public function adminSettings()
	{
		return require_once( "$this->plugin_path/templates/taxonomy.php" );
	}

	public function morkvaupOptionsGroup( $input )
	{
		return $input;
	}

	public function morkvaupAdminSection()
	{
		echo 'Введіть свій API ключ для початку щоб плагін міг працювати.';
	}

  public function morkvaupAuthBearer()
	{
		$value = esc_attr( get_option( 'production_bearer_ecom' ) );
		echo '<input type="text" class="regular-text" name="production_bearer_ecom" value="' . $value . '" placeholder="API ключ">';
		echo '';
	}
  public function morkvaupProdBearer()
  {
    $value = esc_attr( get_option( 'production_bearer_status_tracking' ) );
    echo '<input type="text" class="regular-text" name="production_bearer_status_tracking" value="' . $value . '" placeholder="API ключ">';
    echo '';
  }
  public function morkvaupCpToken()
  {
    $value = esc_attr( get_option( 'production_cp_token' ) );
    echo '<input type="password" class="regular-text" name="production_cp_token" value="' . $value . '" placeholder="API ключ">';
    echo '';
  }
  public function morkvaupprinttype()
  {
    $value = esc_attr( get_option( 'proptype' ) );
    $values= array('p','p','p');
    for( $i=0; $i<sizeof($values); $i++){
      if( $i == $value){
        $values[$i] = 'selected';
      }

    }
    echo '
          <select  class="regular-text" name="proptype">
            <option '.$values[0].' value="0">100*100 мм</option>
            <option '.$values[1].' value="1">100*100 мм для друку на форматі А4</option>
            <option '.$values[2].' value="2">100*100 мм для друку на форматі А5</option>
          </select>';
    echo '<p></p>';
  }

public function cityWhAutocreatettn(){
  echo '<input disabled type="checkbox" class="regular-text" /><p>Накладна буде ствона після оформлення замовлення</p>';
}

public function cityWhAutosearch(){
  echo '<input disabled type="checkbox" class="regular-text" /><p>Місто та відділення можна буде вибирати з переліку Select2.</p>';
}

public function morkvaupzone(){
  if ( get_option( 'zone_ukrposhta' ) === false ){
    update_option( 'zone_ukrposhta', 1 );
  }
  $activate = get_option( 'zone_ukrposhta' );
  $checked = $activate;
  $current = 1;
  $echo = false;
  echo '<input '. $activate .' type="checkbox" class="regular-text" name="zone_ukrposhta" value="1" ' . checked($checked, $current, $echo) . ' /><p>Якщо вам потрібно налаштувати зони доставки, перейдіть до <a href="admin.php?page=wc-settings&tab=shipping">налаштувань WooСommerce</a>.</p>';
}

public function mrkvup_default_order_weight_cb() {
    $weight = esc_attr( get_option( 'mrkvup_default_order_weight' ) );
    echo '<input type="text" class="regular-text" name="mrkvup_default_order_weight" value="' . $weight . '" placeholder="Введіть вагу відправлення за замовчуванням">';
    echo '<p>Вказане тут значення буде використовуватись для розрахунку вартості всіх відправлень.<br>Вагу треба вказати в грамах.</p>';
}

public function mrkvup_default_order_length_cb() {
    $length = esc_attr( get_option( 'mrkvup_default_order_length' ) );
    echo '<input type="text" class="regular-text" name="mrkvup_default_order_length" value="' . $length . '" placeholder="Введіть довжину відправлення за замовчуванням">';
    echo '<p>Вказане тут значення буде використовуватись для розрахунку вартості всіх відправлень.<br>Довжину треба вказти в сантиметрах.</p>';
}

  public function morkvaupsenduptype() {
    $packingTypeValue = get_option( 'senduptype' );
    $packingTypesValues = array( 'SMALL_BAG');
    $packingTypeChoice = array( 'Дрібний пакет з відстеженням (до 2 кг)' );
    $addSelectedType = array( ' ', ' ', ' ' );
    for ( $i = 0; $i < sizeof( $packingTypesValues ); $i++ ){
        if ( $packingTypesValues[$i] == $packingTypeValue ){
          $addSelectedType[$i] = 'selected';
        }
    }
    echo '<select ' . $packingTypeValue . ' id="senduptype" name="senduptype">';
    for( $i = 0; $i < sizeof( $packingTypesValues ); $i++) {
        echo '<option '. $addSelectedType[$i] . ' value="' . $packingTypesValues[$i] . '">' . $packingTypeChoice[$i] . '</option>';
    }
    echo '</select>';
  }

    public function morkvaupsendtype()
  {
    $value = esc_attr( get_option( 'sendtype' ) );
    $values= array('p','p');
    $sendtypes = array('EXPRESS', 'STANDARD');
    for( $i=0; $i<sizeof($values); $i++){
      if( $sendtypes[$i] == $value){
        $values[$i] = 'selected';
      }

    }
    echo '
          <select  class="regular-text" name="sendtype">
            <option '.$values[0].' value="EXPRESS">EXPRESS</option>
            <option '.$values[1].' value="STANDARD">STANDARD</option>
          </select>';
    echo '<p></p>';
  }

  // public function morkvaupsendwtype()
  // {
  //   $value = esc_attr( get_option( 'sendwtype' ) );
  //   $values= array('p','p');
  //   $sendtypes = array('W2W', 'W2D');
  //   for( $i=0; $i<sizeof($values); $i++){
  //     if( $sendtypes[$i] == $value){
  //       $values[$i] = 'selected';
  //     }

  //   }
  //   echo '
  //         <select  class="regular-text" name="sendwtype">
  //           <option '.$values[0].' value="W2W">Відділення - Відділення</option>
  //           <option '.$values[1].' value="W2D">Відділення - Двері</option>
  //         </select>';
  //   echo '<p></p>';
  // }

  public function morkvaupActivateen() {
		echo '<input disabled type="checkbox" class="regular-text" /><p>Якщо не обрати цей пункт, створення накладної Укрпошти буде доступне лише для методу доставки Укрпошти</p>';
	}
  public function morkvaupukrposhta_calculate_rates() {
    echo '<input disabled type="checkbox" class="regular-text" />
    <p>Ціна доставки буде розраховуватись і додаватись на сторінці Оформлення замовлення.
    Дані беруться з <a target="_blank" href="https://ukrposhta.ua/ua/taryfy-ukrposhta-standart">тарифної таблиці</a>.<br>
    Для розрахунку міжнародної доставки потрібні ключі API,</p>';
  }

  public function morkvaupukrposhta_calculate_rates_currency()
    {
      $value = esc_attr( get_option( 'ukrposhta_calculate_rates_currency' ) );
      $values= array('', '', '' );
      $sendtypes = array('USD','EUR','UAH');
      for( $i=0; $i<sizeof($values); $i++){
        if( $sendtypes[$i] == $value){
          $values[$i] = 'selected';
        }
      }
      echo '
            <select  class="regular-text" name="ukrposhta_calculate_rates_currency">
              <option '.$values[0].' value="USD">USD</option>
              <option '.$values[1].' value="EUR">EUR</option>
              <option '.$values[2].' value="UAH">UAH</option>
            </select>';
      echo '<p></p>';
    }

  public function morkvaupukrposhta_calculate_rates_currency_val() {

    echo '<label><input disabled type="checkbox" class="regular-text"  />Громіздка посилка</label>';
    echo '<br><label><input disabled type="checkbox" class="regular-text" />Авіа доставка</label>';
    echo '<br><label><input disabled type="checkbox" class="regular-text" />Доставка кур\'єром
            <span style="color: #b32d2e;">(тимчасово не доступно)</span></label>';
    echo '<br><label><input disabled type="checkbox" class="regular-text" />Сповіщення SMS по прибутті</label>';

    echo '<p>Опції впливають на вартість доставки.<br>
            Розрахунок доставки відбувається у валюті, вказаній
            <a target="_blank" href="admin.php?page=wc-settings&tab=general">в настройках Woocommerce</a>
        </p>';
  }

  public function morkvaupukrposhta_international_tracked() {

    echo '<label><input disabled type="checkbox" class="regular-text" id="ukrposhta_international_tracked"  /></label>';
  }

	public function morkvaupmorkva_ukrposhta_default_price() {
		$phone = esc_attr( get_option( 'morkva_ukrposhta_default_price' ) );
		echo '<input type="text" class="regular-text" name="morkva_ukrposhta_default_price" value="' . $phone . '" placeholder="">';
    echo '<p>Вказане тут значення буде додаватись до ціни замовлення замість розрахунку ціни ("Додавати вартість доставки до суми замовлення?").<br>Ціна вказується у валюті, встановленій <a target="_blank" href="admin.php?page=wc-settings&tab=general"> в налаштуваннях WooСommerce</a>.</p>';
	}

	public function morkvaupPhone() {
		$phone = esc_attr( get_option( 'phone' ) );
		echo '<input type="text" class="regular-text" name="phone" value="' . $phone . '" placeholder="0901234567">';
		echo '<p>Підказка: основний формат 0987654321 (без +38)</p>';
	}

  public function morkvaupEdrpou() {
    $edrpou = esc_attr( get_option( 'edrpou' ) );
    echo '<input type="text" class="regular-text" name="edrpou" value="' . $edrpou . '" placeholder="Вісім цифр">';
  }

  public function morkvaupTin() {
    $up_tin = esc_attr( get_option( 'up_tin' ) );
    echo '<input type="text" class="regular-text" name="up_tin" value="' . $up_tin . '" placeholder="Десять цифр">';
  }

  public function morkvaup_sender_type() {
    $senderValue = get_option( 'up_sender_type' );
    $senderValues = array( 'INDIVIDUAL' );
    $senderTypeChoice = array( 'Фізичну особу' );
    $addSelectedSender = array( ' ', ' ', ' ' );
    for ( $i = 0; $i < sizeof( $senderValues ); $i++ ){
        if ( $senderValues[$i] == $senderValue ){
          $addSelectedSender[$i] = 'selected';
        }
    }
    echo '<select ' . $senderValue . ' id="up_sender_type" name="up_sender_type">';
    echo '<option value="">Ваш вибір...</option>';
    for( $i = 0; $i < sizeof( $senderValues ); $i++) {
        echo '<option '. $addSelectedSender[$i] . ' value="' . $senderValues[$i] . '">' . $senderTypeChoice[$i] . '</option>';
    }
    echo '</select>';
  }

  public function morkvaupCompanyName() {
    $up_company_name = esc_attr( get_option( 'up_company_name' ) );
    echo '<input type="text" class="regular-text" name="up_company_name" value="' . $up_company_name . '" placeholder="Не більше 60 символів">';
  }

	public function morkvaupNames() {
		$names = esc_attr( get_option( 'names1' ) );
		echo '<input type="text" class="regular-text" name="names1" value="' . $names . '" placeholder="Петренко">';
	}

  public function morkvaupCityLatin() {
    echo '<input disabled type="text" class="regular-text" value="" placeholder="Kyiv">';
  }

  public function mrkvupInvoiceName() {
    echo '<input disabled type="text" class="regular-text"  value="" placeholder="Введіть назву відправлення англійською">';
    echo '<span class="tooltip"><span style="color: #53575e;cursor:help;" class="dashicons dashicons-editor-help"></span>
            <span class="tooltip-text">У назві відпралення англійською забороняється вказувати наступні слова:
                BRYUKI, ACCESSEROISE, ACCESSORIES, GIFT, GIFT BOX, GIFTS, HANDMADE GIFT, KOSTYUM, KURTKA,
                ODEZHDA, PODAROK, PRESENT, PREZENT, SHAPKA, SOLDATYKY, SOUVENIR, SOUVENIR SET, SOUVENIRS,
                SUVENIER, SUVENIR, TAINA, XYDI, Other, item, cadeau, або лише цифри (наприклад 963258).
                Довжина не повинна перевищувати 32 символи.</span>
            </span>';
  }

  public function morkvaupStreetLatin() {
    echo '<input disabled type="text" class="regular-text" value="" placeholder="street">';
  }

  public function morkvaupNumLatin() {
    echo '<input disabled type="text" class="regular-text"  value="" placeholder="Kyiv">';
  }
  public function morkvaupNamesLatin() {
    echo '<input disabled type="text" class="regular-text" value="" placeholder="Петренко Петро Петрович">';
  }

  public function morkvaupNames2() {
    $names = esc_attr( get_option( 'names2' ) );
    echo '<input type="text" class="regular-text" name="names2" value="' . $names . '" placeholder="Петро">';
  }
  public function morkvaupNames3() {
    $names = esc_attr( get_option( 'names3' ) );
    echo '<input type="text" class="regular-text" name="names3" value="' . $names . '" placeholder="Петрович">';
  }

	public function morkvaupFlat() {
		$flat = esc_attr( get_option( 'flat' ) );
		echo '<input type="text" class="regular-text" name="flat" value="' . $flat . '" placeholder="номер">';
	}

	public function morkvaupWarehouseAddress()
	{
		$warehouse = esc_attr( get_option( 'woocommerce_store_postcode' ) );


		echo '<input type="text" class="regular-text" name="warehouse" value="' . $warehouse . '" placeholder="Франка 14" readonly>';
		echo '<p>Налаштування цього поля беруться із <a href="admin.php?page=wc-settings&tab=general">налаштувань Woocommerce </a></p>';
	}

	public function morkvaupInvoiceDescription()
	{
		$invoice_description = get_option('up_invoice_description');

    echo '<textarea  id=td45 name="up_invoice_description" rows="5" cols="54">' . $invoice_description . '</textarea>
<span id=sp1 class=shortspan>+ Вартість</span>
<select class=shortspan id=shortselect>
  <option value="0" disabled selected style="display:none"> + Перелік</option>
  <option value="list" > + Перелік товарів (з кількістю)</option>
  <option value="list_qa"> + Перелік товарів ( з артикулами та кількістю)</option>
</select>
<select class=shortspan id=shortselect2>
  <option value="0" disabled selected style="display:none"> + Кількість</option>
  <option value="qa"> + Кількість позицій</option>
  <option value="q"> + кількість товарів</option>
</select>
<p>значення шорткодів, при натисненні кнопок додаються в кінець текстового поля</p>
';

    $path = MUP_PLUGIN_PATH . 'public/partials/morkvaup-plugin-invoices-page.php';
		if(!file_exists($path)){
		 echo '<p>Функція опису за промовчанням працює у PRO версії. у Free потрібно буде заповнювати опис кожного відправлення вручну.</p>';
		}
	}

	public function morkvaupInvoiceWeight()
	{
		$activate = get_option( 'invoice_weight' );
		$checked = $activate;
		$current = 1;
		$echo = false;
		echo '<input type="checkbox" class="regular-text" name="invoice_weight" value="1" ' . checked($checked, $current, $echo) . ' />';
	}

  public function morkvaupDefaultPayer()
  {
     $value =  get_option( 'morkva_ukrposhta_default_payer' );

     $values= array( 'mrkvup_recipient', 'mrkvup_sender' );
     $volues= array( 'Одержувач','Відправник' );
     $vilues= array('', '');
     for ($i=0; $i<sizeof($values); $i++) {
         if ($values[$i] == $value) {
             $vilues[$i] = 'selected';
         }
     }
     echo '<select '.$value.' id="morkva_ukrposhta_default_payer" name="morkva_ukrposhta_default_payer">
    <p> </p>';
     for ($i=0; $i<sizeof($values); $i++) {
         echo '<option '. $vilues[$i] .' value="'.$values[$i].'">'.$volues[$i].'</option>';
     }
     echo '</select>';
  }

  public function morkvaupSenderIBAN()
  {
      $senderIban = esc_attr( get_option( 'mrkvup_sender_iban' ) );
      echo '<input type="text" class="regular-text" name="mrkvup_sender_iban" value="' . $senderIban . '" placeholder="29 символів">';
      echo '<span class="tooltip"><span style="color:#53575e;cursor:help;" class="dashicons dashicons-editor-help"></span>
        <span class="tooltip-text">У це поле потрібно внести номер розрахункового рахунку Відправника у міжнародному форматі IBAN</span>';
  }

  public function getPayOnDeliveryOnSenderAccount()
  {
      $activate = get_option( 'morkva_ukrposhta_transfer_postpay_to_sender_bank_account' );
      $checked = $activate;
      $current = 1;
      $echo = false;
      echo '<input type="checkbox" class="regular-text" name="morkva_ukrposhta_transfer_postpay_to_sender_bank_account" value="1" ' . checked( $checked, $current, $echo ) . ' />';
      echo '<span class="tooltip"><span style="color:#53575e;cursor:help;" class="dashicons dashicons-editor-help"></span>
        <span class="tooltip-text">Отримати післяплату на розрахунковий рахунок можливо лише для Відправника - юридичної особи або ФОП і
        для типу відправлення STANDARD з оголошеною цінністю.</span>';
  }

    public function morkvaupParcelItemsGlobalHsCode()
    {
        echo '<input disabled type="text" class="regular-text" placeholder="Введіть код ТН ЗЕД для всіх товарів">';
        echo '<span class="tooltip"><span style="color: #53575e;cursor:help;" class="dashicons dashicons-editor-help"></span>
            <span class="tooltip-text">Код товарної накладної зовнішньоекономічної діяльності (ТН ЗЕД)<br>має містити лише цифри (від 6 до 10 цифр).</span>
            </span>';
    }

    public function morkvaupParcelItemsAttrHsCode()
    {
        echo '<form><select disabled id="mrkvup_parcelitems_attr_hscode" >';
        echo '<option value="">' . __( 'Виберіть атрибут товару, що встановлює коди ТН ЗЕД', 'woo-ukrposhta-pro' ) . '</option>';
        echo '</select></form>';
    }

  public function morkvaupCheckoutFieldsPosition() {
    $packingTypeValue = get_option( 'mrkvup_checkout_fields_position' );
    $packingTypesValues = array( 'billing', 'additional' );
    $packingTypeChoice = array( 'Після Платіжні дані', 'Перед Примітки до замовлень' );
    $addSelectedType = array( ' ', ' ' );
    for ( $i = 0; $i < sizeof( $packingTypesValues ); $i++ ){
        if ( $packingTypesValues[$i] == $packingTypeValue ){
          $addSelectedType[$i] = 'selected';
        }
    }
    echo '<select ' . $packingTypeValue . ' id="mrkvup_checkout_fields_position" name="mrkvup_checkout_fields_position">';
    for( $i = 0; $i < sizeof( $packingTypesValues ); $i++) {
        echo '<option '. $addSelectedType[$i] . ' value="' . $packingTypesValues[$i] . '">' . $packingTypeChoice[$i] . '</option>';
    }
    echo '</select>';
  }

	public function morkvaupEmailTemplate()
	{
		$content = get_option( 'morkvaup_email_template' );
		$editor_id = 'morkvaup_email_editor_id';

		wp_editor( $content, $editor_id, array( 'textarea_name' => 'morkvaup_email_template' ) );
	}
	public function morkvaupEmailSubject()
	{
		$subject = get_option( 'morkvaup_email_subject' );

		echo '<input type="text" name="morkvaup_email_subject" class="regular-text" value="' . $subject . '" />';
	}



 }
