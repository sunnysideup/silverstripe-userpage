<?php

/**
	* Page containing an edit details form
	* Uses Member::getMemberFormFields() to know what to make available for editing
	*/
class RegisterAndEditDetailsPage extends Page {

	static $icon = "userpage/images/treeicons/RegisterAndEditDetailsPage";

	static $can_be_root = false;

	static $db = array(
		"ThankYouTitle" => "Varchar(255)",
		"ThankYouContent" => "HTMLText",
		"WelcomeTitle" => "Varchar(255)",
		"WelcomeContent" => "HTMLText",
		"TitleLoggedIn" => "Varchar(255)",
		"MenuTitleLoggedIn" => "Varchar(255)",
		"MetaTitleLoggedIn" => "Varchar(255)",
		"ContentLoggedIn" => "HTMLText",
		"ErrorEmailAddressAlreadyExists" => "Varchar(255)",
		"ErrorBadEmail" => "Varchar(255)",
		"ErrorPasswordDoNotMatch" => "Varchar(255)",
		"ErrorMustSupplyPassword" => "Varchar(255)"
	);

	static $register_group_title = "Registered users";

	static $register_group_code = "registrations";

	static $register_group_access_key = "REGISTRATIONS";

	protected function showLoggedInFields() {
		if(!$this->isCMSRead() && Member::currentUser()  ) {
			return true;
		}
	}

	protected function isCMSRead () {
		return $this->isCMS || Director::urlParam("URLSegment") == "admin";
	}

	/**
	 * Returns a link to this page that will, on completion,
	 * redirect back to the another page
	 *@param String - $link
	 *@return String - $link
	 **/

	public function link_for_going_to_page_via_making_user($link) {
		$registerAndEditDetailsPage = DataObject::get_one("RegisterAndEditDetailsPage");
		if($registerAndEditDetailsPage) {
			return $registerAndEditDetailsPage->Link()."/?BackURL=".urlencode($link);
		}
	}

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$this->isCMS = true;
		$fields->addFieldToTab('Root.Content.LoggedIn', new TextField('TitleLoggedIn', 'Title when user is Logged In'));
		$fields->addFieldToTab('Root.Content.LoggedIn', new TextField('MenuTitleLoggedIn', 'Navigation Label when user is Logged In'));
		$fields->addFieldToTab('Root.Content.Welcome', new TextField('WelcomeTitle', 'Welcome Title (afer user creates an account)'));
		$fields->addFieldToTab('Root.Content.Welcome', new HTMLEditorField('WelcomeContent', 'Welcome message (afer user creates an account)'));
		$fields->addFieldToTab('Root.Content.UpdatingDetails', new TextField('ThankYouTitle', 'Thank you Title (afer user updates their details)'));
		$fields->addFieldToTab('Root.Content.UpdatingDetails', new HTMLEditorField('ThankYouContent', 'Thank you message (afer user updates their details)'));
		$fields->addFieldToTab('Root.Content.LoggedIn', new HTMLEditorField('ContentLoggedIn', 'Content when user is Logged In'));
		$fields->addFieldToTab('Root.Content.ErrorMessages', new TextField('ErrorEmailAddressAlreadyExists', 'Error shown when email address is already registered'));
		$fields->addFieldToTab('Root.Content.ErrorMessages', new TextField('ErrorBadEmail', 'Bad email'));
		$fields->addFieldToTab('Root.Content.ErrorMessages', new TextField('ErrorPasswordDoNotMatch', 'Error shown when passwords do not match'));
		$fields->addFieldToTab('Root.Content.ErrorMessages', new TextField('ErrorMustSupplyPassword', 'Error shown when new user does not supply password'));
		return $fields;
	}

	public function canCreate() {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		return !DataObject::get_one("SiteTree", "{$bt}ClassName{$bt} = 'RegisterAndEditDetailsPage'");
	}

	public function requireDefaultRecords() {
		parent::requireDefaultRecords();
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		$update = array();
		if(!$group = DataObject::get_one("Group", "{$bt}Code{$bt} = '".self::$register_group_code."'")) {
			$group = new Group();
			$group->Code = self::$register_group_code;
			$group->Title = self::$register_group_title;
			$group->write();
			Permission::grant( $group->ID, self::$register_group_access_key);
			DB::alteration_message("GROUP: ".self::$register_group_code.' ('.self::$register_group_title.')' ,"created");
		}
		elseif(DB::query("SELECT * FROM Permission WHERE {$bt}GroupID{$bt} = ".$group->ID." AND {$bt}Code{$bt} = '".self::$register_group_access_key."'")->numRecords() == 0) {
			Permission::grant($group->ID, self::$register_group_access_key);
		}
		$page = DataObject::get_one("RegisterAndEditDetailsPage");
		if(!$page) {
			$page = new RegisterAndEditDetailsPage();
			$page->Title = "Register";
			$page->MetaTitle = "Register";
			$page->URLSegment = "register";
			$page->MenuTitle = "Register";
			$update[] = "created RegisterAndEditDetailsPage";
		}
		if($page) {

			//REGISTER
			if(strlen($page->Content) < 17){$page->Content = "<p>Please log in or register here.</p>"; $update[] =  "updated Content";}

			//WELCOME !
			if(!$page->WelcomeTitle){$page->WelcomeTitle = "Thank you for registering"; $update[] =  "updated WelcomeTitle";}
			if(strlen($page->WelcomeContent) < 17){$page->WelcomeContent = "<p>Thank you for registration. Please make sure to remember your username and password.</p>"; $update[] =  "updated WelcomeContent";}

			// WELCOME BACK
			if(!$page->TitleLoggedIn){$page->TitleLoggedIn = "Welcome back"; $update[] =  "updated TitleLoggedIn";}
			if(!$page->MenuTitleLoggedIn){$page->MenuTitleLoggedIn = "Welcome back"; $update[] =  "updated MenuTitleLoggedIn";}
			if(!$page->MetaTitleLoggedIn){$page->MetaTitleLoggedIn = "Welcome back"; $update[] =  "updated MetaTitleLoggedIn";}
			if(strlen($page->ContentLoggedIn) < 17){$page->ContentLoggedIn = "<p>Welcome back - you can do the following ....</p>"; $update[] =  "updated ContentLoggedIn";}

			//THANK YOU FOR UPDATING
			if(!$page->ThankYouTitle){$page->ThankYouTitle = "Thank you for updating your details"; $update[] =  "updated ThankYouTitle";}
			if(strlen($page->ThankYouContent) < 17){$page->ThankYouContent = "<p>Thank you for updating your details. </p>"; $update[] =  "updated ThankYouContent";}

			//ERRORS!
			if(!$page->ErrorEmailAddressAlreadyExists){$page->ErrorEmailAddressAlreadyExists = "Sorry, that email address is already in use by someone else. You may have setup an account in the past or mistyped your email address."; $update[] =  "updated ErrorEmailAddressAlreadyExists";}
			if(!$page->ErrorBadEmail){$page->ErrorBadEmail = "Sorry, that does not appear a valid email address."; $update[] =  "updated ErrorBadEmail";}
			if(!$page->ErrorPasswordDoNotMatch){$page->ErrorPasswordDoNotMatch = "Your passwords do not match. Please try again."; $update[] =  "updated ErrorPasswordDoNotMatch";}
			if(!$page->ErrorMustSupplyPassword){$page->ErrorMustSupplyPassword = "Your must supply a password."; $update[] =  "updated ErrorMustSupplyPassword";}
			if(count($update)) {
				$page->writeToStage('Stage');
				$page->publish('Stage', 'Live');
				DB::alteration_message($page->ClassName." created/updated: <ul><li>".implode("</li><li>", $update)."</li></ul>", 'created');
			}
		}
	}
}

class RegisterAndEditDetailsPage_Controller extends Page_Controller {

	protected static $fields_to_remove = array("Locale","DateFormat", "TimeFormat");
		static function set_fields_to_remove($v) {self::$fields_to_remove = $v;}
		static function get_fields_to_remove() {return self::$fields_to_remove;}
		static function add_field_to_remove($s) {self::$fields_to_remove[] = $s;}

	protected static $required_fields = array("FirstName","Email");
		static function set_required_fields($v) {self::$required_fields = $v;}
		static function get_required_fields() {return self::$required_fields;}

	protected static $minutes_before_member_is_not_new_anymore = 30;
		static function set_minutes_before_member_is_not_new_anymore($v) {self::$minutes_before_member_is_not_new_anymore = $v;}
		static function get_minutes_before_member_is_not_new_anymore() {return self::$minutes_before_member_is_not_new_anymore;}

	function init() {
		parent::init();
		if($this->showLoggedInFields()) {$field = "TitleLoggedIn";}else {$field = "Title";} $this->Title = $this->getField($field);
		if($this->showLoggedInFields()) {$field = "MenuTitleLoggedIn";}else {$field = "MenuTitle";} $this->MenuTitle =  $this->getField($field);
		if($this->showLoggedInFields()) {$field = "MetaTitleLoggedIn";}else {$field = "MetaTitle";} $this->MetaTitle =  $this->getField($field);
		if($this->showLoggedInFields()) {$field = "ContentLoggedIn";}else {$field = "Content";}$this->Content =  $this->getField($field);
	}

	function index() {
		if($this->isAjax()) {
			return $this->renderWith(array("RegisterAndEditDetailsPageAjax", "RegisterAndEditDetailsPage"));
		}
		return array();
	}

	function Form() {
		if(isset($_REQUEST["BackURL"])){
			Session::set('BackURL', $_REQUEST["BackURL"]);
		}
		$member = Member::currentUser();
		$fields = new FieldSet();

		$passwordField = null;
		if($member) {
			$name = $member->getName();
			//if($member && $member->Password != '') {$passwordField->setCanBeEmpty(true);}
			$action = new FormAction("submit", "Update your details");
			$action->addExtraClass("updateButton");
			$actions = new FieldSet($action);
		}
		else {
			$passwordField = new ConfirmedPasswordField("Password", "Password");
			$action = new FormAction("submit", "Register");
			$action->addExtraClass("registerButton");
			$actions = new FieldSet($action);
			$member = new Member();
		}
		$memberFormFields = $member->getMemberFormFields();

		if($memberFormFields) {
			if(is_array(self::$fields_to_remove) && count(self::$fields_to_remove)) {
				foreach(self::$fields_to_remove as $fieldName) {
					$memberFormFields->removeByName($fieldName);
				}
			}
			$fields->merge($memberFormFields);
		}
		if($passwordField) {
			$fields->push($passwordField);
		}
		foreach(self::$required_fields as $fieldName) {
			$fields->fieldByName($fieldName)->addExtraClass("RequiredField");
		}
		$requiredFields = new CustomRequiredFields(self::$required_fields);
		$form = new Form($this, "Form", $fields, $actions, $requiredFields);
		// Load any data avaliable into the form.
		if($member) {
			$member->Password = null;
			$form->loadDataFrom($member);
		}
		$data = Session::get("FormInfo.Form_Form.data");
		if(is_array($data)) {
			$form->loadDataFrom($data);
		}

		// Optional spam protection
		if(class_exists('SpamProtectorManager')) {
			SpamProtectorManager::update_form($form);
		}
		if(!isset($_REQUEST["Password"])) {
			$form->fields()->fieldByName("Password")->SetValue("");
		}
		return $form;
	}


	/**
	 * Save the changes to the form
	 */
	function submit($data, $form) {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		$member = Member::currentUser();
		$newMember = false;
		Session::set("FormInfo.Form_Form.data", $data);
		$emailField = new EmailField("Email");
		$emailField->setValue($data["Email"]);
		if($emailField)  {
			if(!$emailField->validate($form->validator)) {
				$form->addErrorMessage("Blurb",$this->ErrorBadEmail,"bad");
				Director::redirectBack();
				return;
			}
		}
		if(!$member) {
			$newMember = true;
			$member = Object::create('Member');
			$form->sessionMessage($this->WelcomeTitle, 'good');
			$id = 0;
		}
		else {
			$form->sessionMessage($this->ThankYouTitle, 'good');
			$id = $member->ID;
		}

		//validation
		if($existingMember = DataObject::get_one("Member", "{$bt}Email{$bt} = '". Convert::raw2sql($data['Email']) . "' AND {$bt}Member{$bt}.{$bt}ID{$bt} <> '$id'")) {
			$mems = DataObject::get("Member", "{$bt}Email{$bt} = '". Convert::raw2sql($data['Email']) . "'");
			$form->addErrorMessage("Blurb",$this->ErrorEmailAddressAlreadyExists,"bad");
			Director::redirectBack();
			return;
		}
		// check password fields are the same before saving
		if($data["Password"]["_Password"] != $data["Password"]["_ConfirmPassword"]) {
			$form->addErrorMessage("Password", $this->ErrorPasswordDoNotMatch,"bad");
			return Director::redirectBack();
		}

		if(!$id && !$data["Password"]["_Password"]) {
			$form->addErrorMessage("Password", $this->ErrorMustSupplyPassword,"bad");
			return Director::redirectBack();
		}
		$password = $member->Password;
		if(isset($data["Password"]["Password"]) && strlen($data["Password"]["Password"]) > 3) {
			$password = $data["Password"]["Password"];
		}
		$form->saveInto($member);
		$member->changePassword($password);
		$member->write();
		if($newMember) {
			$form->saveInto($member);
			$member->write();
		}
		//adding to group
		$group = DataObject::get_one("Group", "{$bt}Code{$bt} = '".RegisterAndEditDetailsPage::$register_group_code."'");
		if($group) {
			$member->Groups()->add($group);
		}
		if($newMember) {
			$member->logIn();
			$link = ContentController::join_links($this->Link() , 'welcome');
		}
		else {
			$link = ContentController::join_links($this->Link() , 'thanks');
		}
		if(!isset($_REQUEST["BackURL"]) && Session::get('BackURL')) {
			$_REQUEST["BackURL"] = Session::get('BackURL');
		}
		if(isset($_REQUEST["BackURL"])){
			$link = urldecode($_REQUEST["BackURL"]);
			Session::set('BackURL', '');
		}
		if($link) {
			Director::redirect($link);
		}
		return array();
	}

	function thanks() {
		$member = Member::currentUser();
		if(!$member) {
			Director::redirect($this->Link());
		}
		if($this->numberOfMinutesMemberIsListed($member) < self::get_minutes_before_member_is_not_new_anymore()) {
			$this->Title = $this->WelcomeTitle;
			$this->Content = $this->WelcomeContent;
		}
		else {
			$this->Title = $this->ThankYouTitle;
			$this->Content = $this->ThankYouContent;
		}
		return array();
	}

	function welcome() {
		if(!Member::currentUser()) {
			Director::redirect($this->Link());
		}
		$this->Title = $this->WelcomeTitle;
		$this->Content = $this->WelcomeContent;
		return array();
	}

	function numberOfMinutesMemberIsListed($member) {
		if($member) {
			$timestamp = strtotime(strval($member->Created));
			$nowTimestamp = time();
			return ($nowTimestamp - $timestamp) / 60;
		}
		return 0;
	}



}
