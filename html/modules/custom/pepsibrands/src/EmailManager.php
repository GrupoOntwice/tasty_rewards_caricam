<?php

/**
 * @file
 */

namespace Drupal\pepsibrands;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class EmailManager{

	public static function instance(){
		return new EmailManager();
	}

	public function sendStacysEmail($email, $firstname = '', $language = 'en'){

	    //@TODO: French translation still doesn't work when being called
	    //from the callback API
	    $credentials = drupal_email_credentials();
	    $email_logo_img = 'email-logo.png';
	    $header_image = 'quaker_unverified.png';



	    if (empty($credentials)){
	        log_var("", "SMTP credentials missing ");
	        return false;
	    }

	    $subject = '';
	    $content = '';
	    $host = \Drupal::request()->getSchemeAndHttpHost();
	    $lang = get_current_langcode(false);

	    $email_copy = $this->getEmailContent($email, $language, $firstname);
	    // $subject_en = $email_copy['subject_en'];


	    $subject = $email_copy['subject'];
	    $content = $email_copy['content'];

	   $header_image = 'stacys-about-us-banner-dsk.webp';
	   $header_image = 'stacys-logo.png';
	   $banner_image = 'email-banner-stacys.png'; // @Rashi you can put the banner.png or whatever name of the image here
	   if ($language == 'fr' || $language == 'fr-ca'){
		$banner_image = 'Stacys_EmailBanner_FR.jpg';
	   }


	    // debug_var($claim_url, 1);
	    $mail = new PHPMailer(true);

	    $slash = DIRECTORY_SEPARATOR;

	    $current_dir = dirname(dirname(dirname(dirname(__DIR__))) );
	    $file = $current_dir . $slash  .  "themes" . $slash  . "brandstheme" . $slash  . "src" . $slash  . "images" . $slash  . "stacys" . $slash  . $header_image;
	    $file2 = $current_dir . $slash  .  "themes" . $slash  . "brandstheme" . $slash  . "src" . $slash  . "images" . $slash  . "stacys" . $slash  . $banner_image;
	//   $file2 = $current_dir . $slash  .  "themes" . $slash  . "tastytheme" . $slash  . "src" . $slash  . "images" . $slash  . "grab-snack" . $slash  . $email_logo_img;
	  // $file3 = $current_dir . $slash  .  "themes" . $slash  . "tastytheme" . $slash  . "src" . $slash  . "images" . $slash  . "grab-snack" . $slash  . $verified_image;

	    $mail->AddEmbeddedImage($file, 'header_img');
	  	$mail->AddEmbeddedImage($file2, 'banner_img');


	  // $mail->AddEmbeddedImage($file3, 'verified_img');

	    $mail->SMTPDebug = 0;
	    //Set PHPMailer to use SMTP.
	    $mail->isSMTP();
	    //Set SMTP host name
	    $mail->Host = "email-smtp.us-east-1.amazonaws.com";
	    //Set this to true if SMTP host requires authentication to send email
	    $mail->SMTPAuth = true;
	    //Provide username and password
	    $mail->Username = $credentials['username'];
	    $mail->Password = $credentials['password'];
	    //If SMTP requires TLS encryption then set it
	    $mail->SMTPSecure = "tls";
	    $mail->Port = 587;

	    $mail->From = "contest@tastyrewards.ca";
	    $mail->FromName = "Tasty Rewards Contest";

	    $mail->addAddress($email, "$firstname ");

	    $mail->isHTML(true);

	    $mail->Subject = $subject;
	    $mail_content = $content;

	    $mail->Body = $mail_content;


	    $mail->AltBody = strip_tags($mail_content);

	    try {
	        $mail->send();


	        $connection = \Drupal\Core\Database\Database::getConnection();

	        if (\Drupal::database()->schema()->tableExists('stacys_rise_entries')) {

				\Drupal::database()->update('stacys_rise_entries')
				            ->fields([
				                'email_sent' => 1,
				            ])
				            ->condition('email',$email)
				            ->execute();

	        }


	        if (\Drupal::database()->schema()->tableExists('pepsi_email_logs')) {
	          $email_type = ($firstname == 'test_admin3889') ? 'stacys_test' : 'stacys_rise';
	          $connection->insert('pepsi_email_logs')
	              ->fields([
	                'subject' => $subject,
	                'recepient' => $email,
	                'email_type' => $email_type,
	                'language' => $language,
	                'enterdate' => date("Y-m-d"),
	                'regdate' => date("Y-m-d H:i:s"),
	              ])
	              ->execute();
	        }


	    } catch (Exception $e) {
	        echo "Mailer Error: " . $mail->ErrorInfo;
	    }
	}


	public function getEmailContent($email, $language, $firstname){
	    $subject = '';
	    $subject_en = '';
	    $content = '';
	    $host = \Drupal::request()->getSchemeAndHttpHost();
	    $lang = get_current_langcode(false);

	    $header_image = 'quaker_verified.png';

	    if ($language == 'fr'){
  	      $header_image = 'quaker-verified-fr.png';
	    }

		// banner image can be called using the following
		// <img src = 'cid:banner_img'/><br><br>

	    // /themes/brandstheme/src/images/stacys/stacys-about-us-banner-dsk.webp


	      $subject = t("Applications Now Open for the Stacy's Rise Project!");
	      $subject = utf8_decode( $subject );
	      if ($language == 'en' || $language == 'en-ca'){
	        $content = "<div style = 'text-align: center'>
                       <div style = 'background-color:#000; width:75%; margin: 0 auto';>
                        <img src = 'cid:header_img'/>
                        </div><br><br>
	                    <img src = 'cid:banner_img'/> <br> <br>
	                    <div style = 'width: 70%; margin: auto; text-align: left; font-size: 18px; color:#616161'>".
	                    t(" Hello $firstname") . "<br>" .
	                    "<p style = 'font-weight: bold; color:#616161'>".t("Applications are now open!")."</p>" .


	            t("Submit your application to the Stacy’s Rise Project<sup style = 'font-size: 10px'>TM</sup> for a chance to receive one of our financial grants — along with valuable community exposure and mentorship from PepsiCo leaders.<br><br>") .

	            "<a style='text-decoration: none; background-color: #1C97A8; border:2px solid #1C97A8; padding: 8px; padding-left: 15px; padding-right: 15px; color: #fff' href='$coupon_link'>". t("Apply Now") . "</a> <br> <br> <br> <br>
	            <div style='font-size: 16px'>" .

	          "<div style='font-size: 15px; text-align: left; color:#616161'>".
	            t('This message was sent to you because you wanted to be notified about updates for the “Stacy’s Rise Project™”, run by PepsiCo Canada. We can be contacted at 2095 Matheson Boulevard East, Mississauga, Ontario, L4W 0G2 or www.pepsico.ca. Unless you have previously opted in, your email address has not been added to any email marketing lists by participating in this program.') . "</div> </div> </div> </div>";

	          }
	          elseif($language == 'fr' || $language == 'fr-ca'){
	            $subject = "Début de la période d'inscription au Projet Ascension Stacy's!";
	            $subject = utf8_decode( $subject );
	            $content = "<div style = 'text-align: center'>
                       <div style = 'background-color:#000; width:75%; margin: 0 auto';>
                        <img src = 'cid:header_img'/>
                        </div><br><br>
	                    <img style = 'width:70%' src = 'cid:banner_img'/> <br> <br>
	                    <div style = 'width: 70%; margin: auto; text-align: left; font-size: 18px; color:#616161'>".
	                    t(" Bonjour $firstname") . "<br>" .
	                    "<p style = 'font-weight: bold; color:#616161'>".t("Les inscriptions sont ouvertes!")."</p>" .

						t("Soumettez votre demande au projet Ascension de Stacy’s et courez la chance de recevoir l’une de nos subventions — en plus d’une précieuse visibilité dans la communauté et d’un mentorat offert par des cadres de PepsiCo. <br><br>") .

	            "<a style='text-decoration: none; background-color: #1C97A8; border:2px solid #1C97A8; padding: 8px; padding-left: 15px; padding-right: 15px; color: #fff' href='$coupon_link'>". t("Envoyez votre demande dès maintenant!") . "</a> <br> <br> <br> <br>
	            <div style='font-size: 16px'>" .

	          "<div style='font-size: 15px; text-align: left; color:#616161'>".
	            t('Vous avez reçu ce message parce que vous avez demandé de recevoir des mises à jour au sujet du Projet Ascension Stacy’s, organisé par PepsiCo Canada. Vous pouvez communiquer avec nous au 2095 Matheson Boulevard East, Mississauga (Ontario)  L4W 0G2, ou visiter le www.pepsico.ca. À moins que vous n’y ayez préalablement consenti, votre adresse électronique n’a pas été ajoutée à des listes de marketing par courriel du fait de votre participation à ce programme.') . "</div> </div> </div> </div>";

	          }


	    $email_copy = [];
	    $email_copy['subject'] = $subject;
	    // $email_copy['subject_en'] = $subject_en;
	    $email_copy['content'] = $content;

	    return $email_copy;
	}

}
