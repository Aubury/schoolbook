(function() {
	
	tinymce.create('tinymce.plugins.saphali_user_discount', {
        init : function(ed, url) {
            ed.addButton('saphali_user_discount', {
                title : 'Добавить информацию о накопительной скидке',
                image : url+'/img/discount.png',
                onclick : function() {
                     ed.selection.setContent('[saphali_user_discount]');  
 
                }
            });
        },
        createControl : function(n, cm) {
            return null;
        },
    });
     tinymce.PluginManager.add('saphali_user_discount', tinymce.plugins.saphali_user_discount);
})();