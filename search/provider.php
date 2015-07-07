<?php
/**
 * ownCloud
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

namespace OCA\Pinit\Search;
use OCA\Pinit\AppInfo\Application;

/**
 * Provide search results from the 'calendar' app
 */
class Provider extends \OCP\Search\Provider {

    private $pinController;
	private $pinWallController;
	private $l10N;
	
    public function __construct() {
		$app = new Application();
		$container = $app->getContainer();
		$this->app = $app;
		$this->pinController = $container->query('PinController');
		$this->pinWallController = $container->query('PinWallController');
		$this->l10N = $container->query('L10N');
	}
	
	/**
	 * 
	 * @param string $query
	 * @return \OCP\Search\Result
	 */
	function search($query) {
		$unescape = function($value) {
			return strtr($value, array('\,' => ',', '\;' => ';'));
		};

		$searchresults = array();
		$results = $this->pinController->searchProperties($query);
		
		foreach($results as $result) {
				
				$link = \OCP\Util::linkToRoute('pinit.page.index').'#' . $result['id'];
				
				$returnData['id']=$result['id'];
				$returnData['description']=$result['title'];
				$returnData['link']=$link;
						
			     $results[]=new \OCA\Pinit\Search\Pinit($returnData);
			
			
		}
		return $results;
	}
}
