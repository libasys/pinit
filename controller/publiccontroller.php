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
use \OCP\AppFramework\Http;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\IRequest;
use \OCP\Share;
use \OCP\IL10N;
use \OCP\IURLGenerator;
use \OCP\ISession;
use \OCP\Security\IHasher;
use \OCP\AppFramework\Http\RedirectResponse;
use \OCP\AppFramework\Utility\IControllerMethodReflector;

class PublicController extends Controller {
		
	private $pinwallController;
	
	private $l10n;
	/** @var \OC\URLGenerator */
	protected $urlGenerator;
	
	/**
	 * @type ISession
	 * */
	private $session;
	
	/**
	 * @type IControllerMethodReflector
	 */
	protected $reflector;

	private $token;
	
	public function __construct($appName, IRequest $request, $pinwallController,  IL10N $l10n, ISession $session, IControllerMethodReflector $reflector, IURLGenerator $urlGenerator) {
		parent::__construct($appName, $request);
		$this->l10n = $l10n;
		$this->pinwallController = $pinwallController;
		$this->urlGenerator = $urlGenerator;
		$this->session = $session;
		$this->reflector=$reflector;
		
	}
	
	public function getLanguageCode() {
        return $this->l10n->getLanguageCode();
    }


    public function beforeController($controller, $methodName) {
		if ($this->reflector->hasAnnotation('Guest')) {
			return;
		}
		$isPublicPage = $this->reflector->hasAnnotation('PublicPage');
		if ($isPublicPage) {
			$this->validateAndSetTokenBasedEnv();
		} else {
			//$this->environment->setStandardEnv();
		}
	}
	
	
	private function validateAndSetTokenBasedEnv() {
			$this->token = $this->request->getParam('t');
	}
	
	
	/**
	*@PublicPage
	 * @NoCSRFRequired
	 * @UseSession
	 */
	public function index($token) {
			
		if ($token) {
			$linkItem = Share::getShareByToken($token, false);
			//Share Fix Link
			if (is_array($linkItem) && isset($linkItem['uid_owner'])) {
				$type = $linkItem['item_type'];
				$pinWallId = $this -> pinwallController -> validateItemSource($linkItem['item_source']);
				
				$shareOwner = $linkItem['uid_owner'];
				$path = null;
				$rootLinkItem = Share::resolveReShare($linkItem);
				$pinWallOwner = $rootLinkItem['uid_owner'];
				$PinwallName = $linkItem['item_target'];
				$ownerDisplayName = \OCP\User::getDisplayName($pinWallOwner);
				
				// stupid copy and paste job
				// stupid copy and paste job
					if (isset($linkItem['share_with'])) {
						// Authenticate share_with
						
						$password=$this->params('password');
						
						if (isset($password)) {
							
							if ($linkItem['share_type'] == \OCP\Share::SHARE_TYPE_LINK) {
								// Check Password
								$newHash = '';
								if(\OC::$server->getHasher()->verify($password, $linkItem['share_with'], $newHash)) {
									$this->session->set('public_link_authenticated', $linkItem['id']);
									if(!empty($newHash)) {

									}
								} else {
									\OCP\Util::addStyle('files_sharing', 'authenticate');
									$params=array(
									'wrongpw'=>true
									);
									return new TemplateResponse('files_sharing', 'authenticate', $params, 'guest');
									
								}
							} else {
								\OCP\Util::writeLog('share', 'Unknown share type '.$linkItem['share_type'].' for share id '.$linkItem['id'], \OCP\Util::ERROR);
									return false;
							}
			
						} else {
							// Check if item id is set in session
							if ( ! $this->session->exists('public_link_authenticated') || $this->session->get('public_link_authenticated') !== $linkItem['id']) {
								// Prompt for password
								\OCP\Util::addStyle('files_sharing', 'authenticate');
								
									$params=array();
									return new TemplateResponse('files_sharing', 'authenticate', $params, 'guest');
								
							}
						}
					}
				
				\OCP\Util::addscript('pinit', '3rdparty/tag-it');
				\OCP\Util::addscript('pinit', '3rdparty/leaflet');
				\OCP\Util::addscript('pinit', '3rdparty/Leaflet.EdgeMarker');
				\OCP\Util::addscript('pinit', '3rdparty/leaflet.markercluster-src');
				\OCP\Util::addscript('pinit', '3rdparty/leaflet.awesome-markers');
				\OCP\Util::addscript('pinit', '3rdparty/gridify');
				\OCP\Util::addscript('pinit', 'jquery.scrollTo');
				\OCP\Util::addScript('pinit', 'public');
			    $PinWallData=$this->pinwallController->getPinWall($pinWallId,true,true,$pinWallOwner);
				
				$params=[
					'token' => $token,
					'requesttoken' => \OCP\Util::callRegister(),
					'displayName' => $ownerDisplayName,
					'PinwallName' => $PinwallName,
					'PinwallBg' => $PinWallData['wallbg'],
				];
				$csp = new \OCP\AppFramework\Http\ContentSecurityPolicy();
				$csp->addAllowedImageDomain('*');
				$csp->addAllowedMediaDomain('*');	
				$csp->addAllowedFrameDomain('*');	
				$response = new TemplateResponse('pinit', 'public',$params,'base');
				$response->setContentSecurityPolicy($csp);
				return $response;
				
			}
			
		}

		$tmpl = new \OCP\Template('', '404', 'guest');
		$tmpl->printPage();
		
		//return new JSONResponse()->setStatus(Http::STATUS_NOT_FOUND);
	}
		
		
	
}