<?php

namespace Drupal\pepsibrands\Controller;

use Drupal\Core\Url;
use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Component\Utility\Unicode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UserAgent;

use Drupal\pepsibrands\BrandsContentFilters;
use Drupal\pepsibrands\ReceiptManager;
use Drupal\pepsibrands\PepsiFormValidation;
use Drupal\pepsibrands\EmailManager;

/**
 * Contains the callback handler used by the OneAll Social Login Module.
 */
class PepsiBrandsController extends ControllerBase {

	    private function is_wrong_language_url(){
			$langcode = get_current_langcode();
			$current_path = \Drupal::service('path.current')->getPath();
			if ($langcode == 'en-ca' && strpos($current_path, "/marques/") !== false
				|| $langcode == 'fr-ca' && strpos($current_path, "/brands/") !== false
				|| $langcode == 'fr-ca' && $current_path === "/brands"
				|| $langcode == 'en-ca' && $current_path === "/marques"
				){
				return true;
			}
			return false;
	    }

	    private function get_brand_alias($brand, $langcode){
	    	if ($brand == 'smartfood'){
	    		if ($langcode == 'en-ca'){
	    			$brand = 'smartfoodpopcorn';
	    		} else{
	    			$brand = 'maissoufflesmartfood';
	    		}
	    	}

	    	if ($brand == 'stacys'){
	    		$brand = 'stacyssnacks';
	    	}

			if ($brand == 'flaminhot' && $langcode == 'fr-ca'){
	    		$brand = 'enflamme';
	    	}

	    	return $brand;
	    }

	    private function is_brand_active($brand){
	    	$active_brands = [
	    		'tostitos', 'lays', 'bare', 'fritolayvarietypacks', 'produitsassortisfritolay',
	    		'crispyminis', 'offtheeatenpathsnacks', 'otep', 'collationsofftheeatenpath', 'cheetos',
	    		'missvickies', 'enflamme', 'flaminhot', 'smartfoodpopcorn', 'maissoufflesmartfood',
	    		'stacyssnacks', 'smartfood', 'capncrunch', 'capitainecrounche', 'sunchips',
	    		'fritolayproduitsassortis', 'popcorners',
	    		 // 'pearlmilling',
	    	];
	    	if (in_array($brand, $active_brands))
	    		return 1;

	    	$query = \Drupal::entityQuery('node');
	    	$query->condition('type', 'brand');
			$query->condition('field_brand_status', 1);
			$has_title_or_machinename = $query->orConditionGroup()
				->condition('title', $brand )
				->condition('field_machinename', $brand );
			$query->condition($has_title_or_machinename);


			$result = $query->execute();
			if (!empty($result)){
				return 1;
			}
			return 0;
	    }

	    private function redirect_correct_lang_route($brand, $route, $brand_params = []){
	    	if (!empty($brand))
	    		$brand_params['brand'] = $brand;

	    	if (in_array($brand, ['smartfoodpopcorn', 'smartfood', 'maissoufflesmartfood'])){
	    		if (strpos($route, 'en-ca.') !== false){
	    			$brand_params['brand'] ='smartfoodpopcorn';
	    		} elseif(strpos($route, 'fr-ca') !== false){
	    			$brand_params['brand'] ='maissoufflesmartfood';
	    		}
	    	}

	    	if ($brand == 'stacys'){
    			$brand_params['brand'] = 'stacyssnacks';
	    	}

	    	return new RedirectResponse(\Drupal\Core\Url::fromRoute($route, $brand_params)->toString()); 
	    }

	    private function check_brand_status($brand){
			if (!$this->is_brand_active($brand) ){
				return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
			}
	    }


	    public function homepage(Request $request, $brand) {
	    	// \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.

	    	$data = [];
			$langcode = get_current_langcode();

			if (!$this->is_brand_active($brand) ){
				return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
			}

			if ($brand !=  $this->get_brand_alias($brand, $langcode)){
				$route_name = \Drupal::routeMatch()->getRouteName();
				$params = ['brand' => $this->get_brand_alias($brand, $langcode)];
				return new RedirectResponse(\Drupal\Core\Url::fromRoute($route_name, $params)->toString()); 
			}

			$template = "pepsibrands_homepage_otep_template";
			
	    	switch ($brand) {
	    		case 'tostitos':
	    			$template = "pepsibrands_homepage_tostitos_template";
	    			if ($this->is_wrong_language_url()){
	    				return $this->redirect_correct_lang_route($brand, $langcode .".pepsibrands.homepage");
	    			}
	    			break;

	    		case 'fritolayvarietypacks':
	    		case 'produitsassortisfritolay':
	    		case 'fritolayproduitsassortis':
	    			$template = "pepsibrands_homepage_fritolay_template";
	    			$fritolay_url = [
	    				'en-ca' => 'fritolayvarietypacks',
	    				'fr-ca' => 'fritolayproduitsassortis',
	    				// 'fr-ca' => 'produitsassortisfritolay',
	    			];

	    			if ($fritolay_url[$langcode] != $brand){
						return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode .".pepsibrands.homepage", ['brand' => $fritolay_url[$langcode]])->toString()); 

	    			}
	    			break;

	    		case 'bare':
	    		case 'popcorners':
	    			$template = "pepsibrands_homepage_bare_template";
	    			if ($this->is_wrong_language_url()){
	    				return $this->redirect_correct_lang_route($brand, $langcode .".pepsibrands.homepage");
	    			}
	    			break;

	    		case 'otep':
	    		case 'offtheeatenpathsnacks':
	    		case 'collationsofftheeatenpath':
	    			$otep_url = [
	    				'en-ca' => 'offtheeatenpathsnacks',
	    				'fr-ca' => 'collationsofftheeatenpath',
	    			];

	    			if ($otep_url[$langcode] != $brand){
						return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode .".pepsibrands.homepage", ['brand' => $otep_url[$langcode]])->toString()); 

	    			}


	    			$template = "pepsibrands_homepage_otep_template";
	    			if ($this->is_wrong_language_url()){
	    				return $this->redirect_correct_lang_route($brand, $langcode .".pepsibrands.homepage");
	    			}
	    			break;

	    		case 'missvickies':
	    		case 'crispyminis':
	    		case 'cheetos':
	    		// case 'doritos':
	    		// case 'ruffles':
	    		case 'quaker':
	    			$template = "pepsibrands_homepage_otep_template";
	    			if ($this->is_wrong_language_url()){
	    				return $this->redirect_correct_lang_route($brand, $langcode .".pepsibrands.homepage");
	    			}
	    			break;

	    		case 'lays':
	    			if ($this->is_wrong_language_url()){
	    				return $this->redirect_correct_lang_route($brand, $langcode .".pepsibrands.homepage");
	    			}
	    			$template = "pepsibrands_homepage_lays_template";
	    			break;

				case 'flaminhot':
				case 'enflamme':
					$template = "pepsibrands_homepage_flaminhot_template";
					$flaminhot_url = [
	    				'en-ca' => 'flaminhot',
	    				'fr-ca' => 'enflamme',
	    			];

	    			if ($flaminhot_url[$langcode] != $brand){
						return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode .".pepsibrands.homepage", ['brand' => $flaminhot_url[$langcode]])->toString()); 
	    			}
					break;

				case 'capitainecrounche':
				case 'capncrunch':
					$template = "pepsibrands_homepage_otep_template";
					$cap_url = [
	    				'en-ca' => 'capncrunch',
	    				'fr-ca' => 'capitainecrounche',
	    			];

	    			if ($cap_url[$langcode] != $brand){
						return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode .".pepsibrands.homepage", ['brand' => $cap_url[$langcode]])->toString()); 
	    			}
					break;
	    		
	    		default:
					// return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
	    			break;
	    	}

			if ($this->is_wrong_language_url()){
				return $this->redirect_correct_lang_route($brand, $langcode .".pepsibrands.homepage");
			}


		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );
	    }

	    public function brandsProducts(Request $request, $brand) {
	    	if (!$this->is_brand_active($brand) ){
				return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
			}
	    	$data = [];
	    	$template = "pepsibrands_products_tostitos_template";
	    	$langcode = get_current_langcode();
	    	if ($brand !=  $this->get_brand_alias($brand, $langcode)){
				$route_name = \Drupal::routeMatch()->getRouteName();
				$params = ['brand' => $this->get_brand_alias($brand, $langcode)];
				return new RedirectResponse(\Drupal\Core\Url::fromRoute($route_name, $params)->toString()); 
			}

	    	if ($this->is_wrong_language_url()){
				return $this->redirect_correct_lang_route($brand, $langcode .".pepsibrands.products");
			}
		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );

	    }

	    public function laysProductPoppables(Request $request){
	    	$data = [];
	    	$template = "pepsibrands_tostitos_product_categories";
		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );
	    }

	    public function recall(Request $request, $brand) {
	    	if (!$this->is_brand_active($brand) ){
				return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
			}

	    	$langcode = get_current_langcode();
	    	$data = [];
	    	if ($brand !=  $this->get_brand_alias($brand, $langcode)){
				$route_name = \Drupal::routeMatch()->getRouteName();
				$params = ['brand' => $this->get_brand_alias($brand, $langcode)];
				return new RedirectResponse(\Drupal\Core\Url::fromRoute($route_name, $params)->toString()); 
			}
	   //  	switch ($brand) {
	   //  		case 'tostitos':
	   //  		case 'lays':
	   //  		case 'bare':
				// case 'fritolayvarietypacks':
				// case 'fritolayvarietypacks':
	   //  		case 'enflamme':
	   //  		case 'flaminhot':
	   //  			$template = "pepsibrands_about_us_template";
	   //  			if ($this->is_wrong_language_url()){
	   //  				return $this->redirect_correct_lang_route($brand, $langcode .".pepsibrands.recall");
	   //  			}

	   //  			if (empty(getConsumerBanner($brand))){
	   //  				// Only allow access to this page when there's an active Alert banner
				// 		return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString());
	   //  			}
	   //  		}

			$template = "pepsibrands_about_us_template";
			if ($this->is_wrong_language_url()){
				return $this->redirect_correct_lang_route($brand, $langcode .".pepsibrands.recall");
			}

			$banner = getConsumerBanner($brand);
			$variables = get_block_content($brand, 'alert');


			if (empty(getConsumerBanner($brand))){
				// Only allow access to this page when there's an active Alert banner
				// return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
			}

		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );

	    }

	    public function comingsoon(Request $request) {
	    	$langcode = get_current_langcode();
	    	$data = [];
	    	$template = "pepsibrands_quaker_thankyou";
			if ($this->is_wrong_language_url()){
				return $this->redirect_correct_lang_route([],$langcode .".pepsibrands.quaker.comingsoon");
			}

	    	return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );
	    }

		public function receiptsEntries(Request $request){
	    	$template = "pepsibrands_quaker_entries";
	    	$data = [];
	    	$status = '';
	    	$data['title'] = "Quaker receipts entries";

	    	$data['all_status'] = [];
	    	$data['status'] = '';

	    	if ($request->getMethod() == 'POST'){
	            $status = trim($request->request->get('status'));
	            $start_date = $request->request->get('start_date');
	            $end_date = $request->request->get('end_date');

	            $data['status'] = $status;
	            $data['start_date'] = $start_date;
	            $data['end_date'] = $end_date;
	    		$data['entries'] = $this->quakerReceiptsEntries($status, $start_date, $end_date);
	    		// debug_var($data,3);
	        } else {
	    		$data['entries'] = $this->quakerReceiptsEntries($status = '');
	        }



	    	return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );

	    }

	    public function quakerReceiptsEntries($status = '', $start_date = '', $end_date = ''){
	    	$with_status = '';
	    	if (empty($status))
	    		$status = \Drupal::request()->query->get('status');

	    	if ($status == 'pending' || $status == 'approved' || $status == 'rejected'){
	    		$with_status = " AND status = '$status' ";
	    	}

	    	$starting_at = "";
	        $ending_at = "";
	        if (!empty($start_date)){
	            $starting_at = " AND t1.enterdate >= '$start_date' ";
	        }
	        if (!empty($end_date)){
	            $ending_at = " AND t1.enterdate <= '$end_date' ";
	        }


	    	$sql = " SELECT * from pepsi_receipts_entries  as t1
	    				WHERE 1
	    			 $with_status
	    			 $starting_at
	    			 $ending_at
	    			order by enterdate desc, status desc limit 200";

	    	$query = \Drupal::database()->query($sql);
	    	$result = $query->fetchAll();
	    	$entries = [];
	    	if (!empty($result)){
	    		foreach($result as $row){
	    			$entries[] = [
	    				'email' => $row->email,
	    				'firstname' => $row->firstname,
	    				'lastname' => $row->lastname,
	    				'filename' => $row->filename,
	    				'status' => $row->status,
	    				'enterdate' => $row->enterdate,
	    			];
	    		}
	    	}

	    	return $entries;
	    }

	    public function getRealjoyEntries(){
	    	$sql = " SELECT * from pepsi_landingpage_entries order by enterdate desc limit 100";

	    	$query = \Drupal::database()->query($sql);
	    	$result = $query->fetchAll();
	    	$entries = [];
	    	if (!empty($result)){
	    		foreach($result as $row){
	    			$entries[] = [
	    				'email' => $row->email,
	    				'firstname' => $row->firstname,
	    				'lastname' => $row->lastname,
	    				'province' => $row->province,
	    				'filename' => $row->filename,
	    				'story' => $row->story,
	    				'enterdate' => $row->enterdate,
	    			];
	    		}
	    	}

	    	return $entries;
	    }

	    public function realjoyEntries(Request $request){

	    	$template = "pepsibrands_realjoy_entries";
	    	$data = [];

	    	$data['entries'] = $this->getRealjoyEntries();

	    	$data['csv_link'] = Url::fromRoute('pepsibrands.realjoy.csvexport')->toString();


	    	return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );
	    }

	    public function realjoyCsvExport(Request $request){
	    	$stories = $this->getRealjoyEntries();

	    	$filename = "modules/custom/pepsibrands/csv/realjoy_stories.csv";
	        $headers = ['email','firstname','lastname', 'province', 'story', 'date'];
	        $content = '';
	        $delimiter = ',';
	        $content .= implode($delimiter,$headers)."\n";
	        foreach($stories as $story) {
	            $winner = [ 
	                $story['email'],
	                $story['firstname'],
	                $story['lastname'],
	                $story['province'],
	                $story['story'],
	                $story['enterdate'],
	            ];

	            $content .= implode($delimiter,$winner)."\n";
	        }
	        file_put_contents($filename, $content);
	        $host = \Drupal::request()->getSchemeAndHttpHost();

	        $file_url = $host . "/$filename";
			header('Content-Type: application/octet-stream');
			header("Content-Transfer-Encoding: Binary"); 
			header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
			readfile($file_url); 
			// unlink($filename);
	        die("");
	    }


	    public function realjoyTerms(Request $request) {
	    	$brand = 'lays';
	    	if (!$this->is_brand_active($brand) ){
				return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
			}
	    	$langcode = get_current_langcode();
			$route_name = \Drupal::routeMatch()->getRouteName();
	    	if ($brand !=  $this->get_brand_alias($brand, $langcode)){
				$params = [];
				return new RedirectResponse(\Drupal\Core\Url::fromRoute($route_name, $params)->toString()); 
			}
	    	$data = [];
	    	$template = "pepsibrands_about_us_template";
			if ($this->is_wrong_language_url()){
				return $this->redirect_correct_lang_route('', $langcode .".pepsibrands.realjoy.terms");
			}

		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );

	    }



	    public function realjoy(Request $request) {
	    	$brand = 'lays'; 
	    	if (!$this->is_brand_active($brand) ){
				return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
			}
	    	$langcode = get_current_langcode();
	    	$data = [];
	    	$post = [];

	    	$data['language'] = get_current_langcode(false);
	    	$data['brand_basepath'] = '/en-ca/brands/' . $brand; 
	    	if ($langcode == 'fr-ca'){
	    		$data['brand_basepath'] = '/fr-ca/brands/' . $brand; 
	    	}
	    	// $template = "pepsibrands_about_us_template";
	    	$template = "pepsibrands_realjoy_landing";
			if ($this->is_wrong_language_url()){
				return $this->redirect_correct_lang_route([],$langcode .".pepsibrands.realjoy.landing");
			}

			$user = getCurrentUser();
			$data['provinces_options'] = $this->CreateProvinceCodeDropdown();
			$data['stories'] =  get_block_content_fields('lays', 'story', $nb_items= 4);
			
			$data["user_firstname"] = '';
			if ($user){
  				$data["user_firstname"] = $user->field_firstname->value;
			}

			$data['videos'] = get_recipes_with_videos('realjoy', 10);

		    $data['tasty_logopath'] =  '/themes/brandstheme/src/images/logo-' . $data['language'] . '.png';
		    $data['logofooterpath'] =  '/themes/brandstheme/src/images/logofooter-' . $data['language'] . '.png';

		    $data['shoplink'] = '';
		    $data['shoplink'] = ($langcode == 'en-us' || $langcode == 'es-us') ? \Drupal::config('ssoconfig_us.adminsettings')->get('snack_url') : \Drupal::config('ssoconfig.adminsettings')->get('snack_url');
		    // $data['featured_products'] = get_featured_products('lays', null); 
		    $data['featured_products'] = get_featured_content('lays', 'product', 'realjoy');

		    $data['coupon_block'] = get_block_content('lays', 'coupon');
		    $data['thankyou'] = 0;

			if ($request->getMethod() == 'POST'){
				$post['email'] = $request->get('email');
				$post['firstname'] = $request->get('firstname');
				$post['lastname'] = $request->get('lastname');
				$post['province'] = $request->get('province');
				$post['story'] = $request->get('story');
				$optin = $request->get('optin');
				$post['optin'] = $optin? 1: 0;
				$post['language'] = get_current_langcode(false);
				if (!empty($request->files->get('uploadImg'))){
	                $files = $request->files->get('uploadImg');
	                $post['uploadImg'] = $files->getClientOriginalName();
	                $post['files'] = $files;
	                $receipt_path = $files->getPathname();
	                $post['imagePath'] = $receipt_path;
	                $post['mimeType'] = $files->getClientMimeType();
	                $unique_filename = uniqid("story_") . $post['uploadImg'];
	                $cfile = ''; 
	                if (function_exists('curl_file_create')) {
	                	// @TODO: save this file somewhere 
	                	// or email it to Nicole
			            $cfile = curl_file_create($post['imagePath'], $post['mimeType'], $unique_filename );
			        }
			        $post['filename'] = $unique_filename;
	            }

				$form_validator = new PepsiFormValidation();
				$data['error'] = $form_validator->laysStoryFormValidate($post);
				if (empty($data['error'])){
		    		$data['thankyou'] = 1;
					$result =  $this->saveStoryEntry($post);
					$recipients  = ['rotsy@bamstrategy.com', 'negin@bamstrategy.com', 'cathryn@bamstrategy.com'];
					// $this->sendEmailNotification($recipients, $post);
				}
				$data['firstname'] = isset($post['firstname'])? $post['firstname'] : '';
				$data['lastname'] = isset($post['lastname'])? $post['lastname'] : '';
				$data['email'] = isset($post['email'])? $post['email'] : '';
				$data['province'] = isset($post['province'])? $post['province'] : '';
				$data['optin'] = isset($post['optin'])? $post['optin'] : 0;

				// $session = \Drupal::request()->getSession();
				// $session->set('quaker_receipt', $data);
				if (empty($data['error'])) {
					// return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode .".pepsibrands.quaker.thankyou")->toString());
				}
			}

		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );

	    }

	    public function sendEmailNotification($recipients, $post){
	    	$content = "<div>";
	    	$content .= $post['email'] . " submitted the story:  ";
	    	$content .= " <p> \" " . $post['story'] . " \" </p>";

	    	$content .= "</div>";
	    	try{
				pepsicontest_send_email($recipients, "New Real Joy story submitted", $content);
	    	} catch(\Exception $e){
	    		log_var($e, "could not send email ", "realjoy");
	    	}

	    }

	    public function saveStoryEntry($post){

	    	$values = [
	            'filename' => $post['filename'],
	            'email' => $post['email'],
	            'firstname' => $post['firstname'],
	            'lastname' => $post['lastname'],
	            'province' => $post['province'],
	            'story' => $post['story'],
	            'language' => $post['language'],
	            'enterdate' => date('Y-m-d'),
	            'regdate' => date('Y-m-d H:i:s'),
	        ];
	        $saved_image = $this->saveUploadedImage($post['files']);
	        // @TODO: email the image
	        if ($saved_image){
	        	send_realjoy_email($post['email'], $post['story'], $saved_image);
	        }
        	$result = \Drupal::database()->insert('pepsi_landingpage_entries')->fields($values)->execute();

	    	return 1;
	    }


	    public function landing(Request $request) {
	    	$brand = 'quaker'; 
	    	if (!$this->is_brand_active($brand) ){
				return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
			}

			$langprefix = get_current_langcode();

	    	return new RedirectResponse(\Drupal\Core\Url::fromRoute($langprefix .".pepsibrands.quaker.expiry")->toString());

	    	
			$redirect_comingsoon = 0;
			if ($redirect_comingsoon){
	            return new RedirectResponse(\Drupal\Core\Url::fromRoute($langprefix . ".pepsibrands.quaker.comingsoon")->toString());
	        }


	    	$langcode = get_current_langcode();
	    	$data = [];
	    	$post = [];

	    	$data['language'] = get_current_langcode(false);
	    	$data['brand_basepath'] = '/en-ca/brands/' . $brand; 
	    	if ($langcode == 'fr-ca'){
	    		$data['brand_basepath'] = '/fr-ca/brands/' . $brand; 
	    	}
	    	// $template = "pepsibrands_about_us_template";
	    	$template = "pepsibrands_quaker_landing";
			if ($this->is_wrong_language_url()){
				return $this->redirect_correct_lang_route([],$langcode .".pepsibrands.quaker.landing");
			}

			$user = getCurrentUser();
			$data["user_firstname"] = '';
			if ($user){
  				$data["user_firstname"] = $user->field_firstname->value;
			}

		    $data['tasty_logopath'] =  '/themes/brandstheme/src/images/logo-' . $data['language'] . '.png';
		    $data['logofooterpath'] =  '/themes/brandstheme/src/images/logofooter-' . $data['language'] . '.png';

		    $data['shoplink'] = '';
		    $data['shoplink'] = ($langcode == 'en-us' || $langcode == 'es-us')?\Drupal::config('ssoconfig_us.adminsettings')->get('snack_url'):\Drupal::config('ssoconfig.adminsettings')->get('snack_url');
		    $data['submitted'] = 0;
			if ($request->getMethod() == 'POST'){
		    	$data['submitted'] = 1;
				$post['email'] = $request->get('email');
				$post['firstname'] = $request->get('firstname');
				$post['lastname'] = $request->get('lastname');
				$post['postalcode'] = $request->get('postalcode');
				$post['postalcode'] = strtoupper($post['postalcode']);
				if (!empty($request->files->get('uploadImg'))){
	                $files = $request->files->get('uploadImg');
	                $post['uploadImg'] = $files->getClientOriginalName();
	                $post['files'] = $files;
	                $receipt_path = $files->getPathname();
	                $post['imagePath'] = $receipt_path;
	                $post['mimeType'] = $files->getClientMimeType();
	            }
				$obj_rm = new ReceiptManager();
				$data['error'] = $obj_rm->validateReceiptForm($post);
				
				if (empty($data['error'])){
					$result =  $obj_rm->processReceipt($post);
					if (empty($result)){
						// $data['error']['uploadImg'] = t('Unknown error occured');
					} else {
						if ($result['status'] == 0){
							$data['error']['uploadImg'] = $result['error'];
						}
					}
				}
				$data['firstname'] = isset($post['firstname'])? $post['firstname'] : '';
				$data['lastname'] = isset($post['lastname'])? $post['lastname'] : '';
				$data['email'] = isset($post['email'])? $post['email'] : '';
				$data['postalcode'] = isset($post['postalcode'])? $post['postalcode'] : '';

				$session = \Drupal::request()->getSession();
				$session->set('quaker_receipt', $data);
				if (empty($data['error'])) {
					return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode .".pepsibrands.quaker.thankyou")->toString());
				}
			}

		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );
	    }

	    public function saveUploadedImage($files, $email = ''){
	    	if (empty($files))
	    		return 0;
	        $image_name = $files->getClientOriginalName();
	        $ext = "jpg";
	        if (substr_compare(strtolower($image_name), $ext, strlen($image_name)-strlen($ext), strlen($ext)) !== 0){
	            $ext = "png";
	        }
	        // $saved_img_name = base64_encode($email) . "." . $ext;
	        $saved_img_name = uniqid("story_") . "." . $ext;

	        $slash = DIRECTORY_SEPARATOR;

	        $target_file = rtrim($_SERVER['DOCUMENT_ROOT'], $slash ) . $slash . "sites" . $slash .
	         "default" . $slash . "files" . $slash . $saved_img_name;

	         //@TODO: Add more validation rules here:
	         // check if the user had already uploaded an image before
	         // Also make sure the userID is included in the filename to make sure it overwrites
	         // the previous image associated to the user

	        if(move_uploaded_file($files->getPathname(), $target_file) ){
	            return $target_file;
	        } else {
	            return 0;
	        }
	    }

	    public function expiry(Request $request){
	    	$langcode = get_current_langcode();
	    	$data = [];
	    	$template = "pepsibrands_quaker_thankyou";
			if ($this->is_wrong_language_url()){
				return $this->redirect_correct_lang_route([],$langcode .".pepsibrands.quaker.expiry");
			}

	    	return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );
	    }
	    
	    public function thankyou(Request $request){

	    	$langcode = get_current_langcode();
	    	$data = [];
	    	$template = "pepsibrands_quaker_thankyou";
			if ($this->is_wrong_language_url()){
				return $this->redirect_correct_lang_route([],$langcode .".pepsibrands.quaker.thankyou");
			}

	    	return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );

	    }

	    public function cheetosRules(Request $request) {
	    	$brand = 'cheetos'; 
	    	if (!$this->is_brand_active($brand) ){
				return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
			}
	    	$langcode = get_current_langcode();
	    	$data = [];
	    	$template = "pepsibrands_about_us_template";
			if ($this->is_wrong_language_url()){
				return $this->redirect_correct_lang_route([],$langcode .".pepsibrands.cheetos.rules");
			}

		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );

	    }

	    public function oatFlour(Request $request) {
	    	$brand = 'quaker'; 
	    	if (!$this->is_brand_active($brand) ){
				return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
			}
	    	$langcode = get_current_langcode();
	    	$data = [];
	    	$template = "pepsibrands_about_us_template";
			if ($this->is_wrong_language_url()){
				return $this->redirect_correct_lang_route([],$langcode .".pepsibrands.oat-flour");
			}

		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );
	    }

	    public function powerOfOats(Request $request) {
	    	$brand = 'quaker'; 
	    	if (!$this->is_brand_active($brand) ){
				return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
			}
	    	$langcode = get_current_langcode();
	    	$data = [];
	    	$template = "pepsibrands_about_us_template";
			if ($this->is_wrong_language_url()){
				// There is no FR translation for this page
				// We simply redirect to the homepage.
				return $this->redirect_correct_lang_route($brand, $langcode .".pepsibrands.homepage");
			}

		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );
	    }
	    public function contestRules(Request $request) {
	    	$brand = 'crispyminis';
	    	$langcode = get_current_langcode();
	    	$data = [];
	    	$template = "pepsibrands_about_us_template";
			if ($this->is_wrong_language_url()){
				return $this->redirect_correct_lang_route('', $langcode .".crispyminis.contest.rules");
			}

		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );
	    }


	    public function aboutUs(Request $request, $brand) {
	    	if (!$this->is_brand_active($brand) ){
				return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
			}
	    	$langcode = get_current_langcode();
	    	if ($brand !=  $this->get_brand_alias($brand, $langcode)){
				$route_name = \Drupal::routeMatch()->getRouteName();
				$params = ['brand' => $this->get_brand_alias($brand, $langcode)];
				return new RedirectResponse(\Drupal\Core\Url::fromRoute($route_name, $params)->toString()); 
			}
	    	$data = [];
	    	$template = "pepsibrands_about_us_template";
			if ($this->is_wrong_language_url()){
				return $this->redirect_correct_lang_route($brand, $langcode .".pepsibrands.about-us");
			}

		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );

	    }



	    public function Signature(Request $request, $brand){
	    	if (!$this->is_brand_active($brand) ){
				return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
			}
	    	$langcode = get_current_langcode();
	    	$data = [];
	    	switch ($brand) {
	    		case 'missvickies':
	    			$template = "pepsibrands_about_us_template";
	    			if ($this->is_wrong_language_url()){
	    				return $this->redirect_correct_lang_route($brand, $langcode .".pepsibrands.signature");
	    			}
	    			break;
	    		
	    		default:
					return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
	    			break;
	    	}
		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );
	    }

	    public function ourStory(Request $request, $brand){
	    	if (!$this->is_brand_active($brand) ){
				return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
			}
	    	$langcode = get_current_langcode();
	    	$data = [];
	    	switch ($brand) {
	    		case 'stacys':
	    			$template = "pepsibrands_about_us_template";
	    			if ($this->is_wrong_language_url()){
	    				return $this->redirect_correct_lang_route($brand, $langcode .".pepsibrands.story");
	    			}
	    			break;
	    		
	    		default:
					return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
	    			break;
	    	}
		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );
	    }


	    public function Flavorul(Request $request, $brand){
	    	if (!$this->is_brand_active($brand) ){
				return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
			}
	    	$langcode = get_current_langcode();
	    	$data = [];
	    	switch ($brand) {
	    		case 'crispyminis':
	    			$template = "pepsibrands_about_us_template";
	    			if ($this->is_wrong_language_url()){
	    				return $this->redirect_correct_lang_route($brand, $langcode .".pepsibrands.flavorful");
	    			}
	    			break;
	    		
	    		default:
					return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
	    			break;
	    	}
		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );
	    }

	    public function recipesWithVideos(Request $request, $brand) {
	    	if (!$this->is_brand_active($brand) ){
				return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
			}
	    	$data = [];
	    	$template = "pepsibrands_videos_template";
	    	$langcode = get_current_langcode();

	    	// @TODO: remove hardcoded tostitos
	    	if ($this->is_wrong_language_url()){
				return $this->redirect_correct_lang_route($brand, $langcode .".pepsibrands.brand.videos");
			}
		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );
	    }


	    public function receiptAjaxAction(Request $request) {
	    	$post['firstname'] = trim($request->request->get('firstname'));
        	$post['lastname'] = trim($request->request->get('lastname'));
        	$post['email'] = trim($request->request->get('email'));
        	$post['postalcode'] = trim($request->request->get('postalcode'));

        	$status = 1;
        	$return = array('status' => $status);

            $json = json_encode($return);
    
        	return new JsonResponse($return);
	    }



	    public function ourBrands(Request $request) {
	    	$data = [];
			$langcode = get_current_langcode();
	    	if ($this->is_wrong_language_url()){
				return $this->redirect_correct_lang_route($brand = '', $langcode .".pepsibrands.our-brands");
			}
	    	$template = "pepsibrands_our_brands_template";
	    	$data['carousel'] = get_brands_landingpage_slides();
	    	// $data['coupon_block'] = get_block_content('Tastyrewards', 'coupon');
	    	$data['coupon_blocks'] = get_couponblock_fields('Tastyrewards');
	    	$data['banner_block'] = get_block_content('Tastyrewards', 'banner');
		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );

	    }

	    public function loadRecipes(Request $request, $brand, $offset) {
	    	$data = [];
	    	$data['status'] = 'fail';
	    	$filter = $request->get('filter');
	    	$lang = $request->get('lang');
	    	$recipes = [];
	    	if (empty($filter)){
	    		$recipes = BrandsContentFilters::instance()->fetch_recipes($brand, $number = 12, $offset, $lang);
	    	} elseif($filter == 'ingredient'){
	    		$tid = $request->get('tid');
	    		$recipes = BrandsContentFilters::instance()->fetch_recipes_by_ingredient($brand, $tid, $lang, $offset);
	    	} elseif($filter == 'occasion'){
	    		$tid = $request->get('tid');
	    		$recipes = BrandsContentFilters::instance()->fetch_recipes_by_occasion($tid, $lang, $offset);
	    	} elseif($filter == 'time'){
	    		$prep_time = $request->get('prep_time');
	    		$recipes = BrandsContentFilters::instance()->fetch_recipes_by_time($brand, $prep_time, $lang, $offset);
	    	} elseif($filter == 'video'){
	    		$recipes = BrandsContentFilters::instance()->fetch_recipes_with_videos($brand, $lang, $offset);
	    	} elseif($filter == 'category'){
	    		$category = $request->get('recipe_type');
	    		$recipes = BrandsContentFilters::instance()->fetch_recipes_by_category($brand, $category, $lang, $offset);
	    	}
			elseif($filter == 'chef'){
	    		$recipes = BrandsContentFilters::instance()->fetch_recipes_with_chef($brand, $lang, $offset);
	    	}
			elseif($filter == 'search'){
	    		$search_term = $request->get('search_term');
	    		$recipes = BrandsContentFilters::instance()->search_recipes($brand, $number = 12, $offset, $search_term, $lang);
	    	}


	    	if (!empty($recipes)){
	    		$data['recipes'] = $recipes;
    			$data['count'] = count($recipes);
	    	} else {
	    		$data['recipes'] = '';
    			$data['count'] = 0;
	    	}
    		$data['status'] = 'success';
	    	echo json_encode($data);
	    	die;
	    }



	    public function brandsProductGroup(Request $request, $brand, $group) {
	    	$langcode = get_current_langcode();
	    	if (!$this->is_brand_active($brand) ){
				return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
			}

	    	if (!$this->is_valid_product_group($brand, $group) ){
				return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
	    	}

	    	$data = [];
	    	$brand_params = [
	    					'brand' => $brand,
	    					'group' => $group,
	    				];

	    	if ($this->is_wrong_language_url()){
				return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode .".pepsibrands.products.group", $brand_params)->toString()); 
			}

			$template = "pepsibrands_tostitos_product_categories";

			return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );	
	    }



	    public function is_valid_product_group($brand, $group){
	    	$langcode = get_current_langcode(false);
	    	$query = \Drupal::entityQuery('taxonomy_term');
	    	$query->condition('vid', 'product_group');
			$query->condition('status', 1);
			$query->condition('field_brand', $brand);
	    	// $wildcard = "";
			$query->condition('field_basename', $group );
			// $query->condition('langcode', $langcode);
			$result = $query->execute();
			
			if (strpos($group, "crispy-minis") !== false)
				return 0;

			if (!empty($result))
				return 1;

			return 0;
	    }


	    public function brandsProductCategories(Request $request, $brand, $category) {
	    	if (!$this->is_brand_active($brand) ){
				return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
			}
	    	$data = [];
	    	$langcode = get_current_langcode();

	    	if ($brand !=  $this->get_brand_alias($brand, $langcode)){
				$route_name = \Drupal::routeMatch()->getRouteName();
				$params = [
					'category' => $category,
					'brand' => $this->get_brand_alias($brand, $langcode)
				];
				return new RedirectResponse(\Drupal\Core\Url::fromRoute($route_name, $params)->toString()); 
			}
	    	$brand_params = [
	    					'brand' => $brand,
	    					'category' => $category,
	    				];

			$template = "pepsibrands_tostitos_product_categories";
	    	switch ($brand) {
	    		case 'stacys':
	    		case 'stacyssnacks':
	    		case 'smartfood':
	    		case 'smartfoodpopcorn':
	    		case 'maissoufflesmartfood':
		    			$template = "pepsibrands_tostitos_product_categories";
		    			if ($this->is_wrong_language_url()){
		    				return $this->redirect_correct_lang_route($brand, $langcode .".pepsibrands.products.categories", $brand_params);
		    			}
	    			break;
	    		case 'tostitos':
	    		// case 'doritos':
	    		case 'crispyminis':
	    		case 'quaker':
	    		case 'missvickies':
	    			$categories = BrandsContentFilters::instance()->get_product_category_basenames($brand);
	    			// if ( $category == 'tostitos-chips'
	    			// 	|| $category == 'salsa-dips'
	    			// 	|| $category == 'simply-tostitos'
	    			// ){
	    			// en-ca.pepsibrands.products.categories
	    			if ($this->is_wrong_language_url()){
	    				return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode .".pepsibrands.products.categories", $brand_params)->toString()); 
	    			}
	    			if (in_array($category, $categories)){
		    			$template = "pepsibrands_tostitos_product_categories";

	    			}

	    			break;

	    		case 'lays':
	    			$categories = BrandsContentFilters::instance()->get_product_category_basenames($brand);
	    			if (in_array($category, $categories)){
		    			$template = "pepsibrands_tostitos_product_categories";
	    			}

	    			if ($this->is_wrong_language_url()){
	    				return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode .".pepsibrands.products.categories", $brand_params)->toString()); 
	    			}

	    			break;
	    		
	    		default:
	    			break;
	    	}

		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );	
	    }


	    public function brandsRecipes(Request $request, $brand) {
	    	$data = [];
	    	$template = "pepsibrands_recipes_tostitos_template";
	    	$langcode = get_current_langcode();
	    	if ($brand !=  $this->get_brand_alias($brand, $langcode)){
				$route_name = \Drupal::routeMatch()->getRouteName();
				$params = ['brand' => $this->get_brand_alias($brand, $langcode)];
				return new RedirectResponse(\Drupal\Core\Url::fromRoute($route_name, $params)->toString()); 
			}
	    	if ($this->is_wrong_language_url()){
				return $this->redirect_correct_lang_route($brand, $langcode .".pepsibrands.recipes");
			}
		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );

	    }

	    public function brandsOccasions(Request $request, $brand) {
	    	$data = [];
	    	$template = "pepsibrands_occasions_template";
	    	$langcode = get_current_langcode();
	    	if ($brand !=  $this->get_brand_alias($brand, $langcode)){
				$route_name = \Drupal::routeMatch()->getRouteName();
				$params = ['brand' => $this->get_brand_alias($brand, $langcode)];
				return new RedirectResponse(\Drupal\Core\Url::fromRoute($route_name, $params)->toString()); 
			}
	    	if ($this->is_wrong_language_url()){
    			$term_type = 'occasions';
	    		if (in_array($brand, ['stacyssnacks', 'stacys']))
	    			$term_type = 'collections';
				return $this->redirect_correct_lang_route($brand, $langcode .".pepsibrands.$term_type");
			}
		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );

	    }

	    public function hockeyLanding(Request $request){
	    	$template = "pepsibrands_about_us_template";
	    	$data = [];
	    	$langcode = get_current_langcode();
	    	if ($this->is_wrong_language_url()){
				return $this->redirect_correct_lang_route($brand, $langcode .".pepsibrands.quaker.hockey.landing");
			}
		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );



	    }

	    public function dropTheHint(Request $request, $brand) {
	    	$data = [];
	    	$template = "pepsibrands_dropthehint_template";
	    	$langcode = get_current_langcode();
	    	if ($this->is_wrong_language_url()){
				return $this->redirect_correct_lang_route($brand, $langcode .".pepsibrands.brand.dropthehint");
			}
		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );

	    }

	    public function emailStacysTest(Request $request){
	    	if ($request->query->has('address') ){
	            $email = $request->query->get('address');
	        } else {
	            die("add the email as ?address=myemail@mail.com");
	        }
	        
	        $obj = new EmailManager();

	        $langcode = get_current_langcode(false);

			$obj->sendStacysEmail($email, "FIRSTNAME", $langcode);
	        
	        
	        die(" sent email to $email ");

	    }

	    public function emailTest(Request $request){
	        if ($request->query->has('address') ){
	            $email = $request->query->get('address');
	        } else {
	            die("add the email as ?address=myemail@mail.com");
	        }
	        $verified = false;
	        if ($request->query->has('verified') ){
	        	$verified = true;
	        }
	        
	        // $verified = $request->query->get('verified');
	        $language = get_current_langcode(false);
	        send_email_receipt($email, $verified, $language, 'test_admin3889');
	        die(" sent email to $email ");
	    }

	    public function loadSocialFeeds(Request $request, $brand) {
	    	// get content type brand social
			$query = \Drupal::entityQuery('node')
			->condition('status', 1) //published or not
			->condition('type', 'brand_social_feed') //content type
			->condition('field_brand_name', $brand)
			->range(0, 1);
			//->pager(1); //specify results to return
			$nids = $query->execute();

			if ($nids && count($nids) > 0){
				$node = null;
				foreach($nids as $nid){
					$node = \Drupal\node\Entity\Node::load($nid);
					break;
				}
				if ($node){
					$feedserie = $node->get('field_feed_content')->getValue();
					$feeds = unserialize($feedserie[0]['value']);
					$i = 0;
					$socialfeed[] = [
						'img'    => isset($feeds[$i]['img'])?$feeds[$i]['img']:'/themes/brandstheme/src/images/lays/lays-cheddar-roll.jpg',
						'href'   => isset($feeds[$i]['url'])?$feeds[$i]['url']:'#',
						'type'   => isset($feeds[$i]['type'])?$feeds[$i]['type']:'',
						];
					$i = 1;
					$socialfeed[] = [
						'img'    => isset($feeds[$i]['img'])?$feeds[$i]['img']:'/themes/brandstheme/src/images/lays/lays-kettle-cooked.jpg',
						'href'   => isset($feeds[$i]['url'])?$feeds[$i]['url']:'#',
						'type'   => isset($feeds[$i]['type'])?$feeds[$i]['type']:'',
						];
					$i = 2;
					$socialfeed[] = [
						'img'    => isset($feeds[$i]['img'])?$feeds[$i]['img']:'/themes/brandstheme/src/images/lays/lays-cheddar.jpg',
						'href'   => isset($feeds[$i]['url'])?$feeds[$i]['url']:'#',
						'type'   => isset($feeds[$i]['type'])?$feeds[$i]['type']:'',
						];
					$i = 3;
					$socialfeed[] = [
						'img'    => isset($feeds[$i]['img'])?$feeds[$i]['img']:'/themes/brandstheme/src/images/lays/lays-sprinkle.jpg',
						'href'   => isset($feeds[$i]['url'])?$feeds[$i]['url']:'#',
						'type'   => isset($feeds[$i]['type'])?$feeds[$i]['type']:'',
						];
					$i = 4;
					$socialfeed[] = [
						'img'    => isset($feeds[$i]['img'])?$feeds[$i]['img']:'/themes/brandstheme/src/images/lays/lays-chips.jpg',
						'href'   => isset($feeds[$i]['url'])?$feeds[$i]['url']:'#',
						'type'   => isset($feeds[$i]['type'])?$feeds[$i]['type']:'',
						];
					$i = 5;
					$socialfeed[] = [
						'img'    => isset($feeds[$i]['img'])?$feeds[$i]['img']:'/themes/brandstheme/src/images/lays/lays-cheddar-new.jpg',
						'href'   => isset($feeds[$i]['url'])?$feeds[$i]['url']:'#',
						'type'   => isset($feeds[$i]['type'])?$feeds[$i]['type']:'',
						];
					$i = 6;
					$socialfeed[] = [
						'img'    => isset($feeds[$i]['img'])?$feeds[$i]['img']:'/themes/brandstheme/src/images/lays/lays-gift-mom.jpg',
						'href'   => isset($feeds[$i]['url'])?$feeds[$i]['url']:'#',
						'type'   => isset($feeds[$i]['type'])?$feeds[$i]['type']:'',
						];

					$html = "";
					$html = $html . '<div class="lays-social-box size22"><a href="'. $socialfeed[0]['href'] .'" target="_blank" > <img src="'. $socialfeed[0]['img'] .'" alt="lays cheddar"> </a></div>';

					$html = $html . '<div class="lays-social-box size11"><a href="'. $socialfeed[1]['href'] .'" target="_blank" > <img src="'. $socialfeed[1]['img'] .'" alt="lays kettle cooked.jpg"> </a></div>';
  
					$html = $html . '<div class="lays-social-box size11"><a href="'. $socialfeed[2]['href'] .'" target="_blank" > <img src="'. $socialfeed[2]['img'] .'" alt="lays cheddar"> </a></div>';
  
					$html = $html . '<div class="lays-social-box size21"><a href="'. $socialfeed[3]['href'] .'" target="_blank" > <img src="'. $socialfeed[3]['img'] .'" alt="lays cheddar"> </a></div>';
  
					$html = $html . '<div class="lays-social-box size21"><a href="'. $socialfeed[4]['href'] .'" target="_blank" > <img src="'. $socialfeed[4]['img'] .'" alt="lays cheddar"> </a></div>';
  
					$html = $html . '<div class="lays-social-box size11"><a href="'. $socialfeed[5]['href'] .'" target="_blank" > <img src="'. $socialfeed[5]['img'] .'" alt="lays cheddar"> </a></div>';
  
					$html = $html . '<div class="lays-social-box size11"><a href="'. $socialfeed[6]['href'] .'" target="_blank" > <img src="'. $socialfeed[6]['img'] .'" alt="lays cheddar"> </a></div>';

					echo $html;
					exit;
				}

			}
			echo "";
			exit;
	    }	

	    public function CreateProvinceCodeDropdown($selected_province = null) {
	        $provinces = ["Alberta", "British Columbia", "Manitoba", "New Brunswick", "Newfoundland and Labrador", "Nova Scotia", "Ontario", "Prince Edward Island", "Quebec", "Saskatchewan", "Northwest Territories", "Nunavut", "Yukon"];

	        $province_codes = [
	            "Alberta" => 'AB',
	            "British Columbia" => 'BC',
	            "Manitoba" => 'MB',
	            "New Brunswick" => 'NB',
	            "Newfoundland and Labrador" => 'NL',
	            "Nova Scotia" => 'NS',
	            "Ontario" => 'ON',
	            "Prince Edward Island" => 'PE',
	            "Quebec" => 'QC',
	            "Saskatchewan" => 'SK',
	            "Northwest Territories" => 'NT',
	            "Nunavut" => 'NU',
	            "Yukon" => 'YT'
	        ];

	        $options = [];
	        foreach ($provinces as $key => $province) {
	            $options[$province_codes[$province]] = $province;
	        }

	        $province_option = '<option value="">' . t('Province') . '</option>';
	        foreach ($options as $key => $province) {

	            $tmp = ($selected_province == $key) ? 'selected' : '';
	            $province_option = $province_option . '<option value="' . $key . '" ' . $tmp . ' >' . t($province) . '</option>';
	        }



	        return $province_option;
	    }

	public function ajaxFindFlavour(Request $request){
		$sweet_salty = trim($request->request->get('sweet_salty'));
		$tangy_spicy = trim($request->request->get('tangy_spicy'));
		$filter_id = !empty($request->request->get('id')) ? trim($request->request->get('id')) : 'sweet_salty';
		

		$post['sweet_salty'] = intval($sweet_salty) == 1 ? 'sweet' : 'salty';
		$post['tangy_spicy'] = intval($tangy_spicy) == 1 ? 'tangy' : 'spicy';

		$product = $this->matchFlavour($post, $filter_id);

		$status = 1;
		$return = [
			'status' => $status,
			'product' => $product,
		];

		$json = json_encode($return);

		return new JsonResponse($return);

	}

	public function matchFlavour($post, $filter_id){
		

		$query = \Drupal::entityQuery('node')
			->condition('status', 1) //published or not
			->condition('type', 'product') 
			->condition('field_brand', 'missvickies');

		$has_flavour = $query->orConditionGroup()
					->condition('field_flavour' , $post['sweet_salty'])
					->condition('field_flavour' , $post['tangy_spicy']);
		$query->condition($has_flavour);
					
		// $query->condition('field_flavour', $post[$filter_id]);


		// $query->condition('field_flavour' , $post['tangy_spicy'], 'IN');
		// $query->condition('field_flavour' , $post['sweet_salty'], 'IN');
		$query->sort('field_weight', 'ASC');

		$query->range(0, 3);
		//->pager(1); //specify results to return
		$result = $query->execute();
		$entity = [];
		if (!empty($result)){
			$nids = array_values($result);
			foreach ($nids as $nid) {
				$node = \Drupal\node\Entity\Node::load($nid);
				$flavour_arr = $node->field_flavour->getValue();
				$flavours = [];
				foreach ($flavour_arr as $key => $flavour) {
					$flavours[] = $flavour['value'];
				}
				if (in_array($post['sweet_salty'], $flavours) && in_array($post['tangy_spicy'], $flavours) ){
					$entity =  BrandsContentFilters::instance()->fetchProductFields($nid);
					break;
				}

				
			}
			// $nid = $nids[0];
			// $entity =  BrandsContentFilters::instance()->fetchProductFields($nid);

		}

		return $entity;

	}

	public function missvickiesLanding(Request $request){
		$brand = 'missvickies';
		if (!$this->is_brand_active($brand) ){
			return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
		}
    	$langcode = get_current_langcode();

		if ($this->is_wrong_language_url()){
			return $this->redirect_correct_lang_route([], $langcode .".pepsibrands.missvickies.flavour");
		}

    	$data = [];
    	$template = "pepsibrands_about_us_template";
    	
    	
	    return array(
            '#theme' => $template,
            '#data'  => $data,
        );

	}

	public function Sparksflyforsummer(Request $request, $brand){
	    	if (!$this->is_brand_active($brand) ){
				return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
			}
	    	$langcode = get_current_langcode();
	    	$data = [];
	    	switch ($brand) {
	    		case 'missvickies':
	    			$template = "pepsibrands_about_us_template";
	    			if ($this->is_wrong_language_url()){
	    				return $this->redirect_correct_lang_route($brand, $langcode .".pepsibrands.sparksflyforsummer");
	    			}
	    			break;
	    		
	    		default:
					return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
	    			break;
	    	}
		    return array(
	            '#theme' => $template,
	            '#data'  => $data,
	        );
	    }

	public function stacysRise(Request $request){
		$brand = 'stacyssnacks'; 
    	if (!$this->is_brand_active($brand) ){
			return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
		}
    	$langcode = get_current_langcode();
    	$data = [];
    	$data['brand'] = $brand;
    	$template = "pepsibrands_stacys_rise";
		if ($this->is_wrong_language_url()){
			return $this->redirect_correct_lang_route([],$langcode .".pepsibrands.stacys.rise");
		}

		$videos = get_brand_videos("stacys", 1);
	    if (!empty($videos)){
    	    $data['video'] = $videos[0];
	    }

	    if ( $request->query->has('open') ){
	    	$data['open_applications'] = 1;
	    }
	    

	    if ($request->getMethod() == 'POST'){
			$post['email'] = $request->get('email');
			$post['firstname'] = $request->get('firstname');
			$post['lastname'] = $request->get('lastname');
			$post['language'] = get_current_langcode(false);
			
			$data['has_error'] = 0;
			$form_validator = new PepsiFormValidation();
			$data['error'] = $form_validator->stacysFormValidate($post);
			if (empty($data['error'])){
				$data['thankyou'] = 1;
				$result =  $this->saveRegistration($post);
			} else {
				$data['has_error'] = 1;
			}
			$data['firstname'] = isset($post['firstname'])? $post['firstname'] : '';
			$data['lastname'] = isset($post['lastname'])? $post['lastname'] : '';
			$data['email'] = isset($post['email'])? $post['email'] : '';
			
		}

		$data['brands_basepath'] = get_brand_basepath();



	    return array(
            '#theme' => $template,
            '#data'  => $data,
        );
	}

	public function saveRegistration($post){

		$values = [
			'email' => $post['email'],
			'firstname' => $post['firstname'],
			'lastname' => $post['lastname'],
			'language' => $post['language'],
			'enterdate' => date('Y-m-d H:i:s'),
		];

		if (\Drupal::database()->schema()->tableExists('stacys_rise_entries')) {
			try{
				$result = \Drupal::database()->insert('stacys_rise_entries')->fields($values)->execute();
			} catch(\Exception $e){
				//
			}
		}

	}		

}