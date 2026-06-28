var tools = {

    /*************************************
     * rootFont();
     * 动态计算根元素(HTML)的font-size
     *
     *************************************/
    rootFont: function() {
        var doc = document;
        var win = window;
        var docEl = doc.documentElement,
        resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
        recalc = function () {
            var viewportWidth = docEl.clientWidth || win.innerWidth || 750;
            var clientWidth = Math.min(viewportWidth, 750);
            if (!clientWidth) return;
            docEl.style.fontSize = 40 * (clientWidth / 750) + 'px';
            if ( typeof(page) != "undefined" && $.isFunction(page.show)) {
                page.show();
                $('.warp').fadeIn('fast');
            } else{
                $('.warp').fadeIn('fast');
            }
        };

        win.addEventListener(resizeEvt, recalc, false);
        win.addEventListener('resize', recalc, false);
        win.addEventListener('pageshow', recalc, false);
        if (win.visualViewport) {
            win.visualViewport.addEventListener('resize', recalc, false);
        }
        doc.addEventListener('DOMContentLoaded', recalc, false);
        recalc();
    }()
}



//弹框
function tanceng(msg)
{
    layer.open({
        content: msg,
        btn: '我知道了'
    });
}


function tancengone(obj)
{
	var str = $(obj).attr('data-txt') ;
	tanceng(str) ;
}

