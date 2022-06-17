<?php

/**
 * @file providing the service that say hello world and hello 'given name'.
 *
 */

namespace Drupal\marketoservices;

class MarketoServicesEmailSoap {

    private $marketoSoapEndPoint;
    private $marketoUserId;
    private $marketoSecretKey;
    private $marketoNameSpace;

    public function __construct() {
        $marketoconf = \Drupal::config('marketoservices.settings')->get('marketo');

        $this->marketoSoapEndPoint = $marketoconf['marketoSoapEndPoint'];
        $this->marketoUserId = $marketoconf['marketoUserId'];
        $this->marketoSecretKey = $marketoconf['marketoSecretKey'];
        $this->marketoNameSpace = $marketoconf['marketoNameSpace'];
    }

    public function postData($leadId, $campaignId = null ) {

        $debug = false;
        // Create Signature
        $dtzObj = new \DateTimeZone("America/New_York");
        $dtObj = new \DateTime('now', $dtzObj);
        $timeStamp = $dtObj->format(DATE_W3C);
        $encryptString = $timeStamp . $this->marketoUserId;
        $signature = hash_hmac('sha1', $encryptString, $this->marketoSecretKey);

        // Create SOAP Header
        $attrs = new \stdClass();
        $attrs->mktowsUserId = $this->marketoUserId;
        $attrs->requestSignature = $signature;
        $attrs->requestTimestamp = $timeStamp;
        $authHdr = new \SoapHeader($this->marketoNameSpace, 'AuthenticationHeader', $attrs);
        $options = array("connection_timeout" => 20, "location" => $this->marketoSoapEndPoint);
        if ($debug) {
            $options["trace"] = true;
        }

        // Create Request
        $leadKey = array("keyType" => "IDNUM", "keyValue" => $leadId);

        $leadList = new \stdClass();
        //$leadList->leadKey = array($leadKey, $leadKey2);
        $leadList->leadKey = array($leadKey);

        $source = "MKTOWS"; //MKTOWS 
        if (!$campaignId)
            $campaignId = "1612";

        $paramsRequestCampaign = new \stdClass();
        $paramsRequestCampaign->campaignId = $campaignId;
        $paramsRequestCampaign->source = $source;
        $paramsRequestCampaign->leadList = $leadList;

        $params = array("paramsRequestCampaign" => $paramsRequestCampaign);

        $soapClient = new \SoapClient($this->marketoSoapEndPoint . "?WSDL", $options);
        try {
            $response = $soapClient->__soapCall('requestCampaign', $params, $options, $authHdr);
            return $response;
        } catch (Exception $ex) {
            //var_dump($ex);
        }
        if ($debug) {
            print "RAW request:\n" . $soapClient->__getLastRequest() . "\n";
            print "RAW response:\n" . $soapClient->__getLastResponse() . "\n";
        }
        return false;
    }

}
