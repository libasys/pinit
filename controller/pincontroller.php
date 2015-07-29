<?php
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
 
namespace OCA\Pinit\Controller;

use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\IRequest;
use \OCA\Pinit\Webthumbnail;
use \OCP\Share;


class PinController extends Controller {

	private $userId;
	private $pinDAO;
	private $l10n;
	private $helperController;
	private $pinwallController;

	public function __construct($appName, IRequest $request, $userId, $pinDAO,  $helperController, $pinwallController, $l10n) {
		parent::__construct($appName, $request);
		$this -> userId = $userId;
		$this->pinDAO = $pinDAO;
		$this->l10n = $l10n;
		$this->helperController = $helperController;
		$this->pinwallController = $pinwallController;
	}
	
	/**
	 *
	 * @NoCSRFRequired
	 * @PublicPage
	 */

	public function showPinDataPublic($id,$token) {

		if ($token != '') {
			$linkItem = Share::getShareByToken($token);
			if (is_array($linkItem) && isset($linkItem['uid_owner'])) {
				// seems to be a valid share
				$type = $linkItem['item_type'];
				//Add fix
				
				$PinWallId = $this -> pinwallController -> validateItemSource($linkItem['item_source']);
				
				$shareOwner = $linkItem['uid_owner'];
		
				$rootLinkItem = \OCP\Share::resolveReShare($linkItem);
				$pinWallOwner = $rootLinkItem['uid_owner'];
		        
				$aPin = $this->getPin($id, true ,true,$pinWallOwner);
				$aPinWall=$this->pinwallController -> getPinWall($PinWallId, true ,true,$pinWallOwner);
				$result=array('pin'=>$aPin, 'pinwall' => $aPinWall);
				
				return $result;
			}
		}

	}
	
	/**
	 * @NoAdminRequired
	 */
	public function showPinData( $id) {
       
		$aPin = $this->getPin($id, true);
		$aPinWall=$this->pinwallController -> getPinWall($aPin['wall_id'], true);
		
		$result=array('pin'=>$aPin, 'pinwall' => $aPinWall);
        
		return $result;
	}
	
	/**
	 *
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function showPinPublic(){
		$token = $this -> params('token');	
		$id = $this -> params('id');	
		$showInfo=$this->showPinDataPublic($id,$token);
		
		return $this->showPinTpl($showInfo);
	}
	
	/**
	 * @NoAdminRequired
	 */
	public function showPin(){
		 $id = $this -> params('id');	
		
		$showInfo=$this -> showPinData($id);
		
		return $this->showPinTpl($showInfo);
	}
	
	/**
	 *
	 *
	 *  @NoAdminRequired
	 */
	public function showPinTpl($showInfoData){
		
	$aPinColors = $this->helperController->getPinColorOptions();
	
	$pinColor=0;
	
	if($showInfoData['pin']['pincolor_id']>0){
		$pinColor=$aPinColors[$showInfoData['pin']['pincolor_id']];
	}
	$divWidth=(isset($showInfoData['pin']['imageWidth']) && $showInfoData['pin']['imageWidth'] > 0 ? ($showInfoData['pin']['imageWidth'] +310) : 400);
	if($showInfoData['pin']['media_url']!=''){
		$divWidth=710;
	}
	$params = array(
	'id' => $showInfoData['pin']['id'],
	'cPublic' => ($showInfoData['pin']['public'] == 1) ? '1' : 0, 
	'newpin' => ($showInfoData['pin']['newpin'] == 1) ? '1' : 0,
	'isPhoto' =>  ($showInfoData['pin']['image'] !='') ? '1' : 0,
	'imgsrc' => (isset($showInfoData['pin']['image']) ? $showInfoData['pin']['image'] : ''), 
	'imgMimeType' => (isset($showInfoData['pin']['imageMimeType']) ? $showInfoData['pin']['imageMimeType'] : ''), 
	'imageWidth' =>$showInfoData['pin']['imageWidth'],
	'imageHeight' =>$showInfoData['pin']['imageHeight'],
	'divWidth' =>$divWidth, 
	'title' => (isset($showInfoData['pin']['title']) ? $showInfoData['pin']['title'] : ''), 
	'description' => (isset($showInfoData['pin']['description']) ? $showInfoData['pin']['description'] : ''), 
	'url' => (isset($showInfoData['pin']['url']) ? $showInfoData['pin']['url'] : ''), 
	'domain' => (isset($showInfoData['pin']['domain']) ? $showInfoData['pin']['domain'] : ''), 
	'choosenPinColor' =>$pinColor , 
	'user_id' =>  $showInfoData['pin']['user_id'], 
	'wallDisplayname' =>  $showInfoData['pinwall']['displayname'], 
	'dateadded' => $this->helperController->relative_modified_date($showInfoData['pin']['addDate']), 
	'modifiedDate' =>  $this->helperController->relative_modified_date($showInfoData['pin']['modifiedDate']),
	'userdisplayname' =>  $showInfoData['pin']['userdisplayname'], 
	'location' =>  $showInfoData['pin']['location'], 
	'tags' =>   (isset($showInfoData['pin']['categories']) ? $showInfoData['pin']['categories'] : ''),
	'mediaUrl' =>(isset($showInfoData['pin']['media_url']) ? $showInfoData['pin']['media_url'] : ''),
	'mediaSite' =>(isset($showInfoData['pin']['media_sitename']) ? $showInfoData['pin']['media_sitename'] : ''),
	'mediaWidth' =>(isset($showInfoData['pin']['media_width']) ? $showInfoData['pin']['media_width'] : ''),
	'mediaHeight' =>(isset($showInfoData['pin']['media_height']) ? $showInfoData['pin']['media_height'] : ''), 
	'mediaDomain' => (isset($showInfoData['pin']['media_domain']) ? $showInfoData['pin']['media_domain'] : ''), 
	);

	$csp = new \OCP\AppFramework\Http\ContentSecurityPolicy();
	$csp->addAllowedImageDomain('data:');
	
	$response = new TemplateResponse('pinit', 'pin.show', $params, '');
	$response->setContentSecurityPolicy($csp);
	   return $response;
	}

	/**
	 *
	 * @NoCSRFRequired
	 * @PublicPage
	 */

	public function getPinsPublic() {

		$token = $this -> params('token');

		if ($token != '') {
			$linkItem = Share::getShareByToken($token);
			if (is_array($linkItem) && isset($linkItem['uid_owner'])) {
				// seems to be a valid share
				$type = $linkItem['item_type'];
				
				//Add Fix
				
				$PinWallId = $this -> pinwallController -> validateItemSource($linkItem['item_source']);
				$shareOwner = $linkItem['uid_owner'];

				$rootLinkItem = Share::resolveReShare($linkItem);
				$pinWallOwner = $rootLinkItem['uid_owner'];

				$aPins = $this->pinDAO->getAll($PinWallId, true, true, $pinWallOwner);
				if(is_array($aPins)){
			
				$result=array();
				foreach($aPins as $pinInfo){
					$result[] = $this->helperController->generatePinRow($pinInfo);
				}	
				$csp = new \OCP\AppFramework\Http\ContentSecurityPolicy();
				$csp->addAllowedImageDomain('data:');	
				$response = new JSONResponse();
				$response->setContentSecurityPolicy($csp);
				$response -> setData($result);
				return $response;
					
				}else{
					return false;
				}
				
			}
		}

	}
	
	/**
	 * @NoAdminRequired
	 */

	public function getPin($id, $shared = true,$shareByLink = false, $shareOwner='') {
		  $pinId = (int) $id;
		  $aPinInfo = $this->pinDAO->getSingle($pinId, $shared); 
		    $result=array();
			
			$PERMISSIONS=0;
		   if($shareByLink==true){
				$owner = $shareOwner;
			   //Add Fix
				$sharedPinwallByLink=\OCP\Share::getItemSharedWithByLink('pinwall','pinwall-'.$aPinInfo['wall_id'],$shareOwner);
				$PERMISSIONS=$sharedPinwallByLink['permissions'];	
				if(!$sharedPinwallByLink || !($sharedPinwallByLink['permissions'] & \OCP\PERMISSION_READ)){
					throw new \Exception(
						$this->l10n->t(
							'You do not have the permissions to read pins.'
						)
					);
				}
			}
		   
		   if($shared == true && $shareByLink==false){
		
		   //check Permissions
			   $pinWall = $this->pinwallController -> getPinWall($aPinInfo['wall_id'],true);
				if ($pinWall['user_id'] != $this->userId) {
					$sharedPinwall = \OCP\Share::getItemSharedWithBySource('pinwall','pinwall-'. $pinWall['id']);
					if (!$sharedPinwall || !($sharedPinwall['permissions'] & \OCP\PERMISSION_READ)) {
						throw new \Exception(
							$this->l10n->t(
								'You do not have the permissions to read pins.'
							)
						);
					}
					$PERMISSIONS = $sharedPinwall['permissions'];
				}else{
					$PERMISSIONS = \OCP\PERMISSION_ALL;
				}
			}
		   
		  $result = $this->helperController->generatePinRow($aPinInfo);
		  $result['permissions'] = $PERMISSIONS;
		
		return $result;
	}
	
	/**
	 * @NoAdminRequired
	 */

	public function getAllPinsUser() {

		$limit= (int)$this->params('limit');
		
		$aPins = $this->pinDAO->getAllPinsFromUser($limit); 
		if(is_array($aPins)){
			
		$result=array();
		foreach($aPins as $pinInfo){
			$result[] = $this->helperController->generatePinRow($pinInfo);
		}	
			
		$response = new JSONResponse();
		$response -> setData($result);
		return $response;
			
		}else{
			return false;
		}

		

	}
	
	/**
	 * @NoAdminRequired
	 */

	public function getPins($limit=0) {

		$wallId = (int)$this -> params('wallId');
		$aPins = $this->pinDAO->getAll($wallId, true); 
		if(is_array($aPins)){
			
		$result=array();
		foreach($aPins as $pinInfo){
			$result[] = $this->helperController->generatePinRow($pinInfo);
		}	
			
		$response = new JSONResponse();
		$response -> setData($result);
		return $response;
			
		}else{
			return false;
		}

		

	}
	
	/**
	 * @NoAdminRequired
	 */
	public static function getPinsVideo($limit=0) {
		         	
		         $owner =  \OCP\User::getUser();
				 $usersql='`user_id` = ?';
				 $values = array($owner);
				
	       	$stmt = \OCP\DB::prepare( "SELECT `id`,`image`,`url`,`title`,`lastmodified`, `categories`,`media_url`,`media_sitename`,`media_width`,`media_height` FROM `*PREFIX*pinit_pins` WHERE ".$usersql." AND media_url !=''  ORDER BY `lastmodified` DESC");
			$result = $stmt->execute($values)->fetchAll();
			
			if(is_array($result)){
				return $result;
			}else{
				return false;
			} 
		
		
	}
	
	/**
	 * @NoAdminRequired
	 */
	public function newPin() {

		$aPinColors = $this->helperController->getPinColorOptions();
		$aPinIcons = $this->helperController->getPinIcons();
		$aPinMarkerColors = $this->helperController->getPinMarkerColor();

		$maxUploadFilesize = \OCP\Util::maxUploadFilesize('/');
		$thumb = '<div id="noimage"></div>';

		$params = array('id' => 'newpin', 'uploadMaxHumanFilesize' => \OCP\Util::humanFileSize($maxUploadFilesize), 'aPinColors' => $aPinColors, 'thumbnail' => $thumb, 'aPinIcons' => $aPinIcons, 'aPinMarkerColors' => $aPinMarkerColors, );
		$csp = new \OCP\AppFramework\Http\ContentSecurityPolicy();
		$csp->addAllowedImageDomain('data:');	
		
		$response = new TemplateResponse('pinit', 'pin.new', $params, '');
		$response->setContentSecurityPolicy($csp);
		
		return $response;

	}
	
	/**
	 * @NoAdminRequired
	 */
	public function newPinSave() {
		$hiddenField = $this -> params('hiddenfield');

		if ($hiddenField != '') {
			
			$userid=\OCP\User::getUser();
			$wallId=$this->params('wall_id');
			
			$pinWall = $this->pinwallController -> getPinWall($wallId,true);
			if ($pinWall['user_id'] != $userid) {
				$sharedPinwall = Share::getItemSharedWithBySource('pinwall','pinwall-'. $pinWall['id']);
				
				if (!$sharedPinwall || !($sharedPinwall['permissions'] & \OCP\PERMISSION_CREATE)) {
					throw new \Exception(
						$this->l10n->t(
							'You do not have the permissions to add pins to the pinwall.'
						)
					);
				}
			}
	        
			$saveArray = $this -> getParams();
			$newId = $this->pinDAO->save($saveArray);
			$result = $this->getPin($newId);
			
			if(\OC::$server->getCache()->hasKey($saveArray['tmpkey'])) {
                \OC::$server->getCache()->remove($saveArray['tmpkey']);
			}
			
			$response = new JSONResponse();
			$response -> setData($result);
			return $response;
		}
	}
	
	
	/**
	 * @NoAdminRequired
	 */
	public function editPin() {

		$id = $this -> params('id');

		$EDITDATA =$this->getPin($id, true);
        $EDITDATA=  $EDITDATA;
		$thumb = '<div id="noimage"></div>';
		$bImage = false;
		if ($EDITDATA['image'] != '') {
			$thumb = '';
			$bImage = true;

			$image = new \OCP\Image();
			$image -> loadFromBase64((string)$EDITDATA['image']);
			\OC::$server->getCache()->set('edit-pin-foto-' . $id, $image -> data(), 600);
		}

		$maxUploadFilesize = \OCP\Util::maxUploadFilesize('/');
		$aPinColors = $this->helperController->getPinColorOptions();
		$aPinIcons = $this->helperController->getPinIcons();
		$aPinMarkerColors = $this->helperController->getPinMarkerColor();
		$sCat = '';
		if ($EDITDATA['categories'] != '') {
			foreach ($EDITDATA['categories'] as $tagInfo) {
				if ($sCat == '') {
					$sCat = $tagInfo['name'];
				} else {
					$sCat .= ',' . $tagInfo['name'];
				}
			}
		}

		$params = array('id' => $id, 
		'isPhoto' => $bImage, 
		'tmpkey' => 'edit-pin-foto-' . $id, 
		'cPublic' => ($EDITDATA['public'] == 1) ? 'checked' : '', 
		'imgsrc' => (isset($EDITDATA['image']) ? $EDITDATA['image'] : ''), 
		'imgMimeType' => (isset($EDITDATA['imageMimeType']) ? $EDITDATA['imageMimeType'] : ''), 
		'title' => (isset($EDITDATA['title']) ? $EDITDATA['title'] : ''), 
		'description' => (isset($EDITDATA['description']) ? $EDITDATA['description'] : ''), 
		'url' => (isset($EDITDATA['url']) ? $EDITDATA['url'] : ''), 
		'thumbnail' => $thumb, 
		'choosenPinColor' => $EDITDATA['pincolor_id'], 
		'aPinColors' => $aPinColors, 
		'aPinIcons' => $aPinIcons, 
		'aPinMarkerColors' => $aPinMarkerColors, 
		'wallId' => $EDITDATA['wall_id'], 
		'dateadded' => $EDITDATA['addDate'], 
		'location' => $EDITDATA['location'], 
		'lon' => $EDITDATA['lon'], 
		'lat' => $EDITDATA['lat'], 
		'icon' => $EDITDATA['icon'], 
		'markercolor' => $EDITDATA['markercolor'], 
		'tags' => $sCat, 
		'userid' => $EDITDATA['user_id'], 
		'uploadMaxHumanFilesize' => \OCP\Util::humanFileSize($maxUploadFilesize),
		'mediaUrl' =>(isset($EDITDATA['media_url']) ? $EDITDATA['media_url'] : ''),
		'mediaSite' =>(isset($EDITDATA['media_sitename']) ? $EDITDATA['media_sitename'] : ''),
		'mediaWidth' =>(isset($EDITDATA['media_width']) ? $EDITDATA['media_width'] : ''),
		'mediaHeight' =>(isset($EDITDATA['media_height']) ? $EDITDATA['media_height'] : ''),
		 );
		$csp = new \OCP\AppFramework\Http\ContentSecurityPolicy();
		$csp->addAllowedImageDomain('data:');	
		$csp->addAllowedMediaDomain('*');	
		$csp->addAllowedFrameDomain('*');	
		
		$response = new TemplateResponse('pinit', 'pin.edit', $params, '');
		$response->setContentSecurityPolicy($csp);
		
		return $response;

	}


	/**
	 * @NoAdminRequired
	 */
	public function editPinSave() {
			
			
		    $saveArray = $this -> getParams();
			$id=$saveArray['id'];
			
			$pin=$this -> getPin($id);
			
			$pinWall = $this->pinwallController ->getPinWall($pin['wall_id'],true);
			
			if ($pinWall['user_id'] != $this->userId) {
				$sharedPinwall = Share::getItemSharedWithBySource('pinwall', 'pinwall-'.$pinWall['id']);
					if (!$sharedPinwall || !($sharedPinwall['permissions'] & \OCP\PERMISSION_UPDATE)) {
					throw new \Exception(
						$this->l10n->t(
							'You do not have the permissions to edit pins.'
						)
					);
				}
			}	

		if ($saveArray['hiddenfield'] != '') {
			
			$bSuccess = $this->pinDAO->update($saveArray);
			if ($bSuccess) {
				$result = $this -> getPin($id,true);
				if(\OC::$server->getCache()->hasKey($saveArray['tmpkey'])) {
                	\OC::$server->getCache()->remove($saveArray['tmpkey']);
				}
				$response = new JSONResponse();
				$response -> setData($result);
				return $response;
			}

		}else{
			return false;
		} 
	}
	
	/**
	 * @NoAdminRequired
	 */
	public function deletePin() {
		    	
		    
			$pinId = $this -> params('id');
			$pin=$this -> getPin($pinId);
			$pinWall = $this->pinwallController -> getPinWall($pin['wall_id'],true);
			if ($pinWall['user_id'] != $this->userId) {
				$sharedPinwall = Share::getItemSharedWithBySource('pinwall','pinwall-'. $pinWall['id']);
				if (!$sharedPinwall || !($sharedPinwall['permissions'] & \OCP\PERMISSION_DELETE)) {
					throw new \Exception(
						$this->l10n->t(
							'You do not have the permissions to delete pins.'
						)
					);
				}
			}
			
			$bSuccess = $this->pinDAO->delete($pinId);
			
			if($bSuccess){
				$result=array('id'=>$pinId);
				$response = new JSONResponse();
				$response -> setData($result);
				return $response;
			}else {
				return false;
			}
		
	}
	
	/**
	 * @NoAdminRequired
	 */
	public function deletePhotoPin() {
		  $pinId = $this -> params('id');
		  $bSuccess = $this->pinDAO->deletePhoto($pinId);
		  if($bSuccess){
				$result=array('id'=>$pinId);
				$response = new JSONResponse();
				$response -> setData($result);
				return $response;
			}else {
				return false;
			}
	}
	
	
	/**
	 * @NoAdminRequired
	 */
	public function movePin() {
		    	
		    $wallId = $this -> params('wall_id');
			$oldWallId = $this -> params('wall_old_id');
			$pinId = $this -> params('pin_id');
			
			$pinWall = $this->pinwallController -> getPinWall($wallId,true);
			if ($pinWall['user_id'] != $this->userId) {
				$sharedPinwall = Share::getItemSharedWithBySource('pinwall','pinwall-'. $pinWall['id']);
				
				if (!$sharedPinwall || !($sharedPinwall['permissions'] & \OCP\PERMISSION_CREATE)) {
					 $msg = (string)$this->l10n->t('You do not have the permissions to move the pins.');
					  $result=array('msglevel'=>'error', 'message'=>$msg);	
					  $response = new JSONResponse();
					  $response -> setData($result);
					  return $response;
				}
			}

			$pinWallOld = $this->pinwallController -> getPinWall($oldWallId,true);
			$MyPin=$this->getPin($pinId,true);
			if (($pinWallOld['user_id'] != $this->userId) && ($MyPin['user_id'] != $this->userId)) {
				  $msg = (string)$this->l10n->t('You do not have the permissions to move the pins.');
				  $result=array('msglevel'=>'error', 'message'=>$msg);	
				  $response = new JSONResponse();
				  $response -> setData($result);
				  return $response;
			}
			
			$bSuccess = $this->pinDAO->move($wallId,$pinId);
			
			if($bSuccess){
				$result=array('msglevel'=>'success','pinId'=>$pinId, 'wallId'=>$wallId);
				$response = new JSONResponse();
				$response -> setData($result);
				return $response;
			}else {
				return false;
			}
		
	}
	
	/**
	 * @NoAdminRequired
	 */
	public function changePinStatus(){
		  $pinId = $this -> params('id');
		  $owner =$this->userId;
		  $pinInfo=$this->getPin($pinId);
		  $pinWallInfo=$this->pinwallController->getPinWall($pinInfo['wall_id'],true);
		  
		  if($pinWallInfo['user_id'] != $owner){
		  	 $result =  array('isPublic'=>1,'id'=>$pinId,'status'=>'locked');
		  }else{
			 $changeStatus=($pinInfo['public'] == 0) ? 1 : 0;	
			 $bSuccess=$this->pinDAO->changeStatus($pinId,$changeStatus);	
			 if($bSuccess){
			 	$result=array('isPublic'=>$changeStatus,'id'=>$pinId,'status'=>'open');
			 }
			
		  }
		  $response = new JSONResponse();
		   $response -> setData($result);
		return $response;
		  
	}
	
	/**
	 * @NoAdminRequired
	 */
	public function addCategoryToPin(){
		  $pinId = $this -> params('pinId');
		  $category =$this -> params('category');
		 
		  $pin=$this -> getPin($pinId);
			
		  $pinWall = $this->pinwallController ->getPinWall($pin['wall_id'],true);
				
			if ($pinWall['user_id'] != $this->userId) {
				$sharedPinwall = Share::getItemSharedWithBySource('pinwall', 'pinwall-'.$pinWall['id']);
					if (!$sharedPinwall || !($sharedPinwall['permissions'] & \OCP\PERMISSION_UPDATE)) {
					throw new \Exception(
						$this->l10n->t(
							'You do not have the permissions to edit pins.'
						)
					);
				}
			}	

		  $categories=$this->pinDAO->addCategory($pinId,$category);	
		  $aCategories = $this->helperController->generateCategoriesColor($categories);
		  
		  $result = [
		  	'status' => 'success',
		  	'data' => $aCategories
		  ];
		  
		  $response = new JSONResponse();
		   $response -> setData($result);
		return $response;
		  
	}
	
	
	/**
	 * @NoAdminRequired
	 */
	public  function searchProperties($searchquery, $shared = false){
		
	  		$usersql='';
			if($shared === true){
				$values = array('%'.addslashes($searchquery).'%');
			}else{
				$values = array('%'.addslashes($searchquery).'%',$this->userId);
				$usersql=' AND `user_id` = ?  ';
			}
			
		$SQL="SELECT  `id`,`title` FROM `*PREFIX*pinit_pins` WHERE   `title` LIKE  ? ".$usersql;
				 
		 $stmt = \OCP\DB::prepare($SQL);
		
		$result = $stmt->execute($values);
		$pins = array();
		if(!is_null($result)) {
			while( $row = $result->fetchRow()) {
				
				$pins[] = $row;
			}
		}

		return $pins;
		
	}
	
	/**
	 * @NoAdminRequired
	 */
	public function getLonLatFromAddress(){
		 $location = $this -> params('location');
		 $name = urlencode($location);

		$renderUrl='http://nominatim.openstreetmap.org/search?format=json&q='.$name.'&limit=1&addressdetails=0&polygon=0';
		
		$locationInfo=$this->helperController->getLocationInfo($renderUrl,false);
		
		    if($locationInfo){
		    	$GeoInfo =json_decode($locationInfo);
				$lat = $GeoInfo[0]->lat;
				$lon = $GeoInfo[0]->lon;
				
				$GPSLatitude=$this->helperController->convertDecimalToDMS($lat);
				$GPSLatitudeRef = ($lat < 0) ? 'S' : 'N';
				$GPSLongitude=$this->helperController->convertDecimalToDMS($lon);
				$GPSLongitudeRef = ($lon < 0) ? 'W' : 'E';
				
				$result=array('lon'=>$lon,'lat'=>$lat,'gpslatref'=>$GPSLatitudeRef,'gpslat'=>$GPSLatitude,'gpslonref'=>$GPSLongitudeRef,'gpslon'=>$GPSLongitude);
				
				 $response = new JSONResponse();
				  $response -> setData($result);
				return $response;
			}else{
				return false;
			}
	}
	
	/**
	 * @NoAdminRequired
	 */
	private function getWebsiteInfoModePic($url){
			 $allowed = array('image/jpeg'=>1,'image/jpg'=>1,'image/gif'=>1,'image/png'=>1,); 
			 	
			$imgdata= $this -> helperController -> get_image_from_url(urldecode($url));
				
				
			if(array_key_exists(trim($imgdata['mimetype']), $allowed) && $imgdata['errno'] == 0){
				$tmpkey = 'webthumbnail-photo-new';
				$image = new \OCP\Image();
				sleep(1);
				// Apparently it needs time to load the data.
				if ($image -> loadFromData($imgdata['content'])) {
					
					if($image->width() > 400 || $image->height() > 400) {
							$image->resize(400); // Prettier resizing than with browser and saves bandwidth.
					}		
					if (\OC::$server->getCache()->set($tmpkey, $image -> data(), 600)) {
						//\OCP\Util::writeLog('pinit','DATACACHEFOUND:'.$imgdata['mimetype'],\OCP\Util::DEBUG);		
						$imgString = $image -> __toString();
						$imgMimeType = $image -> mimeType();
						
						$resultData=array(
						'imgdata' => $imgString, 
						'mimetype' => $imgMimeType, 
						'message' => 'Success', 
						'tmp' => $tmpkey, 
						'metainfo' => '',
						);
						
						$response = new JSONResponse();
					  $response -> setData($resultData);
					return $response;
					}
					
			 	}
			}
		
	}
	
	/**
	 * @NoAdminRequired
	 */
	private function getWebsiteInfoModeMeta($url){
		  	
		  $allowed = array('image/jpeg'=>1,'image/jpg'=>1,'image/gif'=>1,'image/png'=>1); 
		 
		  $allowedPlayers=array(
			   'vimeo.com'=>1,
			    'www.vimeo.com'=>1,
			   'soundcloud.com'=>1,
			   'www.soundcloud.com'=>1,
			   'youtube.com'=>1,
			   'm.youtube.com'=>1,
			    'www.youtube.com'=>1,
			   'muzu.tv'=>1,
			   'www.muzu.tv'=>1,
			   'www.vevo.com'=>1,
			   'vevo.com'=>1,
		  );
		  $domain='';
		  $urlInfo=parse_url(urldecode($url));
		  $domain=$urlInfo['host'];
		 
			
		  $html = $this->helperController -> get_web_page(urldecode($url));
          $metaInfo = array();
		  
			if(!$html['errno']){
			$doc = new \DOMDocument();
			@$doc -> loadHTML(mb_convert_encoding($html['content'], 'HTML-ENTITIES', 'UTF-8'));
			
			$title = $doc -> getElementsByTagName('title');
			
		
			$metaInfo['title'] = filter_var($title -> item(0) -> nodeValue,FILTER_SANITIZE_STRING,FILTER_FLAG_NO_ENCODE_QUOTES);
			$metas = $doc -> getElementsByTagName('meta');
		
			for ($i = 0; $i < $metas -> length; $i++) {
				$meta = $metas -> item($i);
				if ($meta -> getAttribute('name') == 'og:title' || $meta -> getAttribute('property') == 'og:title' || $meta -> getAttribute('itemprop') == 'og:title') {
					$metaInfo['title'] =filter_var($meta -> getAttribute('content'),FILTER_SANITIZE_STRING,FILTER_FLAG_NO_ENCODE_QUOTES);
					
				}
				if ($meta -> getAttribute('name') == 'description' || $meta -> getAttribute('name') == 'og:description' || $meta -> getAttribute('property') == 'og:description' || $meta -> getAttribute('itemprop') == 'og:description') {
					$metaInfo['description'] =filter_var($meta -> getAttribute('content'),FILTER_SANITIZE_STRING);
					
				}
				if ($meta -> getAttribute('name') == 'twitter:image:src' || $meta -> getAttribute('property') == 'og:image' || $meta -> getAttribute('name') == 'og:image') {
					if(filter_var($meta -> getAttribute('content'), FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)){
						$metaInfo['imagesrc'] = $meta -> getAttribute('content');
					}	
					
				}
				
				if ($meta -> getAttribute('property') == 'og:type'){
					//video, audio	
					$metaInfo['type']=filter_var($meta -> getAttribute('content'),FILTER_SANITIZE_STRING);
				}
				
				if($meta -> getAttribute('name') == 'twitter:player' || $meta -> getAttribute('property') == 'twitter:player')	{
					if(filter_var($meta -> getAttribute('content'), FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)){
					    
					    if(array_key_exists($domain,$allowedPlayers)){
					    	if(stristr($domain, 'vevo.com')){
					    		$temp=explode('&',$meta -> getAttribute('content'));
								$playerurl=$temp[0].'&autoplay=0';
					    	}elseif(stristr($domain, 'soundcloud.com')){
					    		$temp=explode('&',urldecode($meta -> getAttribute('content')));
								$playerurl=$temp[0].'&auto_play=false&hide_related=false&show_comments=false&show_user=false&show_reposts=false&visual=false';
					    	}
					    	else{
					    		$playerurl = $meta -> getAttribute('content');
					    	}
							//\OCP\Util::writeLog('pinit','URL:'.$playerurl,\OCP\Util::DEBUG);
					    	$metaInfo['video_secure_url']=urldecode($playerurl);
					    }	
					    
					}
				}

				if($meta -> getAttribute('property') == 'og:video:width')	{
					$metaInfo['video_width']=filter_var($meta -> getAttribute('content'),FILTER_SANITIZE_STRING);
				}
			  
			  if($meta -> getAttribute('property') == 'og:video:height')	{
					$metaInfo['video_height']=filter_var($meta -> getAttribute('content'),FILTER_SANITIZE_STRING);
				}
			  //MUZU.TV,YouTube,Vimeo,SoundCloud
			  if($meta -> getAttribute('property') == 'og:site_name')	{
					$metaInfo['sitename']=strtolower(filter_var($meta -> getAttribute('content'),FILTER_SANITIZE_STRING));
				}
			  
			}
			
	    }//no Error
	    
			$imgString='';
			$imgMimeType='';
			$tmpkey='';
			if (array_key_exists('imagesrc', $metaInfo)) {
		
				//$data = @file_get_contents($metaInfo['imagesrc']);
				$imgdata=$this->helperController -> get_image_from_url(urldecode($metaInfo['imagesrc']));
				if(array_key_exists(trim($imgdata['mimetype']), $allowed) && $imgdata['errno'] == 0){
					$tmpkey = 'webthumbnail-photo-new-'.time();
					$image = new \OCP\Image();
					sleep(1);
					// Apparently it needs time to load the data.
					if ($image -> loadFromData($imgdata['content'])) {
						if($image->width() > 400 || $image->height() > 400) {
							$image->resize(400); // Prettier resizing than with browser and saves bandwidth.
						}	
						if (\OC::$server->getCache()->set($tmpkey, $image -> data(), 600)) {
							$imgString = $image -> __toString();
							$imgMimeType = $image -> mimeType();
							
						}
						
					}
				}
			}
			
			$resultData=array(
				'imgdata' => $imgString, 
				'mimetype' => $imgMimeType, 
				'message' => 'Success', 
				'tmp' => $tmpkey, 
				'metainfo' => $metaInfo,
				);
				
				$response = new JSONResponse();
			  $response -> setData($resultData);
			return $response;
	
	
	    
	}

   /**
	 * @NoAdminRequired
	 */
	private function getWebsiteInfoModeScreen($url){
		//<img src="http://api.webthumbnail.org/?width=420&height=330&screen=1280&url=http://opsound.org/" alt="Generated by WebThumbnail.org" />
		
		$loadUrl='http://api.webthumbnail.org/?width=420&height=330&screen=1280&url='.urldecode($url);
		//\OCP\Util::writeLog('pinit','MIME PIC URL:'.$loadUrl,\OCP\Util::DEBUG);
		 return $this->getWebsiteInfoModePic($loadUrl);
		
		/*
			$user = \OCP\User::getUser();
			$view = new \OC\Files\View('/' . $user . '/files/');
			$path = $view -> getLocalFolder('/');
		
			$pathCaptureTmp = tempnam($path, 'webthumbnail-');
			$pathCapture = $pathCaptureTmp . '.png';
			$thumb = new \OCA\Pinit\Webthumbnail($url);
		
			$thumb -> setWidth(420) -> setHeight(330) -> setFormat('jpg') -> setScreen('1280') -> captureToFile($pathCapture);
		
			$tmpkey = 'webthumbnail-photo-new';
			$emptyPicture = '/9j/4AAQSkZJRgABAQAAAQABAAD//gA+Q1JFQVRPUjogZ2QtanBlZyB2MS4wICh1c2luZyBJSkcgSlBFRyB2OTApLCBkZWZhdWx0IHF1YWxpdHkK/9sAQwAIBgYHBgUIBwcHCQkICgwUDQwLCwwZEhMPFB0aHx4dGhwcICQuJyAiLCMcHCg3KSwwMTQ0NB8nOT04MjwuMzQy/9sAQwEJCQkMCwwYDQ0YMiEcITIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy/8AAEQgBkAH0AwEiAAIRAQMRAf/EAB8AAAEFAQEBAQEBAAAAAAAAAAABAgMEBQYHCAkKC//EALUQAAIBAwMCBAMFBQQEAAABfQECAwAEEQUSITFBBhNRYQcicRQygZGhCCNCscEVUtHwJDNicoIJChYXGBkaJSYnKCkqNDU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6g4SFhoeIiYqSk5SVlpeYmZqio6Slpqeoqaqys7S1tre4ubrCw8TFxsfIycrS09TV1tfY2drh4uPk5ebn6Onq8fLz9PX29/j5+v/EAB8BAAMBAQEBAQEBAQEAAAAAAAABAgMEBQYHCAkKC//EALURAAIBAgQEAwQHBQQEAAECdwABAgMRBAUhMQYSQVEHYXETIjKBCBRCkaGxwQkjM1LwFWJy0QoWJDThJfEXGBkaJicoKSo1Njc4OTpDREVGR0hJSlNUVVZXWFlaY2RlZmdoaWpzdHV2d3h5eoKDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uLj5OXm5+jp6vLz9PX29/j5+v/aAAwDAQACEQMRAD8A9/ooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAP//Z';
			try {
				if ($thumb -> isCaptured()) {
		
					$imagePng = imagecreatefrompng($pathCapture);
					$newImgJpg = $path . 'image-' . time() . '.jpg';
					imagejpeg($imagePng, $newImgJpg, 90);
					imagedestroy($imagePng);
		
					$image = new \OC_Image();
					//sleep(1);
					if ($image -> loadFromFile($newImgJpg)) {
						
		
						if (\OC::$server->getCache()->set($tmpkey, $image -> data(), 600)) {
							$imgString = $image -> __toString();
							$imgMimeType = $image -> mimeType();
		
							@unlink($pathCapture);
							@unlink($newImgJpg);
							@unlink($pathCaptureTmp);
							if ($emptyPicture == $imgString) {
								$imgString = '';
							}
							$resultData=array(
								'imgdata' => $imgString, 
								'mimetype' => $imgMimeType, 
								'message' => 'Success', 
								'tmp' => $tmpkey, 
								'metainfo' =>'',
								);
								
								$response = new JSONResponse();
							  $response -> setData($resultData);
							return $response;
						}
					}
		
				} else {
					//OCP\JSON::error(array('message' => 'Could not Load Image'));
				}
			} catch(\OCA\Pinit\WebthumbnailException $e) {
				//OCP\JSON::error(array('message' => $e -> getMessage()));
				exit() ;
			}
			*/
	}
	
	/**
	 * @NoAdminRequired
	 */
	public function getWebsiteInfo(){
		
		
		 $renderUrl = $this -> params('url');
		 $mode=$this -> params('mode');
		 
		 if ($mode == 'pic') {
		 	return $this->getWebsiteInfoModePic($renderUrl);
		}//ENDE PIC MODE
	    if ($mode == 'meta') {
	    	  return $this->getWebsiteInfoModeMeta($renderUrl);

		}//END META MODE
		if ($mode == 'screen') {
			return $this->getWebsiteInfoModeScreen($renderUrl);
		}
		
	}
	
}
