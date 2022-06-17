"use strict";

var endState = function(game){};

endState.prototype = {

    init:function () {

        Common.initialize(this);

    },

    create:function(){

        this.cameras.main.fadeIn(fadeInTime, 0, 0, 0);

        this.addLoose();

    },

    addLoose:function () {

        var heading_img = "Rematch_Text";
        var heading2_img = "Ribbon_Text";
        var playagain_img = "PlayAgain_Button";
        var exit_img = "ExitButton";
        var pos = {
            "heading": 190,
            "heading2": 370,
            "playagain": 360,
            "exit": 580,
        };

        if(lang === 'fr'){
            heading_img = heading_img+"_fr";
            heading2_img = heading2_img+"_fr";
            playagain_img = playagain_img+"_fr";
            exit_img = exit_img+"_fr";
            pos = {
                "heading": 240,
                "heading2": 415,
                "playagain": 410,
                "exit": 590,
            };
        }

        this.bg = Common.addSprite(0,0,'bg1_01');
        this.bg.setOrigin(0);

        this.heading = Common.addSprite(this.cX,pos.heading,heading_img);
        this.heading.setOrigin(0.5,0);

        this.heading2 = Common.addSprite(this.cX,pos.heading2,heading2_img);
        this.heading2.setOrigin(0.5,0);

        this.cheetah = Common.addSprite(this.cX*2,1027,'Cheetah_04');
        this.cheetah.setOrigin(1,1);

        this.playAgainButton = Common.addButtonR(this.cX,pos.playagain,playagain_img);
        this.playAgainButton.on('pointerdown',()=>{

            mode = 'practise';
            score = 0;
            chesterScore = 0;
            resultBGM.stop();

            this.cameras.main.fadeOut(fadeOutTime, 0, 0, 0);
            this.cameras.main.once(Phaser.Cameras.Scene2D.Events.FADE_OUT_COMPLETE, (cam, effect) => {
                this.scene.start('start');
            })

        },this);

        this.exitButton = Common.addButtonR(this.cX,pos.exit,exit_img);
        this.exitButton.on('pointerdown',()=>{

            window.location.href = window.landing_page_url;

        },this);

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

        this.bg.setTexture('bgl2');
        this.bg.displayWidth = this.cX*2;
        this.bg.displayHeight = this.cY*2;

        this.heading.x = this.cX/2+20;
        this.heading.y = this.cY-180;
        this.heading.setScale(0.8);

        this.playAgainButton.x = this.cX/2 + 20;
        this.playAgainButton.y = this.cY - 40;
        this.playAgainButton.setScale(0.8);

        this.heading2.x = this.cX/2 + 20;
        this.heading2.y = this.cY - 20;
        this.heading2.setScale(0.8);

        this.exitButton.x = this.cX/2+20;
        this.exitButton.y = this.cY + 170;
        this.exitButton.setScale(0.8);

        this.cheetah.x = this.cX*2-90;
        this.cheetah.y = 578;
        this.cheetah.setScale(0.8);

        if(lang === 'fr'){
            this.playAgainButton.y = this.cY - 20;
            this.heading2.y = this.cY + 10;
        }

        this.red_button.visible = false;
        this.blue_button.visible = false;
        this.green_button.visible = false;

    },

};
