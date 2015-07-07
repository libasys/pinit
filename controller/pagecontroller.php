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
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\IRequest;
use \OCP\IL10N;
/**
 * Controller class for main page.
 */
class PageController extends Controller {
	
	private $userId;
	private $pinDAO;
	private $l10n;
	private $helperController;
	

	public function __construct($appName, IRequest $request,  $helperController, $userId, IL10N $l10n, $pinDAO) {
		parent::__construct($appName, $request);
		$this -> userId = $userId;
		$this->l10n = $l10n;
		$this->helperController = $helperController;
		$this->pinDAO = $pinDAO;
		
	}
	
	public function getLanguageCode() {
        return $this->l10n->getLanguageCode();
    }

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		
		
        $maxUploadFilesize = \OCP\Util::maxUploadFilesize('/');
        
        $aPinColors=$this->helperController->getPinColorOptions();
		$config = \OC::$server->getConfig();
		
		$csp = new \OCP\AppFramework\Http\ContentSecurityPolicy();
		$csp->addAllowedImageDomain('*');
		$csp->addAllowedMediaDomain('*');	
		$csp->addAllowedFrameDomain('*');	
		
		/*
		$iPins = 70;
		
		for($i = 0; $i < $iPins; $i++){
			$saveArray=[
			'pname' => 'counter-'.$i,
			'wall_id' => 2
			];	
			$this->pinDAO->save($saveArray);
		}*/
		
		$response = new TemplateResponse('pinit', 'index');
		$response->setContentSecurityPolicy($csp);
		$response->setParams(array(
			'uploadMaxFilesize' => $maxUploadFilesize,
			'uploadMaxHumanFilesize' => \OCP\Util::humanFileSize($maxUploadFilesize),
			'aPinColors' => $aPinColors,
			'allowShareWithLink' => $config->getAppValue('core', 'shareapi_allow_links', 'yes'),
			'mailNotificationEnabled' => $config->getAppValue('core', 'shareapi_allow_mail_notification', 'no'),
			'mailPublicNotificationEnabled' => $config->getAppValue('core', 'shareapi_allow_public_notification', 'no'), 
			
		));

		return $response;
	}
}