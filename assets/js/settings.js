jQuery(document).ready(function($) {
    var range_sliderrange=document.getElementById("sliderrange");
    var range_slidervalue=document.getElementById("sliderrangeoutput");
    range_slidervalue.innerHTML = range_sliderrange.value; // Display the default range-slider__range value
    $('#sliderrangeoutput').append(' Days')

    range_sliderrange.oninput = function() {
        range_slidervalue.innerHTML = this.value;
        $('#sliderrangeoutput').append(' Days')
    }
    
    var range_sliderrange2=document.getElementById("sliderrange2");
    var range_slidervalue2=document.getElementById("sliderrangeoutput2");
    range_slidervalue2.innerHTML = range_sliderrange2.value; // Display the default range-slider__range value
    $('#sliderrangeoutput2').append(' or above')

    range_sliderrange2.oninput = function() {
        range_slidervalue2.innerHTML = this.value;
        $('#sliderrangeoutput2').append(' or above')
    }
    
    var range_sliderrange3=document.getElementById("sliderrange3");
    var range_slidervalue3=document.getElementById("sliderrangeoutput3");
    range_slidervalue3.innerHTML = range_sliderrange3.value; // Display the default range-slider__range value
    $('#sliderrangeoutput3').append(' or above')

    range_sliderrange3.oninput = function() {
        range_slidervalue3.innerHTML = this.value;
        $('#sliderrangeoutput3').append(' or above')
    }

    var range_sliderrange4=document.getElementById("sliderrange4");
    var range_slidervalue4=document.getElementById("sliderrangeoutput4");
    range_slidervalue4.innerHTML = range_sliderrange4.value; // Display the default range-slider__range value
    $('#sliderrangeoutput4').append(' Minutes')

    range_sliderrange4.oninput = function() {
        range_slidervalue4.innerHTML = this.value;
        $('#sliderrangeoutput4').append(' Minutes')
    }

    if (typeof Cookies.get('pvb-hide-donation-div') !== 'undefinied') {
        $("#pvbdonationhide").show();
    }

    $("#pvbdonationclosebutton").click(function() {
        $("#pvbdonationhide").remove();
        Cookies.set('pvb-hide-donation-div', true, { expires: 365 });
    });
    
    $(function () {
		"use strict";
		var configChosen = {
            '.chosen-select'           : {},
            '.chosen-select-deselect'  : {allow_single_deselect:true},
            '.chosen-select-no-single' : {disable_search_threshold:10},
            '.chosen-select-no-results': {no_results_text:'Nothing Found'},
            '.chosen-select-width'     : {width:"100%"}
		}
		for (var selector in configChosen) {
            $(selector).chosen(configChosen[selector]);
		}
    });
});