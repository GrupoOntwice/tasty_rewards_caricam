<?php

/**
 * @file
 */

namespace Drupal\pepsibrands;


class ReceiptManager{

    private $endpoint;
    private $base_url = 'http://52.27.9.178:9000/';

	public function __construct(){
        $env = \Drupal\Core\Site\Settings::get("environment");
        if ($env == 'staging' || $env == 'dev'){
            $this->endpoint = 'https://bamquakerapi.staging.com/api/bamquaker/' ;
        } elseif($env == 'prod'){
            $this->endpoint = 'https://bamquakerapi.com/api/bamquaker/' ;
        }

    }

    public function processReceipt($post){
        $env = \Drupal\Core\Site\Settings::get("environment");
        // if ($env == 'dev')
        //     return 1;

        
        if (!empty($post['files'])){
            $files = $post['files'];
            $receipt_path = $files->getPathname();
            $result =  $this->sendReceipt($post);
            return $result;
        }
        return 0;
    }

    private function getCurlValue($file_fullpath, $contentType, $filename){

        if (function_exists('curl_file_create')) {
            return curl_file_create($file_fullpath, $contentType, $filename);
        }
    }

    public function sendReceipt($post){
        if (empty($this->get_3tl_api_key()) ){
            log_var("API key is missing, add it at /admin/config/sfmcservices/adminsettings ", "3tierLogic API error");
            return null;
        }

        $return = [
            'status' => 0,
        ];

        $api_key = $this->get_3tl_api_key();
        $token = $this->get_3tl_token();
        $base_url = $this->get_3tl_baseurl();
        $callback_url = $this->get_3tl_callback();
        $url = rtrim($base_url, "/") . '/ocr/image/upload?api_key=' . $api_key ;

        if (strlen($post['uploadImg']) > 100 ){
            $return['error'] = t('the filename cannot exceed 100 characters');
            return $return;
        }
        // 
        $files = $post['files'];

        if (empty($files))
            return null;
        $mimeType = $files->getClientMimeType();  // 'image/jpeg'
        
        // @TODO: Remove space from filename
        $unique_filename = uniqid("receipt_") . $post['uploadImg']; 
        $cfile = $this->getCurlValue($post['imagePath'], $mimeType, $unique_filename );
         
        $curl = curl_init();
        $random = rand(3,50);

        $payload = [
            'email' => $post['email'],
            'first_name' => $post['firstname'],
            'last_name' => $post['lastname'],
            'image' => $cfile,
            'access_token' => $token,
            // 'callback_url' => 'https://tastyrewards-stage.pepext.com/api/receipt/process',
            'callback_url' => $callback_url,
            // 'campaign_id' => '111',
            // 'receipt_user_id' => $random,
        ];

        $curl_params = [
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_SSL_VERIFYPEER => false, // the production endpoint doesn't work without this
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $payload,
        ]; 
        curl_setopt_array($curl, $curl_params);
        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        


        if (curl_errno($curl)) {
            echo 'Error:' . curl_error($curl);
            log_var('Error:' . curl_error($curl) , " QUaker receipt error", 'Quaker-Receipt');
            $return['error'] = t('Unknown error');
            return $return;
        }

        $duplicate_image = false;
        $response_arr = json_decode($response);
        if ( strpos($response, 'duplicate image') !== false ){
            $return['error'] = t('Duplicate receipt');
        } else {
            $return['status'] = 1;
        }


        $this->saveReceiptEntry($post, $unique_filename);

        // log_var($response, "quaker receipt successfully sent", 'Quaker-Receipt');

        return $return;

    }

    public function saveReceiptEntry($post, $unique_filename){

        $sql = "INSERT IGNORE INTO pepsi_receipts_entries (filename, email, firstname, lastname, postalcode, enterdate, regdate) VALUES ('$unique_filename', )";
        $language = get_current_langcode(false);

        $values = [
            'filename' => $unique_filename,
            'email' => $post['email'],
            'firstname' => $post['firstname'],
            'lastname' => $post['lastname'],
            'postalcode' => $post['postalcode'],
            'enterdate' => date('Y-m-d'),
            'regdate' => date('Y-m-d H:i:s'),
            'status' => 'pending',
            'language' => $language,
        ];

        $result = \Drupal::database()->insert('pepsi_receipts_entries')->fields($values)->execute();
    }

    public function requestAccessToken(){

        $endpoint = rtrim( $this->base_url, "/")  . "/ocr/client/accessToken";
        $endpoint = 'http://snap3-v1.3tlstaging.com/ocr/client/accessToken';
        $credentials = [
            'consumerKey' => 'ffa179b3e58d4b11f32b3dc238cbf03f',
            'consumerSecret' => '383a2be0c82091982375b648181f7c1b',
        ];

        $payload = json_encode($credentials);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $endpoint);

        curl_setopt($ch, CURLOPT_POSTFIELDS, ''. $payload);
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $curl_result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
            die("---ERROR ");
        }
        debug_var(" ACcess token ");
        debug_var($curl_result, 3);
    }

    public function validateReceiptForm($post){
        $error = [];

        if (!$this->nameValidation($post['firstname']))
            $error['firstname'] = t('Please enter a valid first name.');

        //lastname val
        if (!$this->nameValidation($post['lastname']))
            $error['lastname'] = t('Please enter a valid last name.');

        if (!$this->PostalCodeValidation($post['postalcode']))
            $error['postalcode'] = t('Please enter a valid postal code.');


        if (!pepsi_validate_email($post['email']))
            $error['email'] = t('Please enter a valid email.');

        if (empty($error)){
            $nb_entries = $this->countUserEntries($post['email']);
            if ($nb_entries > 3 ){
                $error['email'] = t('Only 4 offers per email address');
            }
        }

        return $error;

    }

    public function countUserEntries($email){
        $email = trim($email);
        $sql = "SELECT * from pepsi_receipts_entries WHERE email = '$email' ";

        $query = \Drupal::database()->query($sql);
        $result = $query->fetchAll();
        if (empty($result)){
            return 0;
        } else {
            $total_entries = 0;
            if (count($result) > 3){
                return count($result);
            }


            foreach ($result as $row) {
                if (trim($row->status) == 'approved'){
                    $total_entries += intval($row->nb_email_sent) ;
                } elseif(trim($row->status) == 'pending'){
                    $total_entries += 1 ;
                }
            }
        }

        return $total_entries;

    }



    public function nameValidation($name) {
        if (!preg_match("/^(?:[\s,.'-]*[a-zA-Z\pL][\s,.'-]*)+$/u", $name))
            return false;
        return true;
    }

    public function sendImage($file, $token){
        $endpoint = "/ocr/image/upload";
        $parameters = [
            'image' => $file,
            'access_token' => $token,
            'callback_url' => '',
        ];


    }

    public function PostalCodeValidation($postalcode, $country_code = 'ca') {
        if ( $country_code == 'ca' && preg_match("/^[ABCEGHJKLMNPRSTVXY]{1}\d{1}[A-Z]{1} *\d{1}[A-Z]{1}\d{1}$/",$postalcode ) ){
            return true;
        }
        else if ( $country_code == 'usa' && preg_match("/^([0-9]{5})(-[0-9]{4})?$/i",$postalcode)){
            return true;
        }
        return false;
    }

    function get_3tl_api_key(){
        $config =  \Drupal::config('sfmcservices.adminsettings');
        if ($config->get('3tl_api_key') ){
            return $config->get('3tl_api_key');
        }
        return '';
    }

    public function get_3tl_baseurl(){
        $config =  \Drupal::config('sfmcservices.adminsettings');
        if ($config->get('3tl_base_url') ){
            return $config->get('3tl_base_url');
        }
        return '';
    }


    public function get_3tl_callback(){
        $config =  \Drupal::config('sfmcservices.adminsettings');
        if ($config->get('3tl_callback_url') ){
            return $config->get('3tl_callback_url');
        }
        return '';
    }

    public function get_3tl_token(){
        $config =  \Drupal::config('sfmcservices.adminsettings');
        if ($config->get('3tl_token') ){
            return $config->get('3tl_token');
        }
        return '';
    }
}