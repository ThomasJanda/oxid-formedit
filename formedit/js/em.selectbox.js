(function($) {

    $.fn.emselectbox = function(options) {
        options = $.extend({
            type:'submit'
        }, options);

        return this.each(function() {
            var emselectbox = $(this);
            emselectbox.attr('tabindex','1111');
            emselectbox.find('.emdropdownitems').attr('tabindex','1112');
            emselectbox.attr('data-keydata','');
            
            var _emselectboxtype=options.type;
            var _emselectboxopen=false;
            
            emselectbox.click(function() {
                var emselectbox = $(this);
                var open=emselectbox.attr('data-open');
                
                if(open=="")
                {
                    emselectbox.opendropdown();             
                }
                else
                {
                    emselectbox.closedropdown();
                }
            }).mouseleave(function() {
                var emselectbox = $(this);
                var open=emselectbox.attr('data-open');
                if(open!="")
                {
                    var timeout=setTimeout(function() {
                        emselectbox.closedropdown();
                    },500);
                    emselectbox.attr('data-timeout',timeout);
                }
            }).mouseenter(function() {
                var emselectbox = $(this);
                var open=emselectbox.attr('data-open');
                if(open!="")
                {
                    var timeout = emselectbox.attr('data-timeout');
                    try 
                    {
                        clearTimeout(timeout);
                    } 
                    catch (e) 
                    {
                    }
                }
            }).keypress(function (e) {
                var emselectbox = $(this);
                var timeout = emselectbox.attr('data-keytimeout');
                try 
                {
                    clearTimeout(timeout);
                } 
                catch (e) 
                {
                }
                    
                var data = emselectbox.attr('data-keydata');
                if(e.keyCode==8 && data.length>0)
                {
                    data=data.substr(0,data.length-1);
                    emselectbox.attr('data-keydata',data);
                }
                if(e.which == 32 || (48 <= e.which && e.which <= 48 + 10) || (65 <= e.which && e.which <= 65 + 25) || (97 <= e.which && e.which <= 97 + 25)) 
                {
                    var c = String.fromCharCode(e.which);
                    data = data + c;
                    emselectbox.attr('data-keydata',data);
                }
                if(e.which == 13)
                {
                    /*
                    alert("enter");
                    emselectbox.click();
                    */
                    emselectbox.find('.emdropdownitem.keyselected').click();
                }

                if(data!="")
                {
                    emselectbox.find('.emdropdownitem .emdropdownitemtitle').each(function(index) {
                        
                        var emselectbox = $(this).closest('.emselectbox');
                        var data = emselectbox.attr('data-keydata');
                        
                        var text = $(this).text();
                        
                        if(text.toLowerCase().substr(0,data.length) == data.toLowerCase())
                        {
                            emselectbox.find('.emdropdownitem').removeClass('keyselected');
                            var emdropdownitems = emselectbox.find('.emdropdownitems')
                            $(this).parent().addClass('keyselected');
                            emdropdownitems.scrollTop(emdropdownitems.scrollTop() + $(this).position().top - emdropdownitems.height()/2 +  $(this).height()/2);
                            return false;
                        }
                    }); 
                }
                else
                {
                    emselectbox.find('.emdropdownitem').removeClass('keyselected');
                }                   

                timeout=setTimeout(function() {
                    emselectbox.attr('data-keydata','');
                    emselectbox.find('.emdropdownitem').removeClass('keyselected');
                },2000);
                emselectbox.attr('data-keytimeout',timeout);
                
                return false;
            });
            

            $.fn.closedropdown = function() {
                var emselectbox=$(this);
                
                emselectbox.find('.emdropdownitems').stop().slideUp(100,function() {
                    $(this).css('height','0px');
                });
                emselectbox.removeClass('open');
                emselectbox.attr('data-open','');
                emselectbox.find('.emdropdownitem').removeClass('keyselected');
            };
            
            $.fn.opendropdown = function() {
                var emselectbox=$(this);
                
                emselectbox.find('.emdropdownitems').stop().slideDown(100,function() {
                    $(this).css('height','auto');
                    var h  = parseInt($(this).height());
                    var hm = parseInt($(this).css('max-height'));
                    if(h==hm)
                    {
                        $(this).css('overflow-y','scroll');
                    }
                    
                });
                emselectbox.addClass('open');
                emselectbox.attr('data-open','1');
            }
            
            emselectbox.find('.emdropdownitems .emdropdownitem').click(function() {
            
                emselectbox.find('.emdropdownitems .emdropdownitem').removeClass('selected');
                $(this).addClass('selected');
                emselectbox.find('.emtitle').text($(this).find('.emdropdownitemtitle').text());
                
                if($(this).find('.emdropdownitemvalue').length > 0)
                {
                    if(emselectbox.find('.emvalue').length > 0)
                    {
                        emselectbox.find('.emvalue').val($(this).find('.emdropdownitemvalue').val());  
                        if(options.type=="submit")
                            emselectbox.closest('form').submit();
                    }
                    if(options.type=="link")
                    {
                        var url=$(this).find('.emdropdownitemvalue').val();
                        var target=$(this).find('.emdropdownitemvalue').attr('data-target');
                        if(target=="_blank")
                            window.open(url);
                        else
                            window.location=url;
                    }
                }
            });
            
            emselectbox.attr('data-open','');
            emselectbox.attr('timeout','');
            
            /*wo selected drinnen ist, dann in main anzeigen*/
            if(emselectbox.find('.selected').length > 0 && emselectbox.find('.emtitle').length > 0)
            {
                emselectbox.find('.emtitle').text(emselectbox.find('.selected').text());  
                if(emselectbox.find('.selected .emdropdownitemvalue').length > 0 && emselectbox.find('.emvalue').length > 0)
                {
                    emselectbox.find('.emvalue').val(emselectbox.find('.selected .emdropdownitemvalue').val());  
                }
            }
            
            
            
            
        });
    };

})(jQuery);