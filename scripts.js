$(function() {

    var ecSubmitButton = $('.panel-footer button[type="submit"]');
    var ecSelectList = $('#ecomail_list_id').parents('.form-group');
    
    ecSubmitButton.hide();
    ecSelectList.hide();
    
    $('<div class="form-group"><label class="control-label col-lg-3"></label><div class="col-lg-9 "><input type="submit" value="Připojit" id="ecConnect" class=""></div></div>').insertAfter("#configuration_form .form-wrapper .form-group:first-child");
    
    $('#ecConnect').click(function(e){
        e.preventDefault();
        /*
        $.get("http://api.ecomailapp.cz/lists?key=asdf", function( data ) {
            alert(data);
        });*/
         $.ajax({
                                        url: '/prestashop/modules/ecomail/ajax-call.php',
                                        type: 'get',
                                        data: 'ajax=true',
                                        success: function(data) {
                                                console.log('success');
                                                // OTHER SUCCESS COMMAND - CHECK THE RETURN VALUE
                                        }
                                });
        return false;
    });
    
});
