jQuery(document).ready(function($) {

    function checkRadioNotification(){
        if($("input[name='mbif_emailto_enable']:checked").val() == 0){
            $("#z_emailto, #z_emailto_secondary").addClass('hidden');
            $("input[name='mbif_emailto']").removeAttr('required');
        }else{
            $("#z_emailto, #z_emailto_secondary").removeClass('hidden');
            $("input[name='mbif_emailto']").prop('required', true);
        }
    }
    
    $("input[name='mbif_emailto_enable']").click(function(){
        checkRadioNotification();
    });

    $(".link-confirm").click(function(e){
        e.preventDefault();
        if (confirm($(this).data("message")) == true) {
            window.location = $(this).attr("href"); 
        }
    });

    checkRadioNotification();
});