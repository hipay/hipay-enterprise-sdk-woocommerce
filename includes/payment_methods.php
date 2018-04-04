<?php 
class HipayEnterprisePaymentMethodClass
{

	public $key;
	public $title;
	public $is_credit_card;
	public $is_local_payment;
	public $is_active;
	public $description;
	public $max_amount;
	public $min_amount;
	public $authorized_currencies;
	public $available_currencies;
	public $authorized_countries;
	public $available_countries;


	function __construct($base_method =[]){

       foreach ($base_method as $key => $value) {
           $this->$key = $value;
       }

	}


	/* GET */

	public function get_key(){
		return $this->key;
	}
	function get_title(){
		return $this->title;
	}
	function get_is_credit_card(){
		return $this->is_credit_card;
	}
	function get_is_local_payment(){
		return $this->is_local_payment;
	}
	function get_is_active(){
		return $this->is_active;
	}
	function get_description(){
		return $this->description;
	}
	function get_max_amount(){
		return $this->max_amount;
	}
	function get_min_amount(){
		return $this->min_amount;
	}
	function get_authorized_countries(){
		return $this->authorized_countries;
	}
	function get_available_countries(){
		return $this->available_countries;
	}
	function get_authorized_currencies(){
		return $this->authorized_currencies;
	}
	function get_available_currencies(){
		return $this->available_currencies;
	}

	function get_json()
	{
		return '{ "key":"'.$this->get_key().'", "title":"'.$this->get_title().'","is_credit_card":"'.$this->get_is_credit_card().'","is_local_payment":"'.$this->get_is_local_payment().'","description":"'.$this->get_description().'","max_amount":"'.$this->get_max_amount().'","min_amount":"'.$this->get_min_amount().'","authorized_countries":"'.$this->get_authorized_countries().'","authorized_currencies":"'.$this->get_authorized_currencies().'","is_active":"'.$this->get_is_active().'","available_countries":"'.$this->get_available_countries().'","available_currencies":"'.$this->get_available_currencies().'"}';
	}


	/* SET */

	function set_key($key){
		$this->key = $key;
	}
	function set_title($title){
		$this->title = $title;
	}
	function set_is_credit_card($is_credit_card){
		$this->is_credit_card = (int)$is_credit_card;
	}
	function set_is_local_payment($is_local_payment){
		$this->is_local_payment = (int)$is_local_payment;
	}
	function set_is_active($is_active){
		$this->is_active = (int)$is_active;
	}
	function set_description($description){
		$this->description = $description;
	}
	function set_max_amount($max_amount){
		$this->max_amount = (float)$max_amount;
	}
	function set_min_amount($min_amount){
		$this->min_amount = (float)$min_amount;
	}

	function set_authorized_countries($authorized_countries){
		$this->authorized_countries = $authorized_countries;
	}

	function set_available_countries($available_countries){
		$this->available_countries = "";
		if (strlen($available_countries) > 0) $this->available_countries = substr($available_countries, 0, -1);
	}

	function set_authorized_currencies($authorized_currencies){
		$this->authorized_currencies = "";
		foreach ($authorized_currencies as $key => $value) {
			$this->authorized_currencies .= $key . ",";	
		}
		if (strlen($this->authorized_currencies) > 0) $this->authorized_currencies = substr($this->authorized_currencies, 0, -1);
	}

	function set_available_currencies($available_currencies){
		$this->available_currencies = "";
		foreach ($available_currencies as $key => $value) {
			$this->available_currencies .= $key . ",";	
		}
		if (strlen($this->available_currencies) > 0) $this->available_currencies = substr($this->available_currencies, 0, -1);
	}

}