var JQuery = $;

(function($) {

$.schoolBell = {}

$.schoolBell.app = null;
    
$.schoolBell.bootstrap = function() {
	$.schoolBell.app = new $.schoolBell.Router(); 
	Backbone.history.start({pushState: true});
};



}) (JQuery);