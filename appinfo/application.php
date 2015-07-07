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
 
namespace OCA\Pinit\AppInfo;

use OC\AppFramework\Utility\SimpleContainer;
use \OCP\AppFramework\App;
use \OCP\Share;
use \OCP\IContainer;
use OCP\AppFramework\IAppContainer;

use \OCA\Pinit\Controller\PinController;
use \OCA\Pinit\Controller\PinWallController;
use \OCA\Pinit\Controller\PageController;
use \OCA\Pinit\Controller\HelperController;
use \OCA\Pinit\Controller\PhotoController;
use \OCA\Pinit\Controller\TagsController;
use \OCA\Pinit\Controller\PublicController;
use \OCA\Pinit\Db\PinDAO;
use \OCA\Pinit\Db\PinWallDAO;



class Application extends App {
	
	public function __construct (array $urlParams=array()) {
		
		parent::__construct('pinit', $urlParams);
        $container = $this->getContainer();
	
	
		$container->registerService('PageController', function(IContainer $c) {
			return new PageController(
			$c->query('AppName'),
			$c->query('Request'),
			$c->query('HelperController'),
			$c->query('UserId'),
			$c->query('L10N'),
			$c->query('PinDAO')
			);
		});
		
		$container->registerService('PublicController', function(IContainer $c) {
			return new PublicController(
			$c->query('AppName'),
			$c->query('Request'),
			$c->query('PinWallController'),
			$c->query('L10N'),
			$c->query('Session'),
			$c->query('OCP\AppFramework\Utility\IControllerMethodReflector'),
			$c->query('ServerContainer')->getURLGenerator()
			);
		});
	
		$container->registerService('PinController', function(IContainer $c) {
			return new PinController(
			$c->query('AppName'),
			$c->query('Request'),
			$c->query('UserId'),
			$c->query('PinDAO'),
			$c->query('HelperController'),
			$c->query('PinWallController'),
			$c->query('L10N')
			);
		});
		$container->registerService('PinWallController', function(IContainer $c) {
			return new PinWallController(
			$c->query('AppName'),
			$c->query('Request'),
			$c->query('UserId'),
			$c->query('PinDAO'),
			$c->query('PinWallDAO'),
			$c->query('HelperController'),
			$c->query('L10N')
			);
		});
	
		$container->registerService('HelperController', function(IContainer $c) {
			return new HelperController(
			$c->query('AppName'),
			$c->query('Request'),
			$c->query('L10N')
			);
		});
		
		$container->registerService('TagsController', function(IContainer $c) {
			return new TagsController(
			$c->query('AppName'),
			$c->query('Request'),
			$c->query('PinDAO'),
			$c->query('PinWallDAO'),
			$c->query('HelperController'),
			$c->query('L10N')
			);
		});
		
		$container->registerService('PhotoController', function(IContainer $c) {
			return new PhotoController(
			$c->query('AppName'),
			$c->query('Request'),
			$c->query('HelperController'),
			$c->query('L10N')
			);
		});
		
		
		
		/**
         * Database Layer
         */
          $container->registerService('PinDAO', function(IContainer $c) {
            return new PinDAO(
            $c->query('ServerContainer')->getDb(),
            $c->query('UserId'),
            $c->query('PinWallDAO'),
            $c->query('L10N')
			);
        });
		
		$container->registerService('PinWallDAO', function(IContainer $c) {
            return new PinWallDAO(
            $c->query('ServerContainer')->getDb()
           
			);
        });
	
          /**
		 * Core
		 */
		$container -> registerService('UserId', function(IContainer $c) {
			return \OCP\User::getUser();
		});
		
		 $container->registerService('URLGenerator', function(IContainer $c) {
			/** @var \OC\Server $server */
			$server = $c->query('ServerContainer');
			return $server->getURLGenerator();
		});
		
		$container -> registerService('L10N', function(IContainer $c) {
			return $c -> query('ServerContainer') -> getL10N($c -> query('AppName'));
		});
		
		$container->registerService('Session', function (IAppContainer $c) {
			return $c->getServer()
					 ->getSession();
			}
		);
		 $container->registerService('Token', function (IContainer $c) {
			return $c->query('Request') ->getParam('token');
			}
		);
		
		//$this->registerProviders();
	}
  
     public function registerProviders() {
			Share::registerBackend('pinwall', '\OCA\Pinit\Controller\ShareController');
			
		}

}

