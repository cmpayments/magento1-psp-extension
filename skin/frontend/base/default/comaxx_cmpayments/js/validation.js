;(function ($, $$, window) {
	//do after load since page sections can be loaded after this file is called.
	document.observe("dom:loaded", function() {
		//only add listerner incase payment method of afterpay is available.
		if($('fields-container-afterpay')) {
			$$('.col-main button').each(function(element) {
				element.observe('click', orderClick);
			});
		}
		
		addValidations();
	});
	
	function orderClick(ev) {
		//always show fields at order button click, otherwise validation of afterpay fields does not always work
		$('fields-container-afterpay').removeClassName('visuallyhidden');
		
		//get elements needed in checks
		var	afterpay_phonenr		= $('cmpayments_ap_billing_phonenumber'),
			afterpay_street			= $('cmpayments_ap_billing_street'),
			afterpay_housenr		= $('cmpayments_ap_billing_housenumber'),
			afterpay_housenradd		= $('cmpayments_ap_billing_housenumberaddition'),
			afterpay_street_ship	= $('cmpayments_ap_shipping_street'),
			afterpay_housenr_ship	= $('cmpayments_ap_shipping_housenumber'),
			afterpay_housenradd_ship= $('cmpayments_ap_shipping_housenumberaddition'),
			magento_phonenr			= $('billing:telephone'),
			magento_address1			= $('billing:street1'),
			magento_address2			= $('billing:street2'),
			magento_address3			= $('billing:street3'),
			magento_address4			= $('billing:street4'),
			magento_address1_ship	= $('shipping:street1'),
			magento_address2_ship	= $('shipping:street2'),
			magento_address3_ship	= $('shipping:street3'),
			magento_address4_ship	= $('shipping:street4'),
			pattern					= "\\b([0-9]+[\\s-]*[a-z,0-9]{0,2}\\b)\\b",
			fields_container		= $('fields-container-afterpay');
		
		//if container is not visible then magento values should always be used
		var overrideFields = !fields_container.visible();
		
		//create regex (g for global)
		var globalRegex = new RegExp(pattern, 'gi');
			
		//if afterpay phonenr is empty check if magento phonenr is available (in case of onepage checkout plugins)
		if(overrideFields || (afterpay_phonenr && afterpay_phonenr.value.trim() === '' && magento_phonenr.value.trim() !== '')) {
			afterpay_phonenr.value = magento_phonenr.value;
		}
		
		//gather combined address string
		var address_array = [];
		(magento_address1) ? address_array.push(magento_address1.value.trim()) : null;
		(magento_address2) ? address_array.push(magento_address2.value.trim()) : null;
		(magento_address3) ? address_array.push(magento_address3.value.trim()) : null;
		(magento_address4) ? address_array.push(magento_address4.value.trim()) : null;
		
		var combinedAddress = address_array.join(" ").trim();
		
		//check if afterpay street is set, if not then try to fill it
		if(overrideFields || (afterpay_street && afterpay_street.value.trim() === '' && combinedAddress !== '')) {
			afterpay_street.value = getAfterpayStreet(combinedAddress, globalRegex);
		}
		
		if(overrideFields || (afterpay_housenr && afterpay_housenr.value.trim() === '' && combinedAddress !== '')) {
			afterpay_housenr.value = getAfterpayHouseNumber(combinedAddress, globalRegex);
		}
	  
		if(overrideFields || (afterpay_housenradd && afterpay_housenradd.value.trim() === '' && combinedAddress !== '')) {
			afterpay_housenradd.value = getAfterpayHouseNumberAddition(combinedAddress, globalRegex);
		}
		
		//gather combined shipping address string
		var shipping_address_array = [];
		(magento_address1_ship) ? shipping_address_array.push(magento_address1_ship.value.trim()) : null;
		(magento_address2_ship) ? shipping_address_array.push(magento_address2_ship.value.trim()) : null;
		(magento_address3_ship) ? shipping_address_array.push(magento_address3_ship.value.trim()) : null;
		(magento_address4_ship) ? shipping_address_array.push(magento_address4_ship.value.trim()) : null;
		
		var combinedShippingAddress = shipping_address_array.join(" ").trim();
		
		//check if afterpay shipping street is set, if not then try to fill it
		if(overrideFields || (afterpay_street_ship && afterpay_street_ship.value.trim() === '' && combinedShippingAddress !== '')) {
			afterpay_street_ship.value = getAfterpayStreet(combinedShippingAddress, globalRegex);
		}
		
		if(overrideFields || (afterpay_housenr_ship && afterpay_housenr_ship.value.trim() === '' && combinedShippingAddress !== '')) {
			afterpay_housenr_ship.value = getAfterpayHouseNumber(combinedShippingAddress, globalRegex);
		}
		
		if(overrideFields || (afterpay_housenradd_ship && afterpay_housenradd_ship.value.trim() === '' && combinedShippingAddress !== '')) {
			afterpay_housenradd_ship.value = getAfterpayHouseNumberAddition(combinedShippingAddress, globalRegex);
		}

		if(!$('shipping:same_as_billing').checked && $('cmpayments_ap_shipping_fields')) {
			$('cmpayments_ap_shipping_fields').show();
		}
	}
	
	function getAfterpayStreet(magento_value, globalRegex) {
		var matches = magento_value.match(globalRegex),
			result  = magento_value;
		
		if(matches != null) {
			//get housenr and remove it from the string to extract the street
			var housenr = Array.prototype.slice.call(matches, -1);
			result = magento_value.replace(housenr, "");
		}
		
		return result.trim();
	}
	
	function getAfterpayHouseNumber(magento_value, globalRegex) {
		var matches = magento_value.match(globalRegex),
			result  = '';
		
		if(matches != null) {
			//get housenr part and extract number
			var housenr = Array.prototype.slice.call(matches, -1);
			result = parseInt(housenr, 10);
		}
		
		//no need to trim (either int or default value)
		return result;
	}
	
	function getAfterpayHouseNumberAddition(magento_value, globalRegex) {
		var matches = magento_value.match(globalRegex),
			result  = '';
		
		if(matches != null) {
			//get housenr and extract addition
			var housenr = Array.prototype.slice.call(matches, -1);
			var number = parseInt(housenr, 10);
			
			result = housenr[0].replace(number, "").replace(new RegExp("-", 'g'), "");
		}
		
		return result.trim();
	}
	
	function addValidations() {
		if (!window.Validation) {
			if (window.console && window.console.log) {
				console.log('Could not add CM Payments validations because window.Validation did not exist at time of this code excecution (should be on-load using window.observe).');
			}
			// Cannot add validations to an object which isn't available
			return;
		}
		
		window.Validation.addAllThese([
			['validate-afterpay-phonenumber', 'Please enter a number of 10 digits. For example: 010-1010101 or (010) 10 10 101', function(v) {
				// -- min length 10 max length 50
				number = v.replace(/\(/g, "").replace(/\)/g, "").replace(/-/g, "").replace(/ /g, "");
				if(number != "0000000000" && (number - 0) == number && number.length >= 10 && number.length <= 50) {
					return true;
				} else {
					return false;
				}
			}]
		]);
	}
	
}($, $$, window));

if (!String.prototype.trim) {
	String.prototype.trim=function(){return this.replace(/^\s+|\s+$/g, '');};
	String.prototype.ltrim=function(){return this.replace(/^\s+/,'');};
	String.prototype.rtrim=function(){return this.replace(/\s+$/,'');};
	String.prototype.fulltrim=function(){return this.replace(/(?:(?:^|\n)\s+|\s+(?:$|\n))/g,'').replace(/\s+/g,' ');};
}
