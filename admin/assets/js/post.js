(function ( $ ) {
	"use strict";

	$(function () {
		var object2 = {};
		
		// Place your administration-specific JavaScript here
		 $( ".add_date_picker" ).each(function(){
		 	object2 = { altField: "#"+$(this).attr("id")+"_alt", altFormat: "yy-mm-dd"};
		 	$.extend( datePickerOb, object2 );
		 	
			 $(this).datepicker(datePickerOb);
		 });
	});

}(jQuery));