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
use \OCP\IRequest;


class TagsController extends Controller {
	
	 
	 /**
	 * @brief categories of the user
	 */
	protected $categories = null;
	private $l10n;
	private $helperController;
	private $pinDAO;
	private $pinWallDAO;
	
	public function __construct($appName, IRequest $request, $pinDAO, $pinWallDAO, $helperController, $l10n) {
		parent::__construct($appName, $request);
		
		$this->l10n = $l10n;
		$this->helperController = $helperController;
		$this->pinDAO = $pinDAO;
		$this->pinWallDAO = $pinWallDAO;
	}
	
	
	/**
	 * @NoAdminRequired
	 */
	public function addTag(){
		$newTag = trim($this -> params('tag'));	
		if($newTag !=''){
			$categories=explode(',',$newTag);	
			$this->getVCategories()->addMultiple($categories, true);
		}
				$existCats = $this -> getTagOptions();
				$tag=array();
				for($i=0; $i<count($existCats); $i++){
					$backgroundColor=	$this -> helperController -> genColorCodeFromText(trim($existCats[$i]),80);
					$tag[$i]=array(
					'name'=>$existCats[$i],
					'bgcolor' =>$backgroundColor,
					'color' => $this -> helperController -> generateTextColor($backgroundColor),
					);
				}
					
				$tagsReturn['tagslist']=$tag;
				$tagsReturn['categories']=$existCats;
				
				$response = new JSONResponse();
				$response -> setData($tagsReturn);
						  
				return $response;
				
	}
	
	/**
	 * @NoAdminRequired
	 */
	public function deleteTag(){
		
		$deleteTag=trim($this -> params('tag'));	
		
		if($deleteTag !=''){
			
			$this->getVCategories()->delete($deleteTag);
		}
		$existCats=$this->getTagOptions();
		
		$tag=array();
		for($i=0; $i<count($existCats); $i++){
			$backgroundColor=	$this -> helperController -> genColorCodeFromText(trim($existCats[$i]),80);
			$tag[$i]=array(
			'name'=>$existCats[$i],
			'bgcolor' =>$backgroundColor,
			'color' => $this -> helperController -> generateTextColor($backgroundColor),
			);
		}
					
		$tagsReturn['tagslist']=$tag;
		$tagsReturn['categories']=$existCats;
		
		$response = new JSONResponse();
		$response -> setData($tagsReturn);
						  
		return $response;
	}
	/**
	 * @NoAdminRequired
	 */
	public function loadTags(){
		$existCats=$this->getTagOptions();
		$tag=array();
		for($i=0; $i<count($existCats); $i++){
			$backgroundColor=	$this -> helperController -> genColorCodeFromText(trim($existCats[$i]),80);
			$tag[$i]=array(
			'name'=>$existCats[$i],
			'bgcolor' =>$backgroundColor,
			'color' => $this -> helperController -> generateTextColor($backgroundColor),
			);
		}
					
		$tagsReturn['tagslist']=$tag;
		$tagsReturn['categories']=$existCats;
		
		$response = new JSONResponse();
		$response -> setData($tagsReturn);
						  
		return $response;
	}
	
	public  function getVCategories() {
		
		if (is_null($this->categories)) {
			$this->categories = \OC::$server->getTagManager()->load('pinit');
			if($this->categories ->isEmpty('pinit')) {
				 $this->scanCategories();
				 $this->categories = \OC::$server->getTagManager()->load('pinit', $this->getDefaultTags());
			}
			
		}
		return $this->categories;
	}
	
	/**
	 * @brief returns the categories of the vcategories object
	 * @return (array) $categories
	 */
	public  function getTagOptions() {
		$getNames = function($tag) {
			return $tag['name'];
		};
		$categories = $this->getVCategories()->getTags();
		$categories = array_map($getNames, $categories);
		return $categories;
	}
	
	
	/**
	 * @brief returns the default categories of ownCloud
	 * @return (array) $categories
	 */
	public  function getDefaultTags() {
		
		return array(
			(string)$this->l10n->t('Sport'),
			(string)$this->l10n->t('Internet'),
			(string)$this->l10n->t('Work'),
			(string)$this->l10n->t('Other'),
		);
	}
	
	/**
	 * scan vcards for categories.
	 * @param $vccontacts VCards to scan. null to check all vcards for the current user.
	 */
	public  function scanCategories($vpins = null) {
		
		if (is_null($vpins)) {
			$pinwalls = $this->pinWallDAO->getAll(\OCP\USER::getUser());
			if(count($pinwalls) > 0) {
				$vpins = array();
				foreach($pinwalls as $pinwall) {
					if($pinwall['user_id'] === \OCP\User::getUser()) {
						$pinwall_pins = $this->pinDAO->getAll($pinwall['id'],true);
						$vpins = $vpins + $pinwall_pins;
					}
				}
			}
		}
			
			if(is_array($vpins) && count($vpins) > 0) {
			
				$categories = \OC::$server->getTagManager()->load('pinit');
				
				$getName = function($tag) {
					return array('name'=>$tag['name']);
			    };
				
				$tags = array_map($getName, $categories->getTags());
			    $categories->delete($tags);
				foreach($vpins as $pinInfo){
					if($pinInfo['categories']!='')	{
					  $categories->addMultiple($pinInfo['categories'], true, $pinInfo['id']);
					}
				}
			}
		
		
	}
	
	
}