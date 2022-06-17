<?php

/**
 * @file
 */

namespace Drupal\pepsibrands;


class PepsiFormValidation{
    public function __construct(){

    }


    public function laysStoryFormValidate($post){
        $error = [];

        if (!$this->nameValidation($post['firstname']))
            $error['firstname'] = t('Please enter a valid first name.');

        //lastname val
        if (!$this->nameValidation($post['lastname']))
            $error['lastname'] = t('Please enter a valid last name.');

        if (empty($post['province']))
            $error['province'] = t('Province is required');

        if (empty($post['optin']))
            $error['optin'] = t('Required field');

        // if (empty($post['uploadImg']))
            // $error['uploadImg'] = t('Image file is required');

        if (!pepsi_validate_email($post['email']))
            $error['email'] = t('Please enter a valid email.');

        // if (empty($error)){
        //     $nb_entries = $this->countUserEntries($post['email']);
        //     if ($nb_entries > 3 ){
        //         $error['email'] = t('Only 4 offers per email address');
        //     }
        // }

        return $error;

    }


    public function stacysFormValidate($post){
        $error = [];

        if (!$this->nameValidation($post['firstname']))
            $error['firstname'] = t('Please enter a valid first name.');

        if (!$this->nameValidation($post['lastname']))
            $error['lastname'] = t('Please enter a valid last name.');



        if (!pepsi_validate_email($post['email']))
            $error['email'] = t('Please enter a valid email.');

        return $error;

    }

    public function nameValidation($name) {
        if (!preg_match("/^(?:[\s,.'-]*[a-zA-Z\pL][\s,.'-]*)+$/u", $name))
            return false;
        return true;
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

}
