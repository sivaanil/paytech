jQuery(document).ready(function(){
	if(jQuery("#_accountis_dealer").val() == 1){
		jQuery('#_accountmaster_customer').parent().parent().hide();
	}
	jQuery("#_accountis_dealer").on('change',function(){
		if(jQuery(this).val() == 0){
			jQuery('#_accountmaster_customer').parent().parent().show();
		}else{
			jQuery('#_accountmaster_customer').parent().parent().hide();
		}
	});
    if(jQuery("#_accountis_dealer").val() == 1){
        jQuery('#_accountcan_place_order').parent().parent().hide();
    }
    jQuery("#_accountis_dealer").on('change',function(){
        if(jQuery(this).val() == 0){
            jQuery('#_accountcan_place_order').parent().parent().show();
        }else{
            jQuery('#_accountcan_place_order').parent().parent().hide();
        }
    });
})
