$(document).ready(function() {		

        // Set specific variable to represent all iframe tags.
		//var iFrames = document.getElementsByTagName('iframe');
        /*
        var iFrames = document.getElementsByClassName('iframeautoheight')

		// Resize heights.
		function iResize()
		{
			// Iterate through all iframes in the page.
			for (var i = 0, j = iFrames.length; i < j; i++)
			{
				// Set inline style to equal the body height of the iframed content.
				iFrames[i].style.height = iFrames[i].contentWindow.document.body.offsetHeight + 'px';
			}
		}
        */

		// Check if browser is Safari or Opera.
		if ($.browser.safari || $.browser.opera)
		{
			// Start timer when loaded.
			/*
            $('iframe.iframeautoheight').load(function()
				{
					setTimeout(iResize, 0);
				}
			);

			// Safari and Opera need a kick-start.
			for (var i = 0, j = iFrames.length; i < j; i++)
			{
				var iSource = iFrames[i].src;
				iFrames[i].src = '';
				iFrames[i].src = iSource;
			}
            */
		}
		else
		{
			// For other good browsers.
			$('iframe.iframeautoheight').load(function()
				{
					// Set inline style to equal the body height of the iframed content.
                    //console.log($(this).context.contentWindow.document.body.offsetHeight);
					$(this).css('height', $(this).context.contentWindow.document.body.offsetHeight + 'px');
				}
			);
		}
	}
);