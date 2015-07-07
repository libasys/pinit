/**
 * ownCloud - Pinit
 *
 * @author Sebastian Doell
 * @copyright 2014 sebastian doell sebastian@libasys.de
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

L.PinMarker = L.Marker.extend({
	  options: {
	 	pinId:0,
	 	wallId:0
	 },
	 initialize: function (latlngs, options) {
		L.Marker.prototype.initialize.call(this, latlngs, options);
	},
	 getPinId:function(){
	 	  return this.options.pinId;
	 }	
});

L.pinMarker = function (latlngs, options) {	 
return new L.PinMarker(latlngs, options);
};


var PinitPublic = function() {
	this.firstLoading = true;
	this.aPinsMap ={};
	this.aPinsHtml = [];
	this.currentViewMode = 0;
	this.maxImagesPerRow = 3;
	this.columnWidth = 230;
	this.currentPinWall = 0;
	this.latestPinIndex =0;
	this.PinMaxLoadonStart=5;
	this.pinContainer = null;
	this.mapObject = null;
	this.mapObjectMarker = {};
	this.layerMarker = null;
	this.currentMomIndex =0;
	this.calcMaxMarkerPerPage=Math.ceil(($('body').width() - 300)/134);
	
};

PinitPublic.prototype.init = function() {
	
	this.firstLoading = true;
  	this.getPins();
  	
	
	$('#refreshPinwall').on('click', this.getPins.bind(this));
	
	$('#showMap').on('click', function() {
		this.showMap();
		return false;
	}.bind(this));
	
	$('#tagsFilter').tagit({
		allowNewTags : false,
		tagsChanged : this.filterTagsChanged.bind(this),
		placeholder : t('pinit', 'Filter by tag')
	});
	
	 $("body").append('<div id="pinlistBg"></div>');
	 $("#pinlistBg").on('click',function(){
 		$('#pinlistBg').hide();
		$("#pinContainerShow").removeClass('isOpenDialog');
		$("#pinContainerShow").hide('fast');
		$("#pinContainerShow").html('');
		if (history && history.replaceState) {
			history.replaceState('', '', '#');
		}
		return false;
 	});
	
  };

PinitPublic.prototype.showMap = function() {
	if(!$('#showMap').hasClass('isMap')){
   	  	$('#showMap').addClass('isMap');
   	  	$('#map').height($('body').height()-74);
   	  	  this.currentViewMode=1;
   	  	  $('#pinlist').hide();
   	  	 $('#map').show();
   	  	  $('#mapPreview').show();
   	  	  this.initMapList();
   	  	 
   	  }else{
   	  	$('#showMap').removeClass('isMap');
   	  	this.currentViewMode=0;
   	  	this.mapObject.removeLayer(this.layerMarker);
   	  	$('#map').hide();
   	  	$('#mapPreview').hide();
   	  	$('#pinlist').show();
      	this.adjustGrid('.pinrow');
   	  }
};

PinitPublic.prototype.showMapPin = function(lat,lon,zoom,oMarker) {
	  this.mapObject.setView([lat, lon], zoom);
	  oMarker.openPopup();
	  //alert(oMarker.getPinId());
};

PinitPublic.prototype.showPin = function(pinId) {
	$.ajax({
			type : 'POST',
			url : OC.generateUrl('apps/pinit/showpinpublic'),
			data :{ 
				id:pinId,
			    token : $('#pinlist').attr('data-token')
			},
			success : function(data) {
                 	$('#pinlistBg').show();
                 	$("#pinContainerShow").html(data);
 					$('#show-pin .avatarrow').avatar($(this).data('user'), 64);
					
					//$("#pinContainer").css({'width':'800px','margin-left':'-400px','top':'80px'});
					
				$("#pinContainerShow").addClass('isOpenDialog');
				$("#pinContainerShow").show('fast');

				$('#showPin-cancel').on('click', function() {
					$('#pinlistBg').hide();
					$("#pinContainerShow").removeClass('isOpenDialog');
					$("#pinContainerShow").hide('fast');
					$("#pinContainerShow").html('');
					if (history && history.replaceState) {
						history.replaceState('', '', '#');
					}
					
					return false;
				});
			}
		});
};


PinitPublic.prototype.adjustGrid = function(filter) {
	 var options ={
			srcNode: filter, // grid items (class, node)
			margin: '15px', // margin in pixel, default: 0px
			width: this.columnWidth+'px', // grid item width in pixel, default: 220px
			resizable: false, // re-layout if window resize
			transition: 'all 0.5s ease', // support transition for CSS3, default: all 0.5s ease
			};
			
			$('#pinlist').gridify(options);
};

PinitPublic.prototype.initPinsList = function(Timer) {
	   
        
         this.latestPinIndex=0;
	     this.loadRowPins();
        this.adjustGrid('.pinrow');
        
        $('#pinlist').removeClass('icon-loading').css({'width':'auto','height':'auto'});
        
		$('#pinlist .avatarrow').each(function() {
			var element = $(this);
			element.avatar(element.data('user'), 64);
		});


		$('.toolTip').tipsy({
			html : true,
			gravity : $.fn.tipsy.autoNS
		});

};

PinitPublic.prototype.initMapList = function() {
	
	var attribution = '&copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>';
	    if( this.mapObject == null){
		   this.mapObject = L.map('map').setView([51.505, -0.09], 2);
				L.tileLayer('http://otile{s}.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.png', {
			    attribution:attribution,
			    maxZoom: 18,
			    subdomains : "1234"
		   }).addTo(this.mapObject);
		   
	      $('#mapPreview .previewNext').on('click',function(){
	       	   	this.nextPagePreview();
	       }.bind(this));
	       $('#mapPreview .previewPrevious').on('click',function(){
	       	   	this.previousPagePreview();
	       }.bind(this));
	       
	  }else{
	  	  this.mapObject.setView([51.505, -0.09], 2);
	  }
	  
	  
	 // this.layerMarker = L.layerGroup();
	 this.layerMarker = L.markerClusterGroup();
	    //showMapPin
	   var mapPreviewPins= [];
	   $('#mappinsInner').empty();
	   var counter=0;
	
	   $.each(this.aPinsMap,function(i,element){
	   	      var redMarker = L.AwesomeMarkers.icon({
				    icon: element.icon,
				    markerColor: element.markercolor
				  });
				 var popupContent='<span class="pinmap-title">'+element.title+'</span>'; 
				    
				      if(element.image!=''){
	   	               popupContent+='<br /><span class="pinmap-image"><img height="150" src="data:' + element.imageMimeType + ';base64,' + element.image+'"  /></span>';
	   	                 
	   	              }
	   	               popupContent+='<br /><span class="pinmap-link"><a href="#'+element.id+'">Details</a></span>';
				   	    this.mapObjectMarker[i] = L.pinMarker([element.lat, element.lon],{'title':element.title,pinId:element.id,icon:redMarker}).bindPopup(popupContent);
			            this.layerMarker.addLayer( this.mapObjectMarker[i]);
                      mapPreviewPins[counter] = element;
             counter++;
	   	   
	   }.bind(this));
	        
	        mapPreviewPins.reverse();
	        
	        $.each(mapPreviewPins,function(i,element){
	        	
	        	  if(element.image!=''){
	   	                
	   	                var divImg=$('<div/>').attr({
	   	                	'class':'mapDescr mapImg',
	   	                	'data-index':i,
	   	                	'title':element.title
	   	                })
	   	                .on('click',function(){
	   	                	this.showMapPin(element.lat,element.lon,16, this.mapObjectMarker[element.id]);
	   	                }.bind(this));
	   	                
	   	                var ImgMap=$('<img/>').attr({
	   	                	'height':80,
	   	                	'src':'data:' + element.imageMimeType + ';base64,' + element.image,
	   	                	'class':'mapPics'
	   	                });
	   	                divImg.append(ImgMap);
	   	                  $('#mappinsInner').append(divImg); 
	   	              }else{
	   	              	 var divtitle=$('<div/>').attr({
	   	                	'class':'mapDescr mapTitle',
	   	                	'data-index':i
	   	                }).on('click',function(){
	   	                	this.showMapPin(element.lat,element.lon,16, this.mapObjectMarker[element.id]);
	   	                }.bind(this));
	   	                
	   	              	var descrMapPin=$('<div/>').attr({
	   	              		'class':'mapTitleInner',
	   	              		'data-index':counter,
	   	              		'title':element.title
	   	              	})
	   	              	.css({
	   	              		'background-color':element.backgroundColor,
	   	              		'color':element.titlecolor
	   	              	})
	   	              	.text(element.title);
	   	              	divtitle.append(descrMapPin);
	   	              	$('#mappinsInner').append(divtitle);
	   	                
	   	              }
	        }.bind(this));
	        
	        var Sides=($('#mappinsInner .mapDescr').length / this.calcMaxMarkerPerPage);
	       $('#mappins').width(this.calcMaxMarkerPerPage * 134);
	       $('#mappinsInner').width(this.calcMaxMarkerPerPage * 134 * (Sides + 1));
	       this.scrollToPreview(0);
	       
	       
	       
	           this.mapObject.addLayer(this.layerMarker);
               L.edgeMarker({'radius':20}).addTo(this.mapObject);
     
};

PinitPublic.prototype.nextPagePreview = function() {
	var mapPinsCount =$('#mappinsInner .mapDescr').length;
   

	if (mapPinsCount > this.currentMomIndex) {
		this.currentMomIndex +=  this.calcMaxMarkerPerPage ;
		if (mapPinsCount < this.currentMomIndex) {
			this.currentMomIndex-=this.calcMaxMarkerPerPage;
		}
	}
  
	this.scrollToPreview(this.currentMomIndex);

};

PinitPublic.prototype.previousPagePreview = function() {

	if (this.currentMomIndex > 0) {
		this.currentMomIndex -= this.calcMaxMarkerPerPage;
		if(this.currentMomIndex < 0){
			this.currentMomIndex=0;
		} 
	}
  
	this.scrollToPreview(this.currentMomIndex);

};

PinitPublic.prototype.scrollToPreview = function(index) {
	
		$('#mappins').scrollTo($('.mapDescr[data-index=' + index + ']'), 800);
	

};
PinitPublic.prototype.loadRowPins = function() {
	
	 var scroll = $('#content-wrapper').scrollTop() + $(window).scrollTop();
	 if(scroll == 0) {
	 	scroll=$(window).height();
	 }
	 var targetHeight = ($(window).height()) + scroll;
	 
	var count=(this.latestPinIndex + this.PinMaxLoadonStart);
	if(this.aPinsHtml.length < count){
		count = this.aPinsHtml.length;
	}
		
	if(this.aPinsHtml.length > this.latestPinIndex && $('#pinlist').height() < targetHeight){
		this.PinMaxLoadonStart=this.maxImagesPerRow;
		
		var biggestHeight=0;
	    var i=this.latestPinIndex;
	     
	    for(i; i< count; i++){
			var pinRow=this.loadPinRow(this.aPinsHtml[i]);
			 $('#pinlist').append(pinRow);
			 if(pinRow.height() > biggestHeight){
	    		biggestHeight=pinRow.height();
	    	}
			
		}
		
		this.latestPinIndex=i;
		this.adjustGrid('.pinrow');
		//this.updateCounterTags();
		//alert($('#pinlist').height());
		if(this.aPinsHtml.length > count){
		   
			$('#pinlist').height(biggestHeight+$('#pinlist').height());
			
			this.loadRowPins();
		}else{
			//this.showMeldung('all Pins loaded');
			return false;
		}
		
		
	}else{
		//this.showMeldung('all Pins loaded');
		return false;
	}
	//alert(this.latestPinIndex);
};

PinitPublic.prototype.getPins = function() {
	var Wallpaper = $('#wallpaper').val();
		if (Wallpaper != '') {
			$('#body-public').css('background', 'url(' + OC.imagePath('pinit', 'bg/' + Wallpaper) + ') repeat');
		} else {
			$('#body-public').css('background', '');
		}
		
		if(this.firstLoading == false){
        	this.aPinsMap = null;
		    this.aPinsMap = {};
		   
		    this.aPinsHtml = null;
		    this.aPinsHtml = [];
		   $('#pinlist').empty();
			
		}
		$('#pinlist').addClass('icon-loading').css({'width':'100%','height':'50%'});
		
		
		
		$.getJSON(OC.generateUrl('apps/pinit/pinspublic'), {
			token : $('#pinlist').attr('data-token'),
		}, function(jsondata) {
			if (jsondata) {
				var data = jsondata;
              this.calcDimensionPins();
               $.each(data, function(i, el) {
					this.aPinsHtml[i] = el;
                    if(el.lon!='' && el.lat!=''){
                     	this.aPinsMap[el.id] = el;
                    }
				}.bind(this));
               // OC.Pinit.currentViewMode
				if (this.firstLoading) {
					this.checkShowEventHash();
					this.firstLoading = false;
				} 
				if(this.currentViewMode==0){
					this.initPinsList(500);
				}
				if(this.currentViewMode==1){
					this.mapObject.removeLayer(this.layerMarker);
					this.initMapList();
				}
				

			}
		}.bind(this));
};

PinitPublic.prototype.showPinOptions = function(evt) {
	$(evt.currentTarget).find('.pintools').show();
};

PinitPublic.prototype.hidePinOptions = function(evt) {
	$(evt.currentTarget).children('.pintools').hide();
};

PinitPublic.prototype.switchToBackSide = function(evt) {
	var Id = $(evt.target).attr('data-id');
	          
		if (!$('div.pinrow[data-id="' + Id + '"]').hasClass('backside')) {
			$('div.pinrow[data-id="' + Id + '"]').addClass('backside');
			$('div.pinrow li.arrowmove[data-id="' + Id + '"]').removeClass('icon-arrowright');
			$('div.pinrow li.arrowmove[data-id="' + Id + '"]').addClass('icon-arrowleft');
			//arrowmove
			$('div.pinrow[data-id="' + Id + '"] .card .face.back').slideDown();

		} else {
			$('div.pinrow[data-id="' + Id + '"]').removeClass('backside');
			$('div.pinrow li.arrowmove[data-id="' + Id + '"]').addClass('icon-arrowright');
			$('div.pinrow li.arrowmove[data-id="' + Id + '"]').removeClass('icon-arrowleft');
			$('div.pinrow[data-id="' + Id + '"] .card .face.back').slideUp();
		}
};

PinitPublic.prototype.loadPinRow = function(element) {
	var filterDescr = 'none';
		if (element.pincolor != '') {
			filterDescr = element.pincolor;
		}

		var div = $('<div/>').attr({
			'data-id' : element.id,
			'class' : 'pinrow ' + filterDescr
		}).on('mouseenter', this.showPinOptions).on('mouseleave', this.hidePinOptions);

		div.css({
			'width' : (this.columnWidth) + 'px',
			'min-width' : (this.columnWidth) + 'px',
			'max-width' : (this.columnWidth) + 'px'
		});

		var divCard = $('<div/>').attr({
			'class' : 'card shadow'
		});
		div.append(divCard);
		var divFront = $('<div/>').attr({
			'class' : 'front face'
		});
		divCard.append(divFront);

		if (element.pincolor != '') {

			var divPinColor = $('<div/>').attr({
				'class' : 'pinstick'
			}).css('background', 'url(' + OC.imagePath('pinit', 'pins/' + element.pincolor + '.png') + ') no-repeat center');
			div.append(divPinColor);
		}
        if(element.newpin == 1){
      		var divPinNew = $('<div/>').attr({
				'class' : 'pinnew'
			}).css('background', 'url(' + OC.imagePath('pinit', 'new-red.png') + ') no-repeat center');
			div.append(divPinNew);
			}
			
			if (element.lon != '') {
          
			var divPinMarker = $('<div/>').attr({
				'class' : 'pinmarker'
			}).css('background', 'url(' + OC.imagePath('pinit', 'marker.png') + ') no-repeat center');
			div.append(divPinMarker);
		}
			//Edit Options
		var ul = $('<ul>').attr('class', 'pintools');
		div.append(ul);
		 /*
			var liReverse = $('<li>').attr({
			'data-id' : element.id,
			'class' : 'icon-arrowright arrowmove toolTip',
			'title' : t('pinit', 'More info')
		}).on('click', this.switchToBackSide);
		ul.append(liReverse);*/
		
		var liBig = $('<li/>');
        var aBig = $('<a/>').attr({
			'class' : 'svg icon-details toolTip',
			'title' : t('pinit', 'Show Details'),
			'href': '#' +element.id
		});
		liBig.append(aBig);
		ul.append(liBig);
			
		if (element.image != '') {
			var imgWidth=(element.imageWidth - 4);
			var imgHeight=(element.imageHeight - 4);
			
			if(element.imageWidth > element.imageHeight || element.imageWidth >= this.columnWidth){
			   var ratio=(imgWidth/this.columnWidth);
			   ratio = Math.round(ratio * 100) / 100;
			   imgWidth=(imgWidth/ratio);
			   imgHeight=(imgHeight/ratio);
			  
			}else{
				//marginLeft=(OC.Pinit.columnWidth-imgWidth)/2;
				
			}
			
			var divImg=$('<a/>')
			.attr({
				'class':'icon-loading img-div',
				'href':'#'+element.id,
				})
			.css({'display':'inline-block','width':(imgWidth-4)+'px','height':+(imgHeight-4)+'px','text-align':'center'});
			
			divFront.append(divImg);
			
			var img = new Image();
			 $(img).hide();
			 $(img).load(function(){
			 	divImg.removeClass('icon-loading');
			 	$(this).fadeIn(800);
			 	 $(img).width=imgWidth;
			 	 $(img).height=imgHeight;
			 	divImg.css({'width':'auto','height':'auto'});
			 })
			.css({
				'max-width' : (this.columnWidth-4) + 'px',
				'width':(imgWidth-4)+'px',
			}).attr({
				'src' : 'data:' + element.imageMimeType + ';base64,' + element.image,
				'data-id' : element.id,
				'class' : 'pinimage',
				'width':imgWidth
			});
			divImg.append(img);
			if (element.media_url != '') {
		 			var divPinMedia = $('<div/>').attr({
						'class' : 'pinmedia'
					}).css({
						'background': 'url(' + OC.imagePath('pinit', 'play.png') + ') no-repeat center',
						'left':((imgWidth / 2) -28)+'px',
						'top':((imgHeight / 2) -24)+'px',
						});
					divImg.append(divPinMedia);
		   		}

		} else {

			var textShadow='1px 1px #000';
             if(element.titlecolor == '#000000'){
             	textShadow='1px 1px #FAFAFA';
             }
			var span0 = $('<a/>').css({
				'color' : element.titlecolor,
				'background-color' : element.backgroundColor,
				'width' : (this.columnWidth - 4) + 'px',
				'text-shadow':textShadow
			}).attr({
				'data-id' : element.id,
				'class' : 'pinrow-noimage',
				'href':'#'+element.id
			}).text(element.title);
			divFront.append(span0);
		}
       
		
		if (element.categories != '') {
			//var tagsPin = element.categories.split(',');
			var aTag = [];
			$(element.categories).each(function(i, el) {
				aTag[i] = $('<a/>').attr({
					'class' : 'pin-tag',
					'data-pinid' : element.id,
					'data-tag' : $.trim(el.name)
				}).css({
					'background-color' : el.bgcolor,
					'color' : el.color
				}).text(el.name).on('click', function() {
					$('#tagsFilter').tagit('add', {
						label : $(this).attr('data-tag'),
						value : $(this).attr('data-tag')
					});
				});
			});
			var divAllTags = $('<span/>').attr({
				'class' : 'pin-tags-all'
			});
			divAllTags.append(aTag);
			divFront.append(divAllTags);
		}

		if (element.url != '' && element.image != '') {
			var span22 = $('<span/>').addClass('pinrow-url-domain').text(t('pinit', 'At')+' ' + element.domain+' '+t('pinit', 'found'));
			divFront.append(span22);
			var span2 = $('<span/>').addClass('pinrow-url-title');
			var aUrl=$('<a/>').attr({
				'class':'toolTip icon-link',
				'href':element.url,
				'title':element.url,
				'target':'_blank', 
			}).text(element.title);
			span2.append(aUrl);
			divFront.append(span2);
		}
		if (element.url != '' && element.image == '') {
			var span23 = $('<span/>').addClass('pinrow-url-domain').text(t('pinit', 'At')+' ' + element.domain+' '+t('pinit', 'found'));
			divFront.append(span23);
			var span2 = $('<span/>').addClass('pinrow-url');
			var aUrl=$('<a/>').attr({
				'class':'toolTip icon-link',
				'href':element.url,
				'title':element.url,
				'target':'_blank', 
			}).text(element.title);
			span2.append(aUrl);
			divFront.append(span2);
		}
		
		if (element.url == '' && element.image != '') {
			var span22 = $('<span/>').addClass('pinrow-url-domain').text(t('pinit', 'Uploaded of')+' ' +  element.userdisplayname);
			divFront.append(span22);
			var span21 = $('<span/>').addClass('pinrow-url-title').text(element.title);
			divFront.append(span21);
		}
		if (element.url == '' && element.image == '') {
			var span22 = $('<span/>').addClass('pinrow-url-domain').text(t('pinit', 'Published of')+' ' +element.userdisplayname);
			divFront.append(span22);
		}
		/*
		var divBack = $('<div/>').attr({
			'data-id' : element.id,
			'class' : 'back face'
		}).on('click', this.switchToBackSide);
		divCard.append(divBack);
        var br1 = $('<br/>');
		divBack.append(br1);
		var spanBack0 = $('<span/>').addClass('pinrow-back-title').text(element.title);
		divBack.append(spanBack0);
		if (element.description != '') {
			var spanBack1 = $('<span/>').addClass('pinrow-descr').text(element.description);
			divBack.append(spanBack1);
		}
		var spanBack2 = $('<span/>').addClass('pinrow-added').text(t('pinit', 'Added: ') + relative_modified_date(element.addDate));
		divBack.append(spanBack2);
		var spanBack3 = $('<span/>').addClass('pinrow-mod').text(t('pinit', 'Modified: ') + relative_modified_date(element.modifiedDate));
		divBack.append(spanBack3);

		var spanBack4 = $('<span/>').addClass('pinrow-autor').html('<div style="height: 32px; width: 32px;" class="avatarrow" data-user="' + element.user_id + '"></div><div class="autor"><b>' + element.userdisplayname + '</b><br/>Autor</div>');
		divBack.append(spanBack4);
		if (element.location != '') {
			//	locationInfo = '<img class="map toolTip" id="geoloc" title="'+element.location+'"  src="http://maps.google.com/maps/api/staticmap?zoom=15&size='+(OC.Pinit.columnWidth - 6)+'x200&maptype=terrain&sensor=false&center=' + element.location + '" />';
			var spanBack5 = $('<span/>').addClass('pinrow-map').text(element.location);
			
			divBack.append(spanBack5);
		}*/

		return div;
};

PinitPublic.prototype.calcDimensionPins = function() {
	if ($('body').width() >= 900 && $('body').width() <= 1440) {
			this.maxImagesPerRow = 5;
		} else if ($('body').width() > 1440) {
			this.maxImagesPerRow = 6;
		} else {
			this.maxImagesPerRow = 3;
			
			if ($('body').width() <= 570){
				this.maxImagesPerRow = 2;
			} 
		}
		this.columnWidth = $('body').width() / this.maxImagesPerRow;
		this.columnWidth = Math.round(this.columnWidth);
		this.columnWidth = (this.columnWidth - 20);
};

PinitPublic.prototype.filterTagsChanged = function(evt) {

	if (this.firstLoading == false) {
 
			if ($('#tagsFilter').tagit('tags').length > 0) {
				
				var filterArray = $('#tagsFilter').tagit('tags');
				$('#pinlist .pinrow').hide();
                $('#pinlist .pinrow').removeClass('activeFilter');

				$('.pinrow').each(function() {
					var element = $(this);
					var filterCounter = 0;
					$(filterArray).each(function(i, filter) {
						if (element.find('.pin-tag[data-tag="' + $.trim(filter.value) + '"]').length > 0) {
							filterCounter++;
						}
					});

					if (filterCounter == filterArray.length) {
						element.addClass('activeFilter');
					}
				});

				$('#pinlist .pinrow.activeFilter').show();
			   this.adjustGrid('.pinrow.activeFilter');
			}

			if ($('#tagsFilter').tagit('tags').length == 0) {
				  $('#pinlist .pinrow').removeClass('activeFilter');
				  $('#pinlist .pinrow').show();
				 this.adjustGrid('.pinrow');
			}
		}
};

PinitPublic.prototype.checkShowEventHash = function() {
	var id = parseInt(window.location.hash.substr(1));
		
		if (id) {
			this.showPin(id);
		}
};

var myPinitPublic=null;

$(window).resize(_.throttle(function() {
	
	myPinitPublic.calcDimensionPins();
	myPinitPublic.calcMaxMarkerPerPage=Math.ceil(($('body').width() - 300)/134);
	
	$('.pinrow').css({
		'width' : (myPinitPublic.columnWidth) + 'px',
		'min-width' : (myPinitPublic.columnWidth) + 'px'
	});
	$('.pinrow img.pinimage').css({
		'max-width' : (myPinitPublic.columnWidth-4) + 'px'
	});
	
	 $('.pinrow .img-div').css({
		'max-width' : (myPinitPublic.columnWidth-4) + 'px'
	});
	$('.pinrow .pinrow-noimage').css({
		'width' : (myPinitPublic.columnWidth - 4) + 'px'
	});
	$('.pinrow .pinrow-map img.map').css({
		'width' : (myPinitPublic.columnWidth - 6) + 'px'
	});
  //  if(myPinitPublic.currentViewMode==0){
		myPinitPublic.adjustGrid('.pinrow');
		$('body').height($(window).height()-62);
//	}
	//alert(OC.Pinit.currentPinWall);
}, 500));


$(document).ready(function() {
	  $('#body-public').addClass('appbody-pinit');
	  $('#body-public').removeClass('appbody-gallery');
	  
	 myPinitPublic = new PinitPublic();
	 myPinitPublic.init();
	 $('body').height($(window).height()-62);
	  $('#content-wrapper').scroll(function() {
		if(myPinitPublic.currentViewMode==0){
			myPinitPublic.loadRowPins();
			
		}
	});
	
}); 

$(window).bind('hashchange', function() {
	myPinitPublic.checkShowEventHash();
	
});
