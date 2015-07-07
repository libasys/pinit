<div id="show-pin" data-id="<?php p($_['id']); ?>">
   
      <span id="innerLeftShow">
 	 <span class="header-left"> 
       <?php p($_['title']); ?>
       </span><br style="clear:both;" /><br />
 	  <?php 
     if($_['isPhoto'] && $_['mediaUrl'] == ''){
     	  $imgData=$_['imgsrc'];
		  print_unescaped('<img src="data:'.$_['imgMimeType'].';base64,'.$imgData.'" />');

     }
	   if($_['mediaUrl'] != ''){
	   	$mediaHeight=400;
		if($_['mediaSite'] == 'soundcloud'){
			$mediaHeight=150;
		}
		  	print_unescaped('<iframe src="'.$_['mediaUrl'].'" id="loadedMedia" width="98%" height="'.$mediaHeight.'" frameborder="no"></iframe>');
		print_unescaped('<span class="pinshow-url" style="padding:10px;">	<a class="icon-link" href="'.$_['mediaUrl'].'" target="_blank" title="'.$_['mediaUrl'].'">'.$l -> t("At").' '.$_['mediaDomain'].' '.$l -> t("found").'</a></span>');
	   }
 ?>
 	<?php if($_['description'] != ''){?>
	 <span class="pinshow-description" style="padding:10px;">	
	<?php p($_['description']); ?>
	</span>
<?php } ?>
    
       <br style="clear:both;" />
        <span class="footer-left">&nbsp;</span>
  </span>
 <span id="innerRightShow"> 
 
<span class="fixInfo">
<span class="pinshow-autor">	
<div style="float:left; border-radius:32px;" class="avatarrow" data-user="<?php p($_['user_id']); ?>"></div>
<span style="float:left;padding-left:10px;line-height:20px;padding-top:10px;">

	<b><?php p($_['userdisplayname']); ?></b>
	<br />
	<?php p($l -> t("Autor")); ?>
	</span>
	<div class="additionalInfo">
		<?php if($_['newpin']==1){
			 print_unescaped('<img class="svg" style="float:right;margin-right:-10px;margin-top:-5px;" src="'.OCP\Util::imagePath('pinit', 'new-red.png').'"  />');
		 } ?>
		<?php if($_['cPublic']==0){
			 print_unescaped('<i class="ioc ioc-lock" style="font-size:28px; margin-top:5px;" title="private Pin"></i>');
		 } ?>
		<?php if($_['choosenPinColor'] !='none'){
			 print_unescaped('<img style="float:right;margin-top:0px;" src="'.OCP\Util::imagePath('pinit', 'pins/'.$_['choosenPinColor'].'.png').'"  />');
		 } ?>	
		</div>
</span>
<span class="pinshow-date">	
<b><?php p($l -> t("Pinwall")); ?>:</b> <?php p($_['wallDisplayname']); ?>
</span>
	<span class="pinshow-date">	
<b><?php p($l -> t("Added: ")); ?></b> <?php p($_['dateadded']); ?>
</span>
<span class="pinshow-date">	
<b><?php p($l -> t("Modified: ")); ?></b> <?php p($_['modifiedDate']); ?>
</span>

 <?php if(($_['url'] != '' && $_['imgsrc']!='') || ($_['url'] != '' && $_['imgsrc']=='')){?>
	<span class="pinshow-url">	
	 <a class="icon-link" href="<?php p($_['url']); ?>" target="_blank" title="<?php p($_['url']); ?>"><?php p($l -> t("At")); ?> <?php p($_['domain']); ?> <?php p($l -> t("found")); ?></a> 
	</span>
<?php } ?>
 <?php if($_['url'] == '' && $_['imgsrc']!=''){?>
	<span class="pinshow-url">	
	 <?php p($l -> t("Uploaded of")); ?> <?php p($_['userdisplayname']); ?>
	</span>
<?php } ?>
<?php if($_['url'] == '' && $_['imgsrc']==''){?>
	<span class="pinshow-url">	
	 <?php p($l -> t("Published of")); ?> <?php p($_['userdisplayname']); ?>
	</span>
<?php } ?>	

<?php if($_['tags'] != ''){
	print_unescaped('<span class="pintag-all">');	
	  foreach($_['tags'] as $tagInfo){
	  	print_unescaped('<span class="pintag" style="background-color:'.$tagInfo['bgcolor'].';color:'.$tagInfo['color'].'">'.$tagInfo['name'].'</span>');
	  }	
	print_unescaped('</span>');	
	 }
	?>	
</span>


 <?php if($_['location'] != ''){?>
 	
	<span style="text-align:center;" class="pinshow-location">	
	  <br />
		<img class="map toolTip" id="geoloc" title="<?php p($_['location']); ?>"  src="http://maps.google.com/maps/api/staticmap?zoom=15&size=260x200&maptype=terrain&sensor=false&center=<?php p($_['location']); ?>" />
	<br /><?php p($_['location']); ?>
	</span>
<?php } ?>	
 </span> 
   	
	<br style="clear:both;">
	   

	
 
		<button id="showPin-cancel" class="icon-close"></button> 
	  
	</div>

