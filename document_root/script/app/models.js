(function($) {
    
$.schoolBell.Plan = Backbone.RelationalModel.extend({
        urlRoot: '/api/plan',
        idAttribute: 'ID',
        relations: [{
            type: Backbone.HasMany,
            key: 'bell',
            relatedModel: '$.schoolBell.Bell',
            reverseRelation: {
                key: 'plan_id',
                includeInJSON: 'ID',
            },
	    includeInJSON: false,
        }]
    });

$.schoolBell.Bell = Backbone.RelationalModel.extend({
        urlRoot: '/api/bell',
        idAttribute: 'ID',
        relations: [{
            type: Backbone.HasMany,
            key: 'alarm',
            relatedModel: '$.schoolBell.Alarm',
            reverseRelation: {
                key: 'bell_id',
                includeInJSON: 'ID',
            },
	    includeInJSON: false,
        }]
    });

$.schoolBell.Alarm = Backbone.RelationalModel.extend({
        urlRoot: '/api/alarm',
        idAttribute: 'ID',
    });

}) (JQuery);