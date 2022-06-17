"use strict";

var screen7 = function(game){};

screen7.prototype = {

    init:function () {

        Common.initialize(this);

    },

    create:function(){

        this.cameras.main.fadeIn(fadeInTime, 0, 0, 0);
        
        if(gameBGM){
            gameBGM.stop();
        }

        chesterBGM = Common.playSound('chester_turn');

        this.addScreen7();

        this.time.delayedCall(3000,()=>{

            mode = "chester";
            this.scene.start('game');

        },null,this);
        
    },

    addScreen7:function () {

        var chester_turnStr = 'chester_turn';
        var tiger_str = 'tiger_02';

        if(lang === 'fr'){
            chester_turnStr = 'chester_turn_fr';
        }

        if(orntn === 'landscape'){
            tiger_str = 'Tiger_L';
        }

        this.bg = Common.addSprite(0,0,'alpha_gamebg');
        this.bg.setOrigin(0);

        this.heading = Common.addSprite(this.cX,410,chester_turnStr);

        this.cannon = Common.addSprite(this.cX,1027,'cannon');
        this.cannon.setOrigin(0.5,1);
        this.addColorButtons();

        this.cheetah = Common.addSprite(this.cX,this.cY*2,tiger_str);
        this.cheetah.setOrigin(0.5,1);

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
            this.heading.setTexture('chester_turn_frl');
        }

        this.bg.setTexture('bgl2');
        this.bg.displayWidth = this.cX*2;
        this.bg.displayHeight = this.cY*2;

        this.heading.x = this.cX/2 + 80;
        this.heading.y = this.cY;
        this.heading.setScale(0.65);

        this.cheetah.x = this.cX+265;
        this.cheetah.y = this.cY*2+40;
        this.cheetah.setScale(0.55);

        this.red_button.visible = false;
        this.blue_button.visible = false;
        this.green_button.visible = false;

    },

};
