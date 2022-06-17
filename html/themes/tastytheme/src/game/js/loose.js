"use strict";

var looseState = function(game){};

looseState.prototype = {

    init:function () {

        Common.initialize(this);

    },

    create:function(){

        this.cameras.main.fadeIn(fadeInTime, 0, 0, 0);

        this.loose_bgm = undefined;
        this.looseEffect_sound = Common.playSound('lose_effect',false);

        this.time.delayedCall(500,()=>{

            resultBGM = Common.playSound('losing_music');

        },null,this);

        this.addLoose();

        if(lang !== 'nfr'){

            this.time.delayedCall(8000,()=>{

                //resultBGM.stop();

                this.cameras.main.fadeOut(fadeOutTime, 0, 0, 0);
                this.cameras.main.once(Phaser.Cameras.Scene2D.Events.FADE_OUT_COMPLETE, (cam, effect) => {
                    if(window.for_fun == 1 && window.is_live == 0){
                        this.scene.start('end');
                    }else{
                        this.scene.start('prize');
                    }
                })

            },null,this);
        }

    },

    addLoose:function () {

        var practice_Text = 'Practice_Text';
        var merry_Go_Text = 'Merry_Go_Text';
        var titlePos = [240, 530];

        if(lang === 'fr'){
            practice_Text = 'Practice_Text1_fr';
            merry_Go_Text = 'Merry_Go_Text_fr';
            titlePos = [400, 340];
            if(window.for_fun == 1){
                practice_Text = 'Practice_Text1_fr';
            }
        }

        this.bg = Common.addSprite(0,0,'bg1_01');
        this.bg.setOrigin(0);

        this.heading = Common.addSprite(this.cX,titlePos[0],practice_Text);
        this.heading.setOrigin(0.5,0);

        this.heading2 = Common.addSprite(this.cX,titlePos[1],merry_Go_Text);

        if(this.sys.game.device.os.desktop){
            this.cheetah = Common.addSprite(this.cX*2,1027,'ChesterWin');
        }else{
            this.cheetah = Common.addSprite(this.cX*2,1027,'Cheetah_03');
        }
        this.cheetah.setOrigin(1,1);

        /*this.bonneChanceButton = Common.addButtonR(this.cX,650,'Bonne_Chance_fr');
        this.bonneChanceButton.on('pointerdown',()=>{});

        if(lang === 'en')
        {
            this.bonneChanceButton.visible = false;
        }*/

        if(orntn === 'landscape') { this.setLandscape(); }

    },

    setLandscape:function () {

        var lratio = 649/1152;

        var titlePos = [150, 400];
        if(lang === 'fr'){
            titlePos = [290, 240];
        }

        this.bg.setTexture('bgl2');
        this.bg.displayWidth = this.cX*2;
        this.bg.displayHeight = this.cY*2;

        this.heading.x = this.cX/2+40;
        this.heading.y = titlePos[0];
        this.heading.setScale(0.8);

        this.heading2.x = this.cX/2+40;
        this.heading2.y = titlePos[1];
        this.heading2.setScale(0.8);

        /*this.bonneChanceButton.x = this.cX/2+40;
        this.bonneChanceButton.y = 510;*/

        this.cheetah.x = this.cX*2-75;
        this.cheetah.y = 580;
        this.cheetah.setScale(0.8);

    },

};
