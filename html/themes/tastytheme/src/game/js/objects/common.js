"use strict";

var Common = {

  initialize:function (parent) {

      this.parent = parent;

      var width = 649;
      var height = 1152;

      if(orntn === "landscape")
      {
        width = 1152;
        height = 649;
      }

      parent.cX = width/2; /* horizontal center position */
      parent.cY = height/2; /* vertical center position */
      parent.gW = parent.game.config.width; /* game width */
      parent.gH = parent.game.config.height; /* game height */

  },
  addButton:function (x,y,key) {

    var btn = this.parent.add.sprite(x,y,key);
    btn.type = key;
    btn.setInteractive();
    return btn;

  },
  addButtonR:function (x,y,key) {

    var btn = this.addSprite(x,y,key);
    btn.type = key;
    btn.setInteractive();
    return btn;

  },
  addSprite:function (x,y,key) {

    /*A Sprite Game Object is used for adding adding image to the canvas.
    sprites can also be used for animations

    syntax: this.add.sprite(x,y,key)

    x - x position of sprite on Canvas
    y - y position of sprite on Canvas
    key - loaded image key

    .setOrigin - for setting anchor/center of sprite,
    syntax: mySprite.setOrigin(x,y);
    (0,0) - top left, (1,0) - top right
    (0,1) - bottom left, (1,1) - bottom right
    0.5,0.5) - center
    default (0.5,0.5),

    .setScale - for scaling the sprite
    syntax: mySprite.setScale(x,y);
    */

    var spr = this.parent.add.sprite(x,y,key);
    spr.setScale(scaleFactor);
    spr.x *= scaleFactor;
    spr.y *= scaleFactor;

    return spr;

  },
  addSpriteP:function (x,y,key) {

    /*physics.add.sprite - Is for adding physics based image to the Canvas
    mySprite.body.velocity.x - To velocity on x-direction
    mySprite.body.velocity.y - To velocity on x-direction
    mySprite.setImmovable(true) - To make sprite uneffected by collisions of other physics objects
    mySprite.setCircle(radius) - Sets this physics body to use a circle for collision instead of a rectangle.
    mySprite.body.setOffset(x,y) - Sets the body offset.
    */

    var spr = this.parent.physics.add.sprite(x,y,key);
    spr.setScale(scaleFactor);
    spr.x *= scaleFactor;
    spr.y *= scaleFactor;

    return spr;

  },
  addContainer:function (x,y) {

    var container = this.parent.add.container(x*scaleFactor,y*scaleFactor);
    container.setScale(scaleFactor);

    return container;

  },
  playSound:function (str,loop=true) {

    if(gameAudio){
      var bgmusic = this.parent.sound.add(str);
      bgmusic.loop = loop;
      bgmusic.play();
    }

   return bgmusic;
 },


};
