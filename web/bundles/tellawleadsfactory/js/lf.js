
jQuery(document).ready(function($){

    $('select.child-list').each(function(i){
        var parent = $(this).data('parent');
        var child_id = $(this).attr('id');
        if(parent){
            var parent_id = 'lffield\\['+parent+'\\]';
            $('#'+parent_id).change(function(){
                getChildOptions($(this).val(), child_id);
            })
        }
    })

});


function getChildOptions(parentValue, child_id)
{
    //alert(parentValue + ' ' + child_id);
    $.get('ajax/list_options', {'parent_value': parentValue, 'list_code': child_id}, updateOptions);
}

function updateOptions(){
    alert('success');
}