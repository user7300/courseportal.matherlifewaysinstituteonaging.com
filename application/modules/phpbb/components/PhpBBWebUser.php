<?php
/**
 * Synchronize Login/Logout events with your forum
 *
 * Modify your configuration file:
 * <pre>
 * return array(
 *     'components'=>array(
 *
 *         // ...
 *
 *         'phpBB'=>array(
 *             'class'=>'phpbb.extensions.phpBB.phpBB',
 *             'path'=>'webroot.forum',
 *         ),
 *
 *         'user'=>array(
 *             'class'=>'phpbb.components.PhpBBWebUser',
 *             'allowAutoLogin'=>true,
 *             'loginUrl'=>array('/site/login'),
 *         ),
 *
 *     ),
 * );
 * </pre>
 *
 * @author ElisDN <mail@elisdn.ru>
 * @link http://www.elisdn.ru
 * @version 1.0
 */

// The class extends your own WebUser class if it exists
// Inheritance: PhpBBWebUser -> WebUser -> CWebUser

class PhpBBWebUser extends WebUser {
	
    private $_identity;

    public function login($identity, $duration=0) {
        $this->_identity = $identity;
        return parent::login($identity, $duration);
    }

    protected function afterLogin($fromCookie) {
		if($this->_identity !== null) {
			if(!Yii::app()->phpBB->login($this->_identity->name, $this->_identity->password_no_hash)) {
				Yii::app()->phpBB->userAdd($this->_identity->name, 
											$this->_identity->password_no_hash, 
											$this->_identity->email,
											$this->_identity->group->name);
				Yii::app()->phpBB->login($this->_identity->name, $this->_identity->password_no_hash);
			}
		}
        parent::afterLogin($fromCookie);
    }

    protected function afterLogout() {
        Yii::app()->phpBB->logout();
        parent::afterLogout();
    }
}