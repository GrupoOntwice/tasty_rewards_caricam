"use strict";

var gameState = function(game){};

gameState.prototype = {

    init:function(){

        Common.initialize(this);

        gameState.obj = this;

        this.bulletSpeed = 1000;

        this.tSpeed = 2.5 * scaleFactor; /*speed of target objects */
        this.mSpeed = 3 * scaleFactor;
        this.timeLimit = 15; /* time limit in seconds */

        this.gameOverFlag = false; /* becomes true when time is over */
        this.startFlag = false; /* becomes true when start button is clicked */

        this.targetSound = this.sound.add('Target');
        this.gunShotSound = this.sound.add('GunShot');

        if(mode === 'practise'){
            this.timeLimit = 10;
        }

        var os = this.sys.game.device.os;
        if(os !== 'desktop'){
            this.mSpeed = 5*scaleFactor;
        }

        //'Player/Joueur:'
        this.player1Str = 'Player:';
        this.player2Str = 'Chester:';

        if(lang === 'fr'){
            this.player1Str = 'Joueur:';
        }

        //this.player1Str = '1:';
        //this.player2Str = '2:';

    },

    chesterTmeCalbak: function(){
        this.chesterTurn();
        this.timer2.reset({ 
            delay: Phaser.Math.Between(300,800), 
            callback: this.chesterTmeCalbak, 
            callbackScope: this, 
            repeat: 1
        });
    },

    create:function() {

        if(mode != 'chester'){

            if(gameBGM === undefined){
                gameBGM = this.sound.add('game_music');
                gameBGM.loop = true;
            }

            gameBGM.setVolume(0.1);

            if(gameBGM.isPlaying === false){
                gameBGM.play();
            }

        }

        //Reset score
        if(mode === 'normal' || mode === 'practise'){
            score = 0;
        }

        this.setScreen(); /*for adding BG and Game Objects*/

        this.addUI();

        this.startGame();

        this.cannon = Common.addSprite(this.cX,1027,'cannon');
        this.cannon.setOrigin(0.5,1);

        this.hand = Common.addSprite(this.cX,this.cY*2,'hand');
        this.hand.setOrigin(0.5,1);
        this.hand.visible = false;

        if(mode === 'chester'){

            this.tSpeed = 2.5 * scaleFactor;//5 * scaleFactor;
            this.mSpeed = 3 * scaleFactor; //6 * scaleFactor;
            this.hand.visible = true;

            var cheterTimeConfig = {
                delay: 500,
                callback: this.chesterTmeCalbak,
                callbackScope: this,
                startAt: 0
            };
            this.timer2 = this.time.addEvent(cheterTimeConfig);

        }

        if(orntn === 'landscape') { this.setLandscape(); }

    },

    setScreen:function () {

        /*for creating top row targets*/
        var y1 = 388;
        var y2 = 582;
        var y3 = 784;

        if(orntn === "landscape"){
            y1 = 240;
            y2 = 345;
            y3 = 450;
        }

        var red_Line = 'Red_Line';
        var green_Line = 'Green_Line';
        var blue_Line = 'Blue_Line';

        if(orntn !== "landscape"){
            red_Line = 'RedPlank_01';
            green_Line = 'GreenPlank_01';
            blue_Line = 'BluePlank_01';
        }

        /* addSprite - A custom reusable method for adding image to canvas*/

        this.bg = Common.addSprite(0,0,'gamebg');
        this.bg.setOrigin(0,0);

        /*A Group is used to create, manipulate, similar Game Objects.*/

        this.bulletGroup = this.add.group();

        this.targetGroup_1 = this.add.group(); /* top row targets group */
        this.createtEntries(this.targetGroup_1,y1,false,'red');

        this.redLine = Common.addSprite(this.cX,400,red_Line);

        this.targetGroup_2 = this.add.group(); /* middle row targets group */
        this.createtEntries(this.targetGroup_2,y2,true,'green');

        this.greenLine = Common.addSprite(this.cX,this.cY+15,green_Line);


        this.targetGroup_3 = this.add.group(); /* bottom row targets group */
        this.createtEntries(this.targetGroup_3,y3,false,'blue');

        this.blueLine = Common.addSprite(this.cX,814,blue_Line);

        this.tEntries_1 = this.targetGroup_1.children.entries; /*tEntries_1 - array of top targets */
        this.tEntries_2 = this.targetGroup_2.children.entries;
        this.tEntries_3 = this.targetGroup_3.children.entries;

        this.rowY1 = this.tEntries_1[0].y; /* TOP/RED target sprite y position */
        this.rowY2 = this.tEntries_2[0].y; /* MIDDLE/GREEN target sprite y position */
        this.rowY3 = this.tEntries_3[0].y; /* BOTTOM/BLUE target sprite y position */

        this.chips = Common.addSprite(this.cX,this.cY+15,'chips1');
        this.chips.visible = false;

        this.logo = Common.addSprite(this.cX,1,'logo_desktop');
        this.logo.setOrigin(0.5,0);
        this.logo.visible = false;

    },

    addUI:function () {

        var xD = 200;
        var yD = 80;

        /* methodaddScaledButton - A custom reusable method to create a button*/

        this.red_button = Common.addButtonR(this.cX-xD,1152-yD,'red_button');
        this.green_button = Common.addButtonR(this.cX,1152-yD,'green_button');
        this.blue_button = Common.addButtonR(this.cX+xD,1152-yD,'blue_button');

        /* sprite events pointerdown,pointerup, pointerover, pointerout*/

        this.red_button.on('pointerdown',()=>{ this.onInputDown(this.red_button);},this);
        this.green_button.on('pointerdown',()=>{ this.onInputDown(this.green_button);},this);
        this.blue_button.on('pointerdown',()=>{ this.onInputDown(this.blue_button);},this);

        this.disableGameButtons();

        /* text style configuration*/

        var fontSize = '4em';
        var fontSize2 = '3em';

        if(os === 'iOS'){
            fontSize = '2.1em';
            fontSize2 = '2em'
        }

        if(orntn === 'landscape'){
            fontSize = '2.5em';
            fontSize2 = '2em'
        }

        var style = {
            fontFamily: 'Ubuntu_Regular',
            fontSize: fontSize,
            color: '#1e1b05',
            align:'center',
            stroke:'#1e1b05',
            strokeThickness:1,
            lineSpacing:0
        };


        var style2 = {
            fontFamily: 'Ubuntu_Regular',
            fontSize: fontSize2,
            color: '#1e1b05',
            align:'left',
            stroke:'#1e1b05',
            strokeThickness:1,
            lineSpacing:0
        };

        /*Text method: this.add.text(x,y,text,style)*/

        this.timer_plate = Common.addSprite(550,820,'timer_plate');

        this.timerField = this.add.text(this.timer_plate.x,this.timer_plate.y,'',style);
        this.timerField.setOrigin(0.5,0.5);
        this.showTime(this.timeLimit);

        this.progressbar_1 = this.add.sprite(20,0,'progressbar');
        this.progressbar_1.setOrigin(0,0.5);

        this.progressbar_2 = this.add.sprite(20,40,'progressbar');
        this.progressbar_2.setOrigin(0,0.5);

        this.progressbar_top1 = this.add.sprite(20,0,'progressbar_top');
        this.progressbar_top1.setOrigin(0,0.5);
        this.progressbar_top1.scaleX = 0;
        if(score > 0){
            this.progressbar_top1.scaleX += 0.05*score;
        }

        this.progressbar_top2 = this.add.sprite(20,40,'progressbar_top');
        this.progressbar_top2.setOrigin(0,0.5);
        this.progressbar_top2.scaleX = 0;

        var progress_text1 = this.add.text(10,-1,this.player1Str,style2);
        progress_text1.setOrigin(1,0.5);

        var progress_text2 = this.add.text(10,39,this.player2Str,style2);
        progress_text2.setOrigin(1,0.5);

        this.progress_container = Common.addContainer(80,800);

        this.progress_container.add(this.progressbar_1);
        this.progress_container.add(this.progressbar_2);

        this.progress_container.add(this.progressbar_top1);
        this.progress_container.add(this.progressbar_top2);

        this.progress_container.add(progress_text1);
        this.progress_container.add(progress_text2);

    },

    createtEntries:function (targetGroup,startY,_bln,id) {

        var n = 4; /* number of targets per row*/
        var startX = -5; /* first target x position */
        var scale = 1;
        var width = 125;

        if(orntn === "landscape")
        {
            n = 8;
            startX = 140;
            scale = 0.6;
            width = 74;
        }

        this.gap = (width + 100)*scaleFactor; /* gap between targets*/

        var bln = _bln;

        /* creates 1 row of targets*/
        for(var i = 0; i < n; i++)
        {
            var type = 'target';
            var radius = 54;
            var offsetX = 0;
            var offsetY = 0;

            if(bln)
            {
                type = 'cheetah';
                radius = 56;
                offsetX = 30;
                offsetY = 10;
            }

            /* addSpriteP - A custom reusable method to add physics based images to canvas*/

            var target = Common.addSpriteP(startX,startY,type);
            target.setOrigin(0.5,1);
            target.setCircle(radius); /* sets collision area as circle */
            target.body.setOffset(offsetX,offsetY);/*sets collision area x,y position*/
            targetGroup.add(target);  /*adds target object to the group*/

            /*target.id (id is a dynamic variable not phaser inbuilt)-target row type RED/GREEN/BLUE*/
            target.id = id;
            target.type = type;
            target.hitted = false;
            target.x = startX + this.gap*i;
            bln = !bln;

            target.setScale(scale*scaleFactor);
            target.sY = scale*scaleFactor;

        }

    },

    onInputDown:function (btn) {

        if(mode === 'chester'){ return; }

        this.addBullet(btn.type);

    },

    addBullet:function (type) {

        this.playSound('GunShot');

        var xpos = this.cannon.x;
        var ypos = this.cannon.y - this.cannon.height-20;

        var bullet = Common.addSpriteP(xpos,ypos,'bullet');

        bullet.setCircle(26); /* sets collision area as circle */
        bullet.body.setOffset(10,22); /*sets collision area x,y position*/
        this.bulletGroup.add(bullet);  /*adds bullet object to the bulletGroup*/

        /*bullet.type (type is a dynamic variable not phaser inbuilt)-type of bullet RED/GREEN/BLUE*/
        bullet.type = type;

        bullet.x = this.cannon.x;
        bullet.y = this.cannon.y - (this.cannon.height+10)*scaleFactor;

        /*body.velocity.x set velocity in horizontal direction
        body.velocity.y set velocity in vertical direction*/
        bullet.body.velocity.y = -this.bulletSpeed;

    },

    /* this function loops continously*/
    update:function () {

        if(this.startFlag === false || this.gameOverFlag === true){ return; }

        /*moves 1 row of target objects in x axis*/

        this.moveLeft(this.tEntries_1);

        this.moveRight(this.tEntries_2);

        this.moveLeft(this.tEntries_3);

        this.Collisions();

        this.removeBullets();

    },

    moveLeft:function (tEntries) {

        var len = tEntries.length;

        for(var i = 0; i < len; i++)
        {
            var target = tEntries[i];
            target.x -= this.tSpeed;

            /*if(orntn === 'landscape')
            {
            if(target.x < 150 || target.x > 1152-120) { target.visible = false; }
            else { target.visible = true; }
            }*/

            if(target.x < 0 - target.width/2){

                var index = i-1;

                /*shifts position horizontal end of the screen & follows
                last target of the row(if the target is first target of the row)
                */
                if(i === 0) { index = len-1;}

                /*shifts position horizontal end of the screen & follows
                left target(if the target is not first target of the row)
                */
                target.x = tEntries[index].x + this.gap;

            }

        }

    },

    moveRight:function (tEntries) {

        var len = tEntries.length;

        for(var i = 0; i < len; i++)
        {
            var target = tEntries[i];
            target.x += this.mSpeed;

            /*if(orntn === 'landscape')
            {
            if(target.x < 100 || target.x > 1152-120) { target.visible = false; }
            else { target.visible = true; }
            }*/

            if(target.x > this.gW + target.width/2){

                var index = i+1;

                /*shifts position horizontal start of the screen & follows first target of the row
                (if the target is last target of the row)*/
                if(i === len-1) { index = 0;}

                /*shifts position horizontal end of the screen & follows left target
                (if the target is not last target of the row)*/
                target.x = tEntries[index].x - this.gap;

            }
        }

    },

    removeBullets:function () {

        var len = this.bulletGroup.children.entries.length; /*number of bullets in a bullet group*/

        for(var i=0; i < len; i++)
        {
            var bullet = this.bulletGroup.children.entries[i];
            var type = bullet.type;
            var diff = 50;

            //698 501 305
            //240, 345, 450

            if(bullet.y < 0 - bullet.height){
                bullet.destroy();
                len = this.bulletGroup.children.entries.length;
            }else if(bullet.y < this.rowY1 && type === "red_button"){ /*hides bullet when bullet is above top row when red button clicked*/
                bullet.visible = false;
            }else if(bullet.y < this.rowY2 && type === "green_button"){ /* hides bullet when bullet is above middle row when green button clicked*/
                bullet.visible = false;
            }else if(bullet.y < this.rowY3 && type === "blue_button"){ /* hides bullet when bullet is above bottom row when blue button clicked*/
                bullet.visible = false;
            }

        }

    },

    Collisions:function () {

        /*overlap method checks two groups physics based sprites overlapping
        onTargetHit will trigger when a bullet overlaps with target*/

        this.physics.add.overlap(this.bulletGroup,this.targetGroup_1,this.onHit,null,this);
        this.physics.add.overlap(this.bulletGroup,this.targetGroup_2,this.onHit,null,this);
        this.physics.add.overlap(this.bulletGroup,this.targetGroup_3,this.onHit,null,this);

    },

    onHit:function(bullet,target){

        /* checks bullet type and target row*/

        if(bullet.type != target.id +"_button") { return; }
        if(target.hitted === true) { return; }

        target.hitted = true;

        this.addFx(target.x+65*scaleFactor,target.y-120*scaleFactor,target.type);

        this.playSound('Target');

        /* for hiding the target hitted by bullet*/
        this.hideByScale(target);

        if(mode === 'chester'){
            if(target.type === 'cheetah'){
                /*
                if(Math.random(1) > 0.5){
                    this.setProgressBar('cheetah',-1);
                }
                */
                this.setProgressBar('cheetah',-1);
            }else {
                this.setProgressBar('cheetah',1);
            }
        }
        if(mode !== 'chester'){
            if(target.type === 'target'){
                this.setProgressBar('target',1);
            }else {
                this.setProgressBar('target',-1);
            }
        }

        //removes bullet from the screen*/
        bullet.destroy();

    },

    addFx:function (x,y,type) {

        var str = 'minus_1';

        if(type === 'target' && mode !== 'chester'){
            str = 'plus_1';
        }else if(type === 'target' && mode === 'chester'){   
            str = 'plus_1';
        }

        var fx = this.add.sprite(x,y,str);

        var tween = this.tweens.add({
            targets: fx,
            alpha: 0.2,
            ease: 'Regular',
            duration: 800,
            delay: 0,
            onComplete:function(){
                fx.destroy();
            }
        });

    },

    setProgressBar:function (type,n) {

        if(mode === 'normal' || mode === 'practise'){
            score += n;
        }else if(mode === 'chester'){
            chesterScore += n;
            if(chesterScore <= 0){
                chesterScore = 0
            }
        }

        var bar = this.progressbar_top1;

        if(type === 'cheetah'){
            bar = this.progressbar_top2;
            bar.scaleX = 0.05 * chesterScore;
        }else{
            bar.scaleX = 0.05 * score;
        }

        if(bar.scaleX > 1){
            bar.scaleX = 1;
        }

        if(bar.scaleX < 0){
            bar.scaleX = 0;
        }
        /*
        console.log(score)
        console.log(chesterScore)
        console.log("Game: "+ mode)
        */

    },

    hideByScale:function (target) {

        var d = 200;

        /* tweens are like animation function's in phaser
        here the tween is used to hide target by scaling down verticaly
        duration: is duration of the effect/animation
        delay: delay is delay time for effect/animation
        easing: type of easing effect
        */

        var tween = this.tweens.add({
            targets: target,
            scaleY: 0,
            ease: 'Regular',
            duration: d,
            delay: 0

        });

        var scale = 1*scaleFactor;

        if(orntn === 'landscape'){
            scale = target.sY;
        }

        var tween2 = this.tweens.add({
            targets: target,
            scaleY: scale,
            ease: 'Regular',
            duration: d,
            delay: d + 1000,
            onComplete:function(){
                target.hitted = false;
            }
        });

    },

    startGame:function () {

        this.startFlag = true;

        this.enableGameButtons();

        var delay = 1000;

        if(mode === 'chester'){
            delay = 1000;
        }

        var timerConfig = {
            delay: delay,
            callback: this.timerCounter,
            callbackScope: this,
            repeat: this.timeLimit,
            startAt: 0
        };

        this.timer = this.time.addEvent(timerConfig);

    },

    chesterTurn:function(){

        var arr = ['red_button','green_button','blue_button'];

        var str = arr[Phaser.Math.Between(0,2)];

        this.hand.x = this[str].x;

        this.addBullet(str);

    },

    timerCounter:function () {

        if(this.gameOverFlag === true) { return;}

        if(this.timeLimit > 0){
            this.timeLimit--;
            /*for converting seconds to minutes:seconds(00:00)format*/
            this.showTime(this.timeLimit);
        }

        if(mode === "practise" && this.timeLimit === 0){
            
            this.timer.paused = true;

            this.gameOverFlag = true;
            this.onTimeComplete();
            return;
        }

        if(mode === "chester" && this.timeLimit === 0){
            if(this.timer2){
                this.timer2.remove()
            }
            this.timer.paused = true;
            this.gameOverFlag = true;
            this.onTimeComplete();
            return;
        }

        if(this.timeLimit === 0){
            this.gameOverFlag = true;
            this.onTimeComplete();
        }

    },


    showTime:function (t) {

        var m = Math.floor(t / 60);
        var sec = t - m * 60;

        var secStr = sec.toString();
        var minStr = m.toString();

        if(sec < 10){ secStr = '0' + sec.toString();}
        if(m < 10){ minStr = '0' + m.toString();}

        var str = minStr + ':' + secStr;

        this.timerField.text = str;

    },

    /* for enabling RED,GREEN,BLUE buttons interaction*/
    enableGameButtons:function () {

        this.red_button.setInteractive();
        this.green_button.setInteractive();
        this.blue_button.setInteractive();

    },
    /* for disabling RED,GREEN,BLUE buttons interaction*/
    disableGameButtons:function () {

        this.red_button.disableInteractive();
        this.green_button.disableInteractive();
        this.blue_button.disableInteractive();

    },

    onTimeComplete:function () {

        //Decie the winner
        if(mode === 'chester'){
            if(chesterScore > 0){
                if(score == chesterScore){
                    this.setProgressBar('chester',1)
                }
            }
        }

        this.onGameOver();

    },

    onGameOver:function (bln=true) {

        this.gameOverFlag = true;

        this.disableGameButtons();

        var n = Phaser.Math.Between(0,2);

        var nextScene = 'zoom';
        var delay = 1000;

        if(mode === 'practise'){
            nextScene = 'screen5';
            delay = practiseDelay || 1000;
            /*if(lang === 'fr')
            {
            mode = 'normal';
            nextScene = 'game';
            }*/
        }else if(mode === 'normal'){
            nextScene = 'timeup';
            delay = playerDelay || 100;
        }else if(mode === 'chester'){
            delay = chesterDelay || 100;
        }

        this.time.delayedCall(delay,()=>{

            this.stopMusic(nextScene);

            this.cameras.main.fadeOut(fadeOutTime, 0, 0, 0);
            this.cameras.main.once(Phaser.Cameras.Scene2D.Events.FADE_OUT_COMPLETE, (cam, effect) => {
                this.scene.start(nextScene);
            })

        },null,this);

    },

    stopMusic:function (nextScene) {

        if(chesterBGM !== undefined){
            chesterBGM.stop();
        }

        if(nextScene === 'zoom'){ //nextScene === 'timeup' || 
            gameBGM.stop();
        }

    },

    playSound:function (str){

        if(gameAudio){
            if(str === 'Target'){
                this.targetSound.play();
            }else if(str === 'GunShot'){
                this.gunShotSound.play();
            }
        }

    },

    setLandscape:function () {

        var lratio = 649/1152;

        this.bg.setTexture('gamebgl');
        this.bg.displayWidth = this.cX*2;
        this.bg.displayHeight = this.cY*2;

        this.logo.visible = true;
        this.chips.visible = true;

        this.greenLine.visible = true;
        this.redLine.visible = true;

        this.blueLine.visible = true;
        this.blueLine.setScale(0.65);
        this.blueLine.y = 465;

        this.redLine.y = this.cY-75;
        this.greenLine.y = this.cY+30;

        this.cannon.y = 579;

        this.hand.setScale(0.5);

        this.timer_plate.x = this.cX+100;
        this.timer_plate.y = 470;

        this.timerField.x = this.timer_plate.x;
        this.timerField.y = this.timer_plate.y;
        this.timer_plate.setScale(0.65);

        this.progress_container.x = this.cX-280;
        this.progress_container.y = 456.5;
        this.progress_container.setScale(0.65);

        var xD = 100;
        var yD = 43;

        this.red_button.setTexture('red_button_d');
        this.blue_button.setTexture('blue_button_d');
        this.green_button.setTexture('green_button_d');
        this.cannon.setTexture('cannon_d');

        this.red_button.x = this.cX-xD;
        this.red_button.y = 649-yD;

        this.green_button.x = this.cX;
        this.green_button.y = 649-yD;

        this.blue_button.x = this.cX+xD;
        this.blue_button.y = 649-yD;

        //this.red_button.setScale(0.55);
        //this.green_button.setScale(0.55);
        //this.blue_button.setScale(0.55);
        //this.cannon.setScale(0.65);

    },

};
