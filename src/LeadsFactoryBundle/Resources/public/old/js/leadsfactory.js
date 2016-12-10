// LeadsFactory JS File

function ajaxCall ( path, data, successCallBack, errorCallBack) {

    $.ajax ({
        data: data,
        type: 'POST',
        url : path
    }).done(successCallBack);

}

// Load Fragment HTML & refresh
function fragmentAjaxCall ( path, data, container, successCallBack ) {

    $.ajax ({
        data: data,
        type: 'POST',
        url : path
    }).done(function (result) {
        container.html(result);
        successCallBack (result );
    });

}

// Load JSon Data
function jsonAjaxCall ( path, data, successCallBack ) {

    $.ajax ({
        data: data,
        type: 'POST',
        url : path
    }).done(function (result) {
        object = jQuery.parseJSON(result);
        successCallBack(object);
    });

}

function setFragmentActions ( prefix, paginationRoute, paginationLimit, container ) {

    if (!$("#"+prefix+"_page").length) alert ("Attention, l'id : #"+prefix+"_per_page n'existe pas");
    if (!$("#"+prefix+"_search").length) alert ("Attention, l'id : #"+prefix+"_search n'existe pas");

    $("#"+prefix+"_page option[value='"+paginationLimit+"']").attr('selected', 'selected');

    if (!container.length) alert ("L'id du container n'existe pas");

    $("#"+prefix+"_search").keypress(function(event){
        if(event.which == 13){
            event.preventDefault();
            var keyword = $(this).val() != '' ? '/'+$(this).val() : ''
            path = paginationRoute + '/1/' + paginationLimit + keyword;

            fragmentAjaxCall( path, "", container, function (content){} )

        }
    });

    $("#"+prefix+"_page").change(function(){
        var keyword = $("#"+prefix+"_search").val() != '' ? '/'+$("#"+prefix+"_search").val() : '';
        path = paginationRoute + '/1/' + $(this).val() + keyword;
        fragmentAjaxCall( path, "", container, function (content){} )
    });

}