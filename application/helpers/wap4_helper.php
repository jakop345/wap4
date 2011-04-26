<?php
if(!defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('check_mobile'))
{
    /**
     * Redirects to mobile versionif user agent is detected as mobile phone by
     * wurfl library
     */
    function check_mobile()
    {
            $ci=& get_instance();
            
            if(!isMobile()) {
                
                $ci->load->library('wurfl');
		$ci->wurfl->load($_SERVER);
                $bIsMobile = $ci->wurfl->getCapability("is_wireless_device");
                if($bIsMobile == "true")
                {
                    redirect("http://".$ci->config->item('mobile_host'), "location");
                    exit;
                }
                
            }
            
    }
}


if ( ! function_exists('load_settings'))
{
    /**
     * Loads all basic settings
     */
    function load_settings()
    {
            $ci=& get_instance();
            
            check_mobile();

            $ci->load->library('ffmpeg');
            $ci->config->load('ffmpeg');
            $uniqid                   = uniqid();
            $ci->data['allowed']    = "'".implode("','", $ci->config->item('ffmpeg_allowed'))."'";
            $ci->data['uniqid']     = $uniqid;
            $ci->data['message']    = '';
            $ci->data['users']      = '';
            $ci->data['attr']       = array('id' => 'conv');
            $ci->data['formats']    = $ci->ffmpeg->ffmpeg_formats;
            $ci->data['navigation'] = $ci->config->item('navigation');
            $ci->data['lang']       = $ci->lang->lang();        

            $ci->load->model('site_model');

            if (!$ci->ion_auth->logged_in())
            $ci->data['max'] = $ci->site_model->get_setting('file_size_unregistered')*1024;
            else
            $ci->data['max'] = $ci->site_model->get_setting('file_size_registered')*1024;
            
    }
}


if ( ! function_exists('irAjax'))
{
    /**
     * Checks if request is intended as ajax request by going trough
     * request_uri parts and searching for "caur_ajax" part
     * @return boolean true if it is ajax request, otherwise- false
     */
    function irAjax()
    {
            $caur_ajax = false;
            $ci=& get_instance();
            $segs = $ci->uri->segment_array();

            foreach ($segs as $segment)
            {
                if($segment == "caur_ajax")
                {
                    $caur_ajax = true;
                    break;
                }
            }
            
            return $caur_ajax;
    }
}

if ( ! function_exists('getTitle'))
{
    /**
     * Returns title tag for header
     * @return string 
     */
    function getTitle()
    {
            $caur_ajax = "main";
            $ci=& get_instance();
            $segs = array_reverse($ci->uri->segment_array());
            
            $additional_title = "";
            
            foreach ($segs as $segment)
            {
                if($segment == "login")
                {
                    $caur_ajax = "login";
                    break;
                }
                elseif($segment == "converter")
                {
                    $caur_ajax = "converter";
                    break;
                }
                elseif($segment == "about")
                {
                    if(!isMobile())
                        $caur_ajax = "about";
                    else
                        $caur_ajax = "mabout";
                    break;
                }
                elseif($segment == "codecs")
                {
                    $caur_ajax = "codecs";
                    break;
                }elseif($segment == "formats")
                {
                    $caur_ajax = "formats";
                    break;
                }
                elseif($segment == "howto")
                {
                    if(!isMobile())
                        $caur_ajax = "howto";
                    else
                        $caur_ajax = "mhowto";
                    break;
                }
                elseif($segment == "create_user")
                {
                    if(!isMobile())
                        $caur_ajax = "create_user";
                    else
                        $caur_ajax = "mcreate_user";
                    break;
                }
                elseif($segment == "news")
                {
                    $caur_ajax = "news";
                    if(is_numeric($ci->uri->segment(4))) {
                        $additional_title = " ".$ci->uri->segment(4)." ";
                    }
                    break;
                } else {
                    
                }
                
            }
            
            /**
             * Special page title for news archive
             */
            if($ci->uri->segment(2) == "news" && $ci->uri->segment(3) == "archive") {
                $ci->load->model('news_model');
                $data = $ci->news_model->get_one_news($ci->uri->segment(4));
                $ci->load->helper('text');
                $n = $data->result_object();
                $nWordsInTitle = (isMobile()) ? 4 : 6;
                return trim(word_limiter(strip_tags($n[0]->news), $nWordsInTitle));
            }
            else
                return lang("title.".$caur_ajax).$additional_title;
    }
}

if ( ! function_exists('array_sort'))
{
    function array_sort($array, $on, $order=SORT_ASC)
    {
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
            break;
            case SORT_DESC:
                arsort($sortable_array);
            break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
    }
}

if ( ! function_exists('objectsIntoArray'))
{
    function objectsIntoArray($arrObjData, $arrSkipIndices = array())
    {
    $arrData = array();
   
    // if input is object, convert into array
    if (is_object($arrObjData)) {
        $arrObjData = get_object_vars($arrObjData);
    }
   
    if (is_array($arrObjData)) {
        foreach ($arrObjData as $index => $value) {
            if (is_object($value) || is_array($value)) {
                $value = objectsIntoArray($value, $arrSkipIndices); // recursive call
            }
            if (in_array($index, $arrSkipIndices)) {
                continue;
            }
            $arrData[$index] = $value;
        }
    }
    return $arrData;
    }
}


if ( ! function_exists('youtube'))
{
    /**
     * loads Youtube view
     */
    function youtube()
    {
        $ci=& get_instance();
        //if($ci->ion_auth->logged_in())
        //{
            $ci->load->view("youtube");
        //}
    }
}

if(!function_exists('sanitize_name')) {
    /**
     * Sanitizes string for seo
     * @param string $string
     * @param boolean $force_lowercase
     * @param boolean $anal
     * @return type 
     */
    function sanitize_name($string, $force_lowercase = true, $anal = false) {
    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
                   "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
                   "â€”", "â€“", ",", "<", ".", ">", "/", "?");
    $clean = trim(str_replace($strip, "", strip_tags($string)));
    $clean = preg_replace('/\s+/', "-", $clean);
    $clean = ($anal) ? preg_replace("/[^a-zā-žA-ZĀ-Ž0-9]/", "", $clean) : $clean ;
    return ($force_lowercase) ?
        (function_exists('mb_strtolower')) ?
            mb_strtolower($clean, 'UTF-8') :
            strtolower($clean) :
        $clean;
    }
}

if(!function_exists('translit')) {
    /**
     * Returns transliterated string
     * @param string $text
     * @return string 
     */
    function translit($text) {
        $text = trim($text, "-");
        $text = utf8_encode($text);
        $text = iconv("UTF-8", "ISO-8859-1//TRANSLIT//IGNORE", $text);
        $text = utf8_encode($text);
        $text = iconv("UTF-8", "ASCII//IGNORE", $text);
        $text = strtolower($text);

        return $text;
    }
    
}

if(!function_exists('isMobile')) {
    /**
     * Checks if it is mobile version of converter
     * @return boolean
     */
    function isMobile() {
        $ci=& get_instance();
        if($_SERVER["SERVER_NAME"] == $ci->config->item("mobile_host") || $_SERVER["SERVER_NAME"] == "testm.wap4.org")
            return true;
        else
            return false;

    }

}