
jQuery(document).ready(function($){

    $('select.child-list').lfList();
});


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
            $('#'+parent_id).change(function(){
                updateChildOptions(parent, $(this).val(), child, defaultLbl);
            })
        }
    })

    updateChildOptions = function(parent, parentValue, child, defaultLbl){
        $.get(settings.ajax_url, {'parent_code': parent, 'parent_value': parentValue, 'default': defaultLbl}, function(data, textStatus){
            child.html(data);
            /** Mise à jour de l'enfant de l'enfant si besoin **/
            var child_id = child.attr('id');
            var child_code = child_id.replace(/lffield\[(\w+)\]/, "$1");
            var childChild = $('select[data-parent='+child_code+']');
            if(childChild.length){
                updateChildOptions(child_code, child.val(), childChild, childChild.data('default'));
            }
        });
    }
};
