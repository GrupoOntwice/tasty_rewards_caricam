"use strict";

var winState = function(game){};

winState.prototype = {

    init:function () {

        Common.initialize(this);

    },

    create:function(){
        
        this.cameras.main.fadeIn(fadeInTime, 0, 0, 0);

        this.win_bgm = undefined;

        this.winEffect_sound = Common.playSound('win_effect',false);

        this.time.delayedCall(400,()=>{

            resultBGM = Common.playSound('congrats_you_win');

        },null,this);

        this.addWin();

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

    addWin:function () {

        var congo_Text = 'Congo_Text';

        if(lang === 'fr'){
            congo_Text = 'Congo_Text_fr';
        }

        this.bg = Common.addSprite(0,0,'bg1_01');
        this.bg.setOrigin(0);

        this.sparkle = Common.addSprite(this.cX/2+155,100,'Sparkle_Mobile');
        this.sparkle.setOrigin(0.5,0);

        this.sparkle2 = Common.addSprite(this.cX/2+155,130,'Sparkle_Desktop');
        this.sparkle2.setOrigin(0.5,0);
        this.sparkle2.visible = false

        this.heading = Common.addSprite(this.cX,430,congo_Text);

        if(this.sys.game.device.os.desktop){
            this.cheetah = Common.addSprite(this.cX*2,1127,'cheetahsad');
        }else{
            this.cheetah = Common.addSprite(this.cX*2,1027,'Cheetah_02');
        }
        this.cheetah.setOrigin(1,1);

        if(orntn === 'landscape') { this.setLandscape(); }

    },

    setLandscape:function () {

        var lratio = 649/1152;

        this.bg.setTexture('bgl2');
        this.bg.displayWidth = this.cX*2;
        this.bg.displayHeight = this.cY*2;

        this.sparkle.visible = false;

        this.sparkle2.setScale(0.53,0.63);
        this.sparkle2.x = this.cX;
        this.sparkle2.y = 100;
        this.sparkle2.visible = true;

        this.heading.x = this.cX/2+50;
        this.heading.y = this.cY;
        this.heading.setScale(0.65);

        this.cheetah.x = this.cX*1.8;
        this.cheetah.y = 577;
        this.cheetah.setScale(0.85);

    },

};
