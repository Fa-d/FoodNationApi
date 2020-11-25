<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

require  '../vendor/autoload.php';
require '../includes/DbOperations.php';

$app = new \Slim\App(['settings' =>['displayErrorDetails' => true]]);

$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
});
$app->get('/helloother/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write($name);
    return $response;
});
$app->get('/getcatagories', function(Request $request, Response $response){
    $db = new DbOperations;
    $users = $db->getItemsByCategorySorted();
    $response_data = array();
    $response_data['error'] = false;
    $response_data['categories'] = $users;
    $response->write(json_encode($response_data));
    return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
});
$app->post('/itemsfromcategories',function(Request $request, Response $response){
    $request_data = $request->getParsedBody();
    $category = $request_data['category'];
    $db = new DbOperations;
    $response_data = array();
    $items_b = $db->getItemsFromCategories($category);
    if($items_b != null){
        $response_data['error'] = false;
        $response_data['items'] = $items_b;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
        
    }else{
        $response_data['error'] = true;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(422);
    }
    
});
$app->post('/getuserinformation', function(Request $request, Response $response){
    $request_data = $request->getParsedBody();
    $id = $request_data['id'];
    $db = new DbOperations;
    $response_data = array();
    $items_a = $db->getItemDetailsbyUser($id);
    $items_b = $db->getUserInformation($id);
    $item = array_merge($items_a,$items_b);
    if($item != null){
        $response_data['error'] = false;
        $response_data['items'] = $item;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
        
    }else{
        $response_data['error'] = true;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(422);
    }
    
});
$app->post('/getitemdetailsdata', function(Request $request, Response $response){
    $request_data = $request->getParsedBody();
    $item = $request_data['item'];
    $db = new DbOperations;
    $response_data  = array();
    $item_a = $db->getItemNameDescription($item);
    $item_b = $db->getItemLocation($item);
    $item_c = $db->getCategoryName($item);
    $item_d = $db->getDatePriceName($item);
    $item_e = $db->getUsersNameIDPhone($item);
    $item_f = $db->getCommentbyItem($item);
    $item = array_merge($item_a,$item_b, $item_c, $item_d, $item_e, $item_f);
    if($item != null){
        $response_data['error'] = false;
        $response_data['items'] = $item;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
        
    }else{
        $response_data['error'] = true;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(422);
    }
    
});
$app->post('/getcategoryname', function(Request $request, Response $response){
    $request_data = $request->getParsedBody();
    $item = $request_data['item'];
    $db = new DbOperations;
    $response_data  = array();
    $item_a = $db->getCategoryName($item);
    if($item_a != null){
        $response_data['error'] = false;
        $response_data['items'] = $item_a;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
        
    }else{
        $response_data['error'] = true;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(422);
        }
});
$app->post("/getimage", function(Request $request, Response $response){

    $request_data = $request->getParsedBody();
    $id = $request_data['id'];

    $db = new DbOperations;

    $images = $db->getImage($id);

    if ($images != null){
        $response_data['error'] = false;
        $response_data['images'] = $images;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
    }else{
        $response_data['error'] = true;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(422);
    }
});
$app->get("/getitembylocation", function(Request $request, Response $response){
    $db = new DbOperations;
    $regional_info = $db->getRegionNameAndItemCount();
    $city_info = $db->getCityNameAndItemCount();
    $response_data['error'] = false;
    $response_data['regions'] = $regional_info;
    $response_data['cities'] = $city_info;
    $response->write(json_encode($response_data));
    return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);

});
$app->post("/getregionalitemdescription", function(Request $request, Response $response){
    $request_data = $request->getParsedBody();
    $regionid = $request_data['regionid'];
    $type = $request_data['type'];

    $db = new DbOperations;
    $items = $db->getItemDescriptionByRegion($regionid, $type);
    if ($items != null){
        $response_data['error'] = false;
        $response_data['items'] = $items;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
    }else{
        $response_data['error'] = true;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(422);
    }
});
$app->get("/getregionsname",function(Request $request, Response $response){
    $db = new DbOperations;
    $regions = $db->getAllRegions();
    if ($regions != null){
        $response_data['error'] = false;
        $response_data['regions'] = $regions;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
    }else{
        $response_data['error'] = true;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(422);
    }
});
$app->post("/getcitybyregion", function(Request $request, Response $response){
    $request_data = $request->getParsedBody();
    $region_id = $request_data['region_id'];
    $db = new DbOperations;
    $cities = $db->getCitiesNameByRegion($region_id);
    if ($cities != null){
        $response_data['error'] = false;
        $response_data['cities'] = $cities;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
    }else{
        $response_data['error'] = true;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(422);
    }
});
$app->post("/insertitem",function(Request $request, Response $response){
    $request_data = $request->getParsedBody();

    $user_id = $request_data['user_id'];
    $category_id = $request_data['category_id'];
    $item_price = $request_data['item_price'];
    $user_ip = $request_data['user_ip'];
    $dt_expiration = $request_data['dt_expiration'];
    $item_title = $request_data['item_title'];
    $item_description = $request_data['item_description'];
    $user_address = $request_data['user_address'];
    $zip = $request_data['zip'];
    $region_name = $request_data['region_name'];
    $city_name = $request_data['city_name'];
    $d_coord_lat = $request_data['d_coord_lat'];
    $d_coord_long = $request_data['d_coord_long'];

    $db = new DbOperations;

    $regions = $db->insertIntoItem($user_id, $category_id, $item_price, $user_ip, $dt_expiration, $item_title, $item_description, 
    $user_address, $zip,  $region_name, $city_name, $d_coord_lat, $d_coord_long);

    if ($regions['success'] != false){
        $response_data = $regions;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
    }else{
        $response_data['error'] = true;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(422);
    }
});
$app->post("/updateitem",function(Request $request, Response $response){
    $request_data = $request->getParsedBody();

    $item_p_key = $request_data['item_p_key'];
    $user_id = $request_data['user_id'];
    $category_id = $request_data['category_id'];
    $item_price = $request_data['item_price'];
    $item_title = $request_data['item_title'];
    $item_description = $request_data['item_description'];
    $user_address = $request_data['user_address'];
    $zip = $request_data['zip'];
    $region_name = $request_data['region_name'];
    $city_name = $request_data['city_name'];
    $d_coord_lat = $request_data['d_coord_lat'];
    $d_coord_long = $request_data['d_coord_long'];
    
    $db = new DbOperations;
    $regions = $db->updateItem($item_p_key, $user_id, $category_id, $item_price, $item_title, $item_description, 
                                $user_address, $zip,  $region_name, $city_name, $d_coord_lat, $d_coord_long);
    if ($regions != false){
        $response_data['error'] = false;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
    }else{
        $response_data['error'] = true;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(422);
    }
});
$app->post("/deleteitem",function(Request $request, Response $response){
    $request_data = $request->getParsedBody();
    $item_p_key = $request_data['item_p_key'];
    $region_name = $request_data['region_name'];
    $city_name = $request_data['city_name'];

    $db = new DbOperations;
    $regions = $db->deleteItem($item_p_key, $region_name, $city_name);
    if ($regions != false){
        $response_data['error'] = false;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
    }else{
        $response_data['error'] = true;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(422);
    }
});
$app->post("/registeruser", function(Request $request, Response $response){
    $request_data = $request->getParsedBody();

    $full_user_name = $request_data['full_user_name'];
    $user_name = $request_data['user_name'];
    $password = $request_data['password'];
    $email = $request_data['email'];
    $website = $request_data['website'];
    $landline = $request_data['landline'];
    $mobile_no = $request_data['mobile_no'];
    $user_address = $request_data['user_address'];
    $zip = $request_data['zip'];
    $has_company = $request_data['has_company'];
    $region_name = $request_data['region_name'];
    $city_name = $request_data['city_name'];
    $ip = $request_data['ip'];
    $coord_lat = $request_data['coord_lat'];
    $coord_long = $request_data['coord_long'];
    $user_desc = $request_data['user_desc'];

    $db = new DbOperations;
    $regions = $db->createNewUser($full_user_name, $user_name, $password, $email, $website, $landline, $mobile_no, $user_address,
                                    $zip,$has_company, $region_name, $city_name, $ip, $coord_lat, $coord_long, $user_desc);
   
    if ($regions != false){
        $response_data['error'] = false;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
    }else{
        $response_data['error'] = true;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(422);
    }
});
$app->post("/useridatregistration", function(Request $request, Response $response){
    $request_data = $request->getParsedBody();
    $password = $request_data['password'];
    $email = $request_data['email'];
    $db = new DbOperations;
    $user_id = $db->getUserIdWhenRegistering($email, $password);
    if ($user_id != 0){
        $response_data['user_id'] = $user_id;

        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
    }else{
        $response_data['user_id'] = 0;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(422);
    }
});
$app->post("/checkpass", function(Request $request, Response $response){
    $request_data = $request->getParsedBody();
    $email = $request_data['email'];
    $password = $request_data['password'];

    $db = new DbOperations;

    $returns = $db->checkPasswordByEmail($email, $password);
   if($returns['error'] == false){
        $response_data['user_id'] = $returns['user_id'];
        $response_data['error'] = false;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                ->withStatus(200);

   }else{
        $response_data['error'] = true;
        $response_data['user_id'] ='';
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(422);
   }
});
$app->post("/addcomment", function(Request $request, Response $response){
    $request_data = $request->getParsedBody();
    $user_id = $request_data['user_id'];
    $item_id = $request_data['item_id'];
    $comment_title = $request_data['comment_title'];
    $comment_body = $request_data['comment_body'];
    $db = new DbOperations;

    $returns = $db->addComment($user_id, $item_id, $comment_title, $comment_body);

    if($returns == true){
        $response_data['error'] = false;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
    }else{
        $response_data['error'] = true;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(422);
    }
});
$app->post("/updatecomment", function(Request $request, Response $response){
    $request_data = $request->getParsedBody();
    $comment_body = $request_data['comment_body'];
    $comment_title = $request_data['comment_title'];
    $comment_id = $request_data['comment_id'];

    $db = new DbOperations;

    $returns = $db->updateComment($comment_body, $comment_title, $comment_id);

    if($returns == true){
        $response_data['error'] = false;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
    }else{
        $response_data['error'] = true;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(422);
    }
});
$app->post("/deletecomment", function(Request $request, Response $response){
    $request_data = $request->getParsedBody();
   
    $user_id = $request_data['user_id'];
    $comment_id = $request_data['comment_id'];

    $db = new DbOperations;

    $returns = $db->deleteComment($user_id, $comment_id);

    if($returns == true){
        $response_data['error'] = false;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
    }else{
        $response_data['error'] = true;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(422);
    }
});
$app->post("/updateuserprofile", function(Request $request, Response $response){

    $request_data = $request->getParsedBody();

    $user_id = $request_data['user_id'];
    $full_user_name = $request_data['full_user_name'];
    $user_name = $request_data['user_name'];
    $password = $request_data['password'];
    $email = $request_data['email'];
    $website = $request_data['website'];
    $landline = $request_data['landline'];
    $mobile_no = $request_data['mobile_no'];
    $user_address = $request_data['user_address'];
    $zip = $request_data['zip'];
    $has_company = $request_data['has_company'];
    $region_name = $request_data['region_name'];
    $city_name = $request_data['city_name'];
    $ip = $request_data['ip'];
    $coord_lat = $request_data['coord_lat'];
    $coord_long = $request_data['coord_long'];
    $user_desc = $request_data['user_desc'];

    $db = new DbOperations;

    $regions = $db->updateUserInfo($user_id, $full_user_name, $user_name, $password, $email, $website, $landline, $mobile_no, $user_address, $zip, $has_company, 
                                        $region_name, $city_name, $ip, $coord_lat, $coord_long, $user_desc);
   
    if ($regions != false){
        $response_data['error'] = false;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
    }else{
        $response_data['error'] = true;
        $response->write(json_encode($response_data));
        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(422);
    }
});
$app->post('/uploadiamgetest', function ($request, $response, $args) {
    $temp_parse = $request->getParsedBody();

    $item_id = $temp_parse['item_id'];
    $category_id = $temp_parse['category_id'];

    $files = $request->getUploadedFiles();
    if (empty($files['image'])) {
        throw new Exception('Expected a newfile');
    }

    $newfile = $files['image'];
    $for_returning1 = array();

    if ($newfile->getError() === UPLOAD_ERR_OK) {
        $filedest =dirname(__FILE__) . "\\oc-content\\uploads\\$category_id\\";

        $uploadFileName =  $item_id.'.jpg';
     
        if(!is_dir($filedest)){

            mkdir($filedest, 0777, true);

            $newfile->moveTo($filedest.$uploadFileName);
            
            $for_returning1['error'] = false;

            $response->write(json_encode($for_returning1));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
        }else{
            $for_returning1['error'] = false;

            $newfile->moveTo($filedest.$uploadFileName);

            $response->write(json_encode($for_returning1));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
        }
    }else{
        $for_returning1['error'] = true;
        $response->write(json_encode($for_returning1));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);
    }
});

$app->run();

