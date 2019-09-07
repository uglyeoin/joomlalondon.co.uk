 jQuery(document).ready(function (){
    if(jQuery('#jform_params_contentsource').val() !== "1"){
        jQuery('.position').parents('.control-group').hide();
        jQuery('.controls .editor').parents('.control-group').show();
    }else{
        jQuery('.position').parents('.control-group').show();
        jQuery('.controls .editor').parents('.control-group').hide();
    }
    jQuery('#jform_params_contentsource').change(function(){
        if(jQuery('#jform_params_contentsource').val() !== "1"){
            jQuery('.position').parents('.control-group').hide(200);
            jQuery('.controls .editor').parents('.control-group').show(200);
        } else {
            jQuery('.position').parents('.control-group').show(400);
            jQuery('.controls .editor').parents('.control-group').hide(200);
        }
    });
});