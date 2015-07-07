<?php
		
		style('pinit', 'jquery.Jcrop');
		
		script('files', 'jquery.fileupload');
		script('pinit', 'jquery.Jcrop');
		script('pinit', '3rdparty/gridify');
		script('pinit', '3rdparty/tag-it');
		script('pinit', '3rdparty/leaflet');
		script('pinit', '3rdparty/Leaflet.EdgeMarker');
		script('pinit', '3rdparty/leaflet.markercluster-src');
		script('pinit', '3rdparty/leaflet.awesome-markers');
		script('pinit', 'jquery.scrollTo');
		script('pinit','pinit');
		style('pinit', '3rdparty/jquery.tagit');
		
		style('pinit', '3rdparty/leaflet');
		style('pinit', '3rdparty/MarkerCluster');
		style('pinit', '3rdparty/MarkerCluster.Default');
		style('pinit', '3rdparty/leaflet.awesome-markers');
		style('pinit', '3rdparty/bootstrap');
		style('pinit', '3rdparty/fontello/css/animation');
		style('pinit', '3rdparty/fontello/css/fontello');
		style('pinit','style');
		style('pinit', 'mobile');
		
		
?>
<input type="hidden" name="mailNotificationEnabled" id="mailNotificationEnabled" value="<?php p($_['mailNotificationEnabled']) ?>" />
<input type="hidden" name="allowShareWithLink" id="allowShareWithLink" value="<?php p($_['allowShareWithLink']) ?>" />
<input type="hidden" name="mailPublicNotificationEnabled"  value="<?php p($_['mailPublicNotificationEnabled']) ?>" />

<form style="display:none;" class="float" id="file_upload_form" action="<?php print_unescaped(\OCP\Util::linkToRoute('pinit.photo.uploadPhoto')); ?>" method="post" enctype="multipart/form-data" target="file_upload_target">
	<input type="hidden" name="id" value="">
	<input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']) ?>">
	<input type="hidden" name="MAX_FILE_SIZE" value="<?php p($_['uploadMaxFilesize']) ?>" id="max_upload">
	<input type="hidden" class="max_human_file_size" value="(max <?php p($_['uploadMaxHumanFilesize']); ?>)">
	<input id="pinphoto_fileupload" type="file" accept="image/*" name="imagefile" />
</form>
<iframe name="file_upload_target" id='file_upload_target' src=""></iframe>
<div id="searchresults" class="hidden"  data-appfilter="pinit"></div>
<div id="loading">
	<i style="margin-top:20%;" class="ioc-spinner ioc-spin"></i>
</div>
<div id="notification" style="display:none;"></div>
<div id="pinloadingmsg"></div>
<div id="controls">
	<div id="first-group" class="button-group" style="float:left;width:258px;padding-left:5px;">	
		
		<button class="button" id="addPinwall"><i class="ioc ioc-add"></i> <?php p($l->t('Pinwall')); ?></button>
		<button class="button" id="addPin"><i class="ioc ioc-add"></i> <?php p($l->t('Pin')); ?></button>
		<button class="button" id="showMap"><i class="ioc ioc-globe"></i> <?php p($l->t('Map')); ?></button>

	</div>
	<div id="second-group" class="button-group" style="float:left;width:420px;">	
		<button class="button filterPins" data-filter="all"><?php p($l->t('All')); ?></button>
	<?php
      	   foreach($_['aPinColors'] as $key => $val){
       	   	if($key == 0) {
       	   		$filter='<button class="button filterPins"  data-filter="'.$val.'">'.$l->t($val).'</button>';
			}else{	
       	   		$filter='<button class="button filterPins" data-filter="'.$val.'"><i class="ioc ioc-pin '.$val.'"></i></button>';
			}
			  print_unescaped($filter);
      	   }
      	?>
      
      	</div>
      	
</div>
<div id="app-navigation">
	<div class="innerNav">
	<br style="clear:both;" />
	<h3><?php p($l -> t("Pinwalls")); ?></h3>
	
	<ul id="pinWalls"></ul>
	
	<br style="clear:both;" /><br />
	
	<ul id="newTag" style="width:230px;margin-left:5px;height:30px;padding:0;padding-left:4px;border-radius:5px;"></ul>
	<ul id="tagsFilter" style="width:230px;margin-left:5px;padding:0;padding-left:4px;border-radius:5px;"></ul>
	<h3><?php p($l -> t("Existing Tags")); ?></h3>
	<br style="clear:both;" />
	<ul id="myTagList" ></ul>
</div>	
</div>
<div id="app-content" class="pinbackground">

		<div id="pinlist"></div>
		 <div id="map" style="display:none;"></div>
		 <div id="mapPreview"  style="display:none;">
			 <input type="button" class="svg previewNext icon-view-next"/>
			<input type="button" class="svg previewPrevious icon-view-previous"/>
			 <div id="mappins"><div id="mappinsInner"></div></div>
		 </div>
</div>
<div id="pinContainer" style="display:none;"></div>
<div id="pinContainerShow" style="display:none;"></div>

<div id="edit_photo_dialog" title="Edit photo">
		<div id="edit_photo_dialog_img"></div>
</div>
<div id="dialogSmall" style="display:none;"></div>
<div id="dialogPin" style="display:none;"></div>


