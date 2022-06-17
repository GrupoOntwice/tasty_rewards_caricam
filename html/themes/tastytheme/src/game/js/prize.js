"use strict";

var prizeState = function(game){};

prizeState.prototype = {

    init:function () {

        Common.initialize(this);

    },

    create:function(){

        this.cameras.main.fadeIn(fadeInTime, 0, 0, 0);

        this.addWin();

    },

    addWin:function () {

        var finger_Button = 'Finger_Button';
        var headingStr = 'Prize_Text';
        var heading2Str = '';
        var titlePos = [220, 380];
        var btnpos = 10;
        var cheetah = 'Cheetah_04';
        var cheetahy = 1027;

        if(lang === 'fr'){
            finger_Button = 'Bonne_Chance_fr';
            headingStr = 'Practice_Text3_fr';
            titlePos = [220, 335];
            btnpos = 30;
        }

        if(lang === 'fr'){
            if(window.is_live == 1 && score > chesterScore){
                headingStr = 'Merry_Go_Text1_fr';
                heading2Str = 'Practice_Text2_fr';
                cheetah = 'Cheetah_02';
            }
        }

        this.bg = Common.addSprite(0,0,'bg1_01');
        this.bg.setOrigin(0);

        this.heading = Common.addSprite(this.cX,titlePos[0],headingStr);
        this.heading.setOrigin(0.5,0);

        if(heading2Str != ''){
            this.heading2 = Common.addSprite(this.cX,titlePos[1],heading2Str);
            this.heading2.setOrigin(0.5,0);
        }

        this.fingerButton = Common.addButtonR(this.cX,this.cY+btnpos,finger_Button);
        this.fingerButton.on('pointerdown',this.onFingerClick,this);

        this.cheetah = Common.addSprite(this.cX*2,cheetahy,cheetah);
        this.cheetah.setOrigin(1,1);

        this.addColorButtons();

        if(orntn === 'landscape') { this.setLandscape(); }

    },

    onFingerClick:function () {

        var ew = 'en-ca/contest/cheetoscarnival/congrats/4064?';
        var fw = 'fr-ca/contest/cheetoscarnival/congrats/4064?';
        var el = 'en-ca/contest/cheetoscarnival/confirm';
        var fl = 'en-ca/contest/cheetoscarnival/confirm';

        var link = ew;

        if(score > chesterScore){
            if (lang === 'en'){
                link = ew;
            }else if(lang === 'fr'){
                link = fw;
            }
        }else{
            if (lang === 'en'){
                link = el;
            }else if(lang === 'fr'){
                link = fl;
            }
        }

        // var linktoopen = window.confirmation_url + "/" + link;

        window.location.href =  window.confirmation_url
        //window.setTimeout(function(){ window.open(linktoopen,'_blank'); }, 2000);

        //this.cameras.main.fadeOut(fadeOutTime, 0, 0, 0);
        //this.cameras.main.once(Phaser.Cameras.Scene2D.Events.FADE_OUT_COMPLETE, (cam, effect) => {
        //    this.scene.start('end');
        //})

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
        var scl = 0.8;

        if(lang == 'fr'){
            scl = 0.70;
        }

        this.bg.setTexture('bgl2');
        this.bg.displayWidth = this.cX*2;
        this.bg.displayHeight = this.cY*2;

        this.heading.x = this.cX/2+20;
        this.heading.y = this.cY-150;
        this.heading.setScale(scl);

        if(this.heading2){
            this.heading2.x = this.cX/2+20;
            this.heading2.y = 245;
            this.heading2.setScale(scl);
        }

        this.fingerButton.x = this.cX/2+20;
        this.fingerButton.y = this.cY+140;
        this.fingerButton.setScale(0.8);

        this.cheetah.x = this.cX*2-90;
        this.cheetah.y = 578;
        this.cheetah.setScale(0.8);

        this.red_button.visible = false;
        this.blue_button.visible = false;
        this.green_button.visible = false;

    },

};
