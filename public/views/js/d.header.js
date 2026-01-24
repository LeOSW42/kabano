var small = 2;

function reduce() {
	$( "header" ).animate({
		height: "45px"
		}, 100, function() {
			// Animation complete.
	});
	$( "header #logo img" ).animate({
		height: "34px"
		}, 100, function() {
			// Animation complete.
	});
	$( "header #logo" ).animate({
		paddingTop: "3px"
		}, 100, function() {
			// Animation complete.
	});
	$( "header li.on-bar" ).animate({
		height: "45px"
		}, 100, function() {
			// Animation complete.
	});
	$( "header li:not(.with-subtitle) a.on-bar" ).animate({
		paddingTop: "15px",
		paddingBottom: "5px"
		}, 100, function() {
			// Animation complete.
	});
	$( "header li.with-subtitle a.on-bar" ).animate({
		paddingTop: "5px",
		paddingBottom: "15px"
		}, 100, function() {
			// Animation complete.
	});
}

function enlarge() {
	$( "header" ).animate({
		height: "65px"
		}, 100, function() {
			// Animation complete.
	});
	$( "header #logo img" ).animate({
		height: "44px"
		}, 100, function() {
			// Animation complete.
	});
	$( "header #logo" ).animate({
		paddingTop: "8px"
		}, 100, function() {
			// Animation complete.
	});
	$( "header li.on-bar" ).animate({
		height: "65px"
		}, 100, function() {
			// Animation complete.
	});
	$( "header li:not(.with-subtitle) a.on-bar" ).animate({
		paddingTop: "25px",
		paddingBottom: "15px"
		}, 100, function() {
			// Animation complete.
	});
	$( "header li.with-subtitle a.on-bar" ).animate({
		paddingTop: "15px",
		paddingBottom: "25px"
		}, 100, function() {
			// Animation complete.
	});
}

$(window).scroll(function() {
	var position = $(window).scrollTop();
	if (position>80 && small!=1 && $('body').width() > 800) {
		small = 1;
		reduce();
	}
	else if (position<=80 && small!=0 && $('body').width() > 800) {
		small = 0;
		enlarge();
	}
});

$(window).ready(function() {
	$( "#logo" ).hover(
	  function() {
	    $("#kabanologotext").show(100);
	  }, function() {
	    $("#kabanologotext").hide(100);
	  }
	)
});