(function ($) {
  function FormValidation(options) {
    this.options = $.extend({
      dataRequired: 'validate',
      inputWrap: '.input-holder',
      submitButton: '[type=submit]',
      event: 'keyup change',
      errorClass: 'error',
      correctClass: 'correct',
      successEvent: function () {
      }
    }, options);
    this.init();
  }

  FormValidation.prototype = {
    init: function () {
      this.findElements();
      this.events();
    },
    findElements: function () {
      this.form = jQuery(this.options.holder);
      this.requiredItems = this.form.find('[data-' + this.options.dataRequired + ']');
      this.submitButton = this.form.find(this.options.submitButton);
    },
    events: function () {
      var self = this;
      self.requiredItems.on(self.options.event, function () {
        var item = jQuery(this);
        var val = item.val();
        self.validateInputs(item, val);
      });
      self.form.on('submit', function () {
        self.requiredItems.trigger('change'); // need for first load page with not empty form item
        var result = self.checkValidate();
        self.form.data('flag', result);
        if (!result){
          var checks = document.getElementsByClassName('checkout-content');
          if(checks[0]){
            var elements = document.getElementsByClassName('input-holder');
            var notes = null; for (var i = 0; i < elements.length; i++) {
              if (elements[i].className == 'input-holder error') {
                notes = elements[i];
                notes.scrollIntoView({block: "center", behavior: "smooth"});
                break;
              }
            }
          }
          return false
        }
      });
    },
    validateInputs: function (item, value) {

      switch (item.data(this.options.dataRequired)) {
        case 'norequired' :
          item.closest(this.options.inputWrap).removeClass(this.options.errorClass).addClass(this.options.correctClass);
          break;
        case 'required' :
          if (value === "") {
            item.closest(this.options.inputWrap).addClass(this.options.errorClass).removeClass(this.options.correctClass);
          } else {
            item.closest(this.options.inputWrap).removeClass(this.options.errorClass).addClass(this.options.correctClass);
          }
          break;
        case 'required-cfmpas' :
          var password = $("#password").val();
          if (value === "") {
            item.closest(this.options.inputWrap).addClass(this.options.errorClass).removeClass(this.options.correctClass);
          } else if (value != password) {
            item.closest(this.options.inputWrap).addClass(this.options.errorClass).removeClass(this.options.correctClass);
          } else if (value !== "" && value === password) {
            item.closest(this.options.inputWrap).removeClass(this.options.errorClass).addClass(this.options.correctClass);
          }
          break;
        case 'required-name' :
          var txtReg = /^[A-Za-z0-9А-ЯЁа-яёҐґІіЇїЄєe ';()-+_=?,.!-/\n/]{2,25}$/;
          if (!txtReg.test(value)) {
            item.closest(this.options.inputWrap).addClass(this.options.errorClass).removeClass(this.options.correctClass);
          } else {
            item.closest(this.options.inputWrap).removeClass(this.options.errorClass).addClass(this.options.correctClass);
          }
          break;
        case 'required-alfa' :
          var txt = /^[A-Za-z0-9А-ЯёЁїЇіІЄєҐґa-я '\n\s]{3,45}$/;
          if(!txt.test(value)){
              item.closest(this.options.inputWrap).addClass(this.options.errorClass).removeClass(this.options.correctClass);
          } else {
              item.closest(this.options.inputWrap).removeClass(this.options.errorClass).addClass(this.options.correctClass);
          }
          break;
        case 'required-email' :
          var pattern = /^([a-z0-9_\.-])+@[a-z0-9-]+\.([a-z]{2,4}\.)?[a-z]{2,4}$/i;
          if (!pattern.test(value)) {
            item.closest(this.options.inputWrap).addClass(this.options.errorClass).removeClass(this.options.correctClass);
          } else {
            item.closest(this.options.inputWrap).removeClass(this.options.errorClass).addClass(this.options.correctClass);
          }
          break;
        case 'required-number':
          //var regNumber = /^[+]38[0-9]{3}[0-9]{3}[0-9]{2}[0-9]{2}$/;
          var regNumber = /^[0-9,+,(), ]{9,24}$/;
          if (!regNumber.test(value) || value === "") {
            item.closest(this.options.inputWrap).addClass(this.options.errorClass).removeClass(this.options.correctClass);
          } else {
            item.closest(this.options.inputWrap).removeClass(this.options.errorClass).addClass(this.options.correctClass);
          }
          break;
        case 'required-textarea' :
            var txtReg = /^[A-Za-z0-9А-ЯёЁїЇіІЄєҐґa-я ':;()-+_=,.!-/\n/]{5,400}$/;
            if(!txtReg.test(value)){
                item.closest(this.options.inputWrap).addClass(this.options.errorClass).removeClass(this.options.correctClass);
            } else {
                item.closest(this.options.inputWrap).removeClass(this.options.errorClass).addClass(this.options.correctClass);
            }
          break;
        case 'required-numeric':
          var regNumeric = /^[0-9]{3,10}$/;
          if (!regNumeric.test(value) || value === "") {
            item.closest(this.options.inputWrap).addClass(this.options.errorClass).removeClass(this.options.correctClass);
          } else {
            item.closest(this.options.inputWrap).removeClass(this.options.errorClass).addClass(this.options.correctClass);
          }
          break;
        case 'required-select':
          if (value === "") {
            item.closest(this.options.inputWrap).addClass(this.options.errorClass).removeClass(this.options.correctClass);
          } else {
            item.closest(this.options.inputWrap).removeClass(this.options.errorClass).addClass(this.options.correctClass);

          }
          break;
        case 'required-date':
          var regDate = /^(0[1-9]|1\d|2\d|3[01])\.(0[1-9]|1[0-2])\.(19|20)\d{2}$/;
          if (!regDate.test(value) || value === "") {
            item.closest(this.options.inputWrap).addClass(this.options.errorClass).removeClass(this.options.correctClass);
          } else {
            item.closest(this.options.inputWrap).removeClass(this.options.errorClass).addClass(this.options.correctClass);
          }
          break;
      }
    },
    checkValidate: function () {
      var self = this;
      // console.log(self.requiredItems.length);
      // console.log(self.form.find('.' + this.options.correctClass).length);
      // console.log(self.form);
      // console.log(self.requiredItems);
      // console.log(this.options.correctClass);

      if (self.requiredItems.length !== self.form.find('.' + this.options.correctClass).length) {
        self.requiredItems.each(function () {
          var item = jQuery(this);
          var val = item.val();
          self.validateInputs(item, val);
        });
        return false
      } else {
        if (typeof self.options.successEvent === 'function') {
          self.options.successEvent();
        }
        return true
      }
    }
  };

  // jquery plugin
  $.fn.formValidation = function (opt) {
    return this.each(function () {
      $(this).data('FormValidation', new FormValidation($.extend(opt, {holder: this})));
    });
  };
})(jQuery);
