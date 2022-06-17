"use strict";

var screen5 = function(game){

};

screen5.prototype = {

  init:function () {

    if(bgmusic !== undefined){ bgmusic.mute = false; }

    Common.initialize(this);
  },

  create:function()
  {
    this.cameras.main.fadeIn(fadeInTime, 0, 0, 0);
    
    gameBGM.setVolume(1);
    this.addScene5();

  },
  addScene5:function ()
  {
    var doIt_Button = 'DoIt_Button';
    var shots_text = 'Real_Text';

    if(lang === 'fr')
    {
      doIt_Button += '_fr';
      shots_text += '_fr';
    }

    this.bg = Common.addSprite(0,0,'alpha_gamebg');
    this.bg.setOrigin(0);

    this.heading = this.add.sprite(0,-50,shots_text);

    this.button = Common.addButton(0,200,doIt_Button);
    this.button.on('pointerdown',this.letsDoItClick,this);

    this.container = Common.addContainer(this.cX,this.cY);
    this.container.add(this.heading);
    this.container.add(this.button);

    this.cannon = Common.addSprite(this.cX,1027,'cannon');
    this.cannon.setOrigin(0.5,1);
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
  letsDoItClick:function () {

    mode = "normal";
    this.scene.start('game');

  },
  setLandscape:function () {

    var lratio = 649/1152;

    if(lang === 'fr')
    {
      this.heading.setTexture('Real_Text_frl');
    }

    this.bg.setTexture('bgl2');
    this.bg.displayWidth = this.cX*2;
    this.bg.displayHeight = this.cY*2;

    this.container.x = this.cX;
    this.container.y = this.cY;
    this.container.setScale(0.65);

    this.red_button.visible = false;
    this.blue_button.visible = false;
    this.green_button.visible = false;
  },

};
