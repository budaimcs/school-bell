(function($) {

$.schoolBell.PlanCollection = Backbone.Collection.extend({
        url: '/api/plan',
        model: $.schoolBell.Plan,
	comparator: 'title'
    });


}) (JQuery);