jQuery(function($) {
	var filters = {
		/**
		 * Method to return absoulte path
		 */
		getAbsolutePath : function(url ,donotSet,options) {			
			var loc = window.location;
			var pathName = loc.pathname.substring(0,loc.pathname.lastIndexOf('/') + 1);		
				
			//let us check the url is non-seo friendly ?
			if(loc.href.indexOf("view") > -1 && loc.href.indexOf("option") > -1 && loc.href.indexOf('catid') > -1  && loc.href.indexOf('Itemid') > -1){
				var absoulte_url = loc.origin+pathName;			 				
				if( $(options.form_id).length > 0  &&  $(options.form_id).attr('data-link') ){
					if($(options.form_id).data('link')){
						absoulte_url =absoulte_url + $(options.form_id).data('link');
					}
				}else if($(options.topFilter_id).length > 0 && $(options.topFilter_id).attr('data-link') ){
					absoulte_url =absoulte_url + $(options.form_id).data('link');
				}else if($('.j2store-product-list').length > 0 && $(options.topFilter_id).attr('data-link') ){					 
					absoulte_url =absoulte_url + $('.j2store-product-list').data('link');					
					//here we have to do another safety measure
				}else if(j2store_product_base_link !==''){					
					absoulte_url =absoulte_url +j2store_product_base_link;
				}			
				url = url.indexOf('&',0)  === -1 ? "&"+url : url;
			 	document.location.href  = absoulte_url+url;
			 
			}else{
				var absoulte_url = loc.href.substring(0,loc.href.length	- ((loc.pathname + loc.search + loc.hash).length - pathName.length));	// remove the first occurance & sign from the url
				url = url.substr(1);
				var site_url = absoulte_url.indexOf("?") === -1 ? "?" : "&";	
				if(donotSet == true){				
					return site_url + url;
				}else{
				document.location.href = site_url + url;
				}
			}				
			
		},

		getQueryParam : function(param) {
			var result = window.location.search.match(new RegExp("(\\?|&)"
					+ param + "(\\[\\])?=([^&]*)"));
			return result ? result[3] : false;
		},

		removeParameter : function(url, parameter, onlyselected) {
			var urlparts = url.split('?');

			if (urlparts.length >= 2) {
				var urlBase = urlparts.shift(); // get first part, and remove
												// from array
				var queryString = urlparts.join("?"); // join it back up
				var prefix = parameter + '=';
				var pars = queryString.split(/[&;]/g);
				for ( var i = pars.length; i-- > 0;)
					// reverse iteration as may be destructive
					if (pars[i].lastIndexOf(prefix, 0) !== -1) // idiom for
																// string.startsWith
						pars.splice(i, 1);

				url = urlBase + '?' + pars.join('&');
			}
			return url;
		}

	}

	$.J2StoreFilters = function(options) {
		this.init(options);
	}

	$.J2StoreFilters.prototype = {
				
		init : function(options) {		 
			
			/**  
			 * let us assing default value for the var . 
			 * incase  the option is empty
			 */  
			if(options.form_id ==''){				
				options.form_id='#productsideFilters';
			}
			
			if(options.topFilter_id ==''){
				options.topFilter_id='#productFilters';
			}
		 
			/** let us first check the element exists **/ 
			if((options.form_id).length != 0){
				
			/**  now  call  trigger the sideFilters  Brands , Vendors , Product Filters **/ 
				this.sideFilter(options);			
			
			/**  now  call trigger the Category filters **/	
				this.categoryFilter(options);
				
			/** now also call the price slider **/
				this.priceSlider(options);
		
			/** Will remove the href params query of manufacturer_ids[] * */
			$(options.form_id).find('.manufacturer-filters a').on('click',function() {
				document.getElementById('j2store-product-loading').style.display='block';
				if (filters.getQueryParam("manufacturer_ids")) {
					location.href = filters.removeParameter(window.location.href,'manufacturer_ids[]');
				} else {
					$(options.form_id).find(".j2store-brand-checkboxes").each(function() {
							this.checked = false;
					});
				}
				document.getElementById('j2store-product-loading').style.display='none';
			});

			/** Will remove the href params query of vendor_ids[] * */
			/** Event fire when Clear Link cliked for Vendors  **/
			$(options.form_id).find('.j2store-product-vendor-filters a').on('click',function() {
						document.getElementById('j2store-product-loading').style.display='block';
						if (filters.getQueryParam("vendor_ids")) {
							location.href = filters.removeParameter(
									window.location.href, 'vendor_ids[]');
						}
						document.getElementById('j2store-product-loading').style.display='none';
					});

			/** Event fire when Clear Link cliked form Product filters  **/
			$(options.form_id).find('.productfilters-list a').on('click',function() {
					document.getElementById('j2store-product-loading').style.display='block';
						if (filters.getQueryParam("productfilter_ids")) {
							jQuery("." + $(this).data('class')).each(
									function() {
										this.checked = false;
							});

					 var url = '';
					 $(options.form_id).find('input[type="checkbox"]').each(function() {
									if (this.checked) {
										url += '&' + this.name+ '=' + this.value;}
								});
					 
					 	if($(options.form_id).find("li.active").length > 0){
					 		var active_li = $(options.form_id).find("li.active").find('a');
					 		url += '&' + $(active_li).data('key') + '='+ $(active_li).data('value');
					 	}
						
					 	if( $(options.form_id+  " #j2store-slider-range").length !=0){					 		
					 		//let us append price filters
					 		url +='&pricefrom='+$(options.form_id).find("#min_price_input").attr('value');								
					 		url +='&priceto='+$(options.form_id).find("#max_price_input").attr('value');
					 	}						
						
						 filters.getAbsolutePath(url ,false,options);
						}
				document.getElementById('j2store-product-loading').style.display='none';
					});
			}
			
			/** call the event fire for TopFilters **/
			this.topFilter(options);		
				
		},
		categoryFilter : function(options){			
			$(options.form_id).find('.j2product-categories').on('click',function(e) {
				document.getElementById('j2store-product-loading').style.display='block';
				var url = '';
				// we can make use this line to clear the filter
				if($('.j2product-categories > a').data('key')) {				
					 url += '&' + $(this).find('a').data('key')+ '='	+ $(this).find('a').data('value');
				}
				
				if($(options.form_id).find("li.active").length != 0){
					$(options.form_id).find('input[type="checkbox"]:checked').each(function(index, el) {
						url += '&'+ $(el).attr('name')+ '='+ $(el).attr('value');
					});					
				}
				
				if($(options.topFilter_id).length != 0){	
					if($(options.topFilter_id).find('input[name=search]').length != 0 ){
						var search = $(options.topFilter_id).find('input[name=search]');				 	
						if(search !=''){
							url += '&' + $(search).attr('name') + '='+ $(search).attr('value');
					 	}
					}
					
					 if($(options.topFilter_id).find('select[name=sortby]').length !=0){
						 var sortby = $(options.topFilter_id).find('select[name=sortby] option:selected');
						 if(sortby){
							 url += '&' + $(options.topFilter_id).find('select[name=sortby]').attr('name') + '='+ $(options.topFilter_id).find('select[name=sortby]').attr('value');
						 }
					 }
				}
				
				if( $(options.form_id+  " #j2store-slider-range").length !=0){
					//let us append price filters
					url +='&pricefrom='+$(options.form_id).find("#min_price_input").attr('value');								
					url +='&priceto='+$(options.form_id).find("#max_price_input").attr('value');
				}
				
				filters.getAbsolutePath(url,false,options);
			});
		},
		
		sideFilter : function(options){			
			$(options.form_id).find('input[type="checkbox"]').on('change',function(el) {
				document.getElementById('j2store-product-loading').style.display='block';
						var url = '';						
						if((options.form_id).length != 0){							
							$(options.form_id).find('input[type="checkbox"]').each(function() {
								if (this.checked) {
									url += '&' + this.name + '='+ this.value;
								}
							});
							
							
							//let us check category related div exists
							if($(options.form_id).find("li.active").length != 0){								
								var active_li = $(options.form_id).find("li.active").find('a');							
								url += '&' + $(active_li).data('key') + '='+ $(active_li).data('value');
							}
													
							
							//check price slider exists
							if($(options.form_id).find("#j2store-slider-range").length !=0){		
								//let us append price filters
								url +='&pricefrom='+$(options.form_id).find("#min_price_input").attr('value');								
								url +='&priceto='+$(options.form_id).find("#max_price_input").attr('value');
							}
						}
						
						if($(options.topFilter_id).length != 0){
							// step 4 : let us check filter search exists				
							if($(options.topFilter_id).find('input[name=search]').length != 0 ){
								 var search = $(options.topFilter_id).find('input[name=search]');
								 	if(search !=''){
								 		url += '&' + $(search).attr('name') + '='+ $(search).attr('value');
								
								 	}	
							}
						 	
							if($(options.topFilter_id).find('select[name=sortby]').length !=0){						 
							 	var sortby = $(options.topFilter_id).find('select[name=sortby] option:selected');
								 if(sortby){
									 url += '&' + $(options.topFilter_id).find('select[name=sortby]').attr('name') + '='+ $(options.topFilter_id).find('select[name=sortby]').attr('value');
								 }
							}													
						}		
						
						 
						filters.getAbsolutePath(url ,false ,options);

					});
		},
		
		topFilter : function(options){
			if($(options.topFilter_id).length != 0){
				var url ='';	
				//prevent the form submit incase search filter exists
				$(options.topFilter_id).submit(function(e){	
					e.preventDefault();	
					//let us check category related div exists
					if($(options.form_id).find("li.active").length > 0){	
						var active_li = $(options.form_id).find("li.active").find('a');
						url += '&' + $(active_li).data('key') + '='+ $(active_li).data('value');
					}
					if($(options.form_id).find("li.active").length != 0){
						 $(options.form_id).find('input[type="checkbox"]:checked').each(function(index, el) {
										url += '&'+ $(el).attr('name')+ '='+ $(el).attr('value');
						 });
					}
							
					 if($(options.topFilter_id).find('input[name=search]').length != 0 ){
						 var search = $(options.topFilter_id).find('input[name=search]');						
						 if(search !=''){
							 url += '&' + $(search).attr('name') + '='+ $(search).attr('value');						 
						 }				
					 }
					 
					 if($(options.topFilter_id).find('select[name=sortby]').length !=0){
						 var sortby = $(options.topFilter_id).find('select[name=sortby] option:selected');
						 url += '&' + $(options.topFilter_id).find('select[name=sortby]').attr('name') + '='+ $(options.topFilter_id).find('select[name=sortby]').attr('value');
					 }
					
					//check price slider exists
					 if( $(options.form_id).find("#j2store-slider-range").length !=0){		 
							//let us append price filters
							url +='&pricefrom='+$(options.form_id).find("#min_price_input").attr('value');								
							url +='&priceto='+$(options.form_id).find("#max_price_input").attr('value');
					}
						
					 filters.getAbsolutePath(url ,false ,options);				
				});
				
			}
		},
		priceSlider:function(options){			
			$(options.form_id).find("#j2store-slider-range").slider({				
										
					slide: function( event, ui ) {
						document.getElementById('j2store-product-loading').style.display='block';
						var url = '';						
						if((options.form_id).length != 0){		
							if($(options.form_id).find("li.active").length > 0){								
								var active_li = $(options.form_id).find("li.active").find('a');
									url += '&' + $(active_li).data('key') + '='+ $(active_li).data('value');									 
							}
							 
							// step 2 :  let us check any filters like brand ,vendor , pfilters exists
							if($(options.form_id).find('input[type="checkbox"]:checked').length !=0){
								$(options.form_id).find('input[type="checkbox"]').each(function() {
										if (this.checked) {
											url += '&' + this.name + '='+ this.value;
										}
								});
							}
						
						}
						
						
						if($(options.topFilter_id).length != 0){				
							var search = $(options.topFilter_id).find('input[name=search]').length;						 	 
						 	if(search > 0 ){
						 		url += '&' + $(options.topFilter_id).find('input[name=search]').attr('name') + '='+ $(options.topFilter_id).find('input[name=search]').attr('value');
						
						 	}						 	
						 	var sortby = $(options.topFilter_id).find('select[name=sortby] option:selected');							
							 if( $(options.topFilter_id).find('select[name=sortby]').length > 0){
								 url += '&' + $(options.topFilter_id).find('select[name=sortby]').attr('name') + '='+ $(options.topFilter_id).find('select[name=sortby]').attr('value');
							 }
						}
						
						if( $(options.form_id).find("#j2store-slider-range").length !=0){	
							//let us append  price filters
							url +='&pricefrom='+ui.values[0];
							url +='&priceto='+ui.values[1];
							
							$(options.form_id).find( "#min_price" ).html(ui.values[ 0 ]);
							$(options.form_id).find( "#max_price" ).html(  ui.values[ 1 ] );
						}							
						
						filters.getAbsolutePath(url ,false ,options);

					}
			});
		},			
	};

/*	var base_url;
	$(document).ready(function() {
		base_url = window.location.href;
		console.log(base_url);
	  });*/
	
	// call the class
	var myClassObj = new $.J2StoreFilters({'form_id':'#productsideFilters' ,'topFilter_id':'#productFilters'});

});
