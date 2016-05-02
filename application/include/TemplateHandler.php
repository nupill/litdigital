<?php
require_once(dirname(__FILE__) . '/../config/general.php');
require_once(dirname(__FILE__) . '/Auth.php');

/**
 * Template functions
 * @author Jonatan
 *
 */
class TemplateHandler {
    
    private $global_file;
    private $header_file;
    private $body_file;
    private $footer_file;
    private $content_file;
    private $navigation_file;
    private $title;
    private $css_files;
    private $js_files;
    private $active_nav_item;
    private $authenticated_only;
    private $authentication_list;
    
    public function __construct() {
        $this->global_file = DEFAULT_GLOBAL_FILE;
        $this->header_file = DEFAULT_HEADER_FILE;
        $this->body_file = DEFAULT_BODY_FILE;
        $this->footer_file = DEFAULT_FOOTER_FILE;
        $this->navigation_file = DEFAULT_NAVIGATION_FILE;
        $this->title = DEFAULT_TITLE;
        $this->css_files = array();
        $this->js_files = array();
        $this->active_nav_item = 0;
        $this->authenticated_only = false;
        $this->authentication_list = array();
    }
    
    public function show() {
        if ($this->is_authenticated_only() && !Auth::check($this->authentication_list)) {
            $this->body_file = DEFAULT_FORBIDDEN_FILE;
        }
        if ($this->template_file_exists($this->global_file)) {
            include(APPLICATION_PATH . '/template/' . $this->global_file);
        }
        else {
            throw new Exception('Global template not found.');
        }
    }
    
    public function show_header() {
        if ($this->template_file_exists($this->header_file)) {
            include (APPLICATION_PATH . '/template/' . $this->header_file);
        }
    }
    
    public function show_body() {
        if ($this->template_file_exists($this->body_file)) {
            include (APPLICATION_PATH . '/template/' . $this->body_file);
        }
    }
    
    public function show_footer() {
        if ($this->template_file_exists($this->footer_file)) {
            include (APPLICATION_PATH . '/template/' . $this->footer_file);
        }
    }
    
    public function show_navigation() {
        if ($this->template_file_exists($this->navigation_file)) {
            include (APPLICATION_PATH . '/template/' . $this->navigation_file);
        }
    }
    
    public function show_content() {
        if ($this->public_file_exists($this->content_file)) {
            include (PUBLIC_PATH . '/' . $this->content_file);
        }
    }
        
    public function import_css() {
        if ($this->css_files) {
            $internal_css_files = array();
            foreach ($this->css_files as $css_file) {
                if ($this->is_external($css_file)) {
                    echo '<link rel="stylesheet" type="text/css" href="'. $css_file . '" />';
                }
                else {
                    $internal_css_files[] = $css_file;
                    echo '<link rel="stylesheet" type="text/css" href="'. CSS_URI . $css_file . '" />';
                }
            }
        }
    }
    
    public function import_js() {
        if ($this->js_files) {
            $internal_js_files = array();
            foreach ($this->js_files as $js_file) {
                if ($this->is_external($js_file)) {
                    echo '<script type="text/javascript" src="' . $js_file . '"></script>';
                }
                else {
                    $internal_js_files[] = $js_file;
                    echo '<script type="text/javascript" src="'. JS_URI . $js_file . '"></script>';
                }
            }
        }
    }
        
    public function is_external($file) {
        if (substr($file, 0, 4) == 'http' || substr($file, 0, 3) == 'www') {
            return true;
        }
        return false;
    }
    
    private function template_file_exists($file) {
        if ($file) {
            return file_exists(APPLICATION_PATH . '/template/' . $file);
        }
        return false;
    }
    
    private function public_file_exists($file) {
        if ($file) {
            return file_exists(PUBLIC_PATH . '/' . $file);
        }
        return false;
    }
    
    public function get_header_file() {
        return $this->header_file;
    }
    
    public function set_header_file($header_file) {
        if ($this->template_file_exists($header_file)) {
            $this->header_file = $header_file;
        }
        else {
            throw new Exception('Header file does not exists.');
        }
    }
    
    public function get_body_file() {
        return $this->body_file;
    }
    
    public function set_body_file($body_file) {
        if ($this->template_file_exists($body_file)) {
            $this->body_file = $body_file;
        }
        else {
            throw new Exception('Body file does not exists.');
        }
    }
    
    public function get_footer_file() {
        return $this->footer_file;
    }
    
    public function set_footer_file($footer_file) {
        if ($this->template_file_exists($footer_file)) {
            $this->footer_file = $footer_file;
        }
        else {
            throw new Exception("Footer file ($footer_file) does not exists.");
        }
    }
    
    public function get_content_file() {
        return $this->content_file;
    }
    
    public function set_content_file($content_file) {
        if ($this->public_file_exists($content_file)) {
            $this->content_file = $content_file;
        }
        else {
            throw new Exception("Content file ($content_file) does not exists.");
        }
    }
    
    public function get_navigation_file() {
        return $this->navigation_file;
    }
    
    public function set_navigation_file($navigation_file) {
        if ($this->template_file_exists($navigation_file)) {
            $this->navigation_file = $navigation_file;
        }
        else {
            throw new Exception("Navigation file ($navigation_file) does not exists.");
        }
    }
    
    public function get_title() {
        return $this->title;
    }
    
    public function set_title($title) {
        $this->title = $title . ' - ' . DEFAULT_TITLE;
    }
    
    public function get_css_files() {
        return $this->css_files;
    }
    
    public function set_css_files($css_files) {
        if (is_array($css_files)) {
            $this->css_files = $css_files;
        }
        else {
            throw new Exception('CSS files must be an array.');
        }
    }
    
    public function add_js_file($js_file) {
        $this->js_files[] = $js_file;
    }
    
    public function add_css_file($css_file) {
        $this->css_files[] = $css_file;
    }
    
    public function get_js_files() {
        return $this->js_files;
    }
    
    public function set_js_files($js_files) {
        if (is_array($js_files)) {
            $this->js_files = $js_files;
        }
        else {
            throw new Exception('JS files must be an array.');
        }
    }
    
    public function get_active_nav_item() {
        return $this->active_nav_item;
    }
    
    public function set_active_nav_item($active_nav_item) {
        if (is_numeric($active_nav_item)) {
            $this->active_nav_item = $active_nav_item;
        }
        else {
            throw new Exception('Active navigation item must be a numeric index');
        }
    }
    
    public function is_authenticated_only() {
        return $this->authenticated_only;
    }
    
    public function set_authenticated_only($authenticated_only) {
        if (is_bool($authenticated_only)) {
            $this->authenticated_only = $authenticated_only;
        }
        else {
            throw new Exception('Authenticated must be a boolean.');
        }
    }
    
    public function set_authentication_list($authentication_list) {
        if (is_array($authentication_list)) {
            $this->authentication_list = $authentication_list;
        }
        else {
            throw new Exception('Authentication list must be an array.');
        }
    }
}
