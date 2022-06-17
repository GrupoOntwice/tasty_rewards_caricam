"use strict";

// window.is_winner = 1; // [0 - Lose, 1 - Win]
// window.landing_page_url = 'https://www.google.com/';
// window.confirmation_url = window.location.href;
window.is_live = window.for_fun === 1 ? 0 : 1; //[0 - testing, 1 - Live]
window.islive = window.is_live;
//window.for_fun = 1;

var width = 649;
var height = 1152;
var scaleFactor = 1;
var lang = window.langcode; //en,fr

var orntn = "portrait";

var mode = 'normal'; //practise, normal, chester
var score = 0;
var chesterScore = 0;

var fadeOutTime = 500;
var fadeInTime = 500;

var practiseDelay = 1000;
var playerDelay = 1000;
var chesterDelay = 1000;

var gameAudio = true;

var bgmusic = undefined;
var chesterBGM = undefined;
var gameBGM = undefined;
var resultBGM = undefined;

var parentStr = '';
var scaleMode = 3;

var w = window.innerWidth;
var h = window.innerHeight;

var os = getMobileOS();

orntn = getOrientation(w,h);

if(orntn === "landscape")
{
    width = 1152;
    height = 649;
    scaleFactor = 1;

    parentStr = 'gamediv';

    if(width > 1152){
        scaleMode = 0;
    }


    if(os === 'iOS'){
        height = window.innerHeight+120;
        scaleFactor = window.innerHeight/649;
        width = 1152*scaleFactor;
    }

}

if(orntn === "portrait" && os === 'iOS'){
    height = window.innerHeight+120;
    scaleFactor = window.innerHeight/1152;
    width = 649*scaleFactor;
}


let gameConfig = {

    type:Phaser.CANVAS, /*renderer type.Phaser.CANVAS, Phaser.HEADLESS, or Phaser.WEBGL*/
    
    _parent: parentStr, /* The div element id that will contain the game canvas. If undefined the game canvas is appended to the document body*/

    scale:
    {

      mode: scaleMode,
      /* Automatically center the canvas within the parent.
      CENTER_BOTH or CENTER_HORIZONTALLY or CENTER_VERTICALLY or NO_CENTER
      */
      autoCenter: Phaser.Scale.CENTER_BOTH
    },
    audio: {
        disableWebAudio: true
    },
    /*game physics configuration*/
    physics:
    {
        default: 'arcade', /* arcade or matter*/
     
		arcade:{
			gravity:{ y: 0 },
			debug: false  /* for showing physics*/
		}
	},
    backgroundColor:0x000000, /*The background color of the game canvas. default black.*/
    width:width, /*width of game in pixels*/
    height:height /*height of game in pixels*/
};

var game = new Phaser.Game(gameConfig);

/*adding scenes to the game */

game.scene.add("boot",bootState); /*for loading preloader assets*/
game.scene.add("preload",preloadState); /*for loading game assets and displaying loading percentage*/
game.scene.add("start",startState); /* start screen */
game.scene.add("help",helpState); /* help screen */

game.scene.add("game",gameState); /* the game scene */
game.scene.add("zoom",zoomState); /* the game scene */
// comment out problematic code 

game.scene.add("timeup",timeupScreen); /* the game scene */

game.scene.add("screen3",screen3);
game.scene.add("screen5",screen5);
game.scene.add("screen7",screen7);
game.scene.add("win",winState);
game.scene.add("loose",looseState);
game.scene.add("prize",prizeState);
game.scene.add("end",endState);

game.scene.start("boot");

window.focus();

function getOrientation(width,height)
{
    var str = 'portrait';

    if(os === 'iOS'){
        str = 'portrait';
    }else if(os === 'Android'){
        str = 'portrait';
    }else if(width > height){
        str = 'landscape';
    }

    return str;

}

/* for detection os type */

function getMobileOS() {

    var userAgent = navigator.userAgent || navigator.vendor || window.opera;

      // Windows Phone must come first because its UA also contains "Android"
    if (/windows phone/i.test(userAgent)) {
        return "Windows Phone";
    }

    if (/android/i.test(userAgent)) {
        return "Android";
    }

    // iOS detection from: http://stackoverflow.com/a/9039885/177710
    if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
        return "iOS";
    }

    return "unknown";
}
