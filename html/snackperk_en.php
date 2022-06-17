<?php 
if (is_safe_uri($_SERVER['REQUEST_URI']) ) {
  $uri = $_SERVER['REQUEST_URI'];
} else {
  // remove the query string
  $uri = strtok($_SERVER['REQUEST_URI'], '?');
}
$language = explode("/", $uri)[1];
// $source = explode("/", $uri)[4];
$source = get_source_from_uri($uri);
$host = $_SERVER['HTTP_HOST'];

$emailAddress = "";
// $emailAddress = isset($_GET['email']) ? $_GET['email']: '' ;
if (isset($_GET['email'])){
  // filter_var() returns the email Address if it's valid, 'false' otherwise
  $emailAddress = filter_var($_GET['email'], FILTER_VALIDATE_EMAIL);
  if ($emailAddress === false){
    $emailAddress = "";
  }
}

function get_language_from_uri($uri){
  $allowed_values = ["en-ca", "fr-ca", "en-us", "es-us"];
  $lang = explode("/", $uri)[1];
  if (!in_array($lang, $allowed_values))
    return "";
  return $lang;

}

function is_safe_uri($uri){
  // /en-ca/iframe2/subscribe/lays
  if (strpos($uri, "/iframe2/subscribe/") !== false && get_source_from_uri($uri) !== null){
    return true;
  }
  return false;
}

function is_valid_source($source){
  $allowed_values = [
     "lays","tostitos","doritos","cheetos","ruffles","smartfood","sunchips","stacysnacks","fritolayvarietypacks","fritolaysnackperks","crispyminis","quakeroats","missvickies","stacyssnacks","sunchips","twistos","fritolaymajestic","fritolay","offtheeatenpathsnacks","baresnacks","collationsofftheeatenpath","collationsbare","fritos","simplyfritolay", "funyuns", "laysfarmtosmiles"
   ];
   if (!in_array($source, $allowed_values))
      return false;

    return true;
}

function get_source_from_uri($uri){
   

   $url_source = explode("/", $uri)[4];
   if (!is_valid_source($url_source))
      return null;

    return $url_source;
}

function translate($string, $language){
  $translations = array();
  $translations["SIGN UP"]['en-ca'] = "SIGN UP";
  $translations["SIGN UP"]['en-us'] = "SIGN UP";
  $translations["SIGN UP"]['es-us'] = "Regístrate con Google";
  $translations["contests"]['en-us'] = "sweepstakes";
  $translations["SIGN UP"]['fr-ca'] = "S'inscrire";
  $translations["Sign up"]['fr-ca'] = "S'inscrire";
  $translations["Sign up"]['es-us'] = "Regístrate";
  $translations["First Name"]['fr-ca'] = "Prénom";
  $translations["First Name"]['es-us'] = "Nombre";
  $translations["Last Name"]['fr-ca'] = "Nom";
  $translations["Last Name"]['es-us'] = "Apellidos";
  $translations["Postal Code"]['en-us'] = "Zip Code";
  $translations["Postal Code"]['fr-ca'] = "Code Postal";
  $translations["Postal Code"]['es-us'] = "Código postal";
  $translations["Password"]['es-us'] = "Contraseña";
  $translations["Password"]['fr-ca'] = "Mot de passe";
  $translations["Date of Birth"]['es-us'] = "Fecha de nacimiento";
  $translations["Date of Birth"]['fr-ca'] = "Date de naissance";
  $translations["Month"]['es-us'] = "Mes";
  $translations["Month"]['fr-ca'] = "Mois";
  $translations["Day"]['es-us'] = "Día";
  $translations["Day"]['fr-ca'] = "Jour";
  $translations["Year"]['es-us'] = "Año";
  $translations["Year"]['fr-ca'] = "Année";
  $translations["Gender"]['es-us'] = "Sexo";
  $translations["Gender"]['fr-ca'] = "Sexe";

  $translations["January"]['es-us'] = "Enero";
  $translations["February"]['es-us'] = "Febrero";
  $translations["March"]['es-us'] = "Marzo";
  $translations["April"]['es-us'] = "Abril";
  $translations["May"]['es-us'] = "Mayo";
  $translations["June"]['es-us'] = "Junio";
  $translations["July"]['es-us'] = "Julio";
  $translations["August"]['es-us'] = "Agosto";
  $translations["September"]['es-us'] = "Septiembre";
  $translations["October"]['es-us'] = "Octubre";
  $translations["November"]['es-us'] = "Noviembre";
  $translations["December"]['es-us'] = "Diciembre";


  $translations["Male"]['es-us'] = "Masculino";
  $translations["Female"]['es-us'] = "Mujer";
  $translations["Other"]['es-us'] = "Otro";


  $translations["Terms & Conditions"]['es-us'] = "Términos y condiciones";
  $translations["Privacy Policy"]['es-us'] = "Política de privacidad";
  $translations["About our Ads"]['es-us'] = "Acerca de nuestros anuncios";


  $translations["To learn more about how we use your information, please read PepsiCo's"]['es-us'] = "Para obtener más información sobre cómo usamos tu información, lee nuestra";

  $translations["or"]['fr-ca'] = "ou";
  $translations["or"]['es-us'] = "o";
  $translations["Create my account"]['fr-ca'] = "S'inscrire";
  $translations["Create my account"]['es-us'] = "Crear mi cuenta";
  $translations["Indicates required field"]['fr-ca'] = "Indique un champ obligatoire";
  $translations["Indicates required field"]['es-us'] = "Indica un campo obligatorio";
  $translations["Join the Tasty RewardsTM Experience"]['en-ca'] = "Join the Tasty Rewards<sup>TM</sup> Experience";
  $translations["Join the Tasty RewardsTM Experience"]['en-us'] = "Join the Tasty Rewards Experience";
  $translations["Join the Tasty RewardsTM Experience"]['fr-ca'] = "Joignez-vous à Primes et Délices";
  $translations["Join the Experience"]['fr-ca'] = "Joignez-vous à Primes et Délices";
  $translations["Join the Experience"]['en-us'] = "<p>Sign up to enjoy the latest PepsiCo coupons, sweepstakes and more!</p>";
  $translations["Join the Experience"]['es-us'] = "<p>¡Regístrate para disfrutar de los últimos cupones, sorteos y mucho más de PepsiCo!</p>";
  $translations["Yes! Sign me up to Tasty Rewards<sup>TM</sup> by email so I never miss out on exciting updates, offers or  contests  (and I have the option to unsubscribe at any time)."]['en-us'] = "Yes! Sign me up to receive email from PepsiCo Tasty Rewards, PepsiCo and its brands so I never miss out on exciting updates, offers or sweepstakes.";

  $translations["Yes! Sign me up to Tasty Rewards<sup>TM</sup> by email so I never miss out on exciting updates, offers or  contests  (and I have the option to unsubscribe at any time)."]['es-us'] = "¡Sí! Quiero recibir correos electrónicos de PepsiCo Premios Deliciosos, PepsiCo y sus marcas para no perderme nunca las emocionantes actualizaciones, ofertas o sorteos.";

  $translations["Already have an account"]['fr-ca'] = "Joignez-vous à Primes et Délices ";
  $translations['PepsiCo Canada<br> 5550 Explorer Drive, 8th Floor, Mississauga, ON L4W 0C3<br> <a class="tasty-site" target="_blank" href="https://www.tastyrewards.ca">www.tastyrewards.ca</a>']['fr-ca'] = 'PepsiCo Canada<br> 5550 Explorer Drive, 8e étage, Mississauga, ON L4W 0C3 <br> <a class="tasty-site" target="_blank" href="https://www.tastyrewards.ca/fr">www.primesetdélices.ca</a>';
  $translations['Your personal information will be collected and used in accordance with our <a class="privacy-policy" target="_blank" href="http://www.pepsico.ca/en/Privacy-Policy.html"> Privacy Policy</a>.']['fr-ca'] = 'Vos renseignements personnels seront utilisés conformément à notre<a class="privacy-policy" target="_blank" href="http://www.pepsico.ca/fr/Politique-de-protection-de-la-vie-privee.html"> Politique de confidentialité</a>';

  $translations["Contacting Us:"]['fr-ca'] = "Pour nous contacter:";

  
  $translations["Don’t forget to subscribe to our Tasty Rewards™ emails so you can stay in the loop with exciting members-only offers and updates! Visit TastyRewards.com/en-ca/ for member exclusive offers"]['fr-ca'] = "N’oubliez pas de vous abonner aux courriels de Primes et Délices afin d’être au courant des toutes dernières offres et nouvelles de dernière minute réservées aux membres.";

  $translations["Don’t forget to subscribe to our Tasty Rewards™ emails so you can stay in the loop with exciting members-only offers and updates! Visit TastyRewards.com/en-ca/ for member exclusive offers"]['es-us'] = "¡No olvides suscribirte a nuestros correos electrónicos de Premios Deliciosos para que puedas estar al tanto de las emocionantes ofertas y actualizaciones exclusivas para miembros!";

  $translations["Don’t forget to <a href='https://www.tastyrewards.com/en-ca/my-account'> subscribe</a> to our Tasty Rewards™ emails so you can stay in the loop with exciting members-only offers and updates. <br><br> Plus, keep an eye out for content and ads tailored to your interests! Check your settings or opt-out at any time <a href='https://www.tastyrewards.com/en-ca/my-account'> here</a>"]['fr-ca'] =  "N’oubliez pas de vous  <a href='https://www.tastyrewards.com/fr-ca/my-account'>inscrire</a> aux courriels de Primes et Délices afin d’être au courant des toutes dernières offres et nouveautés réservées aux membres. <br> <br> De plus, demeurez à l’affût des publicités et du contenu adaptés à vos intérêts. Vérifiez vos paramètres ou désinscrivez-vous en tout temps en cliquant <a href='https://www.tastyrewards.com/fr-ca/my-account'>ici</a>. ";

  $translations["Don’t forget to <a href='https://www.tastyrewards.com/en-ca/my-account'> subscribe</a> to our Tasty Rewards™ emails so you can stay in the loop with exciting members-only offers and updates. <br><br> Plus, keep an eye out for content and ads tailored to your interests! Check your settings or opt-out at any time <a href='https://www.tastyrewards.com/en-ca/my-account'> here</a>"]['es-us'] =  "¡No olvides suscribirte a nuestros correos electrónicos de Premios Deliciosos para que puedas estar al tanto de las emocionantes ofertas y actualizaciones exclusivas para miembros!";


  $translations["Thanks for signing up! Just one more thing…"]['fr-ca'] = "Nous vous remercions de votre inscription! Une dernière petite chose…";
  $translations["Thanks for signing up! Just one more thing…"]['es-us'] = "¡Gracias por registrarte! Solo una cosa más...";
  $translations["You’re all set!"]['fr-ca'] = "Votre inscription est maintenant complétée!";
  $translations["You’re all set!"]['es-us'] = "¡Ya está!";
  $translations['Thanks for joining the Tasty Rewards™ crew. Get ready for tasty surprises coming your way! Visit <a target="_blank" href="https://www.tastyrewards.com/en-ca"> TastyRewards.com/en-ca/</a>  for member exclusive offers']['fr-ca'] = "Nous vous remercions de faire partie de la communauté de Primes et Délices. Préparez-vous à faire une foule de découvertes délicieuses!";
  $translations['Thanks for joining the Tasty Rewards™ crew. Get ready for tasty surprises coming your way! Visit <a target="_blank" href="https://www.tastyrewards.com/en-ca"> TastyRewards.com/en-ca/</a>  for member exclusive offers']['es-us'] = " Gracias por unirte al equipo de PepsiCo Premios Deliciosos. ¡Prepárate para sorpresas en tu camino!";

  $translations['Thanks for joining the Tasty Rewards™ crew. Get ready for tasty surprises coming your way! <br> Also, keep an eye out for content and ads tailored to your interests! Check your settings or opt-out at any time <a href="https://www.tastyrewards.com/en-ca/my-account"> here</a>']['fr-ca'] = 'Nous vous remercions de faire partie de la communauté de Primes et Délices. Préparez-vous à faire une foule de découvertes délicieuses! <br><br> De plus, demeurez à l’affût des publicités et du contenu adaptés à vos intérêts. Vérifiez vos paramètres ou désinscrivez-vous en tout temps en cliquant <a href="https://www.tastyrewards.com/fr-ca/my-account">ici</a>.';
  $translations['Thanks for joining the Tasty Rewards™ crew. Get ready for tasty surprises coming your way! <br> Also, keep an eye out for content and ads tailored to your interests! Check your settings or opt-out at any time <a href="https://www.tastyrewards.com/en-ca/my-account"> here</a>']['es-us'] = 'Gracias por unirte al equipo de PepsiCo Premios Deliciosos. ¡Prepárate para sorpresas en tu camino! Además, no pierdas de vista los contenidos y anuncios adaptados a tus intereses. Chequea tu configuración o date de baja en cualquier momento. ';



  $translations["You must be at least 13 years of age or older to be a Tasty Rewards™ Member. Why do we collect your birthday? We use your birthday to send you a birthday greeting each year, and to provide the most relevant content for our members."]['en-us'] = "You must be at least 13 years of age or older to be a PepsiCo Tasty Rewards member and some sweepstakes entries require other age eligibility requirements. PepsiCo Tasty Rewards also uses your date of birth to send you a birthday greeting each year and to provide the most relevant content for members.";
  $translations["You must be at least 13 years of age or older to be a Tasty Rewards™ Member. Why do we collect your birthday? We use your birthday to send you a birthday greeting each year, and to provide the most relevant content for our members."]['es-us'] = "La edad mínima para poder darse de alta en el programa PepsiCo Premios Deliciosos es de 13 años, aunque este requisito de edad varía para la participación en determinados sorteos. PepsiCo Premios Deliciosos también utiliza la fecha de nacimiento para enviar anualmente una felicitación de cumpleaños y ofrecer el contenido más relevante a sus miembros.";

  $translations["Must have a minimum of 8 and a maximum of 16 characters containing only numbers and letters. Must include at least one number, one uppercase letter and one lowercase letter."]['fr-ca'] = "Votre mot de passe doit comporter entre 8 et 16 caractères et être composé uniquement de chiffres et de lettres, incluant au moins un chiffre, une lettre majuscule et une lettre minuscule.";
  $translations["Must have a minimum of 8 and a maximum of 16 characters containing only numbers and letters. Must include at least one number, one uppercase letter and one lowercase letter."]['en-us'] = "Password must contain:<br> 
    At least 8 characters <br>
    A lowercase letter <br>
    An uppercase letter <br>
    A number <br>
    A symbol
    ";
  $translations["Must have a minimum of 8 and a maximum of 16 characters containing only numbers and letters. Must include at least one number, one uppercase letter and one lowercase letter."]['es-us'] = "La contraseña debe contener:<br>
    Al menos 8 caracteres<br>
    Una letra minúscula<br>
    Una letra mayúscula<br>
    Un número<br>
    Un símbolo.";
  

  $translations["We use your gender to provide the most relevant and inclusive content for our members. This question is optional if you prefer not to share this information with us."]['es-us'] = "Usamos el género de nuestros miembros para proporcionar el contenido más relevante e inclusivo. Esta pregunta es opcional, en caso de que prefiera no compartir esta información con nosotros.";
  /*
Usamos el género de nuestros miembros para proporcionar el contenido más relevante e inclusivo. Esta pregunta es opcional, en caso de que prefiera no compartir esta información con nosotros.
  */
  if (!empty($translations[$string][$language])){
    return $translations[$string][$language];
  } else {
    return $string;
  }
  
}
  /* <?php print(translate("Join the Tasty RewardsTM Experience", $language) ); ?> */
?>
<!DOCTYPE html>

<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <title>Title</title>
  <style>
      .fb-hidden.modal__social-button {
    justify-content: center !important;
  }
  </style>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css" />
  <!-- inject:css -->
  <link rel="stylesheet" href="/themes/tastytheme/src/css/style-iframe.css">
  <!-- /modules/custom/pepsibam/js/register.js -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>

  <script type="text/javascript" src="/themes/tastytheme/src/js/facebook-iframe.js"></script>
  <script src="https://apis.google.com/js/platform.js" async defer></script>
  <script type="text/javascript" src="/core/misc/drupal.js"></script>
  <script type="text/javascript" src="/core/misc/drupalSettingsLoader.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/ScrollMagic/2.0.7/ScrollMagic.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/ScrollMagic/2.0.7/plugins/animation.gsap.min.js"></script>
  <script type="text/javascript" src="/themes/tastytheme/src/js/bootstrap-multiselect.js"></script>
  <script type="text/javascript" src="/themes/tastytheme/src/js/script.js"></script>
  <script type="text/javascript">
      function captchaSubmit(token) {
      console.log(token);
      document.getElementById('grecaptcharesponse').value = token;
      window.submitRegistration();
      // submitRegistrationForm();

      // signUpSubmit();
      //document.getElementById("contact").submit    ();
      }
  </script>

  <script type="text/javascript">
    window.language = '<?php print($language) ?>';
  </script>
  
  <!-- endinject -->

  <!-- Google Tag Manager -->

    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-TW8ZBJH');</script>

    <!-- End Google Tag Manager -->    
</head>

<body onload="page_ready();" class="iframe-form <?php print(get_language_from_uri($uri)); ?>">
  <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TW8ZBJH" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
  <header>

    <!---------------------Sign in modal--------------------->



    <!---------------------Sign up modal--------------------->

    <div class="modal__container-sign-up">
      <div class="modal">
<!--          <p class="modal__container-paragraph">--><?php //print(translate("Join the Tasty RewardsTM Experience", $language) ); ?><!--</p>-->
          <div class="modal__container-paragraph">
              <img class="modal__container-paragraph-logo" src="/themes/tastytheme/src/images/logo-<?php echo $language == 'fr-ca'? 'fr' : ($language == 'en-us'? 'en-us-pepsi' : ( $language == 'es-us'? 'es-us' : 'en') ) ;?>.png" alt="logo" />
              <?php print(translate("Join the Experience", $language) ); ?>



             </div>

        <form action="" method="POST" id="register-form" class="form-wrapper" autocomplete="off">
          <input type="hidden" id="language" value="<?php print(get_language_from_uri($uri)); ?>">
          <input type="hidden" id="source" value="<?php print(get_source_from_uri($uri)); ?>">
          <input type="hidden" id="iframe-form" value="1">
          <input type="hidden" id="grecaptcharesponse" name="grecaptcharesponse">
          
          <?php
          /*
          <div class="modal__social-button fb-hidden">
            <button type="submit" class="modal__sign-up-fb fbconnect btn hidden">
              <img src="/themes/tastytheme/src/images/fb-logo.PNG" alt="facebook" />
              
              <?php print(translate("Sign up", $language) ); ?>
            </button>
            <div class="g-signin2 google-registration hidden" id="google-registration" onclick="ClickLogin(1)" data-onsuccess="onSignIn" data-theme="dark"></div>
            <button type="button" class="modal__sign-up-google" onclick="googleSignUp()">
              <img src="/themes/tastytheme/src/images/google.png" alt="google" />
              <?php print(translate("Sign up", $language) ); ?>
            </button>
          </div>

          <p class="modal__container-or">- <?php print(translate("or", $language) ); ?> -</p>
          */
          ?>

          <?php if ($language != 'en-us'){ ?>
              <p class="modal__inquiry">* <?php print(translate("Indicates required field", $language) ); ?></p>
          <?php } ?>
          <div class="form-group">
            <label id="email" for="email" style="display:none;">Email</label>
            <input type="text" class="modal__sign-up-input" name="email" id="email" placeholder="<?php echo $language == 'fr-ca'?  'Adresse courriel *' : ( $language == 'es-us'? 'Dirección de correo electrónico' : 'Email *') ; ?>" value="<?php print($emailAddress); ?>">
            <span class="help-block err_email"></span>
          </div>

          <!-- <div class="modal__user-name"> -->
            <div class="form-group">
              <label id="firstname" for="firstname" style="display:none;">First Name</label>
              <input type="text" class="modal__user-first-name" name="firstname" id="firstname" placeholder="<?php print(translate("First Name", $language) ); ?> *">
              <span class="err_firstname help-block"></span>
            </div>
            <div class="form-group">
              <label id="lastname" for="lastname" style="display:none;">Last Name</label>
              <input type="text" class="modal__user-last-name" name="lastname" id="lastname" placeholder="<?php print(translate("Last Name", $language) ); ?> *">
              <span class="help-block err_lastname"></span>
            </div>
          <!-- </div> -->
          <div class="form-group">
            <label id="postalcode" for="postalcode" style="display:none;">Postal Code</label>
            <input type="text" class="modal__sign-up-input" name="postalcode" id="postalcode" placeholder=" <?php print(translate("Postal Code", $language) ); ?>*">
            <span class="help-block err_postalcode"></span>
          </div>

          <div class="modal__user-password">
              <label id="password" for="password" style="display:none;">Password</label>
              <input type="password" class="modal__sign-up-input" name="password" id="password" placeholder="<?php print(translate("Password", $language) ); ?> *">

              <span toggle="#password-field-signup" class="fa-eye field-icon toggle-password-signup"></span>
              <span class="help-block err_password"></span>
            <a href="#/" class="modal__user-info-icon tip">
              <img src="/themes/tastytheme/src/images/information.png" alt="information" />
              <p><?php print(translate("Must have a minimum of 8 and a maximum of 16 characters containing only numbers and letters. Must include at least one number, one uppercase letter and one lowercase letter.", $language) ); ?>
                
              </p>
            </a>
          </div>

          <p class="modal__container-birth"> <?php print(translate("Date of Birth", $language) ); ?> *</p>
       <?php if ($language == 'en-ca' || $language == 'en-us' || $language == 'es-us' ) {?>
        <div class="form-group">
          <div class="modal__sign-up-birth">
            <input type="hidden" id='bday' name='bday' class="form-control">
            <?php if ($language == 'en-ca'){  ?>
              <select class="modal__sign-up-select form-control" name="bday_day" id="bday_day">
                <option value><?php print(translate("Day", $language) ); ?></option>
                <!-- <option value="1">1</option>
                <option value="2">2</option> -->
                <?php for ($day = 1; $day <= 31; $day++){ ?>
                  <option value="<?php echo $day ?>"><?php echo $day ?></option>
                <?php } ?>
                
              </select>
            <?php } ?>
            <select class="modal__sign-up-select form-control" name="bday_month" id="bday_month">
              <option value><?php print(translate("Month", $language) ); ?></option>
              <option value="01"><?php print(translate("January", $language) ); ?></option>
              <option value="02"><?php print(translate("February", $language) ); ?></option>
              <option value="03"><?php print(translate("March", $language) ); ?></option>
              <option value="04"><?php print(translate("April", $language) ); ?></option>
              <option value="05"><?php print(translate("May", $language) ); ?></option>
              <option value="06"><?php print(translate("June", $language) ); ?></option>
              <option value="07"><?php print(translate("July", $language) ); ?></option>
              <option value="08"><?php print(translate("August", $language) ); ?></option>
              <option value="09"><?php print(translate("September", $language) ); ?></option>
              <option value="10"><?php print(translate("October", $language) ); ?></option>
              <option value="11"><?php print(translate("November", $language) ); ?></option>
              <option value="12"><?php print(translate("December", $language) ); ?></option>
            </select>
            <?php if ($language == 'en-us' || $language == 'es-us'){  ?>
              <select class="modal__sign-up-select form-control" name="bday_day" id="bday_day">
                <option value><?php print(translate("Day", $language) ); ?></option>
                <!-- <option value="1">1</option>
                <option value="2">2</option> -->
                <?php for ($day = 1; $day <= 31; $day++){ ?>
                  <option value="<?php echo $day ?>"><?php echo $day ?></option>
                <?php } ?>
                
              </select>
            <?php } ?>
            <select class="modal__sign-up-select form-control" name="bday_year" id="bday_year" >
              <option value="years"><?php print(translate("Year", $language) ); ?></option>
              <?php for ($year = 2006; $year > 1900; $year--){ ?>
                <option value="<?php echo $year ?>"><?php echo $year ?></option>

              <?php } ?>
              <!-- <option value="1991">1991</option> -->
            </select>

            <a href="#/" class="modal__user-info-icon tip">
              <img src="/themes/tastytheme/src/images/information.png" alt="information" />
              <p>
                <?php print(translate("You must be at least 13 years of age or older to be a Tasty Rewards™ Member. Why do we collect your birthday? We use your birthday to send you a birthday greeting each year, and to provide the most relevant content for our members.", $language) ); ?>
               

              </p>
            </a>
          </div>
          <span class="help-block err_bday"></span>
        </div>
        <div class="form-group">
          <div class="modal__sign-up-gender">
            <select class="modal__sign-up-select-gender form-control" name="gender" id="gender">
              <option value><?php print(translate("Gender", $language) ); ?></option>
              <option value="M"><?php print(translate("Male", $language) ); ?></option>
              <option value="F"><?php print(translate("Female", $language) ); ?></option>
              <option value="O"><?php print(translate("Other", $language) ); ?></option>
            </select>
            <?php if ( $language == 'en-us' || $language == 'es-us' ) {?>

            <a href="#/" class="modal__user-info-icon tip">
              <img src="/themes/tastytheme/src/images/information.png" alt="information" />
              <p>
                <?php print(translate("We use your gender to provide the most relevant and inclusive content for our members. This question is optional if you prefer not to share this information with us.", $language) ); ?>
              

              </p>
            </a>

            <?php } // endif language == (en-us or es-us)  ?>
          </div>
        </div>


          
        <?php } elseif ($language == 'fr-ca') {?>
            <div class="form-group">
          <div class="modal__sign-up-birth">
            <input type="hidden" id='bday' name='bday' class="form-control">
            <select class="modal__sign-up-select" name="bday_day" id="bday_day">
              <option value>Jour</option>
              <!-- <option value="1">1</option>
              <option value="2">2</option> -->
              <?php for ($day = 1; $day <= 31; $day++){ ?>
                <option value="<?php echo $day ?>"><?php echo $day ?></option>
              <?php } ?>
              
            </select>
            <select class="modal__sign-up-select" name="bday_month" id="bday_month">
              <option value>Mois</option>
              <option value="01">Janvier</option>
              <option value="02">Février</option>
              <option value="03">Mars</option>
              <option value="04">Avril</option>
              <option value="04">Mai</option>
              <option value="06">Juin</option>
              <option value="07">Juillet</option>
              <option value="08">Août</option>
              <option value="09">Septembre</option>
              <option value="10">Octobre</option>
              <option value="11">Novembre</option>
              <option value="12">Decembre</option>
            </select>
            <select class="modal__sign-up-select" name="bday_year" id="bday_year" >
              <option value="years">Année</option>

              <?php for ($year = 2006; $year > 1900; $year--){ ?>
                <option value="<?php echo $year ?>"><?php echo $year ?></option>
              <?php } ?>
            </select>

            <a href="#/" class="modal__user-info-icon tip">
              <img src="/themes/tastytheme/src/images/information.png" alt="information" />
              <p>
                Vous devez avoir 13 ans ou plus pour faire partie du programme Primes et Délices.
Pourquoi demandons-nous votre date de naissance?
Nous l’utilisons pour vous envoyer une carte d’anniversaire et pour fournir du contenu pertinent à nos membres.


              </p>
            </a>
          </div>
          <span class="help-block err_bday"></span>
        </div>

          <div class="modal__sign-up-gender">
            <select class="modal__sign-up-select-gender" name="gender">
              <option value>Sexe</option>
              <option value="M">Homme</option>
              <option value="F">Femme</option>
              <option value="O">Autre</option>
            </select>
          </div>
        <?php }?>
        
          <div class="modal__sign-up-select-checkbox <?php print( $language == 'en-us'? '': '' ); ?>">
            <label id="optin" for="optin" style="display:none;">Checkbox</label>
            <input type="checkbox" name="checkbox" id="optin">
            <input type="hidden" name="optin-canada" id="optin-canada" value="0">
            <?php if ($language == 'en-ca' || $language == 'en-us' || $language == 'es-us') {?>
            <p class="modal__checkbox-text"><?php print(translate("Yes! Sign me up to Tasty Rewards<sup>TM</sup> by email so I never miss out on exciting updates, offers or  contests  (and I have the option to unsubscribe at any time).", $language) ); ?> </p>

            <?php } elseif ($language == 'fr-ca') {?>
            <p class="modal__checkbox-text"> Inscrivez-moi! J’aimerais recevoir par courriel des informations concernant Primes et Délices. Ainsi, je ne raterai aucune nouveauté ou offre ni aucun concours (et je pourrai me désabonner à n’importe quel moment, si je le désire). </p> 
          <?php } ?>
          </div>
          <div class="modal__sign-up-select-button">
            <!--<input class="modal__sign-up-button" type="submit" value="<?php print(translate("Create my account", $language) ); ?>"> -->
            
             <button class="g-recaptcha modal__sign-up-button" data-sitekey="6LfHi6QUAAAAAOmu4l357IDLsLFuhXLbeKpHG9XZ" data-callback="captchaSubmit" data-badge="inline"> <?php print(translate("Create my account", $language) ); ?> <span class="glyphicon glyphicon-spin"></span></button>
          </div>

          <div class="modal__sign-up hidden">
            Already have an account? <a class="modal__sign-up-link" href="#/">Sign in</a>
          </div>
          <div class="modal__sign-up legal-copy">
          <?php if ($language == 'en-ca' || $language == 'fr-ca' ) {?>
            <span class="contact-us"><?php print(translate("Contacting Us:", $language) ); ?> </span>  <br>
            
            <?php print(translate('PepsiCo Canada<br> 5550 Explorer Drive, 8th Floor, Mississauga, ON L4W 0C3<br> <a class="tasty-site" target="_blank" href="https://www.tastyrewards.ca">www.tastyrewards.ca</a>', $language) ); ?>
            <br>
            <br>
            <?php print(translate('Your personal information will be collected and used in accordance with our <a class="privacy-policy" target="_blank" href="http://www.pepsico.ca/en/Privacy-Policy.html"> Privacy Policy</a>.', $language) ); ?>
                    <?php } else {?>
                          <br>

            <?php
                  $privacy_policy = "https://www.pepsico.com/legal/privacy";
                  $terms_conditions = "https://www.pepsico.com/legal/terms-of-use";
                  $about_our_ads = "https://policy.pepsico.com/aboutads.htm";
                  if ($language == 'es-us'){
                    $privacy_policy = "https://www.tastyrewards.com/es-us/politica-de-privacidad";
                    $terms_conditions = "https://www.tastyrewards.com/es-us/condiciones-de-uso";
                    $about_our_ads = "https://www.tastyrewards.com/es-us/politica-en-materia-de-publicidad";
                  }
             ?>

        <?php print(translate("To learn more about how we use your information, please read PepsiCo's", $language) ); ?> <a class="privacy-policy" target="_blank" href="<?php print($privacy_policy); ?>"> <?php print(translate("Privacy Policy", $language) ); ?></a>, <a class="privacy-policy" target="_blank" href="<?php print($terms_conditions); ?>"><?php print(translate("Terms & Conditions", $language) ); ?></a> and <a class="privacy-policy" target="_blank" href="<?php print($about_our_ads); ?>"><?php print(translate("About our Ads", $language) ); ?></a>.
            
            <?php } ?>
          </div>

        </form>
      </div>
    </div>

  </header>
  <div class="row snackperksreg-confirm hidden" >
          <div class="col-xs-12">
            <?php if ($language == 'en-us'){ ?>
              <h2>You are officially signed up for PepsiCo Tasty Rewards!</h2>
              <p>Soon you’ll be receiving e-mails with exclusive PepsiCo updates. Check your inbox and enjoy the Tasty life.</p>
            <!-- <p><a href="https://www.savingstar.com/brands/fritolay?utm_source=FritoLay&utm_medium=FLRegistrationConfirmationPage&utm_campaign=Wave2Jul1" target="_blank">GET YOUR FIRST PERK</a></p> -->
          <?php } else { ?>
             <div class="subscriber hidden">
                  <h2><?php print(translate("You’re all set!", $language) ); ?></h2>
                  <p><?php print(translate('Thanks for joining the Tasty Rewards™ crew. Get ready for tasty surprises coming your way! <br> Also, keep an eye out for content and ads tailored to your interests! Check your settings or opt-out at any time <a href="https://www.tastyrewards.com/en-ca/my-account"> here</a>', $language) ); ?>
                     
                  </p>
               
             </div>
             <div class="member hidden">
                  <h2><?php print(translate("Thanks for signing up! Just one more thing…", $language) ); ?></h2>
                  <p><?php print(translate("Don’t forget to <a href='https://www.tastyrewards.com/en-ca/my-account'> subscribe</a> to our Tasty Rewards™ emails so you can stay in the loop with exciting members-only offers and updates. <br><br> Plus, keep an eye out for content and ads tailored to your interests! Check your settings or opt-out at any time <a href='https://www.tastyrewards.com/en-ca/my-account'> here</a>", $language) ); ?> 
                     
                  </p>
               
             </div>
          <?php } ?>
          </div>
        </div>

 <script>

 /**********************************************
  *** PASSWORD VIEW
  * **********************************************/


 $(document).on('click', '.toggle-password', function() {
     if ($(".toggle-password").hasClass("hide-password")){
         $(".toggle-password").removeClass("hide-password");
         $("#edit-pass").attr("type", "password");
     }else{
         $(".toggle-password").addClass("hide-password");
         $("#edit-pass").attr("type", "text");
     }
 });

 $(document).on('click', '.sign_in', function() {
     $(".toggle-password").removeClass("hide-password");
     $("#edit-pass").attr("type", "password");
 });


 $(document).on('click', '.sign_up', function() {
     $(".toggle-password-signup").removeClass("hide-password");
     $("#password").attr("type", "password");
 });

 $(".toggle-password-signup").click(function(){
     if ($(".toggle-password-signup").hasClass("hide-password")){
         $(".toggle-password-signup").removeClass("hide-password");
         $("#password").attr("type", "password");
     }else{
         $(".toggle-password-signup").addClass("hide-password");
         $("#password").attr("type", "text");
     }
 });


 /**********************************************
  *** END PASSWORD VIEW
  **********************************************/

  var zipcode_validation = false;  

  $(".modal__sign-up-button").click(function(event){
          event.preventDefault();
          //window.submitRegistration();
          
      });  
  
  function fillBirthdayOptions(){
    var start = 1900;
    var end = 2006;
    var options = "<option value>Year</option>";
    for(var year = end ; year >=start; year--){
      options += '<option value="' + year + '">'+ year +'</option>';
    }
    document.getElementById("bday_year").innerHTML = options;

    options = "<option value>Day</option>";
    for(var day = 1 ; day <=31; day++){
      options += '<option value="' + day + '">'+ day +'</option>';
    }
    document.getElementById("bday_day").innerHTML = options;
  }

  function isValidEmailAddress(emailAddress) {
          var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
          return pattern.test(emailAddress);
      };

  function inlineValidation(){
    $("#bday_day, #bday_month, #bday_year").focusout(function() {
        if ( $(this).val() === "") {
            $(this).removeClass("success")
        }
        else{
            $(this).addClass("success")
            $(this).removeClass("has-error")
        }

        if ($("#bday_day").val() !== "" && $("#bday_month").val() !== "" && $("#bday_year").val() !== ""  ){
                addSuccess($(this),true);
                clearErrorMsg('bday');
        }    

        if ($("#bday_day").val() === "" || $("#bday_month").val() === "" || $("#bday_year").val() === ""  ){
                addSuccess($(this),false);
        }
        
        
    });
  }

  function clearErrorMsg(field, success = 0){
        $(".err_"+field).html('');
        $('#' + field).removeClass('has-error')
        if (success){
          $('#' + field).addClass('success')
        }
      } 

  function addSuccess(obj,isSuccess){
        if (isSuccess ){
            obj.parents(".form-group").addClass("success");
            obj.parents(".form-group").removeClass("has-error");
        }
        else{
            obj.parents(".form-group").addClass("has-error");
            obj.parents(".form-group").removeClass("success");
        }
    }



  function toggle_optin_value(){
    $("#optin").click(function(){ 
      if ($("#optin-canada").val() == 1 ){
        $("#optin-canada").val(0);
      } else {
        $("#optin-canada").val(1);
      }
    });
  }


  function page_ready(){
    toggle_optin_value();
    facebook_iframe();
    // fillBirthdayOptions();
    inlineValidation();

    var error_translations = {
      'Please enter a valid email' : 'Introduzca una dirección de correo electrónico válida',
      'Please enter a valid first name' : 'Introduzca un nombre válido',
      'Please enter a valid last name' : 'Introduzca un apellido válido',
      'Please enter a valid postal code' : 'Introduzca un código postal válido',
      'Please enter a valid password, the password must contain at least 8 chars, one Upper and Lower case' : 'Introduzca una contraseña válida, la contraseña debe contener al menos 8 caracteres, una mayúscula y una minúscula',
      'Please enter a valid birthdate' : 'Introduzca una fecha de nacimiento válida',
    };


    function translate_error(msg){
      if (window.language == 'es-us'){
        if (msg in error_translations){
          return error_translations[msg];
        }
      } 
        return msg;
    }
      

      function validateSignUpForm(){
        var is_valid = true
        var msg = 'Please enter a valid '
        var field = ''
        if ($("#email").val() == '') {field = 'email' ; $("#" + field).addClass('has-error'); 
            $(".err_"+field).html(translate_error("Please enter a valid email") );   
            is_valid = false;
          }
          // if ($("#email").val() == '') {field = 'email' ; $("#" + field).addClass('has-error'); $(".err_"+field).html(msg + field);   is_valid = false;}
            if ($("#firstname").val() == ''){
              field = 'firstname' ; 
              $("#" + field).addClass('has-error'); 
              $(".err_"+field).html(translate_error(msg + "first name") );   
              is_valid = false;
           }
          if ($("#lastname").val() == ''){field = 'lastname' ; $("#" + field).addClass('has-error'); $(".err_"+field).html( translate_error( msg + "last name") );   
            is_valid = false;
          }
          if ($("#postalcode").val() == ''){field = 'postalcode' ; $("#" + field).addClass('has-error'); 
            $(".err_"+field).html( translate_error( msg + "postal code") );   
            is_valid = false;
          }

          if ($("#password").val() == ''){ field = 'password' ; $("#" + field).addClass('has-error'); 
            $(".err_"+field).html( translate_error( msg + field + ", the password must contain at least 8 chars, one Upper and Lower case") );   
            is_valid = false;
          }      
          if ($("#bday_day").val() == ''){field = 'bday' ; $("#" + field).parent().addClass('has-error'); 
            $(".err_bday").html( translate_error( msg + "birthdate") );   

            is_valid = false;
          }
          if ($("#bday_month").val() == ''){field = 'bday' ;  $("#bday_month").addClass('has-error'); 
            $(".err_bday").html( translate_error( msg + "birthdate") );  
            is_valid = false; 
          }
          if ($("#bday_year").val() == ''){field = 'bday' ; $("#bday_year").addClass('has-error'); 
            $(".err_bday").html(msg + "birthdate");  
            is_valid = false; 
          }
          // if ($("#gender").val() == ''){field = 'gender';  $("#gender").addClass('has-error'); $(".err_"+field).html(msg + "gender"); is_valid = false; }

          return is_valid
      }
      
      //language variable coming from twig (registration.html.tgiw)
      $("#casl").val($('.casl').html());
      
      window.submitRegistration = function submitRegistrationForm() {
         clearError();
         $("#bday").val($("#bday_year").val() + '-' + $("#bday_month").val() + '-' + $("#bday_day").val());
         let bday = $("#bday").val();
         
         $("#grecaptcharesponse").parent(".form-group").removeClass("has-error");
         
         // if (grecaptcha.getResponse() == ""){
         //      $("#grecaptcharesponse").parent(".form-group").addClass("has-error");
         //      scrollToError();
         //      return false;
         // }

         var validForm = validateSignUpForm();
         if (validForm === false){
          return false
         }

         // $("#grecaptcharesponse").val(grecaptcha.getResponse());
         var language =  $("#language").val();  
         $(".btnregister span").addClass('glyphicon-refresh');

         var form_data = { 'bday' : $('#bday').val(), 
                           'email' : $('#email').val() , 
                           'language' : $('#language').val() , 
                           'firstname': $('#firstname').val() , 
                           'lastname': $('#lastname').val() , 
                           'source': $('#source').val() , 
                           'optin': $('#optin').val() , 
                           'optin-canada': $('#optin-canada').val() , 
                           'postalcode': $('#postalcode').val() , 
                           'grecaptcharesponse': $('#grecaptcharesponse').val() , 

                           'iframe-form': '1' , 
                           // 'grecaptcharesponse': grecaptcharesponse , 
                           'gender': $('#gender').val()  , 
                           'password': $('#password').val()
                         };
         $.ajax({
            url:"/" + language + "/pepsi/register/ajaxaction",
            // url:"/pepsi/register/ajaxaction",
            type: "POST",
            data:  form_data, // $('#register-form').serialize(),
            success:function(result) {
                data = JSON.parse(result)
                console.log(data);
                if (data.status){
                    
                    // START: tracking the registration on signup: SubscriptionMember / SubscriptionNewsletter
                    var event_value = "";
                    if(jQuery('#optin').is(':checked')){
                        // event_value = "SubscriptionMember";
                        // window.dataLayer = window.dataLayer || [];
                        // window.dataLayer.push({
                        //     'event': event_value
                        // });
                        // console.log(event_value);

                        event_value = "SubscriptionNewsletter";
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push({
                            'event': event_value
                        });
                        console.log(event_value);
                        $('.snackperksreg-confirm .subscriber').removeClass('hidden')
                    }else{
                        event_value = "SubscriptionMember";
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push({
                            'event': event_value
                        });
                        console.log(event_value);
                        $('.snackperksreg-confirm .member').removeClass('hidden')
                    }
                    // END: tracking the registration on signup

                    // window.location.href = data.route;
                    $('header').addClass('hidden');
                    $('.snackperksreg-confirm').removeClass('hidden')
                }
                else {
                    $('.has-error').children('.help-block').html('');
                    $('.has-error').removeClass('has-error');
                                      
                    //refresh the token
                    $('#csrfToken').val(data.token);
                    clearError();
                    $.each(data.errors, function(field, msg) {
                        $("#"+field).parents(".form-group").removeClass("success");
                        if (field == 'bday') {
                          $("#"+field).parent().addClass('has-error');
                          $(".err_"+field).html(msg);
                        }
                        else {
                          $(".err_"+field).html(msg);
                          $("#"+field).parent().not('form').addClass('has-error');
                        }
                    });
                    grecaptcha.reset();
                    $(".btnregister span").removeClass('glyphicon-refresh');
                    scrollToError();
                }
            }, error: function(result){
              console.log(result);
            }
          });
          
      }
      clearError();
      
      //Clear error msg and label 
      function clearError() {
          $(".has-error").each(function() {
              $(this).removeClass('.has-error');
              
          });
      }
      
      function scrollToError() {

          var errorelem = $(".has-error").first();
          if (errorelem.offset() == undefined){
            return;
          }
          var errortop = errorelem.offset().top - $(".navbarcontainer").height();

          //console.log(errortop);
          
          $('html, body').animate({
              scrollTop: errortop
          }, 500);
          //focusinput = errorelem.children('input');
          //focusinput.blur().focus().val(focusinput.val());
      }
              
      
      /* inline Validation */
      
      $("#bday_day, #bday_month, #bday_year").focusout(function() {
          if ( $(this).val() === "") {
              $(this).removeClass("success")
          }
          else{
              $(this).addClass("success")
          }

          if ($("#bday_day").val() !== "" && $("#bday_month").val() !== "" && $("#bday_year").val() !== ""  ){
                  addSuccess($(this),true);
                  clearErrorMsg('bday');
          }    

          if ($("#bday_day").val() === "" || $("#bday_month").val() === "" || $("#bday_year").val() === ""  ){
                  addSuccess($(this),false);
          }
          
          
      });  

      
      
      
      $("form input, form select").focusout(function() {
          var nameid = $(this).attr('id');
          //firstname| lastname | email | confirm_email | password | confirm_password | bday_day | bday_month | bday_year | postalcode
          if (nameid === 'firstname' || nameid === 'lastname' || nameid === 'password' || nameid === 'confirm_password' ) {
              if ($(this).val().length === 0 ){
                  addSuccess($(this),false);
              }
              else{
                  addSuccess($(this),true);
                  clearErrorMsg(nameid)
              }
          }
          if (nameid === 'email' || nameid === 'confirm_email' ){
              if (isValidEmailAddress($(this).val())) {
                  addSuccess($(this),true);
                  clearErrorMsg(nameid)
              }
              else{
                  addSuccess($(this),false);
                  if ($(this).val() !== '' ){  clearErrorMsg(nameid) }
              }
          }
          if (nameid === 'confirm_email') {
              if (isValidEmailAddress($(this).val()) && $(this).val() === $("#email").val()  ){
                  addSuccess($(this),true);
                  clearErrorMsg(nameid)
              }
              else{
                  addSuccess($(this),false);
              }
          }
          
          if (nameid === 'postalcode') {
              if (isGoodPostalCode($(this).val())){
                  addSuccess($(this),true);
                  clearErrorMsg(nameid)
              }
              else{
                  addSuccess($(this),false);
              }
          }
          if (nameid === 'password') {
              if (isGoodPassword($(this).val())){
                  addSuccess($(this),true);
              }
              else{
                  addSuccess($(this),false);
              }
          }
          
          if (nameid === 'confirm_password') {
              if ($(this).val() === $("#password").val() && $(this).val().length > 0  ){
                  addSuccess($(this),true);
              }
              else{
                  addSuccess($(this),false);
              }
          }
      });
      
      function addSuccess(obj,isSuccess){
          if (isSuccess ){
              obj.parents(".form-group").addClass("success");
              obj.parents(".form-group").removeClass("has-error");
          }
          else{
              obj.parents(".form-group").addClass("has-error");
              obj.parents(".form-group").removeClass("success");
          }
      }
      
      
      
      function isGoodPostalCode(postalcode){
        if (zipcode_validation === false && postalcode != ''){
          return true;
        }
          var regex = new RegExp(/^[ABCEGHJKLMNPRSTVXY]\d[ABCEGHJKLMNPRSTVWXYZ]( )?\d[ABCEGHJKLMNPRSTVWXYZ]\d$/i);
          if (regex.test(postalcode))
              return true;
          else return false;
      }
      
      function isGoodPassword(password){
          var regex = new RegExp(/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{8,16}$/);
          if (regex.test(password))
              return true;
          else return false;
      }
      /* end inline Validation */
      
  }
  
  </script>


</body>

</html>