jQuery(window).on('load', function() {

  // Checks input 'Вага, кг' on 'Нові відправлення' page on zero.
  function checkInvoiceCargoMass(e) {
    let input = document.getElementById('invoice_cargo_mass');
    if (input.value == 0) {
      input.setCustomValidity('Введіть значення для ваги відправлення.');
    } else {
      input.setCustomValidity(''); // input is fine -- reset the error message
    }
  }
  function checkInvoiceMaxLength(e) {
    let input = document.getElementById('max_order_lenght');
    if (input.value == 0) {
      input.setCustomValidity('Введіть значення для найбільшої сторони відправлення.');
    } else {
      input.setCustomValidity(''); // input is fine -- reset the error message
    }
  }
  if ( "morkvaup_invoice" == location.search.split('page=')[1] && document.querySelector('.morkvaup_checkforminputs') ) {
    document.querySelector('.morkvaup_checkforminputs').addEventListener('click', checkInvoiceCargoMass, true);
    document.querySelector('.morkvaup_checkforminputs').addEventListener('click', checkInvoiceMaxLength, true);
  }

  function morkvaup_ukr(string) { // Check if letters are ukrainian in string
      string = string.replace(/[^а-яА-ЯіІїЇєЄёыэЭ ]/ig,'');
      return string
  }

  function checkSenderCyrillic(e) {
    let valSender = jQuery('#sender_first_name').val();
    if (valSender != morkvaup_ukr(valSender)) {
      e.preventDefault();
      alert('Ім\`я відправника треба писати кирилицею.\nВиправіть це та повторіть спробу.');
    }
  }

  function checkRecipientCyrillic(e) {
    let valRec = jQuery('#rec_first_name').val();
    if (valRec != morkvaup_ukr(valRec)) {
      e.preventDefault();
      alert('Прізвище одержувача треба писати кирилицею.\nВиправіть це та повторіть спробу.');
    }
  }

  function checkInvoiceDescrInterLatin(e) { // Функція наразі не використовується
    // const latinPattern = /^[A-z\u00C0-\u00ff]+$/;
    const latinPattern = /^([A-Za-z\u00C0-\u00D6\u00D8-\u00f6\u00f8-\u00ff\s]*)$/;
    var valInvoiceDescr = jQuery('#up_invoice_description').val();
    if (!latinPattern.test(valInvoiceDescr) && valInvoiceDescr!==undefined) {
      e.preventDefault();
      alert('Додаткову інформацію треба писати латиницею.\nВиправіть це та повторіть спробу.');
    }
  }

  // If Country = Ukraine then #international checkbox is unchecked on morkvaup_invoice page.
  var country_rec = jQuery('#country_rec').val(), recipientCountry;
  if (country_rec) recipientCountry = country_rec;
  else recipientCountry = 'UA'; // Set input field #country_rec value 'UA' when 'Країна' field is absent on Checkout page

  // HTML-form activation for International invoice.
  const checkbox = document.getElementById('up-international');
  if(jQuery('#up-international').prop("checked") == true){
      console.log("Checkbox is checked.");
      document.getElementById('i4x').classList.add('international');
      document.getElementById('i5x').classList.add('international');
      document.getElementById('i6x').classList.add('international');
  } else if(recipientCountry == 'UA') {
      // If Country = Ukraine then cyrillic symbols validation in two fields: #sender_first_name and #rec_first_name (Прізвище).
      if (jQuery('.morkvaup_checkforminputs').length > 0) {
        document.querySelector('.morkvaup_checkforminputs').addEventListener('click', checkSenderCyrillic, true);
        document.querySelector('.morkvaup_checkforminputs').addEventListener('click', checkRecipientCyrillic, true);
      }
  }
  if (!!checkbox) {
    checkbox.addEventListener('change', (event) => {
      if (event.target.checked) {
        console.log('inter checked')
        document.getElementById('i4x').classList.add('international');
        document.getElementById('i5x').classList.add('international');
        document.getElementById('i6x').classList.add('international');
        // If Country != Ukraine then latin symbols validation in field: #up_invoice_description (Додаткова інформація).
        // document.querySelector('.morkvaup_checkforminputs').addEventListener('click', checkInvoiceDescrInterLatin, true);
      } else {
        console.log('inter not checked')
        document.getElementById('i4x').classList.remove('international');
        document.getElementById('i5x').classList.remove('international');
        document.getElementById('i6x').classList.remove('international');

      }
    })
  }

  var sp1 = document.getElementById("sp1");
  if (sp1) {
    sp1.addEventListener("click", function() {
      textareavalue = jQuery('#td45').val();
      var va = 'p';
      jQuery('#td45').val(textareavalue + ' [' + va + ']')
    });

    jQuery("select#shortselect").change(function() {
      textareavalue = jQuery('#td45').val();
      va = jQuery(this).val();
      jQuery('#td45').val(textareavalue + ' [' + va + ']')
    });

    jQuery("select#shortselect2").change(function() {
      textareavalue = jQuery('#td45').val();
      va = jQuery(this).val();
      jQuery('#td45').val(textareavalue + ' [' + va + ']')

    });


  }


  jQuery(function() {

    jQuery('.formsubmitup').on('click', function(e) {
      att = jQuery(this).attr('alert');
      if (att != '') {
        alert(att);
      }

      // jQuery(this).parent().submit();
      jQuery(this).parent().trigger("submit");
    });
    jQuery('.handlediv').on('click', function(e) { //when content of metabox couldnt be open
      //jQuery(this).parent().toggleClass('closed');
      aria = jQuery(this).attr('aria-expanded');
      if (aria == 'true') {
        //jQuery(this).attr('aria-expanded', 'false');
      } else {
        //jQuery(this).attr('aria-expanded', 'true');
      }
    });


    jQuery('#invoice_other_fields .insideup .button').on('click', function(e) {



      text = jQuery(this).text();
      console.log(text);
      if (text == ' Друк накладної') {
        text = 'Ви дійсно бажаєте друкувати накладну';
        console.log('text1');
      }
      if (text == ' Друк стікера') {
        text = 'Ви дійсно бажаєте друкувати стікер';
        console.log('text2');
      }
      if (text == 'Відпралення...') {
        text = 'Ви дійсно бажаєте Відправити на e-mail';
        console.log('text3');
      }
      if (!confirm(text + '?')) {
        e.preventDefault();
        alert("Операцію відхилено");
      }
    });

  });



  var MyDiv1 = document.getElementById("messagebox");
  if (MyDiv1) {
    var h = MyDiv1.getAttribute('data');
    //h-=20;
    //var MyDiv2 = document.getElementById('messagebox');
    //MyDiv2.innerHTML = MyDiv1.innerHTML;
    //MyDiv2.style.height = h + 'px';
    MyDiv1.style.height = h + 'px';
    MyDiv1.style.padding = 8 + 'px';
    //MyDiv1.childNodes[0].style.padding = 0 ;
    //MyDiv2.classList.add('error');
  }

  var MyDiv3 = document.getElementById("nnnid");
  if (MyDiv3) {
    MyDiv3 = document.getElementById("nnnid");
    var h = 182 + 'px';
    console.log(h);
    var MyDiv4 = document.getElementById('messagebox');
    MyDiv4.innerHTML = MyDiv3.innerHTML;
    MyDiv4.style.height = h;
    MyDiv4.style.padding = '8px';
    MyDiv4.classList.add('updated');
  }


});


// Mrk_UP_Myttn_List_Table Bulk actions
jQuery(document).ready(function() {

  var isСheckbup = document.getElementsByClassName("checkbup");

      if(isСheckbup){

        var newarr = [];
        // Check life cycle status (CREATED, REGISTERED)
        jQuery.each(jQuery(".startcodeup"), function(){
          if(jQuery(this).attr('codeup')=='created'){
            newarr.push(jQuery(this).attr('ttnup'));
          }
        });

        jQuery('#bulklistnewup,#bulklistnewup2').val( newarr.join(",") );

        jQuery("#bulk-action-selector-top").on( "change", function(){
          // Bulk_delete action on change
          var chkdarrLength = jQuery('input[name=bulklistup], input[name=bulklistup2]').val().split(',').length;
          var bulkActionName = document.getElementById("bulk-action-selector-top").value; console.log('bulkActionName = ' + bulkActionName);
          if (('bulk_delete' == bulkActionName)) {
            // jQuery('.bulk_actions_form').removeAttr('target');
            jQuery('.bulk_actions_form').prop( "target", false );
          } else if (('bulk_print' == bulkActionName)) {
            jQuery('.bulk_actions_form').attr('target', '_blank');
          }
          if ((chkdarrLength > 1)  && ('bulk_delete' == bulkActionName)) {
            alert('Групова дія "Видалити" знаходиться у розробці.' );
          }
        }); // onchange function

        jQuery("input.checkbup").on( "change", function(){

          var favorite = [];
          var favoritedelete = [];
          jQuery.each(jQuery(".checkbup:checked"), function(){
          favorite.push(jQuery(this).val());
          favoritedelete.push(jQuery(this).attr('valuedup'));
          });
          tir = favorite.join(",");
          jQuery('#bulklistup, #bulklistup2').val( tir );
          jQuery('#bulklistdeleteup, #bulklistdeleteup2').val( favoritedelete.join(",") );

          if (jQuery(this).is(":checked")) {
            jQuery(this).attr('checked', 'checked');
          } else {
            // jQuery(this).removeAttr('checked');
            jQuery(this).prop( "checked", false );
          }

          var chkdarrLength = jQuery('input[name=bulklistup], input[name=bulklistup2]').val().split(',').length;
          var bulkActionName = document.getElementById("bulk-action-selector-top").value; console.log('bulkActionName = ' + bulkActionName);
          // Bulk_print action
          if (chkdarrLength > 1 && ('bulk_print' == bulkActionName)) {
            jQuery('.bulk_actions_form2, .bulk_actions_form').attr( 'action', '/wp-content/plugins/woo-ukrposhta-pro/admin/partials/pdfbulkprint.pdf' );
          } else {
            jQuery('.bulk_actions_form2, .bulk_actions_form').attr( 'action', '' );
          }

       }); // onchange function

       // Choose all invoices in the WP_List_Table
       jQuery('#cb-select-all-form').change(function(){
         setTimeout(function() {

           var favorite = [];
           var favoritedelete = [];
           jQuery.each(jQuery(".checkbup:checked"), function(){
           favorite.push(jQuery(this).val());
           favoritedelete.push(jQuery(this).attr('valuedup'));
           });
           tir = favorite.join(",");
           jQuery('#bulklistup, #bulklistup2').val( tir );
           jQuery('#bulklistdeleteup, #bulklistdeleteup2').val( favoritedelete.join(",") );

         }, 20);
       });

        jQuery('#doaction, #doaction2').on('click', function(e) {
          var chkdarrLength = jQuery('input[name=bulklistup], input[name=bulklistup2]').val().split(',').length;
          if (chkdarrLength < 2) {
            e.preventDefault();
            alert('Оберіть два відправлення або більше.' );
          }
        });

      } // if(checkbup)

}); // ready()



// Adds input fields according to the sender type on plugin Settings page.
function switchSenderType(senderType) {
  switch(senderType) {
        case 'INDIVIDUAL':
              jQuery( '.names1, .names2, .names3, .phone' ).fadeIn(700);
              jQuery( '.edrpou, .up_company_name, .transfer_postpay, .mrkvup_sender_iban' ).fadeOut(500);
              break;
        case 'COMPANY':
              jQuery( '.up_company_name, .mrkvup_sender_iban, .transfer_postpay, .edrpou, .phone' ).fadeIn(700);
              jQuery( '.names1, .names2, .names3, .up_tin' ).fadeOut(500);
              break;
        case 'PRIVATE_ENTREPRENEUR':
              jQuery( '.up_company_name, .names1, .names2, .names3, .mrkvup_sender_iban, .transfer_postpay, .up_tin, .phone' ).fadeIn(700);
              jQuery( '.edrpou' ).fadeOut(500);
              break;
        default:
            jQuery( '.edrpou, .names1, .names2, .names3, .up_company_name, .up_tin, .phone' ).hide();
    }
}
// Removes checkbox field 'ukrposhta_international_tracked' for PARCEL international shipment on plugin Settings page.

jQuery(document).ready(function() {
    // Adds input fields according to the sender type
    var upSenderType = jQuery( '#up_sender_type' ).val();
    switchSenderType(upSenderType);
    jQuery('#up_sender_type').change(function() {
        upSenderTypeChange = jQuery( '#up_sender_type' ).val();
        switchSenderType(upSenderTypeChange);
    });
    // Removes checkbox for PARCEL international shipment
    var upPackingTypeChange;
    var upPackingType = jQuery('#senduptype').val();
    
    jQuery('#senduptype').on('change', function() {
        upPackingTypeChange = jQuery('#senduptype').val();
        
    });
}); // ready()
