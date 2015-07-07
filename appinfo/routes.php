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
 
namespace OCA\Pinit;


use \OCA\Pinit\AppInfo\Application;

$application = new Application();

$application->registerRoutes($this, ['routes' => [
	['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
	['name' => 'public#index', 'url' => '/s/{token}', 'verb' => 'GET'],
	['name' => 'public#index','url'  => '/s/{token}', 'verb' => 'POST', 'postfix' => 'auth'],
	['name' => 'pin#getPins',	'url' => '/pins',	'verb' => 'GET'],
	['name' => 'pin#getPin',	'url' => '/pin',	'verb' => 'GET'],
	['name' => 'pin#getPinsPublic',	'url' => '/pinspublic',	'verb' => 'GET'],
	['name' => 'pin#getAllPinsUser',	'url' => '/getallpinsuser',	'verb' => 'GET'],
	['name' => 'pin#showPin',	'url' => '/showpin',	'verb' => 'POST'],
	['name' => 'pin#showPinPublic',	'url' => '/showpinpublic',	'verb' => 'POST'],
	['name' => 'pin#newPin',	'url' => '/newpin',	'verb' => 'POST'],
	['name' => 'pin#newPinSave',	'url' => '/newpinsave',	'verb' => 'POST'],
	['name' => 'pin#editPin',	'url' => '/editpin',	'verb' => 'POST'],
	['name' => 'pin#editPinSave',	'url' => '/editpinsave',	'verb' => 'POST'],
	['name' => 'pin#deletePin',	'url' => '/deletepin',	'verb' => 'POST'],
	['name' => 'pin#deletePhotoPin',	'url' => '/deletephotopin',	'verb' => 'GET'],
	['name' => 'pin#movePin',	'url' => '/movepin',	'verb' => 'GET'],
	['name' => 'pin#addCategoryToPin',	'url' => '/addcategorytopin',	'verb' => 'GET'],
	['name' => 'pin#getLonLatFromAddress',	'url' => '/lonlataddresspin',	'verb' => 'GET'],
	['name' => 'pin#getWebsiteInfo',	'url' => '/getwebsiteinfopin',	'verb' => 'GET'],
	['name' => 'pin#changePinStatus',	'url' => '/changepinstatus',	'verb' => 'GET'],
	['name' => 'pinWall#getPinWalls',	'url' => '/pinwalls',	'verb' => 'GET'],
	['name' => 'pinWall#newPinWall',	'url' => '/newpinwall',	'verb' => 'GET'],
	['name' => 'pinWall#editPinWall',	'url' => '/editpinwall',	'verb' => 'GET'],
	['name' => 'pinWall#deletePinWall',	'url' => '/deletepinwall',	'verb' => 'GET'],
	['name' => 'pinWall#getPinWallBackground',	'url' => '/getpinwallbg',	'verb' => 'GET'],
	['name' => 'pinWall#saveSortOrderPinwall',	'url' => '/savesortorderpinwall',	'verb' => 'GET'],
	['name' => 'photo#getImageFromCloud',	'url' => '/getimagefromcloud',	'verb' => 'GET'],
	['name' => 'photo#cropPhoto',	'url' => '/cropphoto',	'verb' => 'POST'],
	['name' => 'photo#saveCropPhoto',	'url' => '/savecropphoto',	'verb' => 'POST'],
	['name' => 'photo#uploadPhoto',	'url' => '/uploadphoto',	'verb' => 'POST'],
	['name' => 'photo#clearPhotoCache',	'url' => '/clearphotocache',	'verb' => 'POST'],
	['name' => 'tags#addTag',	'url' => '/addtag',	'verb' => 'GET'],
	['name' => 'tags#deleteTag',	'url' => '/deletetag',	'verb' => 'GET'],
	['name' => 'tags#loadTags',	'url' => '/loadtags',	'verb' => 'GET'],
]]);

\OCP\API::register('get',
		'/apps/pinit/api/v1/shares',
		array('\OCA\Pinit\API\Local', 'getAllShares'),
		'pinit');
\OCP\API::register('get',
		'/apps/pinit/api/v1/shares/{id}',
		array('\OCA\Pinit\API\Local', 'getShare'),
		'pinit');		


