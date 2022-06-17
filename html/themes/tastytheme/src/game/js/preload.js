"use strict";

var preloadState = function(game){};

preloadState.prototype = {

    /*init function is invoked automatically*/
    init:function(){
        
        this.cX = this.game.config.width*0.5;
        this.cY = this.game.config.height*0.5;

        var loadingbar_bg = this.add.sprite(this.cX-411*0.5,this.cY,"loadingbar_bg");
        loadingbar_bg.setOrigin(0,0.5);
        loadingbar_bg.width = 411;

        this.loadingbar = this.add.sprite(this.cX-411*0.5,this.cY,"loadingbar");
        this.loadingbar.setOrigin(0,0.5);

        this.loadingbar.setScale(0,0.5);

    },

    /*in preload function all game assets are loaded like images, sounds, animations */
    preload:function(){

        var d = new Date();
        var t = d.getTime(); /*here t is used to avoid cache of browser*/

        /* baseURL to locate assets folder*/
        this.load.baseURL = "/themes/tastytheme/src/game/assets/";
        this.load.crossOrigin = "anonymous";

        /*this.load.image(key,path);* is used to load images */

        this.load.image('retry_button','ui/retry_button.png?v='+t);
        this.load.image('game_over','ui/game_over.png?v='+t);

        this.load.image('bg1','bg1.png?v='+t);
        this.load.image('bg1_01','bg1_01.png?v='+t);
        this.load.image('alpha_gamebg','alpha_gamebg.png?v='+t);
        this.load.image('gamebg','gamebg.png?v='+t);
        this.load.image('zoom_bg','zoom_bg.png?v='+t);
        this.load.image('z2','z2.png?v='+t);

        this.load.image('GreenPlank_01','GreenPlank_01.png?v='+t);
        this.load.image('RedPlank_01','RedPlank_01.png?v='+t);
        this.load.image('BluePlank_01','BluePlank_01.png?v='+t);

        //scene 1 start
        this.load.image('Cheetos_Carnival','Screen_1/Cheetos_Carnival.png?v='+t);
        this.load.image('LetsPlayBG','Screen_1/LetsPlayBG.png?v='+t);
        this.load.image('LetsPlayText','Screen_1/LetsPlayText.png?v='+t);
        this.load.image('En_Button','Screen_1/En_Button.png?v='+t);

        //scene 2 help
        this.load.image('GotIt_button','Screen_2/GotIt_button.png?v='+t);
        this.load.image('HowTo','Screen_2/HowTo.png?v='+t);
        this.load.image('tiger_01','Screen_2/tiger_01.png?v='+t);

        //scene 3
        this.load.image('start_button','Screen_3/start_Button.png?v='+t);
        this.load.image('skip_button','Screen_3/skip_Button.png?v='+t);
        this.load.image('shots_text','Screen_3/shots_text.png?v='+t);

        // Game Scene
        this.load.image('bullet','gameScreen_4/bullet.png?v='+t);

        this.load.image('progressbar','gameScreen_4/progressbar_01.png?v='+t);
        this.load.image('timer_plate','gameScreen_4/timer_plate.png?v='+t);
        this.load.image('progressbar_top','gameScreen_4/progressbar_top.png?v='+t);

        this.load.image('target','gameScreen_4/target.png?v='+t);
        this.load.image('cheetah','gameScreen_4/cheetah.png?v='+t);

        this.load.image('blue_button','gameScreen_4/blue_button.png?v='+t);
        this.load.image('red_button','gameScreen_4/red_button.png?v='+t);
        this.load.image('green_button','gameScreen_4/green_button.png?v='+t);

        this.load.image('cannon','gameScreen_4/cannon.png?v='+t);
        this.load.image('hand','gameScreen_4/hand.png?v='+t);

        this.load.image('plus_1','gameScreen_4/plus_1.png?v='+t);
        this.load.image('minus_1','gameScreen_4/minus_1.png?v='+t);

        //scene 5

        this.load.image('DoIt_Button','Screen_5/DoIt_Button.png?v='+t);
        this.load.image('Real_Text','Screen_5/Real_Text.png?v='+t);

        //scene 7

        this.load.image('chester_turn','Screen_7/chester_turn.png?v='+t);
        this.load.image('tiger_02','Screen_7/tiger_02.png?v='+t);

        //win scene 10
        this.load.image('Cheetah_02','winScreen_10/Cheetah_02.png?v='+t);
        this.load.image('Congo_Text','winScreen_10/Congo_Text.png?v='+t);
        this.load.image('Sparkle_Desktop','winScreen_10/sparkle_desktop.png?v='+t);
        this.load.image('Sparkle_Mobile','winScreen_10/sparkle_mobile.png?v='+t);
        this.load.image('cheetahsad','winScreen_10/cheetahsad.png?v='+t);

        //loose scene 11
        this.load.image('Cheetah_03','looseScreen_11/Cheetah_03.png?v='+t);
        this.load.image('Merry_Go_Text','looseScreen_11/Merry_Go_Text.png?v='+t);
        this.load.image('Practice_Text','looseScreen_11/Practice_Text.png?v='+t);
        this.load.image('ChesterWin','looseScreen_11/ChesterWin.png?v='+t);

        //prize scene 12
        this.load.image('Cheetah_04','prizeScreen_12/Cheetah_04.png?v='+t);
        this.load.image('Prize_Text','prizeScreen_12/Prize_Text.png?v='+t);
        this.load.image('Finger_Button','prizeScreen_12/Finger_Button.png?v='+t);

        //end scene 13
        this.load.image('Cheetah_05','endScreen_13/Cheetah_05.png?v='+t);
        this.load.image('ExitButton','endScreen_13/ExitButton.png?v='+t);
        this.load.image('PlayAgain_Button','endScreen_13/PlayAgain_Button.png?v='+t);

        this.load.image('Rematch_Text','endScreen_13/Rematch_Text.png?v='+t);
        this.load.image('Ribbon_Text','endScreen_13/Ribbon_Text.png?v='+t);

        this.loadAudioAssets(t);

        this.loadLandScapeAssets(t);

        this.loadFrenchAssets(t);

        /* 'progress' event to calcute loading progress*/
        this.load.on("progress",this.onLoadingProgress,this);
        
    },

    loadFrenchAssets:function(t) {

        this.load.image('FR_Button','french/Slide_1/FR_Button.png?v='+t);
        this.load.image('GotIt_button_fr','french/Slide_2/GotIt_button_fr.png?v='+t);
        this.load.image('start_button_fr','french/Slide_3/start_button_fr.png?v='+t);
        this.load.image('skip_button_fr','french/Slide_3/skip_button_fr.png?v='+t);

        this.load.image('Cheetos_Carnival_fr','french/Slide_1/Cheetos_Carnival_fr.png?v='+t);
        this.load.image('LetsPlayText_fr','french/Slide_1/LetsPlayText_fr.png?v='+t);

        //scene 2 help
        this.load.image('HowTo_fr','french/Slide_2/HowTo_fr.png?v='+t);
        this.load.image('Cheetah_Ticket_frl','french/Slide_2/Cheetah_Ticket_frl.png?v='+t);

        //scene 3 select
        this.load.image('shots_text_fr','french/Slide_3/shots_text_fr.png?v='+t);
        this.load.image('shots_text_frl','french/Slide_3/shots_text_frl.png?v='+t);

        //scene 5
        this.load.image('DoIt_Button_fr','french/Slide_5/DoIt_Button_fr.png?v='+t);
        this.load.image('Real_Text_fr','french/Slide_5/Real_Text_fr.png?v='+t);
        this.load.image('Real_Text_frl','french/Slide_5/Real_Text_frl.png?v='+t);

        //scene 7 chester turn
        this.load.image('chester_turn_fr','french/Slide_7/chester_turn_fr.png?v='+t);
        this.load.image('chester_turn_frl','french/Slide_7/chester_turn_frl.png?v='+t);

        //scene 11
        this.load.image('Congo_Text_fr','french/Slide_11/Congo_Text_fr.png?v='+t);

        //Time up
        this.load.image('Timeup_frl','timeup/timeup_fr_l.png?v='+t);
        this.load.image('Timeup_frp','timeup/timeup_fr_p.png?v='+t);

        //scene 12
        this.load.image('Practice_Text_fr','french/Slide_12/Practice_Text_fr.png?v='+t);
        this.load.image('Practice_Text1_fr','french/Slide_12/Practice_Text1_fr.png?v='+t);
        this.load.image('Practice_Text2_fr','french/Slide_12/Practice_Text2_fr.png?v='+t);
        this.load.image('Practice_Text3_fr','french/Slide_12/Practice_Text3_fr.png?v='+t);
        this.load.image('Merry_Go_Text_fr','french/Slide_12/Merry_Go_Text_fr.png?v='+t);
        this.load.image('Merry_Go_Text1_fr','french/Slide_12/Merry_Go_Text1_fr.png?v='+t);

        //scene 13
        this.load.image('Bonne_Chance_fr','french/Slide_13/Bonne_Chance_fr.png?v='+t);

        //end scene 13
        this.load.image('ExitButton_fr','french/endScreen_13/ExitButton.png?v='+t);
        this.load.image('PlayAgain_Button_fr','french/endScreen_13/PlayAgain_Button.png?v='+t);

        this.load.image('Rematch_Text_fr','french/endScreen_13/Rematch_Text.png?v='+t);
        this.load.image('Ribbon_Text_fr','french/endScreen_13/Ribbon_Text.png?v='+t);

    },

    loadLandScapeAssets:function (t) {

        this.load.image('bgl1','landscape/bgl1.png?v='+t);
        this.load.image('bgl2','landscape/bgl2.png?v='+t);
        this.load.image('bgl3','landscape/bgl3.png?v='+t);
        this.load.image('z2L','landscape/z2L.png?v='+t);


        this.load.image('chips1','landscape/chips1.png?v='+t);
        this.load.image('logo_desktop','landscape/logo_desktop.png?v='+t);

        this.load.image('gamebgl','landscape/gamebgl.png?v='+t);
        this.load.image('bg_zooml','landscape/bg_zooml.png?v='+t);

        this.load.image('Tiger_L','landscape/Tiger_L.png?v='+t);

        this.load.image('Cheetah_Ticket_L','landscape/Cheetah_Ticket_L.png?v='+t);
        this.load.image('Green_Line','landscape/Green_Line.png?v='+t);
        this.load.image('Red_Line','landscape/Red_Line.png?v='+t);
        this.load.image('Blue_Line','landscape/Blue_Line.png?v='+t);

        this.load.image('green_button_d','landscape/green_button_d.png?v='+t);
        this.load.image('blue_button_d','landscape/blue_button_d.png?v='+t);
        this.load.image('red_button_d','landscape/red_button_d.png?v='+t);
        this.load.image('cannon_d','landscape/cannon_d.png?v='+t);

        this.load.image('Timeup_enl','timeup/timeup_en_l.png?v='+t);
        this.load.image('Timeup_enp','timeup/timeup_en_p.png?v='+t);

    },

    loadAudioAssets:function (t) {

        this.load.audio('Target','audio/Target.mp3?v='+t);
        this.load.audio('GunShot','audio/GunShot.mp3?v='+t);
        this.load.audio('Firework','audio/Firework.mp3?v='+t);

        this.load.audio('bgmusic','audio/bgmusic.mp3?v='+t);

        this.load.audio('game_music','audio/game_music.mp3?v='+t);

        this.load.audio('chester_turn','audio/chester_turn.mp3?v='+t);

        this.load.audio('win_effect','audio/win_effect.mp3?v='+t);
        this.load.audio('congrats_you_win','audio/congrats_you_win.mp3?v='+t);

        this.load.audio('lose_effect','audio/lose_effect.mp3?v='+t);
        this.load.audio('losing_music','audio/losing_music.mp3?v='+t);

        this.load.audio('drum_roll','audio/drum_roll.mp3?v='+t);

    },

    onLoadingProgress:function(per){

        /* per gives the percentage of loading between 0.0 to 1.0 */
        this.loadingbar.scaleX = per;

    },

    /*create function is invoked after all the assets are loaded*/
    create:function(){

        /* scene.start(scene_name) is used to naviagte between scenes */
        this.cameras.main.fadeOut(fadeOutTime, 0, 0, 0);
        this.cameras.main.once(Phaser.Cameras.Scene2D.Events.FADE_OUT_COMPLETE, (cam, effect) => {
            this.scene.start('start');
        })

        //start,help,screen3,screen5,screen7
        //zoom,win,loose,prize,end,game

    },

};
