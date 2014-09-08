(function($) {

$.schoolBell.PlanTabsView = Backbone.View.extend({
        initialize: function(){
		_.bindAll( this, 'render', 'render_plan_tabs', 'on_title_changed', 'add_new_plan', 'tab_changed', 'navigate', 'on_remove', 'show_actual' );
		this.collection.bind( 'reset', this.render); 
		this.collection.bind( 'add', this.render);
		this.collection.bind( 'remove', this.on_remove); 
		this.collection.bind( 'change:title', this.on_title_changed);
// 		this.listenTo( $.schoolBell.app, "route:plan_list", this.change_active_id );
//		this.model.bind('add', this.render_thread_summary); 
        },
	template: function( templateElement, data )
	{
		return _.template( $( templateElement ).html(), data );
	},
        render: function(){
		console.log( 'render PlanTabsView' );
		this.$el.find(".tabs").html("");
		this.collection.sort;
		this.collection.each( this.render_plan_tabs, this );
// 		this.render_plan_tabs( { attributes: { title:"+", ID:"addnew" } }, this.collection.length + 1 );
		return this;
        },
	render_plan_tabs: function( plan, key )
	{
		console.log( 'render_plan_tabs ' + this.attributes.id );
		context = plan.attributes;
		context._loop_index = key;
		context.id = this.attributes.id;
// 		context.cid = plan.cid;
		this.$el.find(".tabs").append( this.template("#plan_tab_template", context ) );
// 		this.$el.find('.tab-control').tabcontrol();
		return this;
	},
	events:
	{
		"click .plan_addnew" : "add_new_plan",
		"change .plan_title" : "on_title_changed_user",
		"tabcontrolchange .tab-control" : "tab_changed"
	},
	on_title_changed: function()
	{
		console.log( 'on_title_changed' );
		this.collection.sort();
		this.render();
	},
	add_new_plan: function()
	{
		console.log( 'add_new_plan' );
		var newmodel = new $.schoolBell.Plan({title:'Új csengetési rend',active:'0',bell:[]});
		newmodel.save( {},{success: _.bind(function(model,response,options) {
			this.collection.add( model );
			this.navigate( model.get('ID') );
			this.show_actual( model.get('ID') );
			var not = $.Notify({
				style: {background: '#008287', color: '#fff'},
				content: "Új csengetési rend létrehozva...",
// 				content: "Az új tervet létrehoztam",
				timeout: 7000,
				
			});
			
		}, this)});
		
	},
	tab_changed: function( event, frame)
	{
		id = $(frame).attr('id').match(/[0-9]*$/);
		console.log( 'tab_changed', id );
		this.navigate(id);
		
	},
	navigate: function(id)
	{
		console.log( "navigate to " + id);
		this.attributes.id = id;
		if( id != 0 )
			$.schoolBell.app.navigate("/plan/" + id );
		else
			$.schoolBell.app.navigate("");
	},
	on_remove: function(model, collection, options)
	{
		this.navigate(0);
		this.show_actual( 0 );
		this.on_title_changed();
	},
	show_actual: function( id )
	{
		console.log( "show: " + id );
		this.$el.find(".active").removeClass("active");
		this.$el.find(".frame").hide()
		
		if( id != 0 )
		{
			this.$el.find("#tab_" + id).addClass("active");
			this.$el.find("#plan_frame_" + id).show();
		}
		else
		{
			this.$el.find(".tabs li:first-child").addClass("active");
			this.$el.find(".frame:first-child").show();
		}
		
		this.$el.find('.tab-control').tabcontrol();
	},
    });

$.schoolBell.PlanFramesView = Backbone.View.extend({
        initialize: function(){
		_.bindAll( this, 'render', 'on_plan_add', 'on_remove' );
		this.collection.bind( 'add', this.on_plan_add); 
		this.collection.bind( 'remove', this.on_remove); 
		this.collection.bind( 'reset', this.render); 
//		this.model.bind('add', this.render_thread_summary); 
        },
	template: function( templateElement, data )
	{
		return _.template( $( templateElement ).html(), data );
	},
        render: function(){
		console.log( 'render PlanFramesView' );
		this.$el.find(".frames").html("");
		this.collection.each( function( plan )
		{
			this.on_plan_add( plan, this.collection, null );
		}, this );
		this.$el.find('.tab-control').tabcontrol();
		return this;
        },
        on_plan_add: function( model, collection, options )
	{
		console.log('PlanFramesView on_plan_add ' + model.get('ID') );
// 		model.attributes.cid = model.cid;
		context = model.attributes;
		context.id = this.attributes.id;
		context._loop_index = collection.indexOf( model );
		this.$el.find(".frames").append( this.template( "#plan_frames_template", model.attributes ) );
		plan_frame_view = new $.schoolBell.PlanFrameView( { model: model, el: this.$el.find(".frames").find("#plan_frame_" + model.get('ID') ) });
		plan_frame_view.render();
		this.$el.find('.tab-control').tabcontrol();
	},
	on_remove: function(model, collection, options) {
		this.$el.find(".frames").find("#plan_frame_" + model.get('ID') ).remove();
	},
    });
	
$.schoolBell.PlanFrameView = Backbone.View.extend({
        initialize: function(){
		_.bindAll( this, 'render', 'on_active_changed_user', 'on_title_changed_user', 'on_bell_add', 'save', 'on_plan_delete_user', 'on_bell_add_user', 'on_bell_delete' );
		this.model.get('bell').bind('add', this.on_bell_add); 
		this.model.bind('change', this.save ); 
		this.model.get('bell').bind('remove', this.render ); 
        },
	template: function( templateElement, data )
	{
		return _.template( $( templateElement ).html(), data );
	},
        render: function(){
		console.log( 'PlanFrameView render' + this.model.get('ID') );
		this.$el.html( this.template("#plan_frame_template", this.model.attributes ) );
		this.model.get('bell').each( function( bell, key )
		{
			this.on_bell_add( bell, this.model.get('bell'), {} );
		}, this);
		return this;
        },
	events:
	{
		"change .plan_active" : "on_active_changed_user",
		"change .plan_title" : "on_title_changed_user",
		"click .plan_delete" : "on_plan_delete_user",
		"click .bell_addnew" : "on_bell_add_user"
	},
	on_active_changed_user: function() { 
		console.log('PlanFrameView on_active_changed_user');
		this.model.set( { active: ( ( this.$el.find(".plan_active").is(':checked') )? '1' : '0' ) } );
// 		this.model.save();
	},
	on_title_changed_user: function() { 
		console.log('PlanFrameView on_title_changed_user');
		this.model.set( { title: this.$el.find(".plan_title").val() } );
// 		this.model.save();
	},
        on_bell_add: function( model, collection, options )
	{
		
		
		key = this.model.get('bell').indexOf( model );
		if( key%4 == 0 && key != 0 )
		{
			this.$el.find('.bell-list').append('<div class="row"></div>');
		}
		console.log('PlanFrameView on_bell_add ' + model.get('ID'), key );
		this.$el.find('.bell-list  > .row:last-child').append('<div class="span3"></div>');
		bell_panel_view = new $.schoolBell.BellPanelView( { model: model, el: this.$el.find('.bell-list  > .row:last-child > .span3:last-child') } );
		bell_panel_view.render( key );
	},
	save: function()
	{
		this.model.save();
	},
	on_plan_delete_user: function()
	{
		console.log('PlanFrameView plan_delete_user ' + this.model.get('ID') );
		
// 		this.model.collection.remove( this.model );
		this.model.destroy({wait: true, success: function(model, response) {
			var not = $.Notify({
					style: {background: '#a20025', color: '#fff'},
	// 				caption: "Csengetési rendv törölve...",
					content: "Csengetési rend törölve...",
					timeout: 7000,
					
				});	
		},
			error: function(){console.log('error');}
		});
	},
	on_bell_add_user: function()
	{
		console.log('PlanFrameView bell_add_user ' );
		
		var newmodel = new $.schoolBell.Bell({title:'Új csengő',melody:'',alarm:[],ID:null});
		this.model.get('bell').add(newmodel);
		newmodel.save( {},{success: _.bind(function(model,response,options) {
// 			this.model.get('bell').add( model );
			var not = $.Notify({
				style: {background: '#008287', color: '#fff'},
				content: "Új csengő létrehozva...",
// 				content: "Az új tervet létrehoztam",
				timeout: 7000,
				
			});
			
		}, this)});
		
		
	},
	on_bell_delete: function()
	{
	}
    });


$.schoolBell.BellPanelView = Backbone.View.extend({
        initialize: function(){
		_.bindAll( this, 'render', 'on_change_ID', 'on_changed_user', 'on_bell_delete_user' );
		this.model.bind( 'reset', this.render); 
		this.model.bind( 'change:ID', this.on_change_ID);
		this.model.get('alarm').bind( 'add remove', this.render); 
// 		this.model.bind( 'change:title change:melody', this.on_changed_user ); 
//		this.model.bind('add', this.render_thread_summary); 
        },
	template: function( templateElement, data )
	{
		return _.template( $( templateElement ).html(), data );
	},
        render: function( index ){
//		this.collection.each( this.render_plan_frames, this );
		
		this.$el.html( this.template("#bell_panel_template", this.model.attributes ) );
		this.model.get('alarm').each( function( alarm, key )
		{
			console.log('#bell_alarms_' + this.get('ID'));
			jQuery('#bell_alarms_' + this.get('ID')).append('<div class="list"></div>');
			alarm_view = new $.schoolBell.AlarmView( { model: alarm, el: jQuery('#bell_alarms_' + this.get('ID') + ' > .list:last-child') } );
			alarm_view.render( key );
		}, this.model);
		return this;
        },
	events:
	{
		"change .bell_melody" : "on_changed_user",
		"change .bell_title" : "on_changed_user",
		"click .bell_delete" : "on_bell_delete_user",
		"click .alarm_addnew" : "on_alarm_add_user"
	},
	on_changed_user: function() {
		console.log('BellPanelView on_changed_user#bell_panel_' + this.model.get('ID'));
		this.model.set( { title: this.$el.find(".bell_title").val() } );
		this.model.set( { melody: this.$el.find(".bell_melody").val() } );
		this.model.save();
		this.$el.find(".panel-header" ).html( this.model.get('title') );
	},
	on_change_ID: function() {
		this.$el.find(".bell_panel").attr('id', this.model.get('ID'));
	},
	on_bell_delete_user: function() {
		console.log('BellPanelView bell_delete_user ' + this.model.get('ID') );
		
// 		this.model.collection.remove( this.model );
		this.model.destroy({wait: true, success: function(model, response) {
			var not = $.Notify({
					style: {background: '#a20025', color: '#fff'},
	// 				caption: "Csengetési rendv törölve...",
					content: "Csengetési rend törölve...",
					timeout: 7000,
					
				});	
		},
			error: function(){console.log('error');}
		});
	},
	on_alarm_add_user: function()
	{
		console.log('BellPanelView alarm_add_user ' );
		
		var newmodel = new $.schoolBell.Alarm({time:'12:00',active:0,days:'1111100',ID:null});
		this.model.get('alarm').add(newmodel);
		newmodel.save( {},{success: _.bind(function(model,response,options) {
// 			this.model.get('bell').add( model );
			var not = $.Notify({
				style: {background: '#008287', color: '#fff'},
				content: "Új időzítő létrehozva...",
// 				content: "Az új tervet létrehoztam",
				timeout: 7000,
				
			});
			
		}, this)});
		
		
	},
    });

$.schoolBell.AlarmView = Backbone.View.extend({
        initialize: function(){
		_.bindAll( this, 'render' );
// 		this.model.bind( 'reset', this.render); 
// 		this.model.bind( 'change', this.render); 
//		this.model.bind('add', this.render_thread_summary); 
        },
	template: function( templateElement, data )
	{
		return _.template( $( templateElement ).html(), data );
	},
        render: function(){
		this.$el.append( this.template("#alarm_template", this.model.attributes ) )
		return this;
        },
	events:
	{
		"change .alarm_time" : "on_changed_user",
		"change .alarm_active" : "on_changed_user",
		"change .alarm_days" : "on_changed_user",
		"click .alarm_delete": "on_alarm_delete_user"
	},
	on_changed_user: function() {
		console.log('AlarmView on_changed_user' );
		this.model.set( { time: this.$el.find(".alarm_time").val() } );
		this.model.set( { active: ( ( this.$el.find(".alarm_active").is(':checked') )? '1' : '0' ) } );
		
		days_field = '';
		days = this.$el.find(".alarm_days").toArray();
		
		for( i=0; i<days.length; i++)
		{
			days_field = days_field + ( ( $(days[i]).is(':checked') )? '1' : '0' );
		}
		this.model.set( { days: days_field } );
		this.model.save();
	},
	on_alarm_delete_user: function()
	{
		console.log('AlarmView alarm_delete_user ' + this.model.get('ID') );
		
// 		this.model.collection.remove( this.model );
		this.model.destroy({wait: true, success: function(model, response) {
			var not = $.Notify({
					style: {background: '#a20025', color: '#fff'},
	// 				caption: "Csengetési rendv törölve...",
					content: "Időzítő törölve...",
					timeout: 7000,
					
				});	
		},
			error: function(){console.log('error');}
		});
	}
    });

}) (JQuery);