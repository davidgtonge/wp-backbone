/**
 * Created by JetBrains PhpStorm.
 * User: Dave Tonge
 * Date: 24/08/11
 * Time: 14:10
 */


//Custom Backbone Sync Method
(function($) {
    Backbone.sync = function(method, model, options) {

        var params = _.extend({
            type:         'POST',
            dataType:     'json',
            url: wpBackboneGlobals.ajaxurl,
            contentType: 'application/x-www-form-urlencoded;charset=UTF-8'
        }, options);

        if (method == 'read') {
            params.type = 'GET';
            params.data = model.id
        }

        if (!params.data && model && (method == 'create' || method == 'update' || method == 'delete')) {
            params.data = JSON.stringify(model.toJSON());
        }


        if (params.type !== 'GET') {
            params.processData = false;
        }

        params.data = $.param({action:'backbone',backbone_method:method,backbone_model:model.dbModel,content:params.data});

        // Make the request.
        return $.ajax(params);


    };

})(jQuery);


//This is the definition of our app
window.App = {
    Views: {},
    Controllers: {},
    Models: {},
    Collections: {},
    Validate: {},
    init: function() {
         this.Data.Session = new Backbone.Model({
             event_id: 'All',
             status: 'All'
         });
         $.getJSON(wpBackboneGlobals.ajaxurl, {action:'backbone',backbone_method:'init',backbone_model:'User'}, function(data) {
            if (data) {
                _(data.collections).each(function(vars, key) {
                    App.Data[key + 's'] = new App.Collections[key + 's'];
                    App.Data[key + 's'].add(vars);
                });
                //If data is successfully retrieved then it is added to our apps collections
                //The validate data is also added to our App
                App.Validate = data.Validate;
                App.Router = new App.Controllers.Router();
                Backbone.history.start();
            }
        });
    },
    //This is where we will store all our collection data
    Data: {}
};


/*!
 * jQuery serializeObject - v0.2 - 1/20/2010
 * http://benalman.com/projects/jquery-misc-plugins/
 *
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */

// Whereas .serializeArray() serializes a form into an array, .serializeObject()
// serializes a form into an (arguably more useful) object.

(function($, undefined) {
    '$:nomunge'; // Used by YUI compressor.

    $.fn.serializeObject = function() {
        var obj = {};

        $.each(this.serializeArray(), function(i, o) {
            var n = o.name,
                v = o.value;

            obj[n] = obj[n] === undefined ? v
                : $.isArray(obj[n]) ? obj[n].concat(v)
                : [ obj[n], v ];
        });

        return obj;
    };

})(jQuery);