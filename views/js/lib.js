document.addEventListener("DOMContentLoaded",function(){
    $("input[name='payment-option']").change(function(){
        var moduleName=$(this).attr('data-module-name');
        $('#cart-pittica-codfee').remove();
        if(moduleName==='pitticacodfee'){
            $('#cart-subtotal-shipping').after('<div class="cart-summary-line" id="cart-pittica-codfee"><span class="label">'+pittica_codfee_label+'</span><span class="value">'+pittica_codfee_fee+'</span></div>');
            $('#js-checkout-summary .cart-summary-totals').replaceWith(pittica_codfee_totals);
            pittica_cart_lock=true;
        }else if(!moduleName.startsWith('pittica')&&!pittica_cart_lock){
            if(moduleName==='pitticacodfee'){
                $.ajax({
                    url:$('#js-checkout-summary').attr('data-refresh-url'),
                    type:"GET",
                    dataType:"json",
                    success:function(data){
                        $('#js-checkout-summary .cart-summary-totals').replaceWith(data.cart_summary_totals);
                    }
                });
            }
        }else{
            pittica_cart_lock=false;
        }
    })
});