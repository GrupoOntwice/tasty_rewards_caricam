(function ($, window) {
	$(document).ready(function() {
		if ($('#contest').length) {
			console.log(" help modal ");
			var lang = $('html')[0].lang;
			var helpBackdrop = $('#need-help-backdrop');
			var helpModal = $('#need-help-modal');
			var safari = $('#need-help-safari');
			var chrome = $('#need-help-chrome');
			var helpClose = $('#need-help-close');
			var helpContent = $('#need-help-content');
	
			var safariContent = ``;
			var chromeContent = ``;

			switch (lang) {
				case 'fr':
					safariContent = `
						<h2>
							Comment activer les cookies dans Safari (Mac):
						</h2>

						<ol>
							<li>Accédez au menu déroulant Safari.</li>
							<li>Sélectionnez préférences.</li>
							<li>Cliquez sur Confidentialité dans le panneau du haut.</li>
							<li>Sous «Bloquer les cookies», sélectionnez l'option «Jamais».</li>
							<li>Pour une sécurité accrue, une fois que vous avez fini d'utiliser le site, veuillez redéfinir le paramètre de confidentialité sur «Toujours».</li>
						</ol>

						<h2>Comment activer les cookies dans Safari (iPhone / iPad):</h2>

						<ol>
							<li>Depuis votre écran d'accueil, ouvrez vos paramètres.</li>
							<li>Faites défiler vers le bas et sélectionnez Safari.</li>
							<li>Sous Confidentialité et sécurité, désactivez «Empêcher le suivi intersite» et «Bloquer tous les cookies».</li>
						</ol>
					`;
					chromeContent = `
						<h2>
							Comment activer les cookies dans Google Chrome (Mac):
						</h2>

						<ol>
							<li>Ouvrez les préférences de Chrome, cliquez sur Paramètres, puis sur Afficher les paramètres avancés.</li>
							<li>Sous Confidentialité, cliquez sur Paramètres de contenu.</li>
							<li>Assurez-vous que "Bloquer les cookies tiers et les données de site" n'est pas coché.</li>
							<li>Si votre navigateur ne figure pas dans la liste ci-dessus, veuillez consulter les pages d'aide de votre navigateur.</li>
						</ol>
					`;
					break;
				case 'es-us':
					safariContent = `
						<h2>
							Cómo habilitar las cookies en Safari (Mac):
						</h2>
			
						<ol>
							<li>Ve al menú desplegable de Safari.</li>
							<li>Selecciona Preferencias.</li>
							<li>Haz clic en Privacidad en el panel superior.</li>
							<li>En "Bloquear cookies" selecciona la opción "Nunca".</li>
							<li>Para aumentar la seguridad, una vez que termines de usar el sitio, vuelve a cambiar la configuración de Privacidad a Siempre.</li>
						</ol>
			
						<h2>Cómo habilitar las cookies en Safari (iPhone/iPad):</h2>
			
						<ol>
							<li>En la pantalla de inicio, abre Ajustes.</li>
							<li>Desplázate hacia abajo y selecciona Safari.</li>
							<li>En Privacidad y Seguridad, desactiva "Evitar el seguimiento cruzado de sitios" y "Bloquear todas las cookies".</li>
						</ol>
					`;
					chromeContent =  `
						<h2>
							Cómo habilitar las cookies en Google Chrome (Mac):
						</h2>
			
						<ol>
							<li>Abre las preferencias de Chrome, haz clic en Configuración y luego en Configuración avanzada.</li>
							<li>En Privacidad, haz clic en Configuración de contenidos.</li>
							<li>Asegúrate de que la opción "Bloquear las cookies de terceros y los datos del sitio" no esté seleccionada.</li>
							<li>Si tu navegador no aparece en la lista anterior, consulta las páginas de ayuda de tu navegador.</li>
						</ol>
					`;
					break;	
				case 'en': 
				case 'en-us':		
				default: 
					safariContent = `
						<h2>
							How to enable cookies in Safari (Mac):
						</h2>
			
						<ol>
							<li>Go to the Safari drop-down menu.</li>
							<li>Select Preferences.</li>
							<li>Click Privacy in the top panel.</li>
							<li>Under "Block cookies" select the option "Never."</li>
							<li>For increased security, once you have finished using the site, please change the Privacy setting back to "Always".</li>
						</ol>
			
						<h2>How to enable cookies in Safari (iPhone/iPad):</h2>
			
						<ol>
							<li>From your home screen, open your Settings.</li>
							<li>Scroll down and select Safari.</li>
							<li>Under Privacy & Security, turn off "Prevent Cross-Site Tracking" and "Block All Cookies".</li>
						</ol>
					`;
					chromeContent =  `
						<h2>
							How to enable cookies in Google Chrome (Mac):
						</h2>
			
						<ol>
							<li>Open Chrome preferences, click on Settings, then Show Advanced Settings.</li>
							<li>Under Privacy, click on Content Settings.</li>
							<li>Make sure "Block third-party cookies and site data" is not checked.</li>
							<li>If your browser is not listed above, please refer to your browser's help pages.</li>
						</ol>
					`;
					break;	
			}
	
			function closeHelpModal() {			
				helpModal.hide();
				helpBackdrop.hide();
			}
	
			function handleHelpModalContent(type) {
				helpContent.empty();
				type === 'chrome' ?
					helpContent.append(chromeContent) :
					helpContent.append(safariContent);
				helpBackdrop.show();
				helpModal.show();
			}
	
			if (safari.length) {
				safari.on('click', function() {
					handleHelpModalContent('safari');
				});
			}
	
			if (chrome.length) {
				chrome.on('click', function() {
					handleHelpModalContent('chrome');
				});
			}
	
			helpClose.on('click', closeHelpModal);
			helpBackdrop.on('click', closeHelpModal);
		}

	});

}(jQuery, window));/*end of file*/

