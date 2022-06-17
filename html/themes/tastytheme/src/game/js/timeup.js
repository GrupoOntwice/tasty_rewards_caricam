"use strict";

var timeupScreen = function(game){};

timeupScreen.prototype = {

    init:function () {

        Common.initialize(this);

    },

    create:function(){

        this.cameras.main.fadeIn(fadeInTime, 0, 0, 0);

        if(gameBGM){
            gameBGM.setVolume(0.2);
        }

        this.addScreen();

        this.time.delayedCall(2000,()=>{

            if(gameBGM){
                gameBGM.setVolume(0.09);
            }

            this.cameras.main.fadeOut(fadeOutTime, 0, 0, 0);
            this.cameras.main.once(Phaser.Cameras.Scene2D.Events.FADE_OUT_COMPLETE, (cam, effect) => {
                this.scene.start('screen7');
            })

        },null,this);
        
    },

    addScreen:function () {

        var timeup_txt = 'Timeup_enp';

        if(lang === 'fr'){
            timeup_txt = 'Timeup_frp';
        }

        this.bg = Common.addSprite(0,0,'bg1');
        this.bg.setOrigin(0);

        this.heading = Common.addSprite(this.cX,this.cY,timeup_txt);
        //this.heading.setScale(0.75);

        this.addColorButtons();

        if(orntn === 'landscape') { this.setLandscape(); }

    },

    addColorButtons:function () {

        var xD = 200;
        var yD = 80;

        this.red_button = Common.addButtonR(this.cX-xD,1152-yD,'red_button');
        this.green_button = Common.addButtonR(this.cX,1152-yD,'green_button');
        this.blue_button = Common.addButtonR(this.cX+xD,1152-yD,'blue_button');

    },

    setLandscape:function () {

        var lratio = 649/1152;

        if(lang === 'fr'){
            this.heading.setTexture('Timeup_frl');
        }else{
            this.heading.setTexture('Timeup_enl');
        }

        this.bg.setTexture('bgl2');
        this.bg.displayWidth = this.cX*2;
        this.bg.displayHeight = this.cY*2;

        this.heading.x = this.cX;
        this.heading.y = this.cY;
        this.heading.setScale(0.5);

        this.red_button.visible = false;
        this.blue_button.visible = false;
        this.green_button.visible = false;

    },

};
