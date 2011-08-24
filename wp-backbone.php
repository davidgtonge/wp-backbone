<?php
/*
Plugin Name: WP-Backbone
Plugin URI: http://www.simplecreativity.co.uk
Description: WordPress Backbone.js Integration
Version: 0.1
Author: Dave Tonge
Author URI: http://www.simplecreativity.co.uk
*/


add_action('init', 'wp_backbone_init');
function wp_backbone_init()
{
    if (!is_admin()) {
        wp_enqueue_script('jquery');
        wp_enqueue_script('underscore', 'http://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.1.7/underscore-min.js');
        wp_enqueue_script('backbone', 'http://cdnjs.cloudflare.com/ajax/libs/backbone.js/0.5.1/backbone-min.js');
        wp_enqueue_script('wp_backbone', plugins_url('wp-backbone') . '/wp_backbone.js');
        wp_localize_script('wp_backbone', 'wpBackboneGlobals', array(
                                                   'ajaxurl' => admin_url('admin-ajax.php'),
                                                   'backbone_nonce' => wp_create_nonce('backbone_nonce')
                                              )
        );
    }

}

//Add a handler to the Wp ajax file to process backbone actions
add_action( 'wp_ajax_backbone', 'backbone_handler' );

function backbone_handler(){


    //Include the idiorm and paris libraries for our ORM functions
    require_once('idiorm.php');
    require_once('paris.php');

    //Connect to the WP database with idiorm
ORM::configure('');
ORM::configure('');
ORM::configure('');

/* Create models for db entries

class testModel extends Model {
}

*/


/*
 * Now we start processing the ajax data
 */

$method = $_REQUEST['backbone_method'];
$model = $_REQUEST['backbone_model'];


//Validation to make sure only our defined models can be accessed. To do: update to use validate array
//$defined_models = array('Hotels','Rooms','RoomTypes');
//if(!in_array($model,$defined_models)) die ('Security Error');

//Convert the incoming json data into an associative array
//Stipslashes is needed because we are sending the json data as part of url encoded form in order to integrate to WP
if(isset($_REQUEST['content'])){
    $vars = json_decode(stripslashes($_REQUEST['content']),true);
}



/*
 * Here is the switch to process the different Restful methods received from backbone.
 */
switch ($method) {
    //This is the read method.
    //Most of the data will be retrieved via the init method and cached in the client's browser, so this will be rarely used
    case 'read':

        if(isset($_REQUEST['content'])) {
            $result = Model::factory('Snl' . $model)->find_one($_REQUEST['content']);
            //Add meta code
            $response = $result->as_array();
        } else {
            $results = Model::factory('Snl' . $model)->find_many;
            $response = array();
            foreach ($results as $result){
                $response[] = $result->as_array();
            }
        }
        echo json_encode($response);

        break;


    case 'create':
        /*
         * This is the create method
         * In an effort to be DRY there isn't a need to define a different method for each model
         * Rather the model is passed as a variable.
         */

        $new_object = Model::factory('Snl' . $model)->create();
        $new_object->update($vars, $model);
        $new_object->save();

        //When app is ready we will be returning data as locale specific
        //return $new_object->as_local($locale);
        //For the moment simply return all the values from the db row
        echo json_encode($new_object->as_array());
        exit;

        break;

    case 'update':
        /*
         * The update method.
         */

        $update_object = Model::factory('Snl' . $model)->find_one($vars['id']);
        $update_object->update($vars, $model);
        $update_object->save();
        echo json_encode($update_object->as_array());
        exit;

        break;

    case 'delete':
        /*
         * The delete method
         */
        $delete_object = Model::factory('Snl' . $model)->find_one($vars['id']);
        $delete_object->delete();
        echo json_encode(array('deleted' => true));
        exit;

        break;

    case 'init':
        /*
         * This is the init method where we populate all our client side models in backbone
         * with the data from the server.
         * We also send the validation array so that both client side and server side validation are in sync
         */

        $data = array(
            'Validate' => $validate,
            'collections' => array()
        );

        foreach($validate as $key => $value){
            $models = Model::factory('Snl' . $key)->find_many();
            $data['collections'][$key] = array();
            foreach ($models as $model){
                $data['collections'][$key][] = $model->as_array();
            }
        }

        echo json_encode($data);
        exit;

        break;


}

exit();




}

