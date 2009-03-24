// var digi_slideshow_conf = [ uid, number, interval, duration, [
//   [ 'Bild11', 'Bild12', 'Bild13' ],
//   [ 'Bild21', 'Bild22', 'Bild23' ]
// ]

function digi_slideshow(conf){

  var me = this;// create self reference
  
  me.current = 0; // current gallery
  me.next = 1; // next gallery
  me.hidden = 1; // hidden box
  me.uid = 'tx-digislideshow-pi1-'+conf[0];
  me.number = conf[1];
  me.interval = conf[2];
  me.duration = conf[3];
  me.galleries = conf[4];
  me.options = {property:'opacity',duration:me.duration};
  me.box = [$(me.uid+'-1'),$(me.uid+'-2')];
  me.img = [$$('#'+me.uid+'-1 img'),$$('#'+me.uid+'-2 img')];
  me.fx = [new Fx.Tween(me.box[0],me.options),new Fx.Tween(me.box[1],me.options)];
  me.fx[0].addEvent("onComplete",function(){ me.step(); });
  me.fx[0].set('visibility','visible');// show box 0
  me.fx[1].set('visibility','hidden');// hide box 1
  
  me.init = function(){
    me.load(0,0); // init box 0 with gallery 0
    me.load(1,1); // init box 1 with gallery 1
    me.toggle.delay(me.interval,me);
  };
  
  me.toggle = function(){// toggle boxes visibility
    me.fx[1-me.hidden].start('1','0');//fadeout
    me.fx[me.hidden].start('0','1');//fadein
    /*me.debug('Fading...');/**/
  };
  
  me.step = function(){// advance gallery
    me.fx[1-me.hidden].set('visibility','hidden');//hide
    me.fx[me.hidden].set('visibility','visible');//show
    me.hidden = ( me.hidden + 1 ) % 2;//toggle
    me.current = ((me.current+1)%me.galleries.length);//cycle
    me.next = ((me.current+1)%me.galleries.length);//cycle
    me.load( me.hidden, me.next );
    me.toggle.delay(me.interval,me);
  };
  
  me.load = function(box,gallery){// load gallery into box
    /*me.debug('Loaded gallery '+gallery+' into box '+box+'. Showing gallery '+me.current+' in box '+(1-me.hidden)+'.');/**/
    for( var i = 0 ; i < me.number ; i++ )if(me.galleries[gallery][i]){// if image present
      me.img[box][i].src = me.galleries[gallery][i];// load image
    }else{// if image missing
      me.img[box][i].src = 'typo3conf/ext/digi_slideshow/res/digitage.gif';// load default image
    }
  };
  
  me.debug = function(msg){
    var now = new Date();
    window.status = '['+('0'+now.getHours()).substr(-2)+':'+('0'+now.getMinutes()).substr(-2)+':'+('0'+now.getSeconds()).substr(-2)+'] '+msg;
  };
  
  me.init();
  
};
