"use strict";

var helpState = function(game){};

helpState.prototype = {

    init:function () {

        Common.initialize(this);

    },

    create:function()
    {   
        this.cameras.main.fadeIn(fadeInTime, 0, 0, 0);

        if(bgmusic === undefined){
            bgmusic = this.sound.add('bgmusic');
            bgmusic.loop = true;
        }

        var gotIt_buttonStr = 'GotIt_button';
        var howToStr = 'HowTo';

        if(lang === 'fr'){
            gotIt_buttonStr += '_fr';
            howToStr += '_fr';
        }

        this.bg = Common.addSprite(0,0,'bg1');
        this.bg.setOrigin(0);

        this.tiger_01 = Common.addSprite(this.cX,200,'tiger_01');
        this.tiger_01.setOrigin(0.5,0);

        this.container = Common.addContainer(this.cX,870);

        this.howTo = this.add.sprite(0,0,howToStr);
        this.container.add(this.howTo);

        this.gotIt_button = Common.addButton(0,198,gotIt_buttonStr);
        this.container.add(this.gotIt_button);

        this.gotIt_button.on('pointerdown',this.onButtonClick,this);

        if(orntn === 'landscape') { this.setLandscape(); }

    },

    onButtonClick:function () {

        this.cameras.main.fadeOut(fadeOutTime, 0, 0, 0);
        this.cameras.main.once(Phaser.Cameras.Scene2D.Events.FADE_OUT_COMPLETE, (cam, effect) => {
            this.scene.start('screen3');
        })

    },

    setLandscape:function () {

        var lratio = 649/1152;

        var cheetah_TicketStr = 'Cheetah_Ticket_L';

        if(lang === 'fr'){
            cheetah_TicketStr = 'Cheetah_Ticket_frl';
        }

        this.bg.setTexture('bgl2');
        this.bg.displayWidth = this.cX*2;
        this.bg.displayHeight = this.cY*2;

        this.container.x = 380;
        this.container.y = 336;
        this.container.setScale(0.8);

        this.howTo.visible = false;

        this.gotIt_button.y = 210;

        this.tiger_01.setTexture(cheetah_TicketStr);
        this.tiger_01.setOrigin(0.5,1);
        this.tiger_01.x = this.cX+15;
        this.tiger_01.y = this.cY*2-70;
        this.tiger_01.setScale(0.55);

    },

    playSound:function () {

        if(gameAudio){
            if(bgmusic.isPlaying === false){
                bgmusic.play();
            }
        }
    },

};
