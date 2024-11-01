<?php
/**
 * OptionsClass
 *
 * EN: update, set, get and delete options in WordPress
 *
 *
 * EN:
 * OptionsClass regroups and manages all options of a plugin or 
 * theme in one option field. The amount of database queries
 * can be reduced and therefore the loading time of blogs
 * can be improved enormously. OptionsClass is designed for
 * developers of WordPress themes or plugins.
 *
 * @package  OptionsClass.php
 * @author   Sergej M&uuml;ller and Frank B&uuml;ltge
 * @since    26.09.2008
 * @change   09.12.2008
 * @access   public
 */


class OptionsClass {
	var $basename;
	var $data;
	var $cache_state;
	
	/**
	 * OptionsClass [Konstruktor]
	 *
	 * EN: set properties and start init
	 *
	 * @package  OptionsClass.php
	 * @author   Sergej M&uuml;ller
	 * @since    26.09.2008
	 * @change   03.12.2008
	 * @access   public
	 * @param    array  $option  Title of the multi-option in the DB [optional]
	 * @param    array  $data    Array with startvalue [optional]
	 */
	function OptionsClass($option = '', $data = array()) {
		if (empty($option) === true) {
			$this->basename = 'OptionsClass_'. md5(get_bloginfo('home'));
		} else {
			$this->basename = $option;
		}
		
		if ($data) {
			$this->init_option($data);
		}
		$this->cache_state = 'clean';
	} // OptionsClass
	
	
	/**
	 * init_option
	 *
	 * EN: init mulit-option in the db
	 *
	 * @package  OptionsClass.php
	 * @author   Sergej M&uuml;ller
	 * @since    26.09.2008
	 * @change   26.09.2008
	 * @access   public
	 * @param    array  $data  Array with startvalues [optional]
	 */
	function init_option($data = array()) {
		$this->data = $data;
		foreach($data as $k => $v ){
			$tmp = $this->basename . '_' .  $k;
			if( $existing = get_option($tmp) ){ // Clean up any existing old-style options
				$data[$k] = $existing;
				delete_option($tmp);
			}
		}
		update_option($this->basename, serialize($data));	
		$this->data = $data;
		$this->cache_state = 'clean';
	} // init_option
	
	
	/**
	 * delete_all_options
	 *
	 * EN: delete the multi-option of the db
	 *
	 * @package  OptionsClass.php
	 * @author   Sergej M&uuml;ller
	 * @since    26.09.2008
	 * @change   26.09.2008
	 * @access   public
	 */
	function delete_all_options() {
		delete_option($this->basename);
		$data = array();
		$this->cache_state = 'clean';
	} // delete_all_options
	
	
	/**
	 * get
	 *
	 * EN: get the value to option
	 *
	 * @package  OptionsClass.php
	 * @author   Sergej M&uuml;ller
	 * @since    26.09.2008
	 * @change   26.09.2008
	 * @access   public
	 * @param    string  $key  Title of the option
	 * @return   mixed         Value of the option [false on error]
	 */
	function get($key) {
		if (empty($key) === true) {
			return false;
		}
		
		if( $this->cache_state == 'dirty' ){
			$this->data = unserialize(get_option($this->basename));
			$this->cache_state = 'clean';
		}
		
		return @$this->data[$key];
	} // get
	
	
	function get_option_array(){
		if( $this->cache_state == 'dirty' ){
			$this->data = unserialize(get_option($this->basename));
			$this->cache_state = 'clean';
		}
		return( @$this->data );
	} // get_option_array

	/**
	 * set
	 *
	 * EN: Set new options to value
	 *
	 * @package  OptionsClass.php
	 * @author   Sergej M&uuml;ller
	 * @since    26.09.2008
	 * @change   07.12.2008
	 * @access   public
	 * @param    mixed    $key    Title of the option [alternativ Array with optionen]
	 * @param    string   $value  Value of the option [optional]
	 * @return   boolean          False on error
	 */
	function set($key, $value = '') {
		if (empty($key) === true) {
			return false;
		}
		
		$new_data = array($key => $value);
		
		if( $this->cache_state == 'dirty' ){
			$this->data = unserialize(get_option($this->basename));
			$this->cache_state = 'clean';
		}
		$this->data = array_merge( $this->data, $new_data );
		
		update_option($this->basename, serialize($this-data) );
		$this->cache_state = 'clean';
		return true;
	} // set

	function get_names(){
		if( $this->cache_state == 'dirty' ){
			$this->data = unserialize(get_option($this->basename));
			$this->cache_state = 'clean';
		}
		return( array_keys($this->data) );
	} // get_names
}
?>
