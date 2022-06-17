(function ($, window) {
	$(document).ready(function() {

		$(document).scroll(function () {
			var top = $(this).scrollTop();
			// This should only be executed on the home page
			var loadAt = $(".contest__detail-wrapper").offset().top - 200;
			if (top > loadAt && $(".contest__menu-detail-wrapper").length 
				&& !window.hasLoadedClosedContest) 
			{
				window.hasLoadedClosedContest = true;

				$(".js-lazyload-contestclosed").each(function(){
					// <img class="contest__menu-detail-section-image inactive grayout" src="{{ file_url(contest.image) }}" alt="{{contest.title}}" />
					var _class = $(this).data('class');
					var _src = $(this).data('src');
					var _alt = $(this).data('alt');
					var _img = `<img class="${_class}" src="${_src}" alt="${_alt}" />`;
					$(this).after(_img);

				});

			}

			var loadOpenContestAt = $(".contest__detail-view").offset().top - 240;
			if (top > loadOpenContestAt && $(".js-lazyload-contestopen").length 
				&& !window.hasLoadedOpenContest) 
			{
				window.hasLoadedOpenContest = true;

				$(".js-lazyload-contestopen").each(function(){
					var _src = $(this).data('src');
					var _alt = $(this).data('alt');
					var _img = `<img src="${_src}" alt="${_alt}" />`;
					$(this).after(_img);

				});

			}


		});

	});

}(jQuery, window));/*end of file*/

