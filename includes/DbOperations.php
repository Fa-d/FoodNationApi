<?php
    class DbOperations
    {
        private $con;
        public function __construct()
        {
            require_once dirname(__FILE__).'/DbConnect.php';
            $db = new DbConnect;
            $this->con = $db->connect();
        }
        public function getAllCatagoriesShortened()
        {
            $stmt = $this->con->prepare("select pk_i_id,  s_name, num_items, s_slug from
            (select count(fk_i_parent_id) as num_items, pk_i_id ,fk_i_parent_id 
                from os3n_t_category 
                group by fk_i_parent_id 
                having count(fk_i_parent_id) > 0) as x 
            inner join 
                (select * from os3n_t_category_description) as y 
            on x.pk_i_id = y.fk_i_category_id;");
            $stmt->execute();
            $stmt->bind_result($pk_i_id, $s_name, $num_items, $s_slug);
            $catagories = array();
            while ($stmt->fetch()) {
                $catagorie  = array();
                $catagorie['pk_i_id'] = $pk_i_id;
                $catagorie['s_name'] = $s_name;
                $catagorie['num_items'] = $num_items;
                $catagorie['s_slug'] = $s_slug;
                array_push($catagories, $catagorie);
            }
            return $catagories;
        }
        public function getItemsByCategorySorted()
        {
            $stmt = $this->con->prepare("select os3n_t_item.fk_i_category_id as category_id, s_name as category_name, count(pk_i_id) as category_count from os3n_t_item 
            inner join os3n_t_category_description on os3n_t_item.fk_i_category_id = os3n_t_category_description.fk_i_category_id
            group by os3n_t_item.fk_i_category_id having count(os3n_t_item.pk_i_id)  > 1  order by count(os3n_t_item.pk_i_id) desc ;");
            $stmt->execute();
            $stmt->bind_result($category_id, $category_name, $category_count);
            $catagories = array();
            while ($stmt->fetch()) {
                $catagorie  = array();
                $catagorie['category_id'] = $category_id;
                $catagorie['category_name'] = $category_name;
                $catagorie['category_count'] = $category_count;
                array_push($catagories, $catagorie);
            }
            return $catagories;
        }
        public function getImage($id)
        {
            $stmt  = $this->con->prepare("select pk_i_id, s_path, s_extension from os3n_t_item_resource where os3n_t_item_resource.pk_i_id = (select fk_i_category_id from os3n_t_item where pk_i_id = ?);");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($pk_i_id, $s_path, $s_extension);
            $item2 = array();
            $items = array();
            while ($stmt->fetch()) {
                $item = array();
                $item['s_path'] = $s_path;
                $item['s_extension'] = $s_extension;
                $item['pk_i_id']  = $pk_i_id;
                array_push($item2, $item);
            }
            $items['images'] = $item2;
            
            return $items;
        }
        public function getItemsFromCategories($categoryNo)
        {
            $stmt = $this->con->prepare("select os3n_t_item.i_price as price,s_contact_name as user_name, os3n_t_item.pk_i_id as item_id, os3n_t_item_resource.pk_i_id as images_id, s_path as url_path,
            s_extension as image_ext, s_title as item_title, os3n_t_category_description.s_name as item_category, s_city as city, s_region as region from os3n_t_item 
                        join os3n_t_item_resource on os3n_t_item.pk_i_id = os3n_t_item_resource.fk_i_item_id
                        join os3n_t_item_description on os3n_t_item.pk_i_id = os3n_t_item_description.fk_i_item_id
                        join os3n_t_item_location on os3n_t_item.pk_i_id = os3n_t_item_location.fk_i_item_id
                        join  os3n_t_category_description on os3n_t_item.fk_i_category_id = os3n_t_category_description.fk_i_category_id
                        where os3n_t_item.fk_i_category_id = ?;");
            $stmt->bind_param("i", $categoryNo);
            $stmt->execute();
            $stmt->bind_result($price, $user_name, $item_id, $images_id, $url_path, $image_ext, $item_title, $item_category, $city, $region);
            $items = array();
            while ($stmt->fetch()) {
                $item = array();
                $item['price'] = $price;
                $item['user_name'] = $user_name;
                $item['item_id'] = $item_id;
                $item['images_id'] = $images_id;
                $item['url_path'] = $url_path;
                $item['image_ext'] = $image_ext;
                $item['item_title'] = $item_title;
                $item['item_category'] = $item_category;
                $item['city'] = $city;
                $item['region'] = $region;

                array_push($items, $item);
            }
            return $items;
        }
        public function getUserInformation($id)
        {
            $stmt = $this->con->prepare("select s_name ,s_email, s_website, s_phone_mobile, s_region, s_city from os3n_t_user where pk_i_id = ?;");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($s_name, $s_email, $s_website, $s_phone_mobile, $s_region, $s_city);
            $items = array();
            $stmt->fetch();
            $items['user_name'] = $s_name;
            $items['user_email'] = $s_email;
            $items['user_website'] = $s_website;
            $items['user_mobile_no'] = $s_phone_mobile;
            $items['user_region'] = $s_region;
            $items['user_city'] = $s_city;

            return $items;
        }
        public function getItemDetailsbyUser($id)
        {
            $stmt = $this->con->prepare("select os3n_t_item.pk_i_id, i_price, s_title, os3n_t_category_description.s_name, s_city, s_region, s_path, s_extension  from os3n_t_item
            join os3n_t_item_resource on os3n_t_item.pk_i_id = os3n_t_item_resource.pk_i_id
            join os3n_t_item_description on os3n_t_item.pk_i_id = os3n_t_item_description.fk_i_item_id
            join os3n_t_item_location on os3n_t_item.pk_i_id = os3n_t_item_location.fk_i_item_id 
            join os3n_t_category_description on os3n_t_item.fk_i_category_id = os3n_t_category_description.fk_i_category_id
            where fk_i_user_id = ?;");

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($pk_i_id, $i_price, $s_title, $s_name, $s_city, $s_region, $s_path, $s_extension);
            $items = array();
            while ($stmt->fetch()) {
                $item = array();
                $item['item_id'] = $pk_i_id;
                $item['item_price'] = $i_price;
                $item['item_name'] = $s_title;
                $item['category_name'] = $s_name;
                $item['user_city'] = $s_city;
                $item['user_region'] = $s_region;
                $item['url_path'] = $s_path;
                $item['image_extention'] = $s_extension;
                array_push($items, $item);
            }
            $item2['users_post_items'] = $items;
            return $item2;
        }
        public function getItemNameDescription($item)
        {
            $stmt1 = $this->con->prepare("select s_title, s_description from os3n_t_item_description join os3n_t_item on os3n_t_item.pk_i_id = os3n_t_item_description.fk_i_item_id where os3n_t_item.pk_i_id = ?;");
            $items = array();

            $stmt1->bind_param("i", $item);
            $stmt1->execute();
            $stmt1->bind_result($s_title, $s_description);
            $stmt1->fetch();

            $items['s_title'] = $s_title;
            $items['s_description'] = $s_description;
            
            return $items;
        }
        public function getItemLocation($item)
        {
            $stmt2 = $this->con->prepare("select s_city, s_region, s_address from os3n_t_item_location where fk_i_item_id = ?;");
            $items = array();

            $stmt2->bind_param("i", $item);
            $stmt2->execute();
            $stmt2->bind_result($s_city, $s_region, $s_address);
            $stmt2->fetch();
      
            $items['s_city'] = $s_city;
            $items['s_region'] = $s_region;
            $items['s_address'] = $s_address;
            
            return $items;
        }
        public function getCategoryName($item)
        {
            $stmt = $this->con->prepare("select s_title from os3n_t_item_description where fk_i_item_id = ? ;");
            $stmt->bind_param("i", $item);
            $stmt->execute();
            $stmt->bind_result($s_title);
            $stmt->fetch();
            $items = array();
            $items['s_title_category'] = $s_title;
            return $items;
        }
        public function getDatePriceName($item)
        {
            $stmt = $this->con->prepare("select s_contact_name, dt_pub_date, i_price from os3n_t_item where pk_i_id = ?;");
            $stmt->bind_param("i", $item);
            $stmt->execute();
            $stmt->bind_result($s_contact_name, $dt_pub_date, $i_price);
            $stmt->fetch();
            $items = array();
            $items['i_price'] = $i_price;
            $items['dt_pub_date'] = $dt_pub_date;
            $items['s_contact_name'] = $s_contact_name;
            return $items;
        }
        public function getUsersNameIDPhone($item)
        {
            $stmt = $this->con->prepare("select pk_i_id, s_name, i_items, dt_reg_date, s_phone_mobile  from os3n_t_user where pk_i_id = (select fk_i_user_id from os3n_t_item where pk_i_id = ?);");
            $stmt->bind_param("i", $item);
            $stmt->execute();
            $stmt->bind_result($pk_i_id, $s_name, $i_items, $dt_reg_date, $s_phone_mobile);
            $stmt->fetch();
            $items = array();
            $items['users_unique_id'] = $pk_i_id;
            $items['users_name'] =$s_name;
            $items['users_items'] = $i_items;
            $items['user_date_registered'] = $dt_reg_date;
            $items['users_mobile_number'] = $s_phone_mobile;
            return $items;
        }
        public function getCommentbyItem($item)
        {
            $stmt = $this->con->prepare("select pk_i_id, s_author_name, s_title, s_body, dt_pub_date, fk_i_user_id from os3n_t_item_comment where fk_i_item_id = ?;");
            $stmt->bind_param("i", $item);
            $stmt->execute();
            $stmt->bind_result($pk_i_id, $s_author_name, $s_title, $s_body, $dt_pub_date, $fk_i_user_id);
            $items = array();
            $item2 = array();
            while ($stmt->fetch()) {
                $item = array();
                $item['comment_id'] = $pk_i_id;
                $item['commenter_name'] = $s_author_name;
                $item['comment_title'] = $s_title;
                $item['comment_body'] = $s_body;
                $item['commented_date'] = $dt_pub_date;
                $item['commenter_id'] = $fk_i_user_id;
                array_push($item2, $item);
            }
            $items['comment'] = $item2;
            return $items;
        }
        public function getItemDescriptionByRegion($regionId, $type)
        {
            if ($type == 1) {
                $stmt = $this->con->prepare("select 
                                                os3n_t_item.i_price as price,s_contact_name as user_name, os3n_t_item.pk_i_id as item_id, os3n_t_item_resource.pk_i_id as images_id, s_path as url_path,
                                                s_extension as image_ext, s_title as item_title, os3n_t_category_description.s_name as item_category, s_city as city, s_region as region 
                                            from os3n_t_item
                                                join os3n_t_item_description on os3n_t_item.pk_i_id = os3n_t_item_description.fk_i_item_id
                                                join os3n_t_item_location on os3n_t_item.pk_i_id = os3n_t_item_location.fk_i_item_id
                                                join os3n_t_item_resource on os3n_t_item.pk_i_id = os3n_t_item_resource.fk_i_item_id
                                                join os3n_t_category_description on os3n_t_item.fk_i_category_id = os3n_t_category_description.fk_i_category_id
                                            where os3n_t_item_location.fk_i_region_id = ?;");
                $stmt->bind_param("i", $regionId);
            } elseif ($type == 2) {
                $stmt = $this->con->prepare("select 
                        os3n_t_item.i_price as price,s_contact_name as user_name, os3n_t_item.pk_i_id as item_id, os3n_t_item_resource.pk_i_id as images_id, s_path as url_path,
                        s_extension as image_ext, s_title as item_title, os3n_t_category_description.s_name as item_category, s_city as city, s_region as region 
                    from os3n_t_item
                        join os3n_t_item_description on os3n_t_item.pk_i_id = os3n_t_item_description.fk_i_item_id
                        join os3n_t_item_location on os3n_t_item.pk_i_id = os3n_t_item_location.fk_i_item_id
                        join os3n_t_item_resource on os3n_t_item.pk_i_id = os3n_t_item_resource.fk_i_item_id
                        join os3n_t_category_description on os3n_t_item.fk_i_category_id = os3n_t_category_description.fk_i_category_id
                    where os3n_t_item_location.fk_i_city_id = ?;");
                $stmt->bind_param("i", $regionId);
            } elseif ($type == 3) {
                $stmt = $this->con->prepare("select os3n_t_item.i_price as price,s_contact_name as user_name, os3n_t_item.pk_i_id as item_id, os3n_t_item_resource.pk_i_id as images_id, s_path as url_path,
                        s_extension as image_ext, s_title as item_title, os3n_t_category_description.s_name as item_category, s_city as city, s_region as region from os3n_t_item 
                        join os3n_t_item_resource on os3n_t_item.pk_i_id = os3n_t_item_resource.fk_i_item_id
                        join os3n_t_item_description on os3n_t_item.pk_i_id = os3n_t_item_description.fk_i_item_id
                        join os3n_t_item_location on os3n_t_item.pk_i_id = os3n_t_item_location.fk_i_item_id
                        join  os3n_t_category_description on os3n_t_item.fk_i_category_id = os3n_t_category_description.fk_i_category_id
                        order by dt_pub_date desc ;");
            }
            
            $stmt->execute();
            $stmt->bind_result($price, $user_name, $item_id, $images_id, $url_path, $image_ext, $item_title, $item_category, $city, $region);
            $items = array();
            while ($stmt->fetch()) {
                $item = array();
                $item['price'] = $price;
                $item['user_name'] = $user_name;
                $item['item_id'] = $item_id;
                $item['images_id'] = $images_id;
                $item['url_path'] = $url_path;
                $item['image_ext'] = $image_ext;
                $item['item_title'] = $item_title;
                $item['item_category'] = $item_category;
                $item['city'] = $city;
                $item['region'] = $region;

                array_push($items, $item);
            }
            return $items;
        }
        public function getRegionNameAndItemCount()
        {
            $stmt = $this->con->prepare("select fk_i_region_id, s_region, COUNT(fk_i_item_id) as count_region  from os3n_t_item_location
            where fk_i_region_id is not null  group by s_region  ORDER BY COUNT(fk_i_item_id) DESC ;");
            $stmt->execute();
            $stmt->bind_result($region_id, $region_name, $item_count);
            $item2 = array();
            while ($stmt->fetch()) {
                $item = array();
                $item['region_id'] = $region_id;
                $item['region_name'] = $region_name;
                $item['item_count'] = $item_count;
                array_push($item2, $item);
            }
            return $item2;
        }
        public function getCityNameAndItemCount()
        {
            $stmt = $this->con->prepare("select fk_i_city_id, s_city, COUNT(fk_i_item_id) as count_city from os3n_t_item_location 
            where fk_i_city_id is not null group by s_city ORDER BY COUNT(fk_i_item_id) DESC;");
            $stmt->execute();
            $stmt->bind_result($city_id, $city_name, $item_count);
            $item2 = array();
            while ($stmt->fetch()) {
                $item = array();
                $item['city_id'] = $city_id;
                $item['city_name'] = $city_name;
                $item['item_count'] = $item_count;
                array_push($item2, $item);
            }
            return $item2;
        }
        public function getAllRegions()
        {
            $stmt = $this->con->prepare("select s_name, pk_i_id from os3n_t_region;");
            $stmt->execute();
            $stmt->bind_result($region_name, $region_id);
            $regions = array();
            while ($stmt->fetch()) {
                $region = array();
                $region['region_name'] = $region_name;
                $region['region_id'] = $region_id;
                array_push($regions, $region);
            }
            return $regions;
        }
        public function getCitiesNameByRegion($region_id)
        {
            $stmt = $this->con->prepare("select pk_i_id, s_name from os3n_t_city where fk_i_region_id = ?;");
            $stmt->bind_param("i", $region_id);
            $stmt->execute();
            $stmt->bind_result($city_id, $city_name);
            $cities = array();
            while ($stmt->fetch()) {
                $city = array();
                $city['city_id'] = $city_id;
                $city['city_name'] = $city_name;
                array_push($cities, $city);
            }
            return $cities;
        }
      
        public function deleteItem($item_p_key, $region_name, $city_name)
        {
            $query = "
                start transaction;
                SET FOREIGN_KEY_CHECKS=0;
                select fk_i_category_id into @category_id from os3n_t_item where pk_i_id =  '$item_p_key';
                select pk_i_id into @region_id from os3n_t_region where s_name = '$region_name';
                select pk_i_id into @city_id from os3n_t_city where s_name = '$city_name';

                Delete from os3n_t_item where pk_i_id =  '$item_p_key';
                delete from os3n_t_item_description where fk_i_item_id = '$item_p_key';
                delete from os3n_t_item_location where fk_i_item_id =  '$item_p_key';
                delete from os3n_t_item_stats where fk_i_item_id =  '$item_p_key';


                SET FOREIGN_KEY_CHECKS=1;
                commit;
            ";
            
            // update os3n_t_category_stats set i_num_items = i_num_items - 1 where fk_i_category_id = @category_id;

            // update os3n_t_city_stats set i_num_items = i_num_items - 1 where fk_i_city_id = @city_id;

            // update os3n_t_region_stats set i_num_items = i_num_items - 1 where fk_i_region_id = @region_id;
            $result = $this->con->multi_query($query);
            $get_item_id = 0;
            do {
                if ($results = $this->con-> store_result()) {
                    while ($row = $results -> fetch_row()) {
                        if ($row[0] != '') {
                            $get_item_id = $row[0];
                        }
                    }
                    $results -> free_result();
                }
            } while ($this->con-> next_result());
            echo mysqli_error($this->con);

            $send = array();
            $send['success'] = $result;
            return $result;
        }
        public function insertIntoItem(
            $user_id,
            $category_id,
            $item_price,
            $user_ip,
            $dt_expiration,
            $item_title,
            $item_description,
            $user_address,
            $zip,
            $region_name,
            $city_name,
            $d_coord_lat,
            $d_coord_long
        )
        {
            $query = "
                start transaction;

                set @fk_c_currency_code := 'BDT';
                set @b_premium := 0;
                set @b_enabled := 1;
                set @b_active := 1;
                set @b_spam := 0;
                set @s_secret := 'asda';
                set @b_show_email := 1;
                set @dt_pub_date := now();
                set @dt_expiration := DATE_ADD(NOW(), INTERVAL 2 week);
                
                select s_email,s_name into @s_contact_email, @s_contact_name from os3n_t_user where pk_i_id = '$user_id';

                insert into os3n_t_item(fk_i_user_id, fk_i_category_id, dt_pub_date, dt_mod_date, f_price, i_price, fk_c_currency_code, s_contact_name, s_contact_email, s_ip, b_premium, b_enabled, b_active, b_spam, s_secret, b_show_email, dt_expiration) values
                ('$user_id', '$category_id', @dt_pub_date, null, null, '$item_price', @fk_c_currency_code, @s_contact_name, @s_contact_email, '$user_ip', @b_premium, @b_enabled, @b_active, @b_spam, @s_secret, @b_show_email, '$dt_expiration');
                SELECT LAST_INSERT_ID() into @item_primary_key;

                SELECT @item_primary_key;

                set @fk_c_locale_code := 'en_US';
                set @s_title := '$item_title';
                set @item_short_description = '$item_description';

                insert into os3n_t_item_description(fk_i_item_id, fk_c_locale_code, s_title, s_description) values
                (@item_primary_key, @fk_c_locale_code, '$item_title', '$item_description');

                set @fk_c_country_code := 88;
                set @s_country := 'Bangladesh';
                set @s_address := '$user_address';
                set @s_zip := '$zip'; 
                set @s_name_region := '$region_name';	
                set @s_name_city := '$city_name';	

                select pk_i_id into @region_id from os3n_t_region where s_name = @s_name_region;
                select pk_i_id into @city_id from os3n_t_city where s_name = @s_name_city;

                set @fk_i_city_area_id := null;
                set @s_city_area := '';
                set @d_coord_lat := '$d_coord_lat';
                set @d_coord_long := '$d_coord_long';

                insert into os3n_t_item_location(fk_i_item_id, fk_c_country_code, s_country, s_address, s_zip, fk_i_region_id, s_region, fk_i_city_id, s_city, fk_i_city_area_id, s_city_area, d_coord_lat, d_coord_long) values
                (@item_primary_key, @fk_c_country_code, @s_country, '$user_address', '$zip', @region_id, '$region_name', @city_id, '$city_name', @fk_i_city_area_id, @s_city_area, '$d_coord_lat', '$d_coord_long');

                insert into os3n_t_item_stats(fk_i_item_id, i_num_views, i_num_spam, i_num_repeated, i_num_bad_classified, i_num_offensive, i_num_expired, i_num_premium_views, dt_date) values
                (@item_primary_key, 0, 0, 0, 0, 0, 0, 0, now());

                update os3n_t_category_stats set i_num_items = i_num_items + 1 where fk_i_category_id = '$category_id';

                update os3n_t_city_stats set i_num_items = i_num_items + 1 where fk_i_city_id = @city_id;

                update os3n_t_region_stats set i_num_items = i_num_items + 1 where fk_i_region_id = @region_id;

                update os3n_t_user set i_items = i_items + 1 where pk_i_id = $user_id;

                set @s_name := 'random';
                set @s_extension := 'jpg';
                set @s_content_type := 'image/jepg';
                set @s_path := 'oc-content/uploads/$category_id/';

                insert into os3n_t_item_resource(fk_i_item_id, s_name, s_extension, s_content_type, s_path) values(@item_primary_key, @s_name, @s_extension, @s_content_type, @s_path);

                commit;
                ";
               
            $result = $this->con->multi_query($query);
            $get_item_id = 0;
            do {
                if ($results = $this->con-> store_result()) {
                    while ($row = $results -> fetch_row()) {
                        if ($row[0] != '') {
                            $get_item_id = $row[0];
                        }
                    }
                    $results -> free_result();
                }
            } while ($this->con-> next_result());
            echo mysqli_error($this->con);

            $send = array();
            $send['success'] = $result;
            $send['category_id'] = $category_id;
            $send['item_id'] = $get_item_id;

            return $send;
        }
       
        public function updateItem(
            $item_p_key,
            $category_id,
            $item_price,
            $item_title,
            $item_description,
            $user_address,
            $zip,
            $ip,
            $region_name,
            $city_name,
            $d_coord_lat,
            $d_coord_long
        )
        {
            $query = "
                start transaction;
                set @dt_mod_date := now();
                
                update os3n_t_item set fk_i_category_id = '$category_id', dt_mod_date = @dt_mod_date, i_price = '$item_price',  s_ip = '$ip'  where pk_i_id = '$item_p_key';  
                
                set @s_title := '$item_title';
                set @item_short_description = '$item_description';
                
                update os3n_t_item_description set s_title = @s_title, s_description = @item_short_description where fk_i_item_id = '$item_p_key';
           
                set @s_address := '$user_address';
                set @s_zip := '$zip'; 
                set @s_name_region := '$region_name';	
                set @s_name_city := '$city_name';	
                
                select pk_i_id into @region_id from os3n_t_region where s_name = @s_name_region;
                select pk_i_id into @city_id from os3n_t_city where s_name = @s_name_city;
                
                set @fk_i_city_area_id := null;
                set @s_city_area := '';
                set @d_coord_lat := '$d_coord_lat';
                set @d_coord_long := '$d_coord_long';
                
                update os3n_t_item_location set  s_address = @s_address , s_zip = @s_zip, fk_i_region_id = @region_id, s_region = @s_name_region, fk_i_city_id = @city_id , s_city = @s_name_city,
                fk_i_city_area_id =  @fk_i_city_area_id, s_city_area = @s_city_area, d_coord_lat = @d_coord_lat, d_coord_long = @d_coord_long where fk_i_item_id = '$item_p_key';
                
                set @s_name := 'random';
                set @s_extension := 'jpg';
                set @s_content_type := 'image/jepg';
                set @s_path := 'oc-content/uploads/$category_id/';
                
                update os3n_t_item_resource set s_name = @s_name , s_extension = @s_extension, s_content_type = @s_content_type, s_path  = @s_path where fk_i_item_id = '$item_p_key'; 
                
                commit;
            ";

            $result = $this->con->multi_query($query);
            $get_item_id = 0;
            do {
                if ($results = $this->con-> store_result()) {
                    while ($row = $results -> fetch_row()) {
                        if ($row[0] != '') {
                            $get_item_id = $row[0];
                        }
                    }
                    $results -> free_result();
                }
            } while ($this->con-> next_result());
            echo mysqli_error($this->con);

            $send = array();
            $send['success'] = $result;

            return $result;
        }

        public function createNewUser(
            $full_user_name,
            $user_name,
            $password,
            $email,
            $website,
            $landline,
            $mobile_no,
            $user_address,
            $zip,
            $has_company,
            $region_name,
            $city_name,
            $ip,
            $coord_lat,
            $coord_long,
            $user_desc
        )
        {
            $query = "
                start transaction;
                set @s_name := '$full_user_name';
                set @s_username := '$user_name';
                set @s_password := '$password';
                set @s_email := '$email';
                set @s_website := '$website';
                set @s_phone_land := '$landline';
                set @s_phone_mobile := '$mobile_no';
                set @s_address := '$user_address';
                set @s_zip := '$zip';
                set @b_company := '$has_company';
                set @s_region := '$region_name';
                set @s_city := '$city_name';
                set @s_access_ip := '$ip';
                set @d_coord_lat := '$coord_lat';
                set @d_coord_long := '$coord_long';
                set @dt_reg_date := now();
                set @dt_access_date := now();
                set @s_country := 'Bangladesh';
                set @fk_c_country_code := 88;
                set @b_enabled := 1;
                set @b_active := 1;
                set @s_secret := 'asdasd0';
                set @dt_mod_date := now();
                set @s_info := '$user_desc';
                set @s_pass_code := 'sdfsd';
                set @s_pass_date := now();
                set @fk_i_city_area_id := null;
                set @i_items := 0;
                set @i_comments := 0;
                
                SELECT pk_i_id INTO @region_id FROM os3n_t_region WHERE s_name = @s_region;
                SELECT pk_i_id INTO @city_id FROM os3n_t_city WHERE s_name = @s_city;
                
                insert into os3n_t_user(dt_reg_date, dt_mod_date, s_name, s_username, s_password, s_secret, s_email, s_website, s_phone_land, s_phone_mobile,
                 b_enabled, b_active, s_pass_code, s_pass_date, s_pass_ip, fk_c_country_code, s_country, s_address, s_zip, fk_i_region_id, s_region, fk_i_city_id, 
                 s_city, fk_i_city_area_id, s_city_area, d_coord_lat, d_coord_long, b_company, i_items, i_comments, dt_access_date, s_access_ip) values
                (@dt_reg_date , @dt_mod_date, @s_name, @s_username, @s_password, @s_secret, @s_email, @s_website, @s_phone_land, @s_phone_mobile, 
                @b_enabled, @b_active, @s_pass_code, @s_pass_date, @s_access_ip, @fk_c_country_code, @s_country, @s_address, @s_zip,  @region_id, 
                @s_region, @city_id , @s_city, @fk_i_city_area_id,  @s_city, @d_coord_lat, @d_coord_long, @b_company, @i_items, @i_comments, @dt_access_date, @s_access_ip);
                
                SELECT LAST_INSERT_ID() into @item_primary_key;
                
                set @fk_c_locale_code := 'en_US';
                
                insert into os3n_t_user_description(fk_i_user_id, fk_c_locale_code, s_info) values(@item_primary_key, @fk_c_locale_code, @s_info);
                
                commit;
            ";
            $result = $this->con->multi_query($query);
            $results = array();
            $results['error'] = $result;
            return $results;
        }
        public function checkPasswordByEmail($email, $password)
        {
            $stmt = $this->con->prepare("select s_region, s_city, s_password, pk_i_id from os3n_t_user where (s_email = ? or s_phone_mobile = ? );");
            $stmt->bind_param("ss", $email, $email);
            $stmt->execute();
            $stmt->bind_result($region, $city, $s_password, $pk_i_id);

            $stmt->fetch();
            $returning = array();
            if ($s_password == $password) {
                $returning['error'] = false;
                $returning['user_id'] = $pk_i_id;
                $returning['city'] = $city;
                $returning['region'] = $region;
            } else {
                $returning['error'] = true;
                $returning['user_id'] = '';
                $returning['city'] = '';
                $returning['region'] = '';
            }
            return $returning;
        }
        public function getUserIdWhenRegistering($email, $password)
        {
            $stmt = $this->con->prepare("select pk_i_id from os3n_t_user where s_email = ? and s_password = ? ;");
            $stmt->bind_param("ss", $email, $password);
            $stmt->execute();
            $stmt->bind_result($pk_i_id);
            $stmt->fetch();
            return $pk_i_id;
        }
        public function imageUpload($image, $title)
        {
            $upload_path = "uploads/$title.jpg";
            $query = "inser into image(title, path) values ('$title', '$image_path');";
            if ($query) {
                $done = $this->con->file_put_contents($upload_path, base64_decode($image));
            }
        }
        public function addComment($user_id, $item_id, $comment_title, $comment_body)
        {
            $query = "
                start transaction;
                set @pk_i_id := '$user_id';

                select s_email, s_name into @user_email, @user_name from os3n_t_user where pk_i_id = '$user_id';
                
                set @fk_i_item_id := '$item_id';
                set @dt_pub_date := now();
                set @s_title := '$comment_title';
                set @s_body := '$comment_body';
                set @b_enabled := 1;
                set @b_active := 1;
                set @b_spam := 0;

                SET FOREIGN_KEY_CHECKS=0;

                insert into os3n_t_item_comment( fk_i_item_id, dt_pub_date, s_title, s_author_name, s_author_email, s_body, b_enabled, b_active, b_spam, fk_i_user_id) values
                ( @fk_i_item_id, @dt_pub_date, @s_title, @user_name, @user_email, @s_body, @b_enabled, @b_active, @b_spam, @pk_i_id);
                
                update os3n_t_user set i_comments = i_comments + 1 where pk_i_id = '$user_id';

                SET FOREIGN_KEY_CHECKS=1;
                commit;
            ";

            $result = $this->con->multi_query($query);
            $results = array();
            $results['error'] = $result;
            return $result;
        }
        public function updateComment($comment_body, $comment_title, $comment_id)
        {
            $stmt = $this->con->prepare("update os3n_t_item_comment set s_body = ? , s_title = ? where pk_i_id = ?;");
            $stmt->bind_param("ssi", $comment_body, $comment_title, $comment_id);
            $return = $stmt->execute();
            // $stmt->fetch();
            return $return;
        }
        public function deleteComment($user_id, $comment_id)
        {
            $query = "
                Delete from os3n_t_item_comment where pk_i_id = '$comment_id';
                update os3n_t_user set i_comments = i_comments - 1 where pk_i_id = '$user_id';
            ";
            $result = $this->con->multi_query($query);
            return $result;
        }
        public function updateUserInfo(
            $user_id,
            $full_user_name,
            $user_name,
            $password,
            $email,
            $website,
            $landline,
            $mobile_no,
            $user_address,
            $zip,
            $has_company,
            $region_name,
            $city_name,
            $ip,
            $coord_lat,
            $coord_long,
            $user_desc)
            {
            $query = "
                start transaction;
                set @user_id := '$user_id';
                set @s_name := '$full_user_name';
                set @s_username := '$user_name';
                set @s_password := '$password';
                set @s_email := '$email';
                set @s_website := '$website';
                set @s_phone_land := '$landline';
                set @s_phone_mobile := '$mobile_no';
                set @s_address := '$user_address';
                set @s_zip := '$zip';
                set @b_company := '$has_company';
                set @s_region := '$region_name';
                set @s_city := '$city_name';
                set @s_access_ip := '$ip';
                set @d_coord_lat := '$coord_lat';
                set @d_coord_long := '$coord_long';
                set @dt_reg_date := now();
                set @dt_access_date := now();
                set @s_country := 'Bangladesh';
                set @fk_c_country_code := 88;
                set @b_enabled := 1;
                set @b_active := 1;
                set @s_secret := 'asdasd0';
                set @dt_mod_date := now();
                set @s_info := '$user_desc';
                set @s_pass_code := 'sdfsd';
                set @s_pass_date := now();
                set @fk_i_city_area_id := null;
                set @i_items := 0;
                set @i_comments := 0;
                
                SELECT pk_i_id INTO @region_id FROM os3n_t_region WHERE s_name = @s_region;
                SELECT pk_i_id INTO @city_id FROM os3n_t_city WHERE s_name = @s_city;
                SET FOREIGN_KEY_CHECKS=0;
                update os3n_t_user set dt_reg_date = @dt_reg_date, dt_mod_date = @dt_mod_date, s_name = @s_name , s_username = @s_username, s_password = @s_password, s_secret = @s_secret,
                s_email =  @s_email, s_website = @s_website, s_phone_land = @s_phone_land, s_phone_mobile = @s_phone_mobile, b_enabled = @b_enabled, b_active =  @b_active,
                s_pass_code = @s_pass_code, s_pass_date = @s_pass_date, s_pass_ip = @s_access_ip, fk_c_country_code =  @fk_c_country_code, s_country =  @s_country, 
                s_address = @s_address, s_zip = @s_zip, fk_i_region_id = @region_id, s_region = @s_region, fk_i_city_id = @city_id, s_city =  @s_city, fk_i_city_area_id = @fk_i_city_area_id,
                s_city_area = @s_city, d_coord_lat = @d_coord_lat, d_coord_long = @d_coord_long, b_company =  @b_company, i_items = @i_items, i_comments =  @i_comments, 
                dt_access_date = @dt_access_date, s_access_ip = @s_access_ip where pk_i_id = '$user_id';
                
                set @fk_c_locale_code := 'en_US';
                
                update os3n_t_user_description set fk_c_locale_code = @fk_c_locale_code, s_info = @s_info where fk_i_user_id = @user_id;
                SET FOREIGN_KEY_CHECKS=1;
                commit;
                
            ";
            $result = $this->con->multi_query($query);
            return $result;
        }
    }




