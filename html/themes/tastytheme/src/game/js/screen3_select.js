"use strict";

var screen3 = function(game){

};

screen3.prototype = {

  init:function () {

    Common.initialize(this);
  },

  create:function()
  {
    this.addScene3();

  },
  addScene3:function () {

    var start_buttonStr = 'start_button';
    var skip_buttonStr = 'skip_button';
    var shots_textStr = 'shots_text';

    if(lang === 'fr')
    {
     start_buttonStr = 'start_button_fr';
     skip_buttonStr = 'skip_button_fr';
     shots_textStr = 'shots_text_fr';
    }

    this.bg = Common.addSprite(0,0,'alpha_gamebg');
    this.bg.setOrigin(0);

    this.container = Common.addContainer(this.cX,this.cY);

    this.heading = this.add.sprite(0,-70,shots_textStr);

    this.container.add(this.heading);

    this.start_button = Common.addButton(0,190,start_buttonStr);
    this.start_button.on('pointerdown',this.onStartClick,this);

    this.skip_button = Common.addButton(0,300,skip_buttonStr);
    this.container.add(this.start_button);

    this.skip_button.on('pointerdown',this.onSkipClick,this);
    this.container.add(this.skip_button);

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
  onStartClick:function () {

    bgmusic.stop();

    mode = 'practise';
    this.scene.start('game');
  },
  onSkipClick:function () {

    bgmusic.stop();

   mode = 'normal';
   this.scene.start('game');

  },
  setLandscape:function () {

    var lratio = 649/1152;

    this.bg.setTexture('bgl3');
    this.bg.displayWidth = this.cX*2;
    this.bg.displayHeight = this.cY*2;

    this.container.x = this.cX;
    this.container.y = this.cY-10;
    this.container.setScale(0.7);

    if(lang === 'fr')
    {
      this.heading.setTexture('shots_text_frl');
    }

    this.red_button.visible = false;
    this.blue_button.visible = false;
    this.green_button.visible = false;

  },

};
