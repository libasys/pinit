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
use OCA\Pinit\PinWall;
use \OCP\Share;

class PinWallDAO  {

    private $db;

 public function __construct(IDb $db) {
        $this->db = $db;
		
    }
 
 public function save($request){
 	        	
 	        $userid=\OCP\User::getUser();
			
			$displayname=(isset($request['name']) ?  filter_var($request['name'],FILTER_SANITIZE_STRING) :'' );
			$active=(isset($request['active']) ? $request['active'] :0 );
			$wallorder=(isset($request['wallorder']) ? $request['wallorder'] :0 );
			$wallBG=(isset($request['wallbackground']) ? $request['wallbackground'] :0 );
			$lastmodified=time();
				
			$stmt = $this->db->prepareQuery( 'INSERT INTO `*PREFIX*pinit_walls` (`displayname`,`active`,`wallorder`,`wallbackground`,`lastmodifieddate`,`user_id`) VALUES(?,?,?,?,?,?)' );
		    $result = $stmt->execute(array($displayname,$active,$wallorder,$wallBG,$lastmodified,$userid));

		    $insertid =  $this->db->getInsertId('*PREFIX*pinit_walls');
			
			return $insertid;
 }
 
  public function update($request){
  	
 	        $userid=\OCP\User::getUser();
			$displayname=(isset($request['name']) ?  filter_var($request['name'],FILTER_SANITIZE_STRING) :'' );
			$active=(isset($request['active']) ? $request['active'] :0 );
			$wallorder=(isset($request['wallorder']) ? $request['wallorder'] :0 );
			$wallBG=(isset($request['wallbackground']) ? $request['wallbackground'] :0 );
			$lastmodified=time();
			$wallId=$request['wall_id'];
				
			$stmt = $this->db->prepareQuery( 'UPDATE `*PREFIX*pinit_walls` SET `displayname` = ?,`active` = ?,`wallorder` = ?,`wallbackground` = ?,`lastmodifieddate` = ? WHERE `user_id` = ? AND `id` = ?' );
		    $result = $stmt->execute(array($displayname,$active,$wallorder,$wallBG,$lastmodified,$userid,$wallId));

		   return true;
 }
  
  public function delete($id){
 	       $userid=\OCP\User::getUser();
				
			$stmt = $this->db->prepareQuery( 'DELETE FROM `*PREFIX*pinit_walls` WHERE `user_id` = ? AND `id` = ?' );
		    $result = $stmt->execute(array($userid,$id));
           
		   
		    Share::unshareAll('pinwall', 'pinwall-'.$id);

			
		   
		   return true;
 }
  
 public function getAll($user=''){
 	 if($user=='') {
			$user=\OCP\User::getUser();
		}
		$values = array($user);
		$stmt = $this->db->prepareQuery( 'SELECT `id`,`user_id`,`displayname`,`active`,`wallorder`,`wallbackground`,`lastmodifieddate` FROM `*PREFIX*pinit_walls` WHERE `user_id` = ?   ORDER BY `wallorder` ASC');
		$result = $stmt->execute($values);
		$aPinWalls = array();
		while( $row = $result->fetchRow()) {
			$aPinWalls[]=$row;
		}
	 
	   if(is_array($aPinWalls)){
	   	    return $aPinWalls;
	   }
 }
 
 public function getSingle($wallId, $shared = false){
 	    $owner = \OCP\User::getUser();
		$usersql='';
		if($shared == true){
			$values = array($wallId);
		}else{
			$values = array($owner,$wallId);
			$usersql='`user_id` = ? AND ';
		}
		
		$stmt = $this->db->prepareQuery( 'SELECT `id`,`user_id`,`displayname`,`active`,`wallorder`,`wallbackground`,`lastmodifieddate` FROM `*PREFIX*pinit_walls` WHERE '.$usersql.' `id` = ?');
		$result = $stmt->execute($values);
		$row = $result->fetchRow();
		
		return $row;
 }
 
 public  function getPinsCount($wallid, $shared = false){
   	        	
   	        $usersql='';	
   	        if($shared == true){
				$usersql='`public` = ? AND ';
				$values = array(1,$wallid);
			 }else{
				$values = array($wallid);
		    }
		 
   	     $stmt = $this->db->prepareQuery( 'SELECT COUNT(`id`) AS COUNTPIN FROM `*PREFIX*pinit_pins` WHERE '.$usersql.' `wall_id` = ?');
		 $result = $stmt->execute($values);
		 $row = $result->fetchRow();
		 
	   return $row['COUNTPIN'];
   }
 
 
 	/**
	 * @NoAdminRequired
	 */
	public function updateSortOrder($request){
				
			$userid=\OCP\User::getUser();
			$wallorder=(isset($request['iOrder']) ? $request['iOrder'] :0 );
			$lastmodified=time();
			$wallId=$request['wallId'];
				
			$stmt = $this->db->prepareQuery( 'UPDATE `*PREFIX*pinit_walls` SET `wallorder` = ?, `lastmodifieddate` = ? WHERE `user_id` = ? AND `id` = ?' );
		    $result = $stmt->execute(array($wallorder,$lastmodified,$userid,$wallId));

		   return true;
	}
 
}