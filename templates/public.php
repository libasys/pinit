<?php
		
	
		style('pinit', '3rdparty/fontello/css/animation');
		style('pinit', '3rdparty/fontello/css/fontello');
		style('pinit', 'style');
		style('pinit', 'public');
		style('pinit', '3rdparty/leaflet');
		style('pinit', '3rdparty/MarkerCluster');
		style('pinit', '3rdparty/MarkerCluster.Default');
		style('pinit', '3rdparty/leaflet.awesome-markers');
		style('pinit', '3rdparty/bootstrap');
		style('pinit', '3rdparty/jquery.tagit');	
		style('pinit', 'mobile');
		style('files_sharing', 'authenticate');
?>
<input type="hidden" id="wallpaper" value="<?php p($_['PinwallBg'])?>" />
<header>
	<div id="header">
		<a href="<?php print_unescaped(link_to('', 'index.php')); ?>"
			title="<?php p($theme -> getLogoClaim()); ?>" id="owncloud">
			<div class="logo-icon svg"></div>
		</a>
		<div id="logo-claim" style="display:none;"><?php p($theme -> getLogoClaim()); ?></div>
		<ul id="tagsFilter" style="padding:0;width:200px;float:left;margin-left:80px;margin-top:1px;"></ul>
		<div class="header-right">
			<button id="showMap"><i class="ioc ioc-globe"></i> <?php p($l->t('Map')); ?></button>
			<button id="refreshPinwall"><i class="ioc ioc-refresh"></i> Pinwall</button>
			
			<span id="details"><?php  p('Pinwall "'.$_['PinwallName'].'" '.$l->t('shared by %s', $_['displayName'])) ?></span>
		</div>
	</div>
</header>
<div id="content-wrapper">
	<div id="pinlist" class="hascontrols" style="margin-top:20px;" data-requesttoken="<?php p($_['requesttoken'])?>" data-token="<?php isset($_['token']) ? p($_['token']) : p(false) ?>"></div>
</div>		 

 <div id="map" style="display:none;"></div>
<div id="mapPreview"  style="display:none;">
			 <input type="button" class="svg previewNext icon-view-next"/>
			<input type="button" class="svg previewPrevious icon-view-previous"/>
			 <div id="mappins"><div id="mappinsInner"></div></div>
		 </div>

	<div class="info">
		<?php print_unescaped($theme -> getLongFooter()); ?>
	</div>

<div id="pinContainerShow" style="display:none;"></div>
