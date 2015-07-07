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
 
namespace OCA\Pinit\Db;

use \OCP\IDb;
use \OCP\Share;

class PinDAO  {

    private $db;
    private $l10n;
	private $userId;
	private $pinWallDAO;

    public function __construct(IDb $db, $userId, $pinWallDAO, $l10n) {
        $this->db = $db;
		$this->l10n = $l10n;
		$this->userId = $userId;
		$this->pinWallDAO = $pinWallDAO;
    }
	
	
	public function save($request){
		
			$userid=$this->userId;
			$pinColor=(isset($request['choosencolor']) ? $request['choosencolor'] :0 );
			$publicPin=(isset($request['ppublic']) ? $request['ppublic'] :0 );
			
			$url=(isset($request['purl']) ? filter_var($request['purl'],FILTER_SANITIZE_STRING) :'' );
			$lon=(isset($request['plon']) ? filter_var($request['plon'],FILTER_SANITIZE_STRING) :'' );
			$lat=(isset($request['plat']) ? filter_var($request['plat'],FILTER_SANITIZE_STRING) :'' );
			$descr=(isset($request['pdescr']) ? filter_var($request['pdescr'],FILTER_SANITIZE_STRING) :'' );
			$title=(isset($request['pname']) ? filter_var($request['pname'],FILTER_SANITIZE_STRING,FILTER_FLAG_NO_ENCODE_QUOTES) :'' );
			$imagedata=(isset($request['imgsrc']) ? filter_var($request['imgsrc'],FILTER_SANITIZE_STRING) :'' );
			$pinMarkerColor=(isset($request['choosenpinmotive']) ? filter_var($request['choosenpinmotive'],FILTER_SANITIZE_STRING) :'blue' );
			$pinMarkerMotive=(isset($request['pinicon']) ? filter_var($request['pinicon'],FILTER_SANITIZE_STRING) :'icon-mapper' );
			$tagsforsave=(isset($request['tagsforsave']) ? filter_var($request['tagsforsave'],FILTER_SANITIZE_STRING) :'' );
			$location =(isset($request['plocation']) ? filter_var($request['plocation'],FILTER_SANITIZE_STRING) :'' );
			//MEDIA
			
			$media_url =(isset($request['media_url']) ? filter_var($request['media_url'],FILTER_SANITIZE_STRING) :'' );
			$media_sitename =(isset($request['media_sitename']) ? filter_var($request['media_sitename'],FILTER_SANITIZE_STRING) :'' );
			$media_height =(isset($request['media_height']) ? filter_var($request['media_height'],FILTER_SANITIZE_STRING) :'' );
			$media_width =(isset($request['media_width']) ? filter_var($request['media_width'],FILTER_SANITIZE_STRING) :'' );
			
			$lastmodified=time();
			$addedTime=time();
			$wallId=$request['wall_id'];
				
			$SQL= 'INSERT INTO `*PREFIX*pinit_pins` (`url`,`title`,`location`,`description`,`pincolor_id`,`user_id`,`image`,`lastmodified`,`public`,`added`,`wall_id`,`categories`,`lon`,`lat`,`icon`,`markercolor`,`media_url`,`media_sitename`,`media_width`,`media_height`) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';	
			$query = $this->db->prepareQuery($SQL);
		    $result = $query->execute(array($url,$title,$location,$descr,$pinColor,$userid,$imagedata,$lastmodified,$publicPin,$addedTime,$wallId,$tagsforsave,$lon,$lat,$pinMarkerMotive,$pinMarkerColor,$media_url,$media_sitename,$media_width,$media_height));

		    $insertid = $this->db->getInsertId('*PREFIX*pinit_pins');
			
			return $insertid;
	}
   
   public function update($request){
			
			$publicPin=(isset($request['ppublic']) ? $request['ppublic'] :0 );
			$pinColor=(isset($request['choosencolor']) ? $request['choosencolor'] :0 );
			$lastmodified=time();
			$id=$request['id'];
			
			$url=(isset($request['purl']) ? filter_var($request['purl'],FILTER_SANITIZE_STRING) :'' );
			$lon=(isset($request['plon']) ? filter_var($request['plon'],FILTER_SANITIZE_STRING) :'' );
			$lat=(isset($request['plat']) ? filter_var($request['plat'],FILTER_SANITIZE_STRING) :'' );
			
			$descr=(isset($request['pdescr']) ? filter_var($request['pdescr'],FILTER_SANITIZE_STRING) :'' );
			$title=(isset($request['pname']) ? filter_var($request['pname'],FILTER_SANITIZE_STRING,FILTER_FLAG_NO_ENCODE_QUOTES) :'' );
			$imagedata=(isset($request['imgsrc']) ? filter_var($request['imgsrc'],FILTER_SANITIZE_STRING) :'' );
			$pinMarkerColor=(isset($request['choosenpinmotive']) ? filter_var($request['choosenpinmotive'],FILTER_SANITIZE_STRING) :'blue' );
			$pinMarkerMotive=(isset($request['pinicon']) ? filter_var($request['pinicon'],FILTER_SANITIZE_STRING) :'icon-mapper' );
			$tagsforsave=(isset($request['tagsforsave']) ? filter_var($request['tagsforsave'],FILTER_SANITIZE_STRING) :'' );
			$location =(isset($request['plocation']) ? filter_var($request['plocation'],FILTER_SANITIZE_STRING) :'' );
			
			$media_url =(isset($request['media_url']) ? filter_var($request['media_url'],FILTER_SANITIZE_STRING) :'' );
			$media_sitename =(isset($request['media_sitename']) ? filter_var($request['media_sitename'],FILTER_SANITIZE_STRING) :'' );
			$media_height =(isset($request['media_height']) ? filter_var($request['media_height'],FILTER_SANITIZE_STRING) :'' );
			$media_width =(isset($request['media_width']) ? filter_var($request['media_width'],FILTER_SANITIZE_STRING) :'' );
			
			
			if($location==''){
				$lon='';
				$lat='';
			}
				
			$stmt =$this->db->prepareQuery( 'UPDATE `*PREFIX*pinit_pins` SET `url` = ?,`title` = ?,`location` = ?,`description` = ?,`pincolor_id` = ?,`image` = ?,`public` = ?,`lastmodified` = ?,`categories` = ? ,`lon` = ?,`lat` = ? ,`icon` = ?,`markercolor` = ?,`media_url` = ?,`media_sitename` = ?,`media_width` = ?,`media_height` = ? WHERE `id` = ?' );
		    $result = $stmt->execute(array($url,$title,$location,$descr,$pinColor,$imagedata,$publicPin,$lastmodified,$tagsforsave,$lon,$lat,$pinMarkerMotive,$pinMarkerColor,$media_url,$media_sitename,$media_width,$media_height,$id));
         
		 return true;
	}
     
	 public function delete($id){
	 	
		    $stmt = $this->db->prepareQuery( 'DELETE FROM `*PREFIX*pinit_pins` WHERE  `id` = ?' );
		    $result = $stmt->execute(array($id));
			
			return true;
	 }
	 
      public function move($wallId, $pinId){
			
			$lastmodified=time();
			$stmt = $this->db->prepareQuery( 'UPDATE `*PREFIX*pinit_pins` SET `wall_id` = ?,`lastmodified` = ? WHERE `id` = ?' );
		    $result = $stmt->execute(array($wallId,$lastmodified,$pinId));
			
			return true;
	}
	  
	  public  function changeStatus($id, $bStatus){
		 
			 $lastmodified=time();
			 
		  	 $stmt = \OCP\DB::prepare( 'UPDATE `*PREFIX*pinit_pins` SET `public` = ?,`lastmodified` = ? WHERE `id` = ?' );
		     $result = $stmt->execute(array($bStatus,$lastmodified,$id));
			 
			return true;
		  
		  
	}
	  
	  public  function addCategory($id, $category,$shared = false){
		
		$usersql='';
		if($shared == true){
			$values = array($id);
		}else{
			$values = array($this->userId,$id);
			$usersql='`user_id` = ? AND ';
		}
				 	
		$stmt = $this->db->prepareQuery( 'SELECT `id`, `categories` FROM `*PREFIX*pinit_pins` WHERE '.$usersql.' `id` = ?');
		$result = $stmt->execute($values);
		$row = $result->fetchRow();	
		 $categories = $row['categories'];
		 if($categories != ''){
		 	$categories.=','.$category;
		 }else{
		 	$categories=$category;
		 }
		 
		 $lastmodified=time();
		 
	  	 $stmt = \OCP\DB::prepare( 'UPDATE `*PREFIX*pinit_pins` SET `categories` = ?,`lastmodified` = ? WHERE `id` = ?' );
	     $result = $stmt->execute(array($categories,$lastmodified,$id));
			 
		return $categories;
		  
		  
	}
	  
	  public function getSingle($id,$shared = false){
	  	  	
	  	  
		  $usersql='';
			if($shared == true){
				$values = array($id);
			}else{
				$values = array($this->userId,$id);
				$usersql='`user_id` = ? AND ';
			}
			 
		$stmt = $this->db->prepareQuery( 'SELECT `id`,`url`,`title`,`location`,`description`,`pincolor_id`,`user_id`,`image`,`wall_id`,`public`,`added`, `lastmodified`, `categories`, `lon`, `lat`,`icon`,`markercolor`,`media_url`,`media_sitename`,`media_width`,`media_height` FROM `*PREFIX*pinit_pins` WHERE '.$usersql.' `id` = ?');
		$result = $stmt->execute($values);
		$row = $result->fetchRow();
		  
		  return $row;
	  	
	  }
	  
	  
	  public function getAll($wallId,$shared = false, $shareByLink=false, $shareOwner=''){
	  				
	  		$PERMISSIONS=\OCP\PERMISSION_ALL;	
	  			
	  		if($shareByLink==true){
				$owner = $shareOwner;
				$pinWall['user_id']='';	
				$sharedPinwallByLink=Share::getItemSharedWithByLink('pinwall','pinwall-'.$wallId,$shareOwner);
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
				$owner = $this->userId;
				$pinWall =$this->pinWallDAO->getSingle($wallId,true);
				if ($pinWall['user_id'] != $owner) {
					$sharedPinwall = Share::getItemSharedWithBySource('pinwall','pinwall-'. $pinWall['id']);
					$PERMISSIONS=$sharedPinwall['permissions'];	
					if (!$sharedPinwall || !($sharedPinwall['permissions'] & \OCP\PERMISSION_READ)) {
						throw new \Exception(
							$this->l10n->t(
								'You do not have the permissions to read pins.'
							)
						);
					}
				}
			}
	  		
	  	   $usersql='';
		
			if($owner !=  $pinWall['user_id']){
				$usersql='`public` = ? AND ';
				$values = array(1,$wallId);
			 }else{
				$values = array($wallId);
				
		    }
			 
		$stmt = $this->db->prepareQuery( 'SELECT `id`,`url`,`title`,`location`,`description`,`pincolor_id`,`user_id`,`image`,`wall_id`,`public` ,`added`, `lastmodified`, `categories`, `lon`, `lat`,`icon`,`markercolor`,`media_url`,`media_sitename`,`media_width`,`media_height` FROM `*PREFIX*pinit_pins` WHERE '.$usersql.' `wall_id` = ? ORDER BY `lastmodified` DESC');
		$result = $stmt->execute($values);
		$aPins = array();
		while( $row = $result->fetchRow()) {
			$row['permissions'] = $PERMISSIONS;
			
			$aPins[]=$row;
		}
		
		if(is_array($aPins)){
			return $aPins;
		}else{
			return false;
		} 
			 
	  	
	  }
      
	   
	  public function getAllPinsFromUser($limit){
		  		
		  	$values=array($this->userId);	
			
		  	$stmt = $this->db->prepareQuery( 'SELECT `id`,`url`,`title`,`location`,`description`,`pincolor_id`,`user_id`,`image`,`wall_id`,`public` ,`added`, `lastmodified`, `categories`, `lon`, `lat`,`icon`,`markercolor`,`media_url`,`media_sitename`,`media_width`,`media_height` FROM `*PREFIX*pinit_pins` WHERE user_id=?  ORDER BY `lastmodified` DESC');
			$result = $stmt->execute($values);
			$aPins = array();
			$iCounter=0;
			while( $row = $result->fetchRow()) {
				
				if($limit > 0 && ($iCounter==$limit)){
					break;
				}
				$aPins[]=$row;
				$iCounter++;
			}
			
			if(is_array($aPins)){
				return $aPins;
			}else{
				return false;
			} 
	  }

        public  function deletePhoto($id){
				
			//$userid=$this->userId;
				
			$stmt = $this->db->prepareQuery( 'UPDATE `*PREFIX*pinit_pins` SET `image`= "" WHERE  `id` = ?' );
		    $result = $stmt->execute(array($id));

		   return true;
	}
	  
	  
}