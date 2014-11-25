(function($) {

	$('#mc-subscribe-form').submit(function() {

	    var $this = $(this),
	    	response = $('#response').hide();

	    if ( $this.valid() ) {

	        $.ajax({
	            url: adminAjax.ajaxurl,
	            type: 'POST',
	            data: {
	                'email' : $('#mce-email').val(),
	                'action': 'newsletter_ajax_request',
	                'nonce' : adminAjax.nonce
	            },
	            beforeSend: function(){

	            },
	            success: function(data){
	                console.log(data);
	                // console.log('success');
	                $this.fadeTo(300,0).slideUp( 500, function(){
                        response.html(data).slideDown().fadeTo(300,1);
                    });
	            },
	            error: function(errorThrown) {
	                console.log(errorThrown);
	                // console.log('failed');
	            }
	        });

	    }

	    return false;
	})

})(jQuery)