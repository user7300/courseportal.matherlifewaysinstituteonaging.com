<?php

class UserController extends ApiController {

	const REMEMBER_ME_DURATION = 2592000; // 30 days in seconds

	public function accessRules() {
		return array_merge(
				parent::accessRules(),
				array(
					array('allow',
							'actions' => array('profile', 'profileSurvey'),
							'users' => array('@'),
					),
					array('deny',
							'actions' => array('profile', 'profileSurvey'),
							'users' => array('*'),
					),
				)
		);
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		$loginModel = new Login;

		if(isset($_POST['ajax']) && $_POST['ajax'] === 'login-form')
		{
			echo CActiveForm::validate($loginModel);
			Yii::app()->end();
		}

		if($loginModel->loadAttributes() && $loginModel->validate())
		{
			$webUser = Yii::app()->getUser();
			if($webUser->login(
					new BasicIdentity($loginModel->username_email, $loginModel->password),
					$loginModel->remember_me ? self::REMEMBER_ME_DURATION : 0))
			{
				$webUser->setFlash('success', t('Welcome {name}!', array('{name}' => $webUser->getModel()->name)));
				$this->redirect($webUser->returnUrl);
			}
			else
			{
				$loginModel->addError('username_email_password', t('Incorrect username or password.'));
			}
		}
		$this->render('pages/login', array('model' => $loginModel));
	}

	public function actionForgotPassword()
	{
		$models = array(
				'UserNameEmail' => new UserNameEmail,
				'EReCaptchaForm' => Yii::createComponent('ext.recaptcha.EReCaptchaForm', Yii::app()->params['reCaptcha']['privateKey'])
		);
		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax'] === 'user-maintenance-form')
		{
			echo CActiveForm::validate($models);
			Yii::app()->end();
		}

		if($models['EReCaptchaForm']->loadAttributes() &&
				$models['UserNameEmail']->loadAttributes() &&
				$models['EReCaptchaForm']->validate() &&
				$models['UserNameEmail']->validate())
		{
			$user = $models['UserNameEmail']->getUser();
			if($user === null)
			{
				$models['UserNameEmail']->addError('name_email', t('The username or email address you have entered could not be found.'));
			}
			else
			{
				if(!$user->getIsActivated())
				{
					Yii::app()->getUser()->setFlash('error',
						t('We could not reset your password because your account has not yet been activated. To have an activation email sent to you again please click ').
							CHtml::link(t('here'), $this->createUrl('resendActivation')));
				}
				else
				{
					$this->sendPasswordResetEmail($user);
					Yii::app()->getUser()->setFlash('success', t('Instructions for resetting your password have been sent to your email.'));
				}
				$this->refresh();
			}
		}
		$this->render('pages/forgotPassword', array('models' => $models));
	}

	public function actionChangePassword($id = null, $session_key = null)
	{
		$ChangePassword = new ChangePassword('change');
		$UserIdentity = new SessionIdentity($id, @CBase64::urlDecode($session_key));

		if($UserIdentity->authenticate())
		{
			$ChangePassword->setScenario('reset');
			$ChangePassword->username_email = $UserIdentity->getModel()->name;
		}

		if($ChangePassword->loadAttributes()) {
			$ChangePassword->validate();
			// if this is an ajax validation request
			if(isset($_POST['ajax']) && $_POST['ajax'] === 'change-password-form')
			{
				echo $ChangePassword->getErrorsAsJSON();
				Yii::app()->end();
			}

			if(!$ChangePassword->hasErrors() && $ChangePassword->getScenario() === 'change')
			{
				$UserIdentity = new BasicIdentity($ChangePassword->username_email, $ChangePassword->current_password);
				if(!$UserIdentity->authenticate())
				{
					$ChangePassword->addError('username_email_current_password', t('Incorrect username or password.'));
				}
			}

			if(!$ChangePassword->hasErrors())
			{
				$user = $UserIdentity->getModel();
				$user->new_password = $ChangePassword->new_password;
				$user->regenerateSessionKey(false);
				if($user->save(true, array('session_key', 'password')))
				{
					$webUser = Yii::app()->getUser();
					if($webUser->login(new BasicIdentity($user->email, $ChangePassword->new_password)))
					{
						$webUser->setFlash('success',
								t('Your account password has been reset. Welcome {email}!', array('{email}' => $webUser->name)));
						$this->redirect(Yii::app()->homeUrl);
					}
				}
			}
		}
		return $this->render('pages/changePassword', array('ChangePassword' => $ChangePassword));
	}

	/**
	 * Displays the register page
	 */
	public function actionRegister()
	{
		$models = array(
					'Register' => new Register,
					'EReCaptchaForm' => Yii::createComponent('ext.recaptcha.EReCaptchaForm', Yii::app()->params['reCaptcha']['privateKey']),
				);
		$models['Register']->agreement_id = 1;

		// collect user input data
		if($models['EReCaptchaForm']->loadAttributes() && $models['Register']->loadAttributes())
		{
			if(isset($_POST['ajax']) && $_POST['ajax'] === 'register-form')
			{
				echo CActiveForm::validateTabular($models);
				Yii::app()->end();
			}

			if($models['EReCaptchaForm']->validate() && $models['Register']->validate())
			{
				$userAgreement = new UserAgreement();
				$userAgreement->agreement_id = $models['Register']->agreement_id;
				$user = new CPUser();
				$user->setAttributes($models['Register']->getAttributes());

				$transaction = Yii::app()->db->beginTransaction();
				try
				{
					if($user->save())
					{
						$userAgreement->user_id = $user->id;
						$userAgreement->save();
					}
				}
				catch(Exception $e)
				{
					$transaction->rollback();
					throw $e;
				}
				if(!($user->hasErrors() || $userAgreement->hasErrors()))
				{
					$transaction->commit();
					$this->sendAccountActivationEmail($user);
					return $this->render('pages/registerConfSent');
				}
				$transaction->rollback();
				$models['Register']->addErrors($user->getErrors());
			}
		}
		// display the register form
		$this->render('pages/register', array('models' => $models));
	}

	public function actionActivate($id, $session_key)
	{
		$userIdentity = new SessionIdentity($id, CBase64::urlDecode($session_key), false);

		if($userIdentity->authenticate())
		{
			$user = $userIdentity->getModel();
			$user->setIsActivated(true);

			if($user->getRelated('activated')->save())
			{
				Yii::app()->getUser()->login($userIdentity, 0);
				$user->regenerateSessionKey();
				Yii::app()->getUser()->setFlash('success',
					t('Your account has been activated. Welcome {email}!',
						array('{email}' => Yii::app()->getUser()->name)));
				$this->redirect(Yii::app()->homeUrl);
			}
		}
		$this->render('pages/activationFailure');
	}

	public function actionResendActivation() {
		$models = array(
				'UserNameEmail' => new UserNameEmail,
				'EReCaptchaForm' => Yii::createComponent('ext.recaptcha.EReCaptchaForm', Yii::app()->params['reCaptcha']['privateKey'])
		);
		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax'] === 'user-maintenance-form') {
			$models['UserNameEmail']->setScenario('ajax');
			echo CActiveForm::validate($models);
			Yii::app()->end();
		}

		if($models['EReCaptchaForm']->loadAttributes() &&
				$models['UserNameEmail']->loadAttributes() &&
				$models['EReCaptchaForm']->validate() &&
				$models['UserNameEmail']->validate())
		{
			$user = $models['UserNameEmail']->getUser();
			if($user->getIsActivated())
			{
				Yii::app()->getUser()->setFlash('error', t('Your account has already been activated. If you have forgotten your password you can recover it by clicking') .
															'&nbsp;' . CHtml::link(t('here'), $this->createUrl('forgotPassword')));
			}
			else
			{
				$this->sendAccountActivationEmail($user);
				Yii::app()->getUser()->setFlash('success', t('We have resent an activation email to') . '&nbsp;' . $user->email);
			}
			$this->refresh();
		}
		$this->render('pages/resendActivation', array('models' => $models));
	}

	public function sendPasswordResetEmail($userModel)
	{
		$message = Yii::app()->mail->getNewMessageInstance();
		$message->layout = 'mail';
		$message->view = 'passwordReset';
		$message->setSubject(t('MatherLifeways Password Reset Request'));
		$message->setBody(array('url' => $userModel->encodeUrl('user/changePassword')), 'text/html');
		$message->setTo($userModel->email);
		$message->setFrom(Yii::app()->params['noReplyEmail']);
		Yii::app()->mail->send($message);
	}

	public function sendAccountActivationEmail($userModel) {
		$message = Yii::app()->mail->getNewMessageInstance();
		$message->layout = 'mail';
		$message->view = 'registrationConfirmation';
		$message->setSubject(t('MatherLifeways Registration Confirmation'));
		$message->setBody(array('user' => $userModel), 'text/html');
		$message->setTo($userModel->email);
		$message->setBcc('amcivor@MatherLifeways.com');
		$message->setFrom(Yii::app()->params['noReplyEmail']);
		Yii::app()->mail->send($message);
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout() {
		Yii::app()->getUser()->logout();
		Yii::app()->getUser()->setFlash('success', t('You have been successfully logged out.'));
		$this->redirect(Yii::app()->homeUrl);
	}

	public function actionProfile() {
		$user = Yii::app()->getUser()->getModel();
		$models = array(
				'Profile' => new UserProfile,
				'avatar' => new Avatar);

		$models['Profile']->setAttributes($user->getAttributes());
		$models['avatar']->user_id = $user->id;

		$surveys = array();
		foreach(array(
				'profile',
				'precourse',
				'postcourse',
				'spencerpowell') as $surveyName) {
			$survey = $this->createWidget(
					'modules.surveyor.widgets.Survey',
					array(
							'id' => $surveyName,
							'options' => array(
								'htmlOptions' => array('style' => 'display:none;'),
								'title' => array('htmlOptions' => array('class' => 'flowers')),
								'form' => array('options' =>
										array(
												'enableAjaxValidation' => true,
												'enableClientValidation' => true
										)),
							)
					)
			);
			$survey->model->user_id = $user->id;
			$survey->processRequest();
			$surveys[] = $survey;
		}

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax'] === 'profile-form') {
			echo CActiveForm::validateTabular($models);
			Yii::app()->end();
		}

		// collect user input data
		if($models['Profile']->loadAttributes() && $models['Profile']->validate() && $models['avatar']->validate())
		{
			$user->setAttributes($models['Profile']->getAttributes());

			if($user->validate())
			{
				$transaction = Yii::app()->db->beginTransaction();
				$exception = null;
				try {
					if((!isset($models['avatar']->image) || (($user->avatar === null || $user->avatar->delete()) && $models['avatar']->save())) && $user->save())
					{
						$transaction->commit();
					}
					$models['Profile']->addErrors($user->getErrors());
				} catch(Exception $e) {
					$exception = $e;
				}
				if($models['Profile']->hasErrors() || $models['avatar']->hasErrors() || isset($exception))
				{
					if(!$models['avatar']->getIsNewRecord())
						$models['avatar']->delete();
					$transaction->rollback();
					if(isset($exception))
						throw $exception;
				}
			}
		}
		$this->render('pages/profile', array('models' => $models, 'surveys' => $surveys));
	}

	/****** START API ACTIONS ******/

	public function actionCreate()
	{
		$model = new Register('pushed');

		if($model->loadAttributes() && $model->validate())
		{
			$user = new CPUser();
			$user->setAttributes($model->getAttributes());

			if($user->save())
			{
				$this->sendAccountActivationEmail($user);
				$user->attachBehavior('toArray', array('class' => 'behaviors.EArrayBehavior'));
				return $this->renderApiResponse(200, $user->toArray(array('id', 'email'), true));
			}
			$model->addErrors($user->getErrors());
		}

		$this->renderApiResponse(400, $model->getErrorsAsJSON());
	}

	public function actionRead() {
		$model = new CPUser('search');

		if(isset($_GET['CPUser']))
			$model->setAttributes($_GET['CPUser']);

		$users = $model->with('group')->findAll($model->getSearchCriteria());
		$data = array();
		foreach($users as $user) {
			$user->attachBehavior('toArray', array('class' => 'behaviors.EArrayBehavior'));
			$data[] = $user->toArray(array_merge($user->getSafeAttributeNames(), array('group' => 'name')), true);
		}

		if(empty($data))
			$this->renderApiResponse(404, $data);
		else
			$this->renderApiResponse(200, $data);
	}

	public function actionUpdate() {
		$requestVars = Yii::app()->getRequest()->getRestParams();
		if(!isset($requestVars['id'])) {
			$this->renderApiResponse(400, array('id' => t('The id of the user to be updated must be specified.')));
			return;
		}

		$model = CPUser::model()->findByPk($requestVars['id']);

		$model->setAttributes($requestVars);

		if($model->save()) {
			$this->renderApiResponse();
		}

		$this->renderApiResponse(400, $model->getErrorsAsJSON());
	}

	public function actionDelete() {
		$requestVars = Yii::app()->getRequest()->getRestParams();
		if(!isset($requestVars['id']))
		{
			return $this->renderApiResponse(400, array('id' => t('The id of the user to be deleted must be specified.')));
		}

		$this->renderApiResponse(200, array('rows_deleted' => CPUser::model()->deleteByPk($requestVars['id'])));
	}

	public function actionOptions() {
		$model = new CPUser('search');
		$response['GET'] =
						array(
							'returns' => t('List of users.'),
							'optional' => $model->getOptionalAttributes(),
							'required' => $model->getRequiredAttributes()
						);
		$model->setScenario('update');
		$response['PUT'] = array(
							'returns' => t('Number of rows effected'),
							'optional' => $model->getOptionalAttributes(),
							'required' => $model->getRequiredAttributes(),
						);
		$response['DELETE'] = array(
							'returns' => t('Number of rows effected'),
							'optional' => array(),
							'required' => array('CPUser' => array('id')),
						);
		$model = new Register('pushed');
		$response['POST'] = array(
							'returns' => array('CPUser' => array('id', 'email')),
							'optional' => $model->getOptionalAttributes(),
							'required' => $model->getRequiredAttributes(),
					);
		$this->renderApiResponse(200, $response);
	}

}