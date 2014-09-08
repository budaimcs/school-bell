(function($) {

$.schoolBell.Router = Backbone.Router.extend({
        routes: {
            "": "plan_list_default",
            "plan/:_id": "plan_list",
        },
	plan_list_default: function() {
		this.plan_list( 0 );
        },
        plan_list: function( _id ) {
		console.log( _id );
	    $('#content').html( _.template( $("#plan_tabs_container_template").html() ) );
            var plan_collection = new $.schoolBell.PlanCollection();
            var plan_tabs_view = new $.schoolBell.PlanTabsView( { el: $('#content'), collection: plan_collection, attributes: { id: _id } } );
// 	    if( _id != "0" ) plan_tabs_view.show( _id );
	    var plan_frames_view = new $.schoolBell.PlanFramesView( { el: $('#content'), collection: plan_collection, attributes: { id: _id } } );
// 	    plan_frames_view.show( _id );
	    plan_collection.fetch({reset: true});
        }/*,
        
        show_plan: function(_id) {
            var thread = new $.schoolBell.Thread({_id: _id});
            var thread_view = new $.schoolBell.ThreadView({el: $('#content'), model: thread});
            thread.fetch();
        },*/
});

}) (JQuery);