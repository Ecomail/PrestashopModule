$(function() {

    var ecApiKey = $('#ecomail_api_key');
    var ecModulePath = $('#ecomail_module_path');
    var ecSubmitButton = $('.panel-footer button[type="submit"]');
    var ecSelectList = $('#ecomail_list_id');
    var ecSelectListGroup = $('#ecomail_list_id').parents('.form-group');
    
    var remoteFormShown = false;
    
    if(ecApiKey.val()==''){    
        ecInitRemoteForm();
    }
    
    ecApiKey.keyup(function(){
        ecInitRemoteForm();
    });
    
    function ecInitRemoteForm(){
        if(remoteFormShown==false){
            ecSubmitButton.hide();
            ecSelectListGroup.hide();

            $('<div class="form-group"><label class="control-label col-lg-3"></label><div class="col-lg-9 "><input type="submit" value="Připojit" id="ecConnect" class=""></div></div>').insertAfter("#configuration_form .form-wrapper .form-group:first-child");

            $('#ecConnect').click(function(e){
                e.preventDefault();
                $(this).val('Připojuji...');
                $.ajax({
                        url: ecModulePath.val() + 'ajax-call.php?key=' + ecApiKey.val(),
                        type: 'get',
                        dataType: 'json',
                        success: function(data) {
                            console.log('success');
                            //console.log(data._embedded);
                            ecSelectList.html('');
                            $.each(data._embedded.lists, function( key, val ) {
                                ecSelectList.append('<option value="' + val.id + '">' + val.name + '</option>');
                            });
                            ecSelectListGroup.show();
                            ecSubmitButton.show();
                            $('#ecConnect').parents('.form-group').remove();
                        }
                });
                return false;
            });
            remoteFormShown = true;
        }
    }
});
