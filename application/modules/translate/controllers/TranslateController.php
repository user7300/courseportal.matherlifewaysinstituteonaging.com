<?php
class TranslateController extends TController {
	
	public function filters()
	{
		return array(
					array('filters.HttpsFilter'),
					'accessControl -viewRendererProgress',
			);
	}
	
	public function accessRules() 
	{
		return array(
					array('allow',
							'expression' => '$user->getIsAdmin()',
					),
					array('deny',
							'users' => array('*'),
					),
			);
	}
	
	/**
	 * override needed to check if its ajax, the redirect will be by javascript
	 */
	public function redirect($url, $terminate = true, $statusCode = 302) {
		if(Yii::app()->getRequest()->getIsAjaxRequest()) {
			if(is_array($url)) {
				$route = isset($url[0]) ? $url[0] : '';
				$url = $this->createUrl($route, array_slice($url, 1));
			}
			Yii::app()->getClientScript()->registerScript('redirect', "window.top.location='$url'");
			if($terminate)
				Yii::app()->end($statusCode);
		} else {
			return parent::redirect($url, $terminate, $statusCode);
		}
	}
	
	public function actionMissingOnPage() 
	{
        if(isset($_POST['Message'])) 
        {
            foreach($_POST['Message'] as $id => $message)
            {
                if(empty($message['translation']))
                    continue;
                $model = new Message();
                $message['id'] = $id;
                $model->setAttributes($message);
                $model->save();
            }
            $this->redirect(Yii::app()->getUser()->getReturnUrl());
        }

        $key = str_replace('.', '_', MPTranslate::ID) . '-missing';
        $missing = array();
        if(isset($_POST[$key]))
        {
            $missing = $_POST[$key];
        }
        else if(Yii::app()->getUser()->hasState($key))
        {
            $missing = Yii::app()->getUser()->getState($key);
        }
        
        $messages = array();
        if(!empty($missing)) 
        {
            Yii::app()->getUser()->setState($key, $missing); 
            foreach($missing as $id => $message)
            {
                $messages[] = new Message;
                end($messages)->setAttributes(array('id' => $id, 'language' => $message['language']));
            }
        }

        $this->render('missing', array('missing' => $missing, 'messages' => $messages));
	}
	
    public function actionGoogleTranslate($message, $targetLanguage = null, $sourceLanguage = null) {
    	$translation = TranslateModule::translator()->googleTranslate($message, $targetLanguage, $sourceLanguage);
        if(is_array($message))
            echo CJSON::encode($translation);
        elseif(is_array($translation))
            echo $translation[0];
        else 
        	echo $translation;
    }
    
    public function actionViewRendererProgress($requestUri)
    {
    	$this->render('viewRendererProgress');
    }
    
    /**
     * Manages all models.
     */
    public function actionIndex() {
    	$this->render('index');
    }
    
}