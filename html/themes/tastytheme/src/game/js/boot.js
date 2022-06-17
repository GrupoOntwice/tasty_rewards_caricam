"use strict";

var bootState = function(game){};

bootState.prototype = {

    /*in preload function all game assets are loaded like images, sounds, animations */
    preload:function(){

        var d = new Date();
        var t = d.getTime(); /*here t is used to avoid cache of browser*/

        /* baseURL to locate assets folder*/
        // this.load.baseURL = "assets/";
        this.load.baseURL = "/themes/tastytheme/src/game/assets/";

        /* this.load.image(key,path);* is used to load images */
        this.load.image('loadingbar_bg','ui/loadingbar_bg.png?v='+t);
        this.load.image('loadingbar','ui/loadingbar.png?v='+t);

    },

    /*create function is invoked after all the assets are loaded*/
    create:function(){
        /* scene.start(scene_name) is used to naviagte between scenes */
        this.scene.start('preload');
    }

};
