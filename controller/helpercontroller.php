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

class HelperController extends Controller {
	
	private $l10n;
	
	public function __construct($appName, IRequest $request, $l10n) {
		parent::__construct($appName, $request);
		$this->l10n = $l10n;
	}
	
	public function generatePinRow($row) {
			$row['imageWidth']=0;
			$row['imageHeight']=0;
			$row['imageMimeType']='image/jpg';
			if($row['image']!=''){
				$image = new \OCP\Image();	
				$image->loadFromBase64($row['image']);
				$row['imageWidth']=$image->width();	
			    $row['imageMimeType']=$image->mimeType();
				$row['imageHeight']=$image->height();			
			}
			
			$row['backgroundColor']='';
			$row['titlecolor']='';
			if($row['image']==''){
				$row['backgroundColor']=$this->genColorCodeFromText(trim($row['title']),90,7);
				$row['titlecolor']=$this->generateTextColor($row['backgroundColor']);
			}
			
			if($row['categories'] != ''){
				$temp=explode(',',$row['categories']);
				for($i=0; $i<count($temp); $i++){
					$backgroundColor=	$this->genColorCodeFromText(trim($temp[$i]),80);
					$row['category'][$i]=array(
					'name'=>$temp[$i],
					'bgcolor' =>$backgroundColor,
					'color' => $this->generateTextColor($backgroundColor),
					);
				}
				
				$row['categories']=$row['category'];
			}
			if($row['url']!=''){
				$urlInfo = parse_url($row['url']);
				$row['domain']=$urlInfo['host'];
			}
			if($row['media_url']!=''){
				$urlMediaInfo=$parse = parse_url($row['media_url']);
				$row['media_domain']=$urlMediaInfo['host'];
			}
			
			$row['pincolor']='';
			if($row['pincolor_id'] > 0){
				$row['pincolor']=$this->getPinColor($row['pincolor_id']);
			}
			
   			
            $row['isPublic']=$row['public'];
			$row['addDate']=$row['added'];
			
			$row['modifiedDate']=$row['lastmodified'];
			
			$today = time();
            $datediff = $today - $row['addDate']; 
				//5 Tage neu ( 432000 = 5tage *24 std *60min *60sec )
				$row['newpin']=0;
                if ($datediff <= 432000) {
                	$row['newpin']=1;
				} 
			
			
			$row['userdisplayname']=\OCP\User::getDisplayName($row['user_id']);
			
			return $row;
	}

	public function generateCategoriesColor($categories){
		if($categories != ''){
			$aCategories='';	
			$temp=explode(',',$categories);
			for($i=0; $i<count($temp); $i++){
				$backgroundColor=	$this->genColorCodeFromText(trim($temp[$i]),80);
				$aCategories[$i]=array(
				'name'=>$temp[$i],
				'bgcolor' =>$backgroundColor,
				'color' => $this->generateTextColor($backgroundColor),
				);
			}
			
			return $aCategories;
		}else{
			return false;
		}
	}
    
	public function getPinColor($id){
		
		switch($id){
			case 1:
				return "blue";
				break;
			case 2:
				return "green";
				break;
			case 3:
				return "red";
				break;
			case 4:
				return "yellow";
				break;
		}
		
	}
	
	/**
	 * @brief returns the options for Pin colors
	 * @return array - valid inputs for monthly repeating events
	 */
	public function getPinColorOptions() {
		return array(
		    '0' => 'none',
			'1' => 'blue',
			'2'  => 'green',
			'3'  => 'red',
			'4'  => 'yellow',
		);
	}


	/*
	 * @brief generates the text color for the calendar
	 * @param string $calendarcolor rgb calendar color code in hex format (with or without the leading #)
	 * (this function doesn't pay attention on the alpha value of rgba color codes)
	 * @return boolean
	 */
	public function generateTextColor($calendarcolor) {
		if(substr_count($calendarcolor, '#') == 1) {
			$calendarcolor = substr($calendarcolor,1);
		}
		$red = hexdec(substr($calendarcolor,0,2));
		$green = hexdec(substr($calendarcolor,2,2));
		$blue = hexdec(substr($calendarcolor,4,2));
		//recommendation by W3C
		$computation = ((($red * 299) + ($green * 587) + ($blue * 114)) / 1000);
		return ($computation > 130)?'#000000':'#FAFAFA';
	}
	
	
	 /**
     * genColorCodeFromText method
     *
     * Outputs a color (#000000) based Text input
     *
     * (https://gist.github.com/mrkmg/1607621/raw/241f0a93e9d25c3dd963eba6d606089acfa63521/genColorCodeFromText.php)
     *
     * @param String $text of text
     * @param Integer $min_brightness: between 0 and 100
     * @param Integer $spec: between 2-10, determines how unique each color will be
     * @return string $output
	  * 
	  */
	  
	 public function genColorCodeFromText($text, $min_brightness = 100, $spec = 10){
        // Check inputs
        if(!is_int($min_brightness)) throw new Exception("$min_brightness is not an integer");
        if(!is_int($spec)) throw new Exception("$spec is not an integer");
        if($spec < 2 or $spec > 10) throw new Exception("$spec is out of range");
        if($min_brightness < 0 or $min_brightness > 255) throw new Exception("$min_brightness is out of range");

        $hash = md5($text);  //Gen hash of text
        $colors = array();
        for($i=0; $i<3; $i++) {
            //convert hash into 3 decimal values between 0 and 255
            $colors[$i] = max(array(round(((hexdec(substr($hash, $spec * $i, $spec))) / hexdec(str_pad('', $spec, 'F'))) * 255), $min_brightness));
        }

        if($min_brightness > 0) {
            while(array_sum($colors) / 3 < $min_brightness) {
                for($i=0; $i<3; $i++) {
                    //increase each color by 10
                    $colors[$i] += 10;
                }
            }
        }

        $output = '';
        for($i=0; $i<3; $i++) {
            //convert each color to hex and append to output
            $output .= str_pad(dechex($colors[$i]), 2, 0, STR_PAD_LEFT);
        }

        return '#'.$output;
    }

   public function getPinIcons() {
		
		return array(
			'map-marker' => (string)$this->l10n->t('Standard'),
			'glass' => (string)$this->l10n->t('Bar'),
			'user' => (string)$this->l10n->t('Person'),
			'music' => (string)$this->l10n->t('Club'),
			'tag' => (string)$this->l10n->t('Event'),
			'shopping-cart' => (string)$this->l10n->t('Shopping'),
			'heart' => (string)$this->l10n->t('Famous'),
		);
	}
   
   public function getPinMarkerColor() {
		return array(
			'blue',
			'red',
			'darkred',
			'orange',
			 'green',
			'darkgreen',
			'purple',
			'cadetblue'
			  );
		
	}
   
   public function getPinWallBackground($id){
		
		switch($id){
			case 1:
				return "wood.png";
				break;
			case 2:
				return "edel.jpg";
				break;
			case 3:
				return "edel-light.png";
				break;
			case 4:
				return "grey.jpg";
				break;
			case 5:
				return "wall-dark.jpg";
				break;
			case 6:
				return "skulls.png";
				break;
			case 7:
				return "green_cup.png";
				break;
		}
		
	}
	
	/**
	 * @brief returns the options for Pinwall Backgrounds
	 * @return array - valid inputs for monthly repeating events
	 * 
	 */
	public  function getPinWallBackgroundOptions() {
		return array(
			'0' => 'none',
			'1' => 'wood.png',
			'2'  => 'edel.jpg',
			'3'  => 'edel-light.png',
			'4'  => 'grey.jpg',
			'5'  => 'wall-dark.jpg',
			'6'  => 'skulls.png',
			'7'  => 'green_cup.png',
		);
	}
	
	public function  relative_modified_date($timestamp) {
		
		
		$timeDiff = time() - $timestamp;
		$diffMinutes = round($timeDiff/60);
		$diffHours = round($diffMinutes/60);
		$diffDays = round($diffHours/24);
		$diffMonths = round($diffDays/31);
		if($timeDiff < 60) { return (string)$this->l10n->t('seconds ago'); }
		elseif($timeDiff < 3600) { return (string)$this->l10n->n('%n minute ago', '%n minutes ago', $diffMinutes); }
		elseif($timeDiff < 86400) { return (string)$this->l10n->n( '%n hour ago', '%n hours ago', $diffHours); }
		elseif($timeDiff < 86400) { return (string)$this->l10n->t('today'); }
		elseif($timeDiff < 172800) { return (string)$this->l10n->t('yesterday'); }
		elseif($timeDiff < 2678400) { return (string)$this->l10n->n('%n day ago', '%n days ago', $diffDays); }
		elseif($timeDiff < 5184000) { return (string)$this->l10n->t('last month'); }
		elseif($timeDiff < 31556926) { return (string)$this->l10n->n( '%n month ago', '%n months ago', $diffMonths); }
		elseif($timeDiff < 63113852) { return (string)$this->l10n->t('last year'); }
		else { return (string)$this->l10n->t('years ago'); }
}


public function convertDecimalToDMS($degree) {
	if ($degree > 180 || $degree < -180){
		return null;
	}
	$degree = abs($degree); // make sure number is positive
	// (no distinction here for N/S
	// or W/E).
	$seconds = $degree * 3600; // Total number of seconds.
	$degrees = floor($degree); // Number of whole degrees.
	$seconds -= $degrees * 3600; // Subtract the number of seconds
	// taken by the degrees.
	$minutes = floor($seconds / 60); // Number of whole minutes.
	$seconds -= $minutes * 60; // Subtract the number of seconds
	// taken by the minutes.
	$seconds = round($seconds*100, 0); // Round seconds with a 1/100th
	// second precision.
	return array(array($degrees, 1), array($minutes, 1), array($seconds, 100));
}

public function gps($coordinate, $hemisphere) {
	for ($i = 0; $i < 3; $i++) {
		$part = explode('/', $coordinate[$i]);
		if (count($part) == 1) {
			$coordinate[$i] = $part[0];
		} else if (count($part) == 2) {
			$coordinate[$i] = floatval($part[0]) / floatval($part[1]);
		} else {
			$coordinate[$i] = 0;
		}
	}
	list($degrees, $minutes, $seconds) = $coordinate;
	$sign = ($hemisphere == 'W' || $hemisphere == 'S') ? -1 : 1;
	return $sign * ($degrees + $minutes / 60 + $seconds / 3600);
}

public function getLocationInfo($url, $userAgent = true) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, 0);
    	curl_setopt($ch, CURLOPT_TIMEOUT, 900); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		if ($userAgent) {
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 GTB5');
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		$tmp = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//\OCP\Util::writeLog('pinit','HTTPCODE:'.$httpCode,\OCP\Util::DEBUG);
		if ($httpCode == 404) {
			return false;
		} else {
			if ($tmp != false) {
				return $tmp;
			}
		}

	}

public function get_image_from_url($url) {
    
	$ch      = curl_init( $url );
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1); 
	$content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $headerInfo  = curl_getinfo( $ch, CURLINFO_CONTENT_TYPE );
	curl_close($ch);
	
	$mime='';
	preg_match( '@([\w/+]+)(;\s+charset=(\S+))?@i', $headerInfo, $matches );
	if ( isset( $matches[1] ) )  {
		$mime = $matches[1];
	}
	
    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
	$header['mimetype'] = $mime;
	
	return $header;
}

public function get_web_page( $url ){
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
    );

    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $headerInfo  = curl_getinfo( $ch, CURLINFO_CONTENT_TYPE );
    curl_close( $ch );
	
	$mime='';
	preg_match( '@([\w/+]+)(;\s+charset=(\S+))?@i', $headerInfo, $matches );
	if ( isset( $matches[2] ) )  {
		$mime = $matches[2];
		\OCP\Util::writeLog('pinit','MIME:'.$mime,\OCP\Util::DEBUG);
	}

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
}
	
	public  function permissionReader($iPermission){
			
			$l= \OC::$server->getL10N('core');
			
			$aPermissionArray=array(
			   16 => $l->t('share'),
			   8 => $l->t('delete'),
			   4 => $l->t('create'),
			   2 => $l->t('update'),
			   1 => 'lesen',
			);
			
			if($iPermission==1) return 'readonly';
			if($iPermission==31) return 'full access';
			
			$outPutPerm='';
			foreach($aPermissionArray as $key => $val){
				if($iPermission>= $key){
					if($outPutPerm=='') $outPutPerm.=$val;
					else $outPutPerm.=', '.$val;
					$iPermission-=$key;
				}
			}
			return $outPutPerm;
		
	}
}