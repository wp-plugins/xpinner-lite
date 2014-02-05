<?php
/*
  Plugin Name: xPinner Lite
  Author: CyberSEO.NET
  Author URI: http://www.cyberseo.net/
  Plugin URI: http://www.cyberseo.net/xpinner-lite/
  Version: 1.1
  Description: Automatically pins images to Pinterest.com
 */

if (!function_exists("get_option") || !function_exists("add_filter")) {
    die();
}

define('XPINNER_OPTIONS', 'xpinner_options');
define('XPINNER_LAST_PIN_TIME', 'xpinner_last_pin_time');
define('XPINNER_LAST_OPEN_POST', 'xpinner_last_open_post');
define('XPINNER_USER_AGENT', 'Mozilla/5.0 (X11; Linux i686; rv:16.0) Gecko/20100101 Firefox/16.0 Iceweasel/16.0');

$xpinner_default_options = array(
    'pinboard' => 'pinterest',
    'pinterest_email' => '',
    'pinterest_password' => '',
    'pinterest_board_id' => '',
    'image_to_pin' => 'first',
    'image_source' => 'post',
    'custom_filed_name' => '',
    'pin_limit' => 3600,
    'limit_older_posts' => 1440,
    'image_min' => 0.3,
    'image_max' => 6,
    'magic' => md5(get_option('site_name'))
);

$xpinner_options = get_option(XPINNER_OPTIONS, array());

foreach ($xpinner_default_options as $item => $value) {
    if (!isset($xpinner_options[$item])) {
        $xpinner_options[$item] = $value;
    };
}

if (is_admin() && isset($_POST['xpinner_save_changes'])) {
    unset($_POST['xpinner_save_changes']);
    $xpinner_options = $_POST;
    update_option(XPINNER_OPTIONS, $xpinner_options);
}

function xpinner_settings() {
    global $xpinner_options, $wp_version;

    $xpinner_message = '';
    if (!strlen($xpinner_message)) {
        if (isset($wp_version) && $wp_version < '3.6.0') {
            $xpinner_message = '<div id="message" class="updated fade"><h3>Warning!</h3>
	      <p>Your WordPress engine is outdated. Please update it at least to version 3.6.0. Otherwise the plugin will may not work correctly.</p></div>';
        }
        if (!is_writable(plugin_dir_path(__FILE__) . 'cookie.txt')) {
            $xpinner_message = '<div id="message" class="updated fade"><h3>Warning!</h3>
	      <p>File <strong>' . plugin_dir_path(__FILE__) . 'cookie.txt' . '</strong> is not writable. You must chmod it to 666. Otherwise the plugin will not work.</p></div>';
        }
    }
    ?>

    <style type="text/css">
        .xpinner-settings { width:68%; margin-right:20px; }
        .xpinner-ad { width:210px; max-width:350px; margin-top:20px; border:1px solid #ddd; background:#fcfcfc; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius: 3px; }
        .xpinner-ad h3 { margin-top:5px; }
        .xpinner-ad h3.metabox-title { background: #f9f9f9 0 -50px repeat-x; background: -moz-linear-gradient(top, #2e9fd2 0%, #21759B 100%); background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#2e9fd2), color-stop(100%,#21759B)); background: -webkit-linear-gradient(top, #2e9fd2 0%,#21759B 100%); background: -o-linear-gradient(top, #2e9fd2 0%,#21759B 100%); background: -ms-linear-gradient(top, #2e9fd2 0%,#21759B 100%); background: linear-gradient(top, #2e9fd2 0%,#21759B 100%); -webkit-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.4) inset; -moz-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.4) inset;        box-shadow: 0 1px 0 rgba(255, 255, 255, 0.4) inset;     border: 1px solid; border-color: #21759b; border-bottom-color: #1e6a8d; -webkit-box-shadow: inset 0 1px 0 rgba(120,200,230,0.5); box-shadow: inset 0 1px 0 rgba(120,200,230,0.5); color: #fff; text-decoration: none; text-shadow: 0 1px 0 rgba(0,0,0,0.5); margin:0; line-height:32px; font-weight:normal; padding:0 10px; }
        .xpinner-ad .inner { padding:15px 10px; }
        .xpinner-ad .xpinner-link { font-family: Georgia, "Times New Roman", "Bitstream Charter", Times, serif; font-style:italic; margin:0; }
        .xpinner-ad .xpinner-link a { vertical-align: middle; padding: 5px 0 0 4px; }
    </style>

    <script type="text/javascript">

        function toggleCustomField() {
            if (document.options.image_source.value == "custom field") {
                document.getElementById("custom_filed_name").style.display = 'table-row';
            } else {
                document.getElementById("custom_filed_name").style.display = 'none'; 
            }
        }        
                                                                                                                                                                                                                        
        function toggleCron() {
            if (document.getElementById('use_cron').checked) {
                document.getElementById("cron_note").style.display = 'inline';
                document.getElementById("pin_limit").style.display = 'none';
            } else {
                document.getElementById("cron_note").style.display = 'none';
                document.getElementById("pin_limit").style.display = 'table-row';
            }
        }
    </script>    

    <div class="wrap">
        <h2>xPinner Lite</h2>
        <p><?php echo $xpinner_message; ?></p>

        <div class="metabox-holder postbox-container xpinner-settings">

            <form method="post" name="options">
                <table class="form-table" style="margin-top: .5em" width="100%">

                    <tr valign="top" onchange="toggleCron();">
                        <th align="left">Use cron</th>
                        <td>
                            <input type="checkbox" name="use_cron" id="use_cron" <?php if (isset($xpinner_options['use_cron'])) echo "checked "; ?>>
                            <div id="cron_note" style="display:none;">- In this mode, you need to manually configure cron at your host. For example, if you want run a cron job once a hour, just add the following line into your crontab:<br />
                                <strong>0 * * * * /usr/bin/curl --silent <?php echo get_option('siteurl') . '/?xpinner=' . $xpinner_options['magic']; ?></strong></div>
                        </td>
                    </tr>                     

                    <tr valign="top" <?php if (!isset($wp_version)) echo 'style="display:none;"'; ?>>
                        <th align="left">Image source</th>
                        <td>
                            <select name="image_source" size="1" onchange="toggleCustomField();">
                                <?php
                                echo '<option ' . (($xpinner_options["image_source"] == "post") ? 'selected ' : '') . 'value="post">post</option>' . "\n";
                                echo '<option ' . (($xpinner_options["image_source"] == "gallery") ? 'selected ' : '') . 'value="gallery">gallery</option>' . "\n";
                                echo '<option ' . (($xpinner_options["image_source"] == "featured image") ? 'selected ' : '') . 'value="featured image">featured image</option>' . "\n";
                                echo '<option ' . (($xpinner_options["image_source"] == "custom field") ? 'selected ' : '') . 'value="custom field">custom field</option>' . "\n";
                                ?>
                            </select>
                        </td>
                    </tr>  

                    <tr valign="top" id="custom_filed_name" <?php if (!isset($wp_version)) echo 'style="display:none;"'; ?>>
                        <th>Custom field name</th>                                    
                        <td>
                            <input type="text" name="custom_filed_name" value="<?php echo $xpinner_options['custom_filed_name']; ?>" size="30">
                        </td>
                    </tr>       

                    <tr valign="top" <?php if (!isset($wp_version)) echo 'style="display:none;"'; ?>>
                        <th align="left">Image to pin</th>
                        <td>
                            <select name="image_to_pin" size="1">
                                <?php
                                echo '<option ' . (($xpinner_options["image_to_pin"] == "first") ? 'selected ' : '') . 'value="first">first</option>' . "\n";
                                echo '<option ' . (($xpinner_options["image_to_pin"] == "last") ? 'selected ' : '') . 'value="last">last</option>' . "\n";
                                echo '<option ' . (($xpinner_options["image_to_pin"] == "random") ? 'selected ' : '') . 'value="random">random</option>' . "\n";
                                ?>
                            </select>
                        </td>
                    </tr>     

                    <tr valign="top" id="pin_limit" <?php if (!isset($wp_version)) echo 'style="display:none;"'; ?>>
                        <th align="left" title="Don't pin more than 100 times per hour!">Don't pin more than once per</th>
                        <td align="left"><input type="text" name="pin_limit" value="<?php echo $xpinner_options['pin_limit']; ?>" size="10"> seconds.</td>
                    </tr>     

                    <tr valign="top" <?php if (!isset($wp_version)) echo 'style="display:none;"'; ?>>
                        <th align="left">Don't pin if post is older than</th>
                        <td align="left"><input type="text" name="limit_older_posts" value="<?php echo $xpinner_options['limit_older_posts']; ?>" size="10"> minutes.</td>
                    </tr>  

                    <tr valign="top">
                        <th align="left">Pin images that bigger than</th>
                        <td align="left">
                            <input type="text" name="image_min" value="<?php echo $xpinner_options['image_min']; ?>" size="2"> Megapixels
                            and smaller than
                            <input type="text" name="image_max" value="<?php echo $xpinner_options['image_max']; ?>" size="2"> Megapixels
                        </td>
                    </tr>                      

                </table>

                <br />

                <table id="pinterest" class="form-table" style="margin-top: .5em;" width="100%">
                    <tr valign="top">
                        <th align="right">Pinterest.com email</th>
                        <td align="left"><input type="text" name="pinterest_email" value="<?php echo $xpinner_options['pinterest_email']; ?>" size="30"></td>
                    </tr>
                    <tr valign="top">
                        <th align="left">Pinterest.com password</th>
                        <td align="left"><input type="text" name="pinterest_password" value="<?php echo $xpinner_options['pinterest_password']; ?>" size="30"></td>
                    </tr>         
                    <tr valign="top">
                        <th align="left">Pinterest.com board title</th>
                        <td align="left"><input type="text" name="pinterest_board_id" value="<?php echo $xpinner_options['pinterest_board_id']; ?>" size="30"></td>
                    </tr>    
                </table>     

                <br />

                <input type="submit" name="xpinner_save_changes" class="button-primary" value="Save Changes" />
            </form>
        </div>

        <div class="xpinner-ad postbox-container">
            <h3 class="metabox-title">Upgrade to xPinner</h3>
            <div class="inner">
                <h3><a href="http://www.cyberseo.net/xpinner/" target="_blank">Get The Full Version!</a></h3>
                <p>The full version of <strong>xPinner</strong> is intended to automatically pin images to two dozen of social pinboards for men, for women and Adult-oriented ones.</p>  
                <p align="center"><a href="http://www.cyberseo.net/xpinner/" target="_blank"><img src="<?php echo plugins_url('/images/xpinner.png', __FILE__); ?>" /></a></p>       
                <hr />
                <p class="xpinner-link">Created by <a href="http://www.cyberseo.net/#contacts" target="_blank">CyberSEO</a></p>
            </div>
        </div>

    </div>

    <script type="text/javascript">
        toggleCustomField();
        toggleCron();
    </script>     
    <?php
}

function xpinner_get_images($xpinner_options, $post) {

    if ($xpinner_options['image_source'] == 'post') {
        preg_match_all('/<img.+?src=[\'\"](.+?)[\'\"].*?>/is', $post->post_content . $post->post_excerpt, $matches);
        $image_urls = array_unique($matches[1]);
    } elseif ($xpinner_options['image_source'] == 'gallery') {
        $gallery = do_shortcode('[gallery size="full"]');
        preg_match_all('/ src="(.+?\.jpg|jpeg|gif|png)" class="attachment-full"/', $gallery, $matches);
        if (count($matches[1])) {
            $image_urls = $matches[1];
        } else {
            global $nggdb, $wpdb;
            if (isset($nggdb)) {
                preg_match('/\[nggallery id=(\d+)\]/', $post->post_content, $matches);
                if (is_numeric($matches[1])) {
                    $imageIDs = $nggdb->get_ids_from_gallery($matches[1]);
                    $image_urls = array();
                    foreach ($imageIDs as $imageID) {
                        $imageID = intval($imageID);
                        list($fileName, $picturepath ) = $wpdb->get_row("SELECT p.filename, g.path FROM $wpdb->nggpictures AS p INNER JOIN $wpdb->nggallery AS g ON (p.galleryid = g.gid) WHERE p.pid = '$imageID' ", ARRAY_N);
                        if (empty($picturepath)) {
                            $picturepath = $wpdb->get_var("SELECT g.path FROM $wpdb->nggpictures AS p INNER JOIN $wpdb->nggallery AS g ON (p.galleryid = g.gid) WHERE p.pid = '$imageID' ");
                        }
                        $image_urls[] = site_url() . '/' . $picturepath . '/' . $fileName;
                    }
                }
            }
        }
    } elseif ($xpinner_options['image_source'] == 'featured image') {
        $featured_image_html = get_the_post_thumbnail($post->ID, 'full');
        preg_match_all('/<img.+?src=[\'\"](.+?)[\'\"].*?>/is', $featured_image_html, $matches);
        $image_urls = array_unique($matches[1]);
    } elseif ($xpinner_options['image_source'] == 'custom field') {
        $image_urls = array(get_post_meta($post->ID, $xpinner_options['custom_filed_name'], true));
    } else {
        $image_urls = array();
    }

    return $image_urls;
}

function xpinner_get_image_url($xpinner_options, $image_urls) {

    if (count($image_urls)) {
        if ($xpinner_options["image_to_pin"] == 'first') {
            $image_url = $image_urls[0];
        } elseif ($xpinner_options["image_to_pin"] == 'last') {
            $image_url = $image_urls[count($image_urls) - 1];
        } elseif ($xpinner_options["image_to_pin"] == 'random') {
            $image_url = $image_urls[rand(0, count($image_urls) - 1)];
        }
    } else {
        return false;
    }

    return $image_url;
}

function xpinner_pin_pinterest($xpinner_options, $post, $image_url) {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, XPINNER_USER_AGENT);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_COOKIEJAR, plugin_dir_path(__FILE__) . 'cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, plugin_dir_path(__FILE__) . 'cookie.txt');
    curl_setopt($ch, CURLOPT_HTTPGET, true);

    $url = 'https://www.pinterest.com/login/?next=%2Flogin%2F';

    curl_setopt($ch, CURLOPT_REFERER, 'https://pinterest.com/login/');
    curl_setopt($ch, CURLOPT_URL, $url);
    $res = curl_exec($ch);

    if (strlen(curl_error($ch))) {
        curl_close($ch);
        return false;
    }

    preg_match('/csrftoken=(.*?);/', $res, $matches);
    $csrftoken = $matches[1];
    preg_match('/_pinterest_sess="(.*?)"/', $res, $matches);
    $_pinterest_sess = $matches[1];

    if (!strlen(trim($_pinterest_sess))) {
        curl_close($ch);
        return false;
    }

    $url = 'https://www.pinterest.com/resource/UserSessionResource/create/';
    $data = 'source_url=%2Flogin%2F&data=%7B%22options%22%3A%7B%22username_or_email%22%3A%22' . urlencode($xpinner_options['pinterest_email']) . '%22%2C%22password%22%3A%22' . urlencode($xpinner_options['pinterest_password']) . '%22%7D%2C%22context%22%3A%7B%22app_version%22%3A%22f94de18%22%7D%7D&module_path=App()%3ELoginPage()%3ELogin()%3EButton(class_name%3Dprimary%2C+text%3DLog+in%2C+type%3Dsubmit%2C+tagName%3Dbutton%2C+size%3Dlarge)';

    curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-CSRFToken:' . $csrftoken, 'HOST:www.pinterest.com', 'X-NEW-APP:1', 'Referer:https://www.pinterest.com/login/', 'X-Requested-With:XMLHttpRequest'));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $res = curl_exec($ch);

    preg_match('/"username": "(.*?)"/', $res, $matches);
    $user = $matches[1];

    if (!strlen($user)) {
        curl_close($ch);
        return false;
    }

    $board_url = urlencode(mb_strtolower(preg_replace('/\s+/', '-', preg_replace('/[\'&-]/', ' ', stripslashes($xpinner_options['pinterest_board_id'])))));

    $url = 'http://www.pinterest.com/' . $user . '/' . $board_url;
    curl_setopt($ch, CURLOPT_HTTPGET, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));

    $res = curl_exec($ch);

    preg_match('/"board_id": "(\d+)"/', $res, $matches);
    $board_id = $matches[1];

    $url = 'http://www.pinterest.com/resource/PinResource/create/';
    $data = 'data=%7B%22options%22%3A%7B%22board_id%22%3A%22' . $board_id . '%22%2C%22description%22%3A%22' . urlencode($post->post_title) . '%22%2C%22link%22%3A%22' . urlencode(get_permalink($post->ID)) . '%22%2C%22image_url%22%3A%22' . urlencode($image_url) . '%22%2C%22method%22%3A%22scraped%22%7D%2C%22context%22%3A%7B%22app_version%22%3A%2291bf%22%7D%7D&source_url=%2Fpin%2Ffind%2F%3Furl%3D' . urlencode($image_url) . '&module_path=App()%3EImagesFeedPage(resource%3DFindPinImagesResource(url%3D' . urlencode($image_url) . '))%3EGrid()%3EPinnable(url%3D' . urlencode($image_url) . '%2C+link%3D' . urlencode(get_permalink($post->ID)) . '%2C+type%3Dpinnable)%23Modal(module%3DPinCreate())';
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-CSRFToken:' . $csrftoken, 'X-Requested-With:XMLHttpRequest'));
    curl_setopt($ch, CURLOPT_REFERER, 'http://pinterest.com/');
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $res = curl_exec($ch);
    curl_close($ch);

    if (strpos($res, '"error": null') === false) {
        return false;
    }

    return true;
}

function xpinner_is_cron() {
    global $xpinner_options;
    return (!is_admin() && isset($_GET['xpinner']) && $_GET['xpinner'] == $xpinner_options['magic'] && isset($xpinner_options['use_cron']));
}

function xpinner_do_pin() {
    global $xpinner_options, $post;

    if (xpinner_is_cron() || !isset($xpinner_options['use_cron'])) {

        if (xpinner_is_cron()) {
            $last_open_post = get_option(XPINNER_LAST_OPEN_POST);
            if ($last_open_post) {
                $post = get_post($last_open_post);
            } else {
                $args = array('numberposts' => 1, 'offset' => 0, 'orderby' => 'post_date', 'order' => 'DESC', 'post_status' => 'publish');
                $posts = get_posts($args);
                $post = get_post($posts[0]->ID);
            }
            unset($_GET['xpinner']);
        }

        $gmt_offset = 3600 * intval(get_option('gmt_offset'));

        if (!isset($xpinner_options['use_cron'])) {
            $last_pin_time = intval(get_option(XPINNER_LAST_PIN_TIME, 0));
        } else {
            $last_pin_time = 0;
        }

        if ($post->post_status == 'publish' && strtotime($post->post_date) >= (time() + $gmt_offset - 60 * intval($xpinner_options['limit_older_posts'])) && time() >= $last_pin_time + intval($xpinner_options['pin_limit'])) {

            update_option(XPINNER_LAST_PIN_TIME, time());

            $images = xpinner_get_images($xpinner_options, $post);

            if (count($images)) {

                $image_url = xpinner_get_image_url($xpinner_options, $images);
                $img_info = getimagesize($image_url);
                $size = floatval($img_info[0]) * floatval($img_info[1]) / 1000000;

                if ($size >= floatval($xpinner_options['image_min']) && $size <= floatval($xpinner_options['image_max'])) {

                    if (get_post_meta($post->ID, 'xPinner', true) != 'pinterest.com' &&
                            strlen($xpinner_options['pinterest_email']) &&
                            strlen($xpinner_options['pinterest_password']) &&
                            strlen($xpinner_options['pinterest_board_id'])) {

                        if (xpinner_pin_pinterest($xpinner_options, $post, $image_url)) {
                            update_post_meta($post->ID, 'xPinner', 'pinterest.com');
                        }

                        file_put_contents(plugin_dir_path(__FILE__) . 'cookie.txt', '', LOCK_EX);
                    }
                }
            }
        }
    } else {
        if (isset($post)) {

            if ($post->post_status == 'publish' &&
                    strtotime($post->post_date) >= (time() + $gmt_offset - 60 * intval($xpinner_options['limit_older_posts']))) {

                if (get_post_meta($post->ID, 'xPinner', true) != 'pinterest.com' &&
                        strlen($xpinner_options['pinterest_email']) &&
                        strlen($xpinner_options['pinterest_password']) &&
                        strlen($xpinner_options['pinterest_board_id'])) {

                    update_option(XPINNER_LAST_OPEN_POST, $post->ID);

                    return;
                }
            }
        }

        update_option(XPINNER_LAST_OPEN_POST, false);
    }
}

if (xpinner_is_cron()) {
    add_action('shutdown', 'xpinner_do_pin');
}

function xpinner_content($content) {
    if ((is_single() || is_page()) && function_exists('xpinner_do_pin')) {
        add_action('shutdown', 'xpinner_do_pin');
    }
    return $content;
}

function xpinner_main_menu() {
    add_options_page('xPinner Lite Settings', 'xPinner Lite', 'administrator', 'xpinner', 'xpinner_settings');
}

if (is_admin()) {
    add_action('admin_menu', 'xpinner_main_menu');
} else {
    add_filter('the_content', 'xpinner_content');
}
?>