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
use \OCP\Share;


class PinWallController extends Controller {

	private $userId;
	private $pinDAO;
	private $pinWallDAO;
	private $l10n;
	private $helperController;
	
	

	public function __construct($appName, IRequest $request, $userId, $pinDAO, $pinWallDAO,  $helperController, $l10n) {
		parent::__construct($appName, $request);
		$this -> userId = $userId;
		$this->pinDAO = $pinDAO;
		$this->pinWallDAO = $pinWallDAO;
		$this->l10n = $l10n;
		$this->helperController = $helperController;
		
	}
	
	/**
	 * @NoAdminRequired
	 */
	public function getPinWallBackground(){
		$result = $this->helperController->getPinWallBackgroundOptions();
		$response = new JSONResponse();
		$response -> setData($result);
						  
		return $response;
	}
	
	/**
	 * @NoAdminRequired
	 */
	public function validateItemSource($itemSource,$itemType='pinwall-'){
	
		if(stristr($itemSource,$itemType)){
			$iTempItemSource=explode($itemType,$itemSource);
			return (int)$iTempItemSource[1];
		}else{
			return $itemSource;
		}
	}
	
	/**
	 * @NoAdminRequired
	 */
	public function getPinWall($wallId, $shared = false, $shareByLink=false, $shareOwner=''){
		
		$aPinWallsInfo = $this->pinWallDAO->getSingle($wallId, $shared); 
		
		$owner=$this->userId;
		
		if($aPinWallsInfo['user_id'] != $owner && !\OC_Group::inGroup($owner, 'admin')) {
			
			if($shareByLink==true){
				$sharedPinwallByLink=\OCP\Share::getItemSharedWithByLink('pinwall','pinwall-'.$aPinWallsInfo['id'],$shareOwner);
				if(!$sharedPinwallByLink || !($sharedPinwallByLink['permissions'] & \OCP\PERMISSION_READ)){
					throw new \Exception(
						$this->l10n->t(
							'You do not have the permissions to read pins.'
						)
					);
				}
				$aPinWallsInfo['permissions'] = $sharedPinwallByLink['permissions'];
			}
			
			if($shareByLink==false && $shared == true){
				$sharedPinwall = \OCP\Share::getItemSharedWithBySource('pinwall', 'pinwall-'.$aPinWallsInfo['id']);
				if (!$sharedPinwall || !($sharedPinwall['permissions'] & \OCP\PERMISSION_READ)) {
						throw new \Exception(
							$this->l10n->t(
								'You do not have the permissions to read pins.'
							)
						);
					}
				$aPinWallsInfo['permissions'] = $sharedPinwall['permissions'];
			}
			
			$aPinWallsInfo['countPins'] = $this->pinWallDAO->getPinsCount($aPinWallsInfo['id'],true);
			
			
		} else {
			$aPinWallsInfo['permissions'] = \OCP\PERMISSION_ALL;
			$aPinWallsInfo['countPins'] =$this->pinWallDAO->getPinsCount($aPinWallsInfo['id']);
		}
		
		
		$aPinWallsInfo['wallbg'] = '';
			if($aPinWallsInfo['wallbackground'] >0){
				$aPinWallsInfo['wallbg'] = $this->helperController -> getPinWallBackground($aPinWallsInfo['wallbackground']);
			}
		$aPinWallsInfo['user_displayname']=\OCP\User::getDisplayName($aPinWallsInfo['user_id']);
		
		return $aPinWallsInfo;
	}
	
	/**
	 * @NoAdminRequired
	 */
	public function getPinWalls($user='',$shared=true){
		  	if($user=='') {
			 	 $user=$this -> userId;
			}
		  
		  $aPinWallsInfo = $this->pinWallDAO->getAll($user); 
		  $sharedPinnwallsInfo = Share::getItemsSharedWith('pinwall', ShareController::FORMAT_PINWALL);
		  
		  if(is_array($aPinWallsInfo)){
		  	 $result=array();
		  	  foreach($aPinWallsInfo as $row){
		  	  	     $row['wallbg'] = '';
						if($row['wallbackground'] >0){
							$row['wallbg'] = $this->helperController -> getPinWallBackground($row['wallbackground']);
						}
						$row['permissions'] = \OCP\PERMISSION_ALL;
						$row['countPins'] = $this->pinWallDAO->getPinsCount($row['id'],false);
						$result[]=$row;
						
		  	  }
		 }
		  
		   
			 
			if(is_array($sharedPinnwallsInfo)){		
				foreach($sharedPinnwallsInfo as $sharedInfo) {
					    $sharedInfo['wallbg'] = '';
						if($sharedInfo['wallbackground'] >0){
							$sharedInfo['wallbg'] = $this->helperController -> getPinWallBackground($sharedInfo['wallbackground']);
						}
						$sharedInfo['countPins'] = $this->pinWallDAO->getPinsCount($sharedInfo['id'],true);
						$result[]=$sharedInfo;
					
				}
			}
			
			if(!count($aPinWallsInfo)) {
					$result[] =$this->addDefault();
			}
			  
		  
		 $response = new JSONResponse();
		$response -> setData($result);
		return $response;
		
		  
	}

	/**
	 * @NoAdminRequired
	 */

    public function newPinWall(){
    	  	$saveArray = $this -> getParams();
			$newId = $this->pinWallDAO->save($saveArray);
			
			$result=array();
			$result=$this->getPinWall($newId);
			
    	  $response = new JSONResponse();
		  $response -> setData($result);
		  return $response;
    }
	
	/**
	 * @NoAdminRequired
	 */
	public function editPinWall(){
		   $saveArray = $this -> getParams();
		   $bSuccess = $this->pinWallDAO->update($saveArray);
			if ($bSuccess) {
				$result[]=array();
				$result = $this->getPinWall($saveArray['wall_id']);

				$response = new JSONResponse();
				$response -> setData($result);
				return $response;
			}
	}
	
	/**
	 * @NoAdminRequired
	 */
	public function deletePinWall(){
		  $wallId = $this -> params('wallId');
		   $aPins=$this->pinDAO->getAll($wallId, true);	
			if(is_array($aPins)){
				foreach($aPins as $pinInfo){
					 $this->pinDAO->delete($pinInfo['id']);
				}
			}
			
			$bSuccess = $this->pinWallDAO->delete($wallId);
			if ($bSuccess) {
				$result=array('id'=>$wallId);
				$response = new JSONResponse();
				$response -> setData($result);
				return $response;
			}
			
			if(count($this->getPinWalls(\OCP\User::getUser())) == 0) {
				 $this->addDefault();
			}
			
	}
	
	/**
	 * @NoAdminRequired
	 */
	public function addDefault() {
		$DefaultName=\OCP\User::getDisplayName().' Pins';
		$saveArray=array('name'=>$DefaultName,'active'=>1,'wallorder'=>1,'wallbackground'=>0);
		$newId = $this->pinWallDAO->save($saveArray);
		
		$result=$this->getPinWall($newId);
		
		return $result;
	}
	
	/**
	 * @NoAdminRequired
	 */
	public  function saveSortOrderPinwall(){
			
			$saveArray['wallId'] = $this -> params('wallId');
			$saveArray['iOrder'] = $this -> params('iOrder');
			
			
			$bSuccess = $this->pinWallDAO -> updateSortOrder($saveArray);
			
			if($bSuccess){
				return true;
			}else {
				return false;
			}
	}
	
}