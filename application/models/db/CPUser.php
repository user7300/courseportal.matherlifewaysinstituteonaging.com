<?php defined('BASEPATH') or exit('No direct script access allowed');  
/**
 * This is the model class for table "user".
 *
 * The followings are the available columns in table 'user':
 * @property integer $id
 * @property string $password
 * @property string $salt
 * @property integer $group_id
 * @property string $email
 * @property string $name
 * @property string $session_key
 * @property string $created
 *
 * The followings are the available model relations:
 * @property Avatar $avatar
 * @property Referral[] $referrals
 * @property Referral[] $referees
 * @property UploadedFile[] $uploadedFiles
 * @property Course[] $courses
 * @property UserCourse[] $userCourses
 * @property Group $group
 * @property UserActivated $userActivated
 * @property UserProfile $userProfile
 */

class CPUser extends ActiveRecord implements IUserIdentity {

	const ERROR_NONE = 0;
	const ERROR_EMAIL_INVALID = 1;
	const ERROR_PASSWORD_INVALID = 2;
	const ERROR_NOT_ACTIVATED = 3;
	const ERROR_UNKNOWN_IDENTITY = 100;
	
	/**
	 * @var integer the authentication error code. If there is an error, the error code will be non-zero.
	 * Defaults to 100, meaning unknown identity. Calling {@link authenticate} will change this value.
	 */
	public $errorCode = self::ERROR_UNKNOWN_IDENTITY;

	private $_state = array();
	private $_pbkdf2Hasher;
	
	public $password_no_hash = null;
	public $password_no_hash_repeat = null;

	public function init() {
		if($this->isNewRecord) {
			$this->salt = $this->getHasher()->getIV();
			$this->session_key = $this->getHasher()->generateIV();
			$this->group_id = null;
		}
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @return CActiveRecord the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table username
	 */
	public function tableName() {
		return '{{user}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		return array(
			array('password, salt, session_key, group_id', 'unsafe'),
			array('email, salt, session_key', 'required', 'except' => 'search'),
			array('name', 'filter', 'filter' => array($this, 'generateName'), 'on' => 'pushedRegister'),
			array('name', 'required', 'except' => 'login, search, passwordReset'),
			array('salt, session_key', 'ext.pbkdf2.PBKDF2validator'),
				
			array('password_no_hash', 'required', 'on' => 'pushedRegister, register, login, passwordReset'),
			array('password_no_hash', 'type', 'allowEmpty' => false, 'on' => 'pushedRegister, register, login, passwordReset'),
				
			array('password_no_hash_repeat', 'required', 'on' => 'register, passwordReset'),
			array('password_no_hash_repeat', 'type', 'allowEmpty' => false, 'on' => 'register, passwordReset'),
			array('password_no_hash_repeat',
					'compare',
					'compareAttribute' => 'password_no_hash',
					'strict' => true,
					'message' => 'Passwords do not match.', 
					'on' => 'register, passwordReset'),
				
			array('password_no_hash', 'hash', 'on' => 'pushedRegister, register, passwordReset'),
				
			array('email', 'length', 'max' => 255),
			array('email', 'email'),
			array('email', 'unique', 'except' => 'login, search, passwordReset'),
			array('email', 'loadUserAttributesFromEmail', 'on' => 'login'),
				
			array('name', 'length', 'max' => 63),
			array('name', 'unique', 'except' => 'login, search, passwordReset'),

			array('password', 'required', 'except' => 'login, search'),
			array('password', 'ext.pbkdf2.PBKDF2validator', 'except' => 'login, search'),

			array('group_id', 'determineGroup', 'on' => 'pushedRegister, register'), 
			array('group_id', 'exist', 
					'attributeName' => 'id', 
					'className' => 'Group', 
					'allowEmpty' => false, 
					'except' => 'login, search, passwordReset'),
			array('group_id', 'required', 'except' => 'search', 'except' => 'login, search, passwordReset'),
				
			array('id, group_id, email, created', 'safe', 'on' => 'search')
        );
	}
	
	public function behaviors() {
		return array(
				'PhpBBUserBehavior' => array(
						'class' => 'phpbb.components.PhpBBUserBehavior',
						'usernameAttribute' => 'name',
						'newPasswordAttribute' => 'password_no_hash',
						'emailAttribute' => 'email',
						'avatarAttribute' => 'avatar',
						'avatarPath' => Yii::getPathOfAlias(Avatar::AVATARS_PATH_ALIAS),
						'forumDbConnection' => 'forumDb',
						'syncAttributes' => array(
								'location' => 'user_from',
						)
				),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		Yii::import('phpbb.models.*');
		return array(
			'avatar' => array(self::HAS_ONE, 'Avatar', 'user_id'),
            'referees' => array(self::HAS_MANY, 'Referral', 'referee'),
            'referrals' => array(self::HAS_MANY, 'Referral', 'referrer'),
            'uploadedFiles' => array(self::HAS_MANY, 'UploadedFile', 'user_id'),
            'group' => array(self::BELONGS_TO, 'Group', 'group_id'),
			'userActivated' => array(self::HAS_ONE, 'UserActivated', 'user_id'),
			'userProfile' => array(self::HAS_ONE, 'UserProfile', 'user_id'),
			'userCourses' => array(self::HAS_MANY, 'UserCourse', 'user_id'),
			'courses' => array(self::HAS_MANY, 'Course', array('course_id' => 'id'), 'through' => 'userCourses'),
			'phpBbUser' => array(self::HAS_ONE, 'PhpBBUser', array('username' => 'username')),
		);
	}
	
	public function getLocation() {
		return $this->userProfile->city . 
		($this->userProfile->state === null ? '' : ', '.$this->userProfile->state); 
	}
	
	public function getIsGuest() {
		return $this->group instanceof Group && $this->group->getIsGuest();
	}
	
	public function getIsAdmin() {
		return $this->group instanceof Group && $this->group->getIsAdmin();
	}
	
	public function getIsEmployee() {
		return $this->group instanceof Group && $this->group->getIsEmployee();
	}
	
	public function getIsActivated() {
		return $this->userActivated instanceof UserActivated && !$this->userActivated->getIsNewRecord();
	}
	
	public function hasCourse($course) {
		if(is_int($course)) {
			$this->getDbCriteria()->mergeWith(array(
					'with' => 'userCourses', 
					'condition' => 'userCourses.course_id=:course_id',
					'params' => array(':course_id' => $course)
				));
		} else if(is_string($course)) {
			$this->getDbCriteria()->mergeWith(array(
					'with' => 'courses', 
					'condition' => 'course.name=:name',
					'params' => array(':name' => $course)
				));
		} else if($course instanceof Course) {
			return $this->hasCourse($course->id);
		}
		return $this;
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
            'id' => t('ID'),
            'password' => t('Password'),
            'salt' => t('Salt'),
            'group_id' => t('Group ID'),
            'email' => t('Email'),
			'name' => t('Username'),
            'session_key' => t('Session Key'),
            'created' => t('Created'),
			'referees' => t('Referees'),
			'referrals' => t('Referrals'),
			'uploadedFiles' => t('Uploaded Files'),
			'userActivated' => t('User Activated'),
			'userProfile' => t('User Profile'),
			'group' => t('Group'),
			'password_no_hash' => t('Password'),
			'password_no_hash_repeat' => t('Repeat Password'),
			'userCourses' => t('User Courses'),
			'courses' => t('Courses'),
		);
	}
	
	public function getSearchCriteria() {
		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id);
		$criteria->compare('group_id',$this->group_id);
		$criteria->compare('email', $this->email, true);
		$criteria->compare('name', $this->name, true);
		$criteria->compare('created', $this->created, true);
		
		return $criteria;
	}
	
	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		return new CActiveDataProvider($this, array(
				'criteria' => $this->getSearchCriteria(),
		));
	}
	
	public function generateName($name) {
		if(!isset($name)) {
			do {
				$name = uniqid('temp_username_');
			} while(self::model()->exists('name = :name', array(':name' => $name)));
		}
		return $name;
	}

	/**
	 * Authenticates this user.
	 */
	public function authenticate() {
		if($this->validate()) {
			$record = $this->find('email = :email', array(':email' => $this->email));
			if($record === null)
				$this->errorCode = self::ERROR_EMAIL_INVALID;
			else if($record->password !== $this->password)
				$this->errorCode = self::ERROR_PASSWORD_INVALID;
			else if($record->userActivated === null || $record->userActivated->getIsNewRecord())
				$this->errorCode = self::ERROR_NOT_ACTIVATED;
			else
				$this->errorCode = self::ERROR_NONE;
		} else {
			$this->errorCode = self::ERROR_UNKNOWN_IDENTITY;
		}
		return $this->errorCode === self::ERROR_NONE;
	}
	
	public function login($remember_me = false, $run_validation = true) {
		if(!$run_validation || $this->validate()) {
			if($remember_me)
				$this->setState('sk', $this->session_key);
			if(Yii::app()->getUser()->login($this, $remember_me ? 3600 * 24 * 7 : 0)) {
				if($this->scenario === 'login')
					$this->setScenario('update');
				return true;
			}
		}
		return false;
	}
	
	public function determineGroup($attribute, $params) {
		$this->group_id = EmployerDomain::model()->exists('name = :name', array(':name' => array_pop(explode('@', $this->email)))) ?
													   Group::model()->find('name = :name', array(':name' => Group::EMPLOYEES))->id :
													   Group::model()->find('name = :name', array(':name' => Group::REGISTERED))->id;
	}
	
	protected function afterFind() {
		$this->getHasher()->iv = $this->salt;
		return parent::afterFind();
	}
	
	public function loadUserAttributesFromEmail($attribute, $params) {
		$record = $this->find('email = :email', array(':email' => $this->$attribute));
		if($record !== null) {
			$this->getHasher()->iv = $record->salt;
			$this->getHasher()->string = $this->password_no_hash;
		}
		if($record === null || $record->password !== $this->getHasher()->getHash())
			$this->addError('User', t('Incorrect email or password.'));
		else if(!$record->getIsActivated())
			$this->addError('User', t('The email and password you have entered are correct, but your account has not yet been activated. If you lost the activation email we sent you after you registered please click ') .
										CHtml::link(t('here'), Yii::app()->createUrl('user/resendActivation')));
		else {
			$this->setPrimaryKey($record->getPrimaryKey());
			$this->setIsNewRecord(false);
			$this->refresh();
		}
	}
	
	public function hash($attribute, $params) {
		$this->password = $this->getHasher()->getHash($this->$attribute);
	}
	
	/**
	 * Regenerates the session key for this user and saves to database.
	 * @return boolean whether the session key was successfully updated for this user.
	 */
	public function regenerateSessionKey() {
		$this->session_key = $this->getHasher()->generateIV();
		return $this->save(true, array('session_key'));
	}
	
	public function getHasher() {
		if($this->_pbkdf2Hasher === null) 
			$this->_pbkdf2Hasher = Yii::createComponent('ext.pbkdf2.PBKDF2');
		return $this->_pbkdf2Hasher;
	}

	/**
	 * Returns a value that uniquely represents the identity.
	 * @return mixed a value that uniquely represents the identity (e.g. primary key value).
	 * The default implementation simply returns {@link name}.
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Returns the display name for the identity (e.g. username).
	 * @return string the display name for the identity.
	 * The default implementation simply returns empty string.
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Returns the identity states that should be persisted.
	 * This method is required by {@link IUserIdentity}.
	 * @return array the identity states that should be persisted.
	 */
	public function getPersistentStates() {
		return $this->_state;
	}

	/**
	 * Sets an array of presistent states.
	 *
	 * @param array $states the identity states that should be persisted.
	 */
	public function setPersistentStates($states) {
		$this->_state = $states;
	}

	/**
	 * Returns a value indicating whether the identity is authenticated.
	 * This method is required by {@link IUserIdentity}.
	 * @return whether the authentication is successful.
	 */
	public function getIsAuthenticated() {
		return $this->errorCode === self::ERROR_NONE;
	}

	/**
	 * Gets the persisted state by the specified name.
	 * @param string $name the name of the state
	 * @param mixed $defaultValue the default value to be returned if the named state does not exist
	 * @return mixed the value of the named state
	 */
	public function getState($name, $defaultValue = null) {
		return isset($this->_state[$name]) ? $this->_state[$name] : $defaultValue;
	}

	/**
	 * Sets the named state with a given value.
	 * @param string $name the name of the state
	 * @param mixed $value the value of the named state
	 */
	public function setState($name, $value) {
		$this->_state[$name] = $value;
	}

	/**
	 * Removes the specified state.
	 * @param string $name the name of the state
	 */
	public function clearState($name) {
		unset($this->_state[$name]);
	}
}