(function ($, Drupal, window) {
    console.log('SSO BAM')

    $(".ssoLogin").click(function(e){
        //var _url = "/moLogin"
        var _url = "/ssologinbackground.html"
        var win  =   PopupCenter(_url, "windowname1", 500, 600); 
        console.log("login");
        var n = 0;
        var pollTimer   =   window.setInterval(function() { 
            console.log(win)
            if (win.closed ) {
                window.clearInterval(pollTimer);
                window.location.reload();
            }
            try {
                if (win.document.URL.indexOf("ssobackground.html") >= 0) {
                    window.clearInterval(pollTimer);
                    win.close();
                    window.location.reload();
                }

            } catch(e) {
            }
            n++;
        }, 300);
        return false;
    });
    
}(jQuery, Drupal, window));

function PopupCenter(url, title, w, h) {
  var userAgent = navigator.userAgent,
      mobile = function() {
        return /\b(iPhone|iP[ao]d)/.test(userAgent) ||
          /\b(iP[ao]d)/.test(userAgent) ||
          /Android/i.test(userAgent) ||
          /Mobile/i.test(userAgent);
      },
      screenX = typeof window.screenX != 'undefined' ? (window.screenX == -8? 0: window.screenX) : window.screenLeft,
      screenY = typeof window.screenY != 'undefined' ? (window.screenY == -8? 0: window.screenY) : window.screenTop,
      outerWidth = typeof window.outerWidth != 'undefined' ? window.outerWidth : document.documentElement.clientWidth,
      outerHeight = typeof window.outerHeight != 'undefined' ? window.outerHeight : document.documentElement.clientHeight - 22,
      targetWidth = mobile() ? null : w,
      targetHeight = mobile() ? null : h,
      V = screenX < 0 ? window.screen.width + screenX : screenX,
      left = parseInt(V + (outerWidth - targetWidth) / 2, 10),
      right = parseInt(screenY + (outerHeight - targetHeight) / 2.5, 10),
      features = [];
  if (targetWidth !== null) {
    features.push('width=' + targetWidth);
  }
  if (targetHeight !== null) {
    features.push('height=' + targetHeight);
  }
  features.push('left=' + left);
  features.push('top=' + right);
  features.push('scrollbars=1');

  var newWindow = window.open(url, title, features.join(','));

  if (newWindow.focus) {
    newWindow.focus();
  }

  return newWindow;
}