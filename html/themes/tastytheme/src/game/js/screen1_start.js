"use strict";

var startState = function(game){};

startState.prototype = {

    init:function () {

        //console.log(this.sys.game.device.os.desktop);

        Common.initialize(this);

    },

    create:function(){

        this.cameras.main.fadeIn(fadeInTime, 0, 0, 0);

        if(bgmusic === undefined){
            bgmusic = this.sound.add('bgmusic');
            bgmusic.loop = true;
        }

        this.playSound();

        this.bg = Common.addSprite(0,0,'bg1');
        this.bg.setOrigin(0);

        this.container = Common.addContainer(this.cX,this.cY);

        this.letsPlayBG = this.add.sprite(0,210,'LetsPlayBG');
        this.letsPlayBG.setOrigin(0.5,0);
        this.container.add(this.letsPlayBG);

        this.cheetos_carnival = this.add.sprite(0,0,'Cheetos_Carnival');
        this.container.add(this.cheetos_carnival);

        this.letsPlayText = this.add.sprite(0,366,'LetsPlayText');
        this.container.add(this.letsPlayText);

        this.letsPlayBG.setInteractive(new Phaser.Geom.Rectangle(-20,90,360,140),Phaser.Geom.Rectangle.Contains);
        this.letsPlayBG.on('pointerdown',this.onButtonClick,this);

        this.en_Button = Common.addButtonR(590,1060,'En_Button');
        this.en_Button.on('pointerdown',this.onEnClick,this);

        this.fr_Button = Common.addButtonR(590,1060,'FR_Button');
        this.fr_Button.on('pointerdown',this.onFRClick,this);

        this.fr_Button.visible = false;

        if(orntn === 'landscape') { this.setLandscape(); }

        if(lang === 'fr'){
            this.onEnClick();
        }

    },

    onEnClick:function () {

        lang = 'fr';
        this.en_Button.visible = false;
        this.fr_Button.visible = true;

        this.setFrench();

    },

    onFRClick:function () {

        lang = 'en';
        this.en_Button.visible = true;
        this.fr_Button.visible = false;

        this.playSound();

        this.cameras.main.fadeOut(fadeOutTime, 0, 0, 0);
        this.cameras.main.once(Phaser.Cameras.Scene2D.Events.FADE_OUT_COMPLETE, (cam, effect) => {
            this.scene.start('start');
        });

    },

    onButtonClick:function () {

        if(this.sys.game.device.os.desktop === false){
            this.scale.startFullscreen();
        }

        this.playSound();

        this.cameras.main.fadeOut(fadeOutTime, 0, 0, 0);
        this.cameras.main.once(Phaser.Cameras.Scene2D.Events.FADE_OUT_COMPLETE, (cam, effect) => {
            this.scene.start('help');
        });
        
    },

    setFrench:function () {

        this.playSound();

        var cheetos_Carnival = 'Cheetos_Carnival';

        if(lang === 'fr'){
            cheetos_Carnival = 'Cheetos_Carnival_fr';
        }

        this.cheetos_carnival.setTexture(cheetos_Carnival);
        this.letsPlayText.setTexture('LetsPlayText_fr');

    },

    setLandscape:function () {

        var lratio = 649/1152;

        this.bg.setTexture('bgl1');
        this.bg.displayWidth = this.cX*2;
        this.bg.displayHeight = this.cY*2;

        this.container.x = this.cX;
        this.container.y = this.cY;
        this.container.setScale(0.65);

        this.en_Button.x = 1006;
        this.en_Button.y = 582;

        this.fr_Button.x = this.en_Button.x;
        this.fr_Button.y = this.en_Button.y;

    },

    playSound:function () {

        if(gameAudio){
            if(bgmusic.isPlaying === false){
                bgmusic.play();
            }
        }

    },

};
