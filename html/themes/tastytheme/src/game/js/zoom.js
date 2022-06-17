"use strict";

var zoomState = function(game){};

zoomState.prototype = {

    init:function(){

        Common.initialize(this);

        gameState.obj = this;

        this.player1Str = 'Player:';
        this.player2Str = 'Chester:';

        this.nextScene = "loose";

        if(lang === 'fr'){
            this.player1Str = 'Joueur:';
        }

        //this.player1Str = '1:';
        //this.player2Str = '2:';

    },

    create:function() {

        this.drum_roll_music = Common.playSound('drum_roll',false);

        this.setScreen();

        this.addUI();

        if(orntn === 'landscape') { this.setLandscape(); }

        /*
        console.log("Zoom")
        console.log(score)
        console.log(chesterScore)
        */

        if(score < 0){ score = 0; }

        if(chesterScore < 0){ chesterScore = 0; }

        var per = score * 0.05;
        var per2 = chesterScore * 0.05;

        if(per2 > 1){ per2 = 1;}
        if(per > 1){ per = 1; }

        //Code for live
        var sflag = true;
        if(window.is_live == 1){
            if(window.is_winner == 1){
                this.nextScene = 'win';
                sflag = false;
            }
        }
        
        if(sflag){
            if(score > chesterScore) {
                this.nextScene = 'win';
            }
        }

        /*
        if(this.nextScene == "win"){
            per = 0.6;
            per2 = 0.4;
        }else{
            per = 0.4;
            per2 = 0.6;
        }
        */

        var tween = this.tweens.add({
            targets: [this.progressbar_top1],
            scaleX: per,
            ease: 'Regular',
            duration: 1000,
            delay: 100,
            onComplete:this.onTweenComplete.bind(this)
        });

        var tween2 = this.tweens.add({
            targets: [this.progressbar_top2],
            scaleX: per2,
            ease: 'Regular',
            duration: 1000,
            delay: 100
        });

  },

    onTweenComplete:function () {

        var nextScene = this.nextScene;
            
        this.time.delayedCall(3000,()=>{

            this.drum_roll_music.stop();

            this.cameras.main.fadeOut(fadeOutTime, 0, 0, 0);
            this.cameras.main.once(Phaser.Cameras.Scene2D.Events.FADE_OUT_COMPLETE, (cam, effect) => {
                this.scene.start(nextScene);
            })

        },null,this);

    },

    setScreen:function () {

        var red_Line = 'Red_Line';
        var green_Line = 'Green_Line';
        var blue_Line = 'z2L';

        if(orntn !== "landscape"){
            red_Line = 'RedPlank_01';
            green_Line = 'GreenPlank_01';
            blue_Line = 'z2';
        }

        this.bg = Common.addSprite(0,0,'zoom_bg');
        this.bg.setOrigin(0,0);

        var y1 = 388-250;
        var y2 = 582-250;
        var y3 = 783-250;

        if(orntn === "landscape"){

            var upY = 185;

            y1 = 240 - upY;
            y2 = 345 - upY;
            y3 = 448 - upY;

        }

        this.targetGroup_1 = this.add.group();
        this.createtEntries(this.targetGroup_1,y1,false,'red');

        this.redLine = Common.addSprite(this.cX,388-250,red_Line);

        this.targetGroup_2 = this.add.group();
        this.createtEntries(this.targetGroup_2,y2,true,'green');

        this.greenLine = Common.addSprite(this.cX,582-250,green_Line);

        this.targetGroup_3 = this.add.group();
        this.createtEntries(this.targetGroup_3,y3,false,'blue');

        this.blueLine = Common.addSprite(0,0,blue_Line);
        this.blueLine.setOrigin(0,0);

        this.tEntries_1 = this.targetGroup_1.children.entries;
        this.tEntries_2 = this.targetGroup_2.children.entries;
        this.tEntries_3 = this.targetGroup_3.children.entries;

        this.cannon = Common.addSprite(this.cX,1027-139,'cannon');
        this.cannon.setOrigin(0.5,1);

    },

    addUI:function () {

        var xD = 200;
        var yD = 80+120;

        this.red_button = Common.addButtonR(this.cX-xD,1152-yD,'red_button');
        this.green_button = Common.addButtonR(this.cX,1152-yD,'green_button');
        this.blue_button = Common.addButtonR(this.cX+xD,1152-yD,'blue_button');

        var fontSize = '4em';
        var fontSize2 = '1.8em';

        if(os === 'iOS'){
            fontSize = '2.1em';
            fontSize2 = '1.8em'
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

        this.timer_plate = Common.addSprite(558,576,'timer_plate');

        this.timerField = this.add.text(558*scaleFactor,576*scaleFactor,'00:00',style);
        this.timerField.setOrigin(0.5,0.5);

        this.progressbar_1 = this.add.sprite(20,0,'progressbar');
        this.progressbar_1.setOrigin(0,0.5);

        this.progressbar_2 = this.add.sprite(20,40,'progressbar');
        this.progressbar_2.setOrigin(0,0.5);

        this.progressbar_top1 = this.add.sprite(3,0,'progressbar_top');
        this.progressbar_top1.setOrigin(0,0.5);
        this.progressbar_top1.scaleX = 0;

        this.progressbar_top2 = this.add.sprite(3,38.5,'progressbar_top');
        this.progressbar_top2.setOrigin(0,0.5);
        this.progressbar_top2.scaleX = 0;

        this.progress_text1 = this.add.text(0,-1,this.player1Str,style2);
        this.progress_text1.setOrigin(1,0.5);
        //this.progress_text1.visible = false;

        this.progress_text2 = this.add.text(0,39,this.player2Str,style2);
        this.progress_text2.setOrigin(1,0.5);
        //this.progress_text2.visible = false;

        this.progress_container = Common.addContainer(68,556);
        this.progress_container.add(this.progressbar_1);
        this.progress_container.add(this.progressbar_2);

        this.progress_container.add(this.progressbar_top1);
        this.progress_container.add(this.progressbar_top2);

        this.progress_container.add(this.progress_text1);
        this.progress_container.add(this.progress_text2);

        this.timer_plate.visible = false;
        this.progressbar_1.visible = false;
        this.progressbar_2.visible = false;

    },


    createtEntries:function (targetGroup,startY,_bln,id) {

        var n = 4;
        var startX = -5;
        var scale = 1;
        var width = 125;

        if(orntn === "landscape"){
            n = 8;
            startX = 140;
            scale = 0.6;
            width = 74;
        }

        this.gap = (width + 100)*scaleFactor;

        var bln = _bln;

        for(var i = 0; i < n; i++)
        {
            var type = 'target';
            var radius = 54;
            var offsetX = 0;
            var offsetY = 0;

            if(bln){
                type = 'cheetah';
                radius = 56;
                offsetX = 30;
                offsetY = 10;
            }

            var target = Common.addSpriteP(startX,startY,type);
            target.setOrigin(0.5,1);
            target.setCircle(radius);
            target.body.setOffset(offsetX,offsetY);
            targetGroup.add(target);

            target.id = id;
            target.type = type;
            target.x = startX + this.gap*i;
            bln = !bln;

            target.setScale(scale*scaleFactor);
            target.sY = scale*scaleFactor;

        }

    },

    setLandscape:function () {

        var lratio = 649/1152;

        this.bg.setTexture('bg_zooml');
        this.bg.displayWidth = this.cX*2;
        this.bg.displayHeight = this.cY*2;

        this.blueLine.displayWidth = this.cX*2;
        this.blueLine.displayHeight = this.cY*2;

        this.redLine.y = 60;
        this.greenLine.y = 165;

        this.timer_plate.visible = true;
        this.progressbar_1.visible = true;
        this.progressbar_2.visible = true;

        this.timer_plate.x = this.cX+100;
        this.timer_plate.y = 305;

        this.progressbar_top1.x = 20
        this.progressbar_top2.x = 20

        this.timerField.x = this.timer_plate.x;
        this.timerField.y = this.timer_plate.y;
        this.timer_plate.setScale(0.65);

        this.progress_container.x = this.cX-280;
        this.progress_container.y = 290;
        this.progress_container.setScale(0.65);

        this.progress_text1.visible = true;
        this.progress_text2.visible = true;

        var xD = 150;
        var yD = 43+45;

        this.red_button.x = this.cX-xD;
        this.red_button.y = 649-yD;

        this.green_button.x = this.cX;
        this.green_button.y = 649-yD;

        this.blue_button.x = this.cX+xD;
        this.blue_button.y = 649-yD;

        this.red_button.setScale(0.8);
        this.green_button.setScale(0.8);
        this.blue_button.setScale(0.8);

    },

};
