function getURLVar(key, query = String(document.location)) {
	var value = [];
	
	query = query.split('?');
	//var query = String(document.location).split('?');

	if (query[1]) {
		var part = query[1].split('&');

		for (i = 0; i < part.length; i++) {
			var data = part[i].split('=');

			if (data[0] && data[1]) {
				value[data[0]] = data[1];
			}
		}

		if (value[key]) {
			return value[key];
		} else {
			return '';
		}
	}
}

$(document).on('change','.trigger-gift',function(e){
	e.preventDefault();
	
	cartId = $(this).data('cartid');
	is_gift = this.checked ? 1 : 0;

	if( cartId ) {
		$.ajax({
			url: "api/update_cart_gift.php",
			data: 'cart_id='+cartId+'&is_gift='+is_gift,                  
			type: 'POST',
			success: function(){							
			}
		});			
	}

});

$(document).on('click','.search-icon-mobile',function(e){
	e.preventDefault();
});

$(document).on('click','.footer-block h5',function(){
	$(this).closest('.footer-block').find('ul').slideToggle(200).toggleClass('active-block');
});

$(document).on('click','button#dropdownMenuFilters',function(){
	$(this).closest('.filtersWrapper').find('.dropdown-menu-filters').fadeToggle(100);
});

$(document).on('click','.toggle-grid',function(e){
	
	e.preventDefault();
	
	var dataGrid 	= $(this).data('togglegrid');
	//var parentGrid 	= '';
	var element 	= $(this);
	
	$('.toggle-grid').removeClass('active-grid');
	element.addClass('active-grid');
	
	if( $('#productsWrapper').length && dataGrid ) {
		
		if( dataGrid == 'grid4' ) {
			$('#productsWrapper').removeClass('data3')
		};
		if( dataGrid == 'grid3' && !$('#productsWrapper').hasClass('data3') ) {
			$('#productsWrapper').addClass('data3')
		} else {
			return false;
		};
		
	};
});

$(document).on('click', '#cartInfoTop',function(){
	if( window.innerWidth <= 992 ) {
		$('#cart1 .cartInnerContent').slideToggle(100);
	};
	
});

function updateURLParameter(url, param, paramVal){
	var newAdditionalURL = "";
	var tempArray = url.split("?");
	var baseURL = tempArray[0];
	var additionalURL = tempArray[1];
	var temp = "";
	if (additionalURL) {
		tempArray = additionalURL.split("&");

		for (var i=0; i < tempArray.length; i++){
			if(tempArray[i].split('=')[0] != param){
				newAdditionalURL += temp + tempArray[i];
				temp = "&";
			}
		}
	}
	
	if (paramVal) {
		var rows_txt = temp + "" + param + "=" + paramVal;
	} else {
		var rows_txt = '';
	}
	baseURL = baseURL.replace(/^.*\/\/[^\/]+/, '')
	return baseURL + "?" + newAdditionalURL + rows_txt;
}
function clamp(num, min, max) {
  return num <= min ? min : num >= max ? max : num;
}

function parallax() {	
	$( ".parallax-image").each(function( index ) {
		var objOffset = $(this).parent().offset();
		var yPos = (objOffset.top - window.pageYOffset)  / 3;
		yPos = clamp(-yPos,-50,100);
		var coords = Math.round(yPos) + 'px';
		$(this).css({"transform": "translate3d(0px, " + coords + ", 0px)"});
	});
}

$(document).on('click','#desktop_menu_expand',function(e) {
	e.preventDefault();
	toggleMenu();
});

$(document).on('click','.menu-overlay',function(e) {
	e.preventDefault();
	toggleMenu();
});

$(document).on('click','a.close-menuNav',function(e) {
	e.preventDefault();
	toggleMenu();
});

$(document).on('click', '#desktop-menu-vertical .dropdown-menu', function (e) {
  e.stopPropagation();
});

$(document).ready(function(){

	$(".espa-banner").delay(10000).fadeOut(600);

    $(".megamenu-categories .dropdown-toggle").hover(function(){

        var dropdownMenu = $(this).siblings(".dropdown-menu");
        //dropdownMenu.parent().toggleClass("show");
        //dropdownMenu.toggleClass("show");
    });
});  

function toggleMenu() {
	var element = $('#desktop-menu-vertical');
	
	$('.dropdown-menu.dropdown-menu-ca').attr('style','');
	if( !$('#header').hasClass('menu-expanded') ) {
		
		var currentWidth = 0;
		
		currentWidth = ( $('body').innerWidth() - (element.outerWidth()) );
		$('.dropdown-menu.dropdown-menu-ca').outerWidth(currentWidth);
		var $height = $(window).outerHeight() - $('#header').outerHeight();
		element.height($height);
		
		$('#header').addClass('menu-expanded');
		$('body').addClass('body-menu-expanded');
		$('.menu-overlay').fadeIn(250);
		
	} else {
		$('#header').removeClass('menu-expanded');
		$('body').removeClass('body-menu-expanded');
		$('.menu-overlay').hide();
	};
}


$(document).ready(function() {
	
	$('li.megamenucategory-li.has-dropdown').hover(
		function () {
			
			var master 		= $(this).closest('.dropdown-menu.dropdown-menu-ca');
			var container 	= $(this).closest('.dropdown-megamenu-wrap');
			var dropElement = $(this).find('.dropdown-menu-block');
			
			var margin 		= $('#header').outerHeight();
			
			var trigElement = $(this);
			
			dropElement.show();
			
			var childPos = dropElement.offset();
			var parentPos = container.parent().offset();		
			
			var mainOffset 	= childPos.top - parentPos.top + (dropElement.outerHeight());
			var mainBound 	= container.innerHeight();

			if(mainOffset > mainBound) {
				dropElement.addClass('offset-top');
				
				var fixedOffsetElement 		= dropElement.offset().top;
				var fixedOffsetContainer 	= container.offset().top;
				var diff = fixedOffsetElement - fixedOffsetContainer;
				
				if(diff < 0) {
					dropElement.removeClass('offset-top');
				}
				
			} else {
				dropElement.removeClass('offset-top');
			};
			
			dropElement.addClass('shown');
			
			//alert(dropElement.prop('scrollHeight') > dropElement.prop('clientHeight'));
			//alert(dropElement.scrollHeight);
		},
		function () {
			var dropElement = $(this).find('.dropdown-menu-block ');
			dropElement.hide();
			dropElement.removeClass('offset-top');
			dropElement.removeClass('shown');
		}
	);	
});

/*
$( document ).ajaxComplete(function() {
	createColorSwipers();
	createSizeSwipers();
});
*/

$(document).ready(function() {
	
	$('body').delegate('.product-block-options-wrapper .product-option-color','click',function() {
		$(this).parent().find('.product-option-color').each(function(index) {
			$(this).removeClass('active');
		});
		
		$(this).addClass('active');
	});
	
	createColorSwipers();
	createSizeSwipers();
	
	$('body').delegate('.product-option-color','click', function () {
		var lazypreloader = $(this).parent().parent().parent().parent().parent().find('.swiper-lazy-preloader');
		var loadingbg = $(this).parent().parent().parent().parent().parent().find('.loading-bg');
		lazypreloader.removeClass('d-none');
		loadingbg.removeClass('d-none');
		$(this).parent().parent().parent().parent().parent().find('.product-thumbnail').parent().attr('href',$(this).attr('data-href'));
		$(this).parent().parent().parent().parent().parent().find('.curColorSelected').html( '<span class="colorBlock">'+$(this).attr('data-name')+'</span>' );
		$(this).parent().parent().parent().parent().parent().find('.product-name a').attr('href',$(this).attr('data-href'));
		$(this).parent().parent().parent().parent().parent().find('.product-thumbnail .current').attr('src',$(this).attr('data-image'));
		if($(this).attr('data-image-2')) {
			$(this).parent().parent().parent().parent().parent().find('.product-thumbnail .image-other').attr('src',$(this).attr('data-image-2'));
			$(this).parent().parent().parent().parent().parent().find('.product-thumbnail .image-other').removeClass('d-none');
		} else {
			$(this).parent().parent().parent().parent().parent().find('.product-thumbnail .image-other').addClass('d-none');
		}
		$(this).parent().parent().parent().parent().parent().find('.product-thumbnail').imagesLoaded( function() {
			lazypreloader.addClass('d-none');
			loadingbg.addClass('d-none');
		});
	});
	
	$('body').delegate('.product-color-button-next','click', function () {

		var container = $(this).closest('.product-block-options-wrapper');
		
		var scrollWidth = parseFloat(window.getComputedStyle(container.find('.product-option-color').get(0)).width) * 4;
		
		var itemWidth = parseFloat(window.getComputedStyle(container.find('.product-option-color').get(0)).width);
	
		var numfit = parseFloat(window.getComputedStyle(container.find('.product-option-color-container').get(0)).width) / itemWidth;
		
		var items = container.find('.product-option-color').length;
		
		var offsetlength = (items) * itemWidth;
		
		var currentOffset = offsetlength + parseFloat(container.find('.product-option-color-container').attr('data-transform'));
		
		var nextOffset = offsetlength + parseFloat(container.find('.product-option-color-container').attr('data-transform')) - scrollWidth;
		
		var animWidth = scrollWidth;
		
		if(nextOffset < itemWidth * 4) {
			animWidth = nextOffset;
		} else {
			animWidth = scrollWidth;
		}
		
		nextOffset = nextOffset - animWidth;
		
		if(currentOffset > 0) {
			container.find('.product-option-color-container').attr('data-transform',parseFloat(container.find('.product-option-color-container').attr('data-transform')) - animWidth);
			container.find('.product-option-color-container').css('transform','translateX('+container.find('.product-option-color-container').attr('data-transform')+'px)')
		}
		
		container.find('.product-color-button-prev').removeClass('swiper-button-disabled');
		
		if (nextOffset <= 0) {
			$(this).addClass('swiper-button-disabled');
		}
	
	});
	
	$('body').delegate('.product-color-button-prev','click', function () {

		var container = $(this).closest('.product-block-options-wrapper');
		
		var scrollWidth = parseFloat(window.getComputedStyle(container.find('.product-option-color').get(0)).width) * 4;
		
		var itemWidth = parseFloat(window.getComputedStyle(container.find('.product-option-color').get(0)).width);
		
		var numfit = parseFloat(window.getComputedStyle(container.find('.product-option-color-container').get(0)).width) / itemWidth;
		
		var items = container.find('.product-option-color').length;
		
		var offsetlength = (items) * itemWidth;
			
		var currentOffset = offsetlength - parseFloat(container.find('.product-option-color-container').attr('data-transform'));
		
		var nextOffset = offsetlength + parseFloat(container.find('.product-option-color-container').attr('data-transform')) + scrollWidth;
		
		var animWidth = scrollWidth;
		
		//console.log(offsetlength);
		//console.log(nextOffset);
		//console.log(offsetlength - nextOffset);
		//console.log( (offsetlength - nextOffset ) + scrollWidth );
		
		if( (itemWidth * 4) > ( offsetlength - nextOffset ) + scrollWidth ) {
			animWidth = ( offsetlength - nextOffset ) + scrollWidth;
		} else {
			animWidth = scrollWidth;
		}	
		
		if(currentOffset > offsetlength) {
			container.find('.product-option-color-container').attr('data-transform',parseFloat(container.find('.product-option-color-container').attr('data-transform'))+animWidth);
			container.find('.product-option-color-container').css('transform','translateX('+container.find('.product-option-color-container').attr('data-transform')+'px)')
		} else {
			$(this).addClass('swiper-button-disabled');
		}
		
		container.find('.product-color-button-next').removeClass('swiper-button-disabled');
		
		if (nextOffset >= offsetlength) {
			$(this).addClass('swiper-button-disabled');
		}
		
	});	
	
	function createColorSwipers() {
		$('.product-block-options-wrapper').each(function() {
			
			var numfit = 4;
			var items = $(this).find('.product-option-color').length;
			if(items > numfit) {
				 $(this).find('.swiper-button-next').removeClass('swiper-button-disabled');
			}
			//console.log('swipers_created');
		});
		
	}


	$('body').delegate('.product-size-button-next','click', function () {
		
		var container = $(this).closest('.product-block-sizes-wrapper');

		var scrollWidth = parseFloat(window.getComputedStyle(container.find('.product-block-size').get(0)).width);	
	
		var numfit = parseFloat(window.getComputedStyle(container.find('.product-block-sizes').get(0)).width) / scrollWidth;
		
		var items = container.find('.product-block-size').length;
		
		var offsetlength = (items-numfit) * scrollWidth;
		
		var currentOffset = offsetlength + parseFloat(container.find('.product-block-sizes').attr('data-transform'));
		
		var nextOffset = offsetlength + parseFloat(container.find('.product-block-sizes').attr('data-transform')) - scrollWidth;

		if(currentOffset > 0) {
			container.find('.product-block-sizes').attr('data-transform',parseFloat(container.find('.product-block-sizes').attr('data-transform')) - scrollWidth);
			container.find('.product-block-sizes').css('transform','translateX('+container.find('.product-block-sizes').attr('data-transform')+'px)')
		}
		container.find('.product-size-button-prev').removeClass('swiper-button-disabled');
		if (nextOffset <= 0) {
			$(this).addClass('swiper-button-disabled');
		}
	
	});
	
	$('body').delegate('.product-size-button-prev','click', function () {

		var container = $(this).closest('.product-block-sizes-wrapper');
		
		var scrollWidth = parseFloat(window.getComputedStyle(container.find('.product-block-size').get(0)).width);
		
		var numfit = parseFloat(window.getComputedStyle(container.find('.product-block-sizes').get(0)).width) / scrollWidth;
		
		var items = container.find('.product-block-size').length;
		
		var offsetlength = (items-numfit) * scrollWidth;
		
		var currentOffset = offsetlength - parseFloat(container.find('.product-block-sizes').attr('data-transform'));
		
		var nextOffset = offsetlength + parseFloat(container.find('.product-block-sizes').attr('data-transform')) +scrollWidth;
		
		if(currentOffset > offsetlength) {
			container.find('.product-block-sizes').attr('data-transform',parseFloat(container.find('.product-block-sizes').attr('data-transform'))+scrollWidth);
			container.find('.product-block-sizes').css('transform','translateX('+container.find('.product-block-sizes').attr('data-transform')+'px)')
		} else {
			$(this).addClass('swiper-button-disabled');
		}
		container.find('.product-size-button-next').removeClass('swiper-button-disabled');
		if (nextOffset >= offsetlength) {
			$(this).addClass('swiper-button-disabled');
		}
		
	});	

	function createSizeSwipers() {
		$('.product-block-sizes-wrapper').each(function() {

			var numfit = 1;
			var items = $(this).find('.product-block-size').length;
			if(items > numfit) {
				 $(this).find('.swiper-button-next').removeClass('swiper-button-disabled');
			}
		});
		
	}
	
	
	requestAnimationFrame(parallax);
	setTimeout(function(){ $('.alert').fadeOut() }, 3000);
	$('.nav-link').click(function() {
		$(this).parent().parent().find('.nav-item').each(function() {
			$(this).removeClass('parent-active');
		});
		$(this).parent().addClass('parent-active');
	});
	
	$(window).scroll(function(){
		requestAnimationFrame(parallax);
        if(window.innerWidth > 991){
            var page = $('html,body');
            if (page.scrollTop() > $('#header').outerHeight()) {
                $('#header').addClass('stick_it');
            } else {
                $('#header ').removeClass('stick_it'); 
            }
        }		
    });
	
	$(window).resize(function(){
		requestAnimationFrame(parallax);
	});
	$('.trigger-search').on('click',function(e){
		e.preventDefault();
		$('#header-bottom').fadeToggle(300);
	});	
	$('.cart-icon').on('click','a',function(e) {
		e.preventDefault();
		$('#cart-container .modal-inner').load('index.php?route=common/cart/info #cart-container .cart-content');	
		setTimeout(function(){
			$('#cart-container').modal('show');
		}, 200);
	});
	$('.form-control').not('#product .form-control').each(function(index) {
		if ($(this).prev('label').length) { 
			$(this).attr('placeholder', $(this).prev('label').text());
			$(this).prev('label').hide();
			$(this).addClass('modified-input');
			$(this).parent('modified-form-group');
		}
		if ($(this).parent().prev('label').length) { 
			$(this).attr('placeholder', $(this).parent().prev('label').text());
			$(this).parent().prev('label').hide();
			$(this).addClass('modified-input');
			$(this).parent().parent('modified-form-group');
		}
	});
	function closeCart(){
		$('#cart-container').removeClass('show');
		setTimeout(function(){
			$('#cart-container').modal('hide');
		}, 50);
	}
	
	function ajaxProducts(url) {
		$('#ajax-wrapper').addClass("loading");
		$('#pagination-top').load(url+" #pagination");
		
		$('#button-load-more i').html('<div class="lds-ring"><div></div></div>').addClass('button-loader');
		
		$('#temp-ajax-container').load(url+"&clean=1"+" #ajax-container", function() {
			//$('#temp-ajax-container').imagesLoaded( function() {
				$('#productsWrapper .pagination-page-container').remove();
				$('#ajax-wrapper').append($('#temp-ajax-container').html());
				$('#temp-ajax-container').html('').attr('data-href',url);
				history.pushState(null, null, url);
				$('#ajax-wrapper').removeClass("loading");
				createColorSwipers();
				createSizeSwipers();
			//});
		});	
	}
	
	$('#input-sort').bind('change', function(e) {
		var url = $(this).val();
		location = url;
	})
	
	/*
	$('#input-sort').bind('change', function(e) {
		if($('#temp-ajax-container').attr("data-href")) {
			var url = $('#temp-ajax-container').attr("data-href");
		} else {
			var url = $(this).val();
		}
		
		var sort = getURLVar('sort',$(this).val());
		var order = getURLVar('order',$(this).val());
		url = updateURLParameter(url,'sort',sort);	
		url = updateURLParameter(url,'order',order);	
		
		ajaxProducts(url);
	});
	$('#input-limit').bind('change', function(e) {
		if($('#temp-ajax-container').attr("data-href")) {
			var url = $('#temp-ajax-container').attr("data-href");
		} else {
			var url = $(this).val();
		}

		var limit = getURLVar('limit',$(this).val());
		url = updateURLParameter(url,'limit',limit);
		url = updateURLParameter(url,'page','');			
		
		ajaxProducts(url);
	});
	
	$('body').delegate('.pagination-page-container a', 'click', function(e) {
		e.preventDefault();
		e.stopPropagation();
		ajaxProducts($(this).attr('href'));				
	});	
	*/
	
	$('body').delegate('.pagination-page-container a', 'click', function(e) {
		e.preventDefault();
		e.stopPropagation();
		ajaxProducts($(this).attr('href'));				
	});		

	$('body').on('click', '#close-cart', function (e) {
		closeCart();
	});

	$('body').on('click', '.modal-backdrop', function (e) {
		closeCart();
	});
	
	$(document).keyup(function (e) {
		if (e.which == 27 && $('body').hasClass('modal-open')) {
			if ($('body').hasClass('modal-open') && $('#cart-container').hasClass('show')){
				closeCart();
			}
		}
	}) 
	
	// Highlight any found errors
	$('.text-danger').each(function() {
		var element = $(this).parent().parent();

		if (element.hasClass('form-group')) {
			element.addClass('has-error');
		}
	});

	// Currency
	$('#form-currency .currency-select').on('click', function(e) {
		e.preventDefault();

		$('#form-currency input[name=\'code\']').val($(this).attr('name'));

		$('#form-currency').submit();
	});

	// Language
	$('#form-language .language-select').on('click', function(e) {
		e.preventDefault();

		$('#form-language input[name=\'code\']').val($(this).attr('name'));

		$('#form-language').submit();
	});

	/* Search */
	$('#search input[name=\'search\']').parent().find('button').on('click', function() {
		var url = $('base').attr('href') + 'index.php?route=product/search';
		var value = $('header #search input[name=\'search\']').val();
		var category = $('header #search select[name=\'category_id\']').val();
		if (value) {
			//url += '&search=' + encodeURIComponent(value) +'&category_id=' + encodeURIComponent(category) + '&&sub_category=true';
			url += '&search=' + encodeURIComponent(value);
		}
		location = url;
	});
	if(window.innerWidth > 992){
		$('#search input[name=\'search\']').focus(function() {
			$(this).parent().addClass('expanded');
		});
		$('#search .select-categories-header-search i').click(function() { 
			$(this).parent().parent().removeClass('expanded');
		});
	} else {
		$('#search .close-arrow').click(function() { 
			$('#header-bottom').fadeOut(300);
		});
		
		$(".ho-filter-trigger-button").on('click',function(e) {
			$("#"+$(this).attr('data-wrapper')).addClass('show');
			$('body').addClass('filters-expanded');
		});
		$(".filters-responsive-close").on('click',function(e) {
			$("#"+$(this).attr('data-wrapper')).removeClass('show');
			$('body').removeClass('filters-expanded');
		});	
		$(".filters-responsive-results").on('click',function(e) {
			$("#"+$(this).attr('data-wrapper')).removeClass('show');
			$('body').removeClass('filters-expanded');
		});	
	}
	$('#search input[name=\'search\']').on('keydown', function(e) {
		if (e.keyCode == 13) {
			$('header #search input[name=\'search\']').parent().find('button').trigger('click');
		}
	});

	// Menu
	$('#menu .dropdown-menu').each(function() {
		var menu = $('#menu').offset();
		var dropdown = $(this).parent().offset();

		var i = (dropdown.left + $(this).outerWidth()) - (menu.left + $('#menu').outerWidth());

		if (i > 0) {
			$(this).css('margin-left', '-' + (i + 10) + 'px');
		}
	});

	// Product List
	$('#list-view').click(function() {
		$('#content .product-grid > .clearfix').remove();

		$('#content .row > .product-grid').attr('class', 'product-layout product-list col-xs-12');
		$('#grid-view').removeClass('active');
		$('#list-view').addClass('active');

		localStorage.setItem('display', 'list');
	});

	// Product Grid
	$('#grid-view').click(function() {
		// What a shame bootstrap does not take into account dynamically loaded columns
		var cols = $('#column-right, #column-left').length;

		if (cols == 2) {
			$('#content .product-list').attr('class', 'product-layout product-grid col-lg-6 col-md-6 col-sm-12 col-xs-12');
		} else if (cols == 1) {
			$('#content .product-list').attr('class', 'product-layout product-grid col-lg-4 col-md-4 col-sm-6 col-xs-12');
		} else {
			$('#content .product-list').attr('class', 'product-layout product-grid col-lg-4 col-md-4 col-sm-6 col-xs-12');
		}

		$('#list-view').removeClass('active');
		$('#grid-view').addClass('active');

		localStorage.setItem('display', 'grid');
	});

	if (localStorage.getItem('display') == 'list') {
		$('#list-view').trigger('click');
		$('#list-view').addClass('active');
	} else {
		$('#grid-view').trigger('click');
		$('#grid-view').addClass('active');
	}

	// Checkout
	$(document).on('keydown', '#collapse-checkout-option input[name=\'email\'], #collapse-checkout-option input[name=\'password\']', function(e) {
		if (e.keyCode == 13) {
			$('#collapse-checkout-option #button-login').trigger('click');
		}
	});

	// tooltips on hover
	$('[data-toggle=\'tooltip\']').tooltip({container: 'body'});

	// Makes tooltips work on ajax generated content
	$(document).ajaxStop(function() {
		$('[data-toggle=\'tooltip\']').tooltip({container: 'body'});
	});

	if($('main > div').attr('id') == 'common-home') {
		$('body').addClass('homePosition');
	}
});

function quickview(e) {
	$('body').append('<div class="quick-view-temp-container"><div id="product-product" class="quickview-container" style="display:none;"></div></div>')

    jQuery.ajax({
        url: e.attr('data-url'),
        success: function(data,status,jqXHR) {
			console.log(data);
            data = jQuery(data).find( '#product-row' );
			
            jQuery('.quickview-container').html(data);
        }
    }).done(function(){
		$.fancybox.open({
			src  : '.quickview-container',
			type : 'inline',
			idleTime: false,
			opts : {
				afterClose: function() {
					$('.quick-view-temp-container').each(function(index) {
						$(this).remove();
					});
				}
			},
			'autoSize': false,
			'width': 1400,
			'height': 800
		});	
	});
}

// Cart add remove functions
var cart = {
	'add': function(product_id, quantity, event = null) {

		var data_price = 0;
		var data_element = null;
		var data_name = '';
		var product_id = product_id;
		var quantity = quantity;

		if( event ) {
			var data_element 	= event;
			var data_price 		= event.data('price').toString().replace(/\,/g, '.');
			var data_name 		= event.data('name');
		};

		$.ajax({
			url: 'index.php?route=checkout/cart/add',
			type: 'post',
			data: 'product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			beforeSend: function() {
				$('#cart > button').button('loading');
			},
			complete: function() {
				$('#cart > button').button('reset');
			},
			success: function(json) {

				$('.alert-dismissible, .text-danger').remove();

				if (json['redirect']) {
					location = json['redirect'];
				}

				if (json['success']) {
					//$('#content').parent().before('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');

					// Need to set timeout otherwise it wont update the total
					
					pintrk('track', 'AddToCart', {
						value: data_price,
						order_quantity: quantity,
						currency: 'EUR',
						product_ids: [data_name + ':' + product_id]
					});
				
					fbq('track', 'AddToCart', {
						content_name: data_name, 
						content_ids: product_id,
						content_type: 'product',
						value: data_price,
						currency: 'EUR'
					});    
				
					gtag('event', 'add_to_cart', {
						"items": [
							{
								id: product_id,
								name: data_name,
								brand:'GINI GROUP',
								category: 'Apparel & Accessories > Jewelry',
								price: data_price,
								quantity: quantity
							}
						]  
					}); 

					setTimeout(function () {
						
						$('#shopping_cart').remove('#shopping_cart .pointer').append(json['total']);
						$('.cart-total').html(json['total']);
						$('.cart-total').attr('data-has-total', json['total']);
					}, 100);

					$('#cart-container .modal-inner').load('index.php?route=common/cart/info #cart-container .cart-content');	
							
					setTimeout(function(){
						$('#cart-container').modal('show');
					}, 200);
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},

	'multiple': function( product_ids = '', quantity = 1 ) {
		
		var ids = [];
		
		if(product_ids) {
			ids = product_ids.split(",");
		};
		  
		$.ajax({
			url: 'index.php?route=checkout/cart/addMultiple',
			type: 'post',
			data: 'product_ids=' + ids + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			beforeSend: function() {
				$('#cart > button').button('loading');
			},
			complete: function() {
				$('#cart > button').button('reset');
			},
			success: function(json) {
				$('.alert-dismissible, .text-danger').remove();

				if (json['redirect']) {
					location = json['redirect'];
				}

				if (json['success'] && !json['redirect']) {

					setTimeout(function () {
						
						$('#shopping_cart').remove('#shopping_cart .pointer').append(json['total']);
						$('.cart-total').html(json['total']);
						$('.cart-total').attr('data-has-total', json['total']);
					}, 100);

					$('#cart-container .modal-inner').load('index.php?route=common/cart/info #cart-container .cart-content');	
							
					setTimeout(function(){
						$('#cart-container').modal('show');
					}, 200);

				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});

	},	

	'update': function(key, quantity) {
		$.ajax({
			url: 'index.php?route=checkout/cart/edit',
			type: 'post',
			data: 'key=' + key + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			beforeSend: function() {
				$('#cart > button').button('loading');
			},
			complete: function() {
				$('#cart > button').button('reset');
			},
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					
					$('#cart > a #cart-total').html(json['total']);
					$('#cart > a #cart-total').attr('data-has-total', json['total']);
					
				}, 200);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					location = 'index.php?route=checkout/cart';
				} else {
					$('#cart-container .modal-inner').load('index.php?route=common/cart/info #cart-container .cart-content');	
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function(key) {
		$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			beforeSend: function() {
				$('#cart > button').button('loading');
			},
			complete: function() {
				$('#cart > button').button('reset');
			},
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					$('#cart > a #cart-total').html(json['total']);
					$('#cart > a #cart-total').attr('data-has-total', json['total']);
				}, 100);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					location = 'index.php?route=checkout/cart';
				} else {
					$('#cart-container .modal-inner').load('index.php?route=common/cart/info #cart-container .cart-content');								
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
}

var voucher = {
	'add': function() {

	},
	'remove': function(key) {
		$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			beforeSend: function() {
				$('#cart > button').button('loading');
			},
			complete: function() {
				$('#cart > button').button('reset');
			},
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
				}, 100);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					location = 'index.php?route=checkout/cart';
				} else {
					$('#cart > ul').load('index.php?route=common/cart/info ul li');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
}

var wishlist = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=account/wishlist/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {
				$('.alert-dismissible').remove();

				if (json['redirect']) {
					location = json['redirect'];
				}

				if (json['success']) {
					$('#content').parent().before('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
					setTimeout(function(){ $('.alert').fadeOut() }, 3000);
				}

				$('.wish-total').html(json['total']);
				$('.wish-total').attr('data-has-total', json['total']);

				//$('html, body').animate({ scrollTop: 0 }, 'slow');
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function() {

	}
}

var compare = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=product/compare/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {
				$('.alert-dismissible').remove();

				if (json['success']) {
					$('#content').parent().before('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
					setTimeout(function(){ $('.alert').fadeOut() }, 3000);
					$('#compare-total').html(json['total']);

					//$('html, body').animate({ scrollTop: 0 }, 'slow');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function() {

	}
}
/* Agree to Terms */
$(document).delegate('.agree', 'click', function(e) {
	e.preventDefault();

	$('#modal-agree').remove();

	var element = this;

	$.ajax({
		url: $(element).attr('href'),
		type: 'get',
		dataType: 'html',
		success: function(data) {
			html  = '<div id="modal-agree" class="modal">';
			html += '  <div class="modal-dialog">';
			html += '    <div class="modal-content">';
			html += '      <div class="modal-header">';
			html += '        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
			html += '        <h4 class="modal-title">' + $(element).text() + '</h4>';
			html += '      </div>';
			html += '      <div class="modal-body">' + data + '</div>';
			html += '    </div>';
			html += '  </div>';
			html += '</div>';

			$('body').append(html);

			$('#modal-agree').modal('show');
		}
	});
});

$(document).on('keyup', '.quickcheckout-content .row-fields input[type=text],input#input-firstname,input#input-lastname, div#account-address input[type=text]', function(){
    $(this).val( removeAccEL($(this).val().toUpperCase()) );
});

$(document).on('keyup', '#input-email, #input-login-email, #input-payment-email', function(){
    $(this).val( $(this).val().toLowerCase() );
});

function removeAccEL( text ) {
	return typeof text !== "string" ?
		// handle cases that text is not a string
		text :
		// global replace of uppercase accented characters
		text.
			replace( /\u0386/g, "\u0391" ). // 'Ά':'Α'
			replace( /\u0388/g, "\u0395" ). // 'Έ':'Ε'
			replace( /\u0389/g, "\u0397" ). // 'Ή':'Η'
			replace( /\u038A/g, "\u0399" ). // 'Ί':'Ι'
			replace( /\u038C/g, "\u039F" ). // 'Ό':'Ο'
			replace( /\u038E/g, "\u03A5" ). // 'Ύ':'Υ'
			replace( /\u038F/g, "\u03A9" ). // 'Ώ':'Ω'
			replace( /\u0390/g, "\u03CA" ). // 'ΐ':'ϊ'
			replace( /\u03AC/g, "\u03B1" ). // 'ά':'α'
			replace( /\u03AD/g, "\u03B5" ). // 'έ':'ε'
			replace( /\u03AE/g, "\u03B7" ). // 'ή':'η'
			replace( /\u03AF/g, "\u03B9" ). // 'ί':'ι'
			replace( /\u03B0/g, "\u03CB" ). // 'ΰ':'ϋ'
			replace( /\u03CC/g, "\u03BF" ). // 'ό':'ο'
			replace( /\u03CD/g, "\u03C5" ). // 'ύ':'υ'
			replace( /\u03CE/g, "\u03C9" ); // 'ώ':'ω'
}

jQuery.fn.extend({
	removeAcc: function() {
		return this.each(function() {
			jQuery.removeAcc( this );
		});
	}
});

// Autocomplete */
(function($) {
	$.fn.autocomplete = function(option) {
		return this.each(function() {
			this.timer = null;
			this.items = new Array();

			$.extend(this, option);

			$(this).attr('autocomplete', 'off');

			// Focus
			$(this).on('focus', function() {
				this.request();
			});

			// Blur
			$(this).on('blur', function() {
				setTimeout(function(object) {
					object.hide();
				}, 200, this);
			});

			// Keydown
			$(this).on('keydown', function(event) {
				switch(event.keyCode) {
					case 27: // escape
						this.hide();
						break;
					default:
						this.request();
						break;
				}
			});

			// Click
			this.click = function(event) {
				event.preventDefault();

				value = $(event.target).parent().attr('data-value');

				if (value && this.items[value]) {
					this.select(this.items[value]);
				}
			}

			// Show
			this.show = function() {
				var pos = $(this).position();

				$(this).siblings('ul.dropdown-menu').css({
					top: pos.top + $(this).outerHeight(),
					left: pos.left
				});

				$(this).siblings('ul.dropdown-menu').show();
			}

			// Hide
			this.hide = function() {
				$(this).siblings('ul.dropdown-menu').hide();
			}

			// Request
			this.request = function() {
				clearTimeout(this.timer);

				this.timer = setTimeout(function(object) {
					object.source($(object).val(), $.proxy(object.response, object));
				}, 200, this);
			}

			// Response
			this.response = function(json) {
				html = '';

				if (json.length) {
					for (i = 0; i < json.length; i++) {
						this.items[json[i]['value']] = json[i];
					}

					for (i = 0; i < json.length; i++) {
						if (!json[i]['category']) {
							html += '<li data-value="' + json[i]['value'] + '"><a href="#">' + json[i]['label'] + '</a></li>';
						}
					}

					// Get all the ones with a categories
					var category = new Array();

					for (i = 0; i < json.length; i++) {
						if (json[i]['category']) {
							if (!category[json[i]['category']]) {
								category[json[i]['category']] = new Array();
								category[json[i]['category']]['name'] = json[i]['category'];
								category[json[i]['category']]['item'] = new Array();
							}

							category[json[i]['category']]['item'].push(json[i]);
						}
					}

					for (i in category) {
						html += '<li class="dropdown-header">' + category[i]['name'] + '</li>';

						for (j = 0; j < category[i]['item'].length; j++) {
							html += '<li data-value="' + category[i]['item'][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[i]['item'][j]['label'] + '</a></li>';
						}
					}
				}

				if (html) {
					this.show();
				} else {
					this.hide();
				}

				$(this).siblings('ul.dropdown-menu').html(html);
			}

			$(this).after('<ul class="dropdown-menu"></ul>');
			$(this).siblings('ul.dropdown-menu').delegate('a', 'click', $.proxy(this.click, this));

		});
	}
})(window.jQuery);
