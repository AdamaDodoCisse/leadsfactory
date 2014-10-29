
/*jQuery(document).ready(function($){

    $('select.child-list').lfList();
});*/

(function ($) {
    /**
     * Listes hiérarchiques
     *
     * @param string options URL
     */
    $.fn.lfList = function(options){

    var settings = $.extend({ajax_url: 'ajax/list_options'}, options);

    this.each(function(i){
        var parent = $(this).data('parent');
        var child = $(this);
        var defaultLbl = $(this).data('default');
        if(parent){
            var parent_id = 'lffield\\['+parent+'\\]';
            var parent_list_code = $('#'+parent_id).data('list');

            if($('#'+parent_id).is('input')){
                $('#'+parent_id).on('input', function(){
                    var validation = $(this).validationEngine('validate');
                    //$(this).validationEngine('hideAll');
                    if(!validation){
                        updateChildOptions(parent_list_code, $(this).val(), child, defaultLbl);
                    }
                })

            }else{

                $('#'+parent_id).change(function(){
                    updateChildOptions(parent_list_code, $(this).val(), child, defaultLbl);
                })
            }
        }
        });

    updateChildOptions = function(parent_list_code, parentValue, child, defaultLbl){
        $.get(settings.ajax_url, {'parent_code': parent_list_code, 'parent_value': parentValue, 'default': defaultLbl}, function(data, textStatus){

            /** Mise à jour de l'enfant **/
            child.html(data);

            /** Mise à jour de l'enfant de l'enfant, si besoin **/
            var child_id = child.attr('id');
            var child_code = child_id.replace(/lffield\[(\w+)\]/, "$1");
            var childChild = $('select[data-parent='+child_code+']');
            var child_list_code = child.data('list');

            /** Rebelote **/
            if(childChild.length){
                updateChildOptions(child_list_code, child.val(), childChild, childChild.data('default'));
            }
        });
    }
};
}(jQuery));