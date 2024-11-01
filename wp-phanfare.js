/**
 * Handle:	wpPhanfareAdmin
 * Version: 0.0.1
 * Deps:	jQuery
 * Enqueue:	true
 * July 4, 2009 Crm, modify for Phanfare
 */

var wpPhanfareAdmin = function() {}

wpPhanfareAdmin.prototype = {
    options           : {},
    generateShortCode : function() {
        var attrs = '';
        
        jQuery.each(this['options'], function(name, value){
            if (value != '') {
                attrs += ' ' + name + '="' + value + '"';
            }
        });
		
		return '[phanfare' + attrs + ']';
    },
    sendToEditor      : function(f) {
        var collection = jQuery(f).find("input[id^=wpPhanfare]:not(input:checkbox),input[id^=wpPhanfare]:checkbox:checked");
        
        var $this = this;
        
        collection.each(function () {
// The 11 is the length of 'wpPhanfare['  (was 10 for "wpSmugmug[")
            var name = this.name.substring(11, this.name.length-1);

            if ( name == 'title' || name == 'description' ) {
				$this['options'][name] = escape(this.value);
			} else {
				$this['options'][name] = this.value;
			}
        });
        
      	var size = jQuery("#wpPhanfare_size").val();      	
      	$this['options']['size'] = size;
        
        var thumbsize = jQuery("input[name='wpPhanfare[thumbsize]']:checked").val();
        $this['options']['thumbsize'] = thumbsize;
        
        var link = jQuery("input[name='wpPhanfare[link]']:checked").val();
        $this['options']['link'] = link;
        
        if ( $this['options']['url'] == '' ) {
			alert("Please enter the URL to your Phanfare gallery RSS feed");
			return false;
		}
        
        send_to_editor(this.generateShortCode());
        return false;
    }
}

var wpPhanfareAdmin = new wpPhanfareAdmin();