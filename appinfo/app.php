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

 $app = new Application(); 
 $c = $app->getContainer();

// add an navigation entry
$navigationEntry = function () use ($c) {
	return [
		'id' => $c->getAppName(),
		'order' => 10,
		'name' => $c->query('L10N')->t('Pinit'),
		'href' => $c->query('URLGenerator')->linkToRoute('pinit.page.index'),
		'icon' => $c->query('URLGenerator')->imagePath('pinit', 'pinit.svg'),
	];
};

$c->getServer()->getNavigationManager()->add($navigationEntry);

\OCP\Share::registerBackend('pinwall', '\OCA\Pinit\Controller\ShareController');
//upcoming version search for 8.2 perhaps patch https://github.com/owncloud/core/pull/17339/files
\OC::$server->getSearch()->registerProvider('OCA\Pinit\Search\Provider', array('app' => 'pinit'));

