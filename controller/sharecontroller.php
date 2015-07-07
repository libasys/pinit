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
use OCP\Share_Backend_Collection;
use \OCP\Share;
use OCA\Pinit\AppInfo\Application;


class ShareController implements Share_Backend_Collection{
	
	const FORMAT_PINWALL = 1;
	
	private $app;
	private $pinwallDao;
	private $pinDao;
	
	
	public function __construct() {
		$app = new Application();
		$container = $app->getContainer();
		$this->app = $app;
		$this->pinwallDao = $container->query('PinWallDAO');
		$this->pinDao = $container->query('PinDAO');
	}
	
	
	public function isValidSource($itemSource, $uidOwner) {
		// Add Fix
		$itemSource = $this -> validateItemSource($itemSource);
		
		$pinnwall =$this->pinwallDao -> getSingle( $itemSource , true);
		if( $pinnwall === false || $pinnwall['user_id'] != $uidOwner) {
			return false;
		}
		return true;
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
	
	public function generateTarget($itemSource, $shareWith, $exclude = null) {
		//Add Fix	
		$itemSource = $this -> validateItemSource($itemSource);
		
		$pinnwall =$this->pinwallDao -> getSingle( $itemSource , true);
		$user_pinwalls = array();
		foreach($this->pinwallDao -> getAll($shareWith) as $user_pinwall) {
			$user_pinwalls[] = $user_pinwall['displayname'];
		}
		
		$name = $pinnwall['displayname'];
		$suffix = '';
		while (in_array($name.$suffix, $user_pinwalls)) {
			$suffix++;
		}

		return $name.$suffix;
	}
	
	public function formatItems($items, $format, $parameters = null) {
		$pinnwalls = array();
		if ($format == self::FORMAT_PINWALL) {
			foreach ($items as $item) {
				//Add Fix
				$item['item_source'] = $this -> validateItemSource($item['item_source']);	
				$pinnwall = $this->pinwallDao -> getSingle( $item['item_source'], true );	
				
				if ($pinnwall) {
					$pinnwall['displayname'] = $item['item_target'];
					$pinnwall['permissions'] = $item['permissions'];
					$pinnwalls[] = $pinnwall;
				}
			}
		}
		return $pinnwalls;
	}
	
	public function isShareTypeAllowed($shareType) {
		return true;
	}
	
	public function getChildren($itemSource) {
		$itemSource = $this -> validateItemSource($itemSource);
			
		$query = \OCP\DB::prepare('SELECT `id`, `title` FROM `*PREFIX*pinit_pins` WHERE `wall_id` = ?');
		$result = $query->execute(array($itemSource));
		$children = array();
		while ($row = $result->fetchRow()) {
			$children[] = array('source' => $row['id'], 'target' => $row['title']);
		}
		return $children;
	}

	
	
}
