<?php

class MPTranslate extends CApplicationComponent {
	
	/**
	 * @const string ID an unique key to be used in many situations
	 * */
	const ID = 'mp-translate';

    /**
     * @staticvar array $messages contains the untranslated messages found
     * */
    static $messages = array();
    
    /**
     * @var $dialogOptions options of the dialog
     * */
    public $dialogOptions = array(
        'autoOpen' => false,
        'modal' => true,
        'width' => 'auto',
        'height' => 'auto',
    );
    
    /**
     * @var string $googleTranslateApiKey your google translate api key
     * set this if you wish to use googles translate service to translate the messages
     * if empty it will not use the service 
     * */
    public $googleApiKey = null;
    
    /**
     * @var string $messageCategory
     * The default category used to identify messages.
     */
    public $messageCategory = self::ID;

    /**
     * @var boolean wheter to auto translate the missing messages found on the page
     * needs google api key to set
     * */
    public $autoTranslate = false;
    
    public $managementAccessRules = array();
    
    public $managementActionFilters = array();
    
    /**
     * @var array $_cache will contain variables
     * */
    private $_cache = array();
    
	/**
	 * handles the initialization parameters of the components
	 */
	public function init() {
		Yii::import('translate.models.AcceptedLanguages');

        function t($message, $params = array ()) {
        	return Yii::t(TranslateModule::translator()->messageCategory, $message, $params, null, null);
        }
        
        return parent::init();
	}
	
	public function processRequest($route) {
		// If there is a post-request, redirect the application to the provided url of the selected language
		if(isset($_POST['language'])) {
			Yii::app()->getRequest()->redirect(Yii::app()->createUrl($route, array('language' => $_POST['language'])));
		}
		
		// Set the application language if provided by GET, session or cookie
		if(isset($_GET['language'])) {
			$language = (string)$_GET['language'];
			unset($_GET['language']);
		} else if (isset(Yii::app()->session['language'])) {
			$language = (string)Yii::app()->session['language'];
		} else if (Yii::app()->user->hasState('language')) {
			$language = (string)Yii::app()->user->getState('language');
		} else if(isset(Yii::app()->getRequest()->cookies['language'])) {
			$language = (string)Yii::app()->getRequest()->cookies['language'];
		} else if(Yii::app()->getRequest()->getPreferredLanguage() !== false) {
			$language = (string)Yii::app()->getRequest()->getPreferredLanguage();
		} else {
			$language = (string)Yii::app()->getLanguage();
		}
		 
		// If the language is not recognized maybe the user didn't add the language part of the address.
		// Redirect to the same uri with the source language set.
		if(!$this->isAdminAcceptedLanguage(Yii::app()->getLocale()->getLanguageID($language))) {
			Yii::app()->getRequest()->redirect(Yii::app()->createUrl(Yii::app()->getRequest()->getRequestUri(), array('language' => Yii::app()->sourceLanguage)));
		}
		
		$this->setLanguage($language);
	}
	
	public function setLanguage($language) {
		Yii::app()->setLanguage($language);
		setLocale(LC_ALL, $language.'.'.Yii::app()->charset);
		Yii::app()->session['language'] = $language;
		Yii::app()->user->setState('language', $language);
		Yii::app()->getRequest()->cookies['language'] = new CHttpCookie('language', $language);
		Yii::app()->getRequest()->cookies['language']->expire = time() + (60 * 60 * 24 * 365 * 2); // (2 year)
	}
	
	public function getYiiAcceptedLocales() {
		return CLocale::getLocaleIds();
	}
	
	/**
	 * returns an array containing all languages accepted by google translate
	 *
	 * @param string $targetLanguage
	 * @return array
	 */
	public function getGoogleAcceptedLanguages() {
		$cacheKey = self::ID . '-cache-google-accepted-languages';
		if(!isset($this->_cache[$cacheKey])) {
			if(($cache = Yii::app()->getCache()) === null || ($languages = $cache->get($cacheKey)) === false) {
				$queryLanguages = $this->queryGoogle(array(), 'languages');
				if($queryLanguages === false) {
					Yii::log(TranslateModule::t('Failed to query Google\'s accepted languages.'));
					return false;
				}
				foreach($queryLanguages->languages as $language) {
					$languages[$language->language] = isset($language->name) ? $language->name : $language->language;
				}
				if($cache !== null)
					$cache->set($cacheKey, $languages);
			}
			$this->_cache[$cacheKey] = $languages;
		}
		return $this->_cache[$cacheKey];
	}
	
	public function getAdminAcceptedLanguages() {
		$cacheKey = self::ID . '-cache-admin-accepted-languages-' . Yii::app()->getLanguage();
		if(!isset($this->_cache[$cacheKey])) {
			if(($cache = Yii::app()->getCache()) === null || ($languages = $cache->get($cacheKey)) === false) {
				$languageDisplayNames = $this->getLanguageDisplayNames();
				foreach(AcceptedLanguages::model()->findAll() as $lang)
					$languages[$lang->id] = $languageDisplayNames[$lang->id];
				if($cache !== null)
					$cache->set($cacheKey, $languages);
			}
			$this->_cache[$cacheKey] = $languages;
		}
		return $this->_cache[$cacheKey];
	}
	
	public function isYiiAcceptedLocale($id) {
		return in_array($id, $this->getYiiAcceptedLocales());
	}
	
	public function isGoogleAcceptedLanguage($id) {
		return array_key_exists(Yii::app()->getLocale()->getLanguageID($id), $this->getGoogleAcceptedLanguages());
	}
	
	public function isAdminAcceptedLanguage($id) {
		return array_key_exists(Yii::app()->getLocale()->getLanguageID($id), $this->getAdminAcceptedLanguages());
	}
	
	public function getLanguageID() {
		return Yii::app()->getLocale()->getLanguageID(Yii::app()->getLanguage());
	}
	
	public function getScriptID() {
		$id = Yii::app()->getLocale()->getScriptID(Yii::app()->getLanguage());
		return $id === null ? Yii::app()->getLanguage() : $id;
	}
	
	public function getTerritoryID() {
		$id = Yii::app()->getLocale()->getTerritoryID(Yii::app()->getLanguage());
		return $id === null ? Yii::app()->getLanguage() : $id;
	}
	
	public function getLanguageDisplayName($id = null, $language = null) {
		return $this->getLocaleDisplayName($id, $language);
	}
	
	public function getLanguageDisplayNames($language = null) {
		return $this->getLocaleDisplayNames($language);
	}
	
	public function getScriptDisplayName($id = null, $language = null) {
		return $this->getLocaleDisplayName($id, $language, 'script');
	}
	
	public function getScriptDisplayNames($language = null) {
		return $this->getLocaleDisplayNames($language, 'script');
	}
	
	public function getTerritoryDisplayName($id = null, $language = null) {
		return $this->getLocaleDisplayName($id, $language, 'territory');
	}
	
	public function getTerritoryDisplayNames($language = null) {
		return $this->getLocaleDisplayNames($language, 'territory');
	}
	
	public function getLocaleDisplayName($id = null, $language = null, $category = 'language') {
		if($id === null) {
			$idMethod = 'get' . ucfirst($category) . 'ID';
			if(!method_exists($this, $idMethod)) {
				Yii::log(TranslateModule::t('Failed to query Yii locale DB. Possible invalid category requested {category}', array('{category}' => $category)));
				return false;
			}
			$id = $this->$idMethod();
		}
		$localeDisplayNames = $this->getLocaleDisplayNames($language, $category);
		return $localeDisplayNames === false ? false : $localeDisplayNames[$id];
	}
	
	public function getLocaleDisplayNames($language = null, $category = 'language') {
		if($language === null)
			$language = Yii::app()->getLanguage();
		$cacheKey = self::ID . "cache-i18n-$category-$language";
	
		if(!isset($this->_cache[$cacheKey])) {
			if(($cache = Yii::app()->getCache()) === null || ($languages = $cache->get($cacheKey)) === false) {
				$method = 'get' . ucfirst($category);
				$idMethod = "{$method}ID";
				if(!method_exists(Yii::app()->getLocale(), $method) || !method_exists(Yii::app()->getLocale(), $idMethod)) {
					Yii::log(TranslateModule::t('Failed to query Yii locale DB. Possible invalid category requested {category}', array('{category}' => $category)));
					return false;
				}
				foreach(CLocale::getLocaleIds() as $id) {
					$id = Yii::app()->getLocale()->$idMethod($id);
					if($id !== null) {
						$item = Yii::app()->getLocale()->$method($id);
						$languages[$id] = $item === null ? $id : $item;
					}
				}
				asort($languages, SORT_LOCALE_STRING);
				if($cache !== null)
					$cache->set($cacheKey, $languages);
			}
			$this->_cache[$cacheKey] = $languages;
		}
		return $this->_cache[$cacheKey];
	}

    /**
     * method that handles the on missing translation event
     * 
     * @param CMissingTranslationEvent $event
     * @return string the message to translate or the translated message if option autoTranslate is set to true
     */
    public function missingTranslation($event) {
        if($event !== null && 
        		(Yii::app()->getLocale()->getLanguageID($event->language) !== Yii::app()->getLocale()->getLanguageID(Yii::app()->sourceLanguage) || 
        			Yii::app()->getMessages()->forceTranslation)) {
        	Yii::import('translate.models.MessageSource');
        	$attributes = array('category' => $event->category, 'message' => $event->message);
        	if(($model = MessageSource::model()->find('message=:message AND category=:category', $attributes)) === null){
        		$model = new MessageSource();
        		$model->attributes = $attributes;
        		if(!$model->save()) {
        			Yii::log(TranslateModule::t('Message {message} could not be added to messageSource table', array('{message}' => $event->message)));
        			return false;
        		}
        	}
        	Yii::import('translate.models.Message');
        	if(($messageModel = Message::model()->find('id = :id AND language = :language', array('id' => $model->id, 'language' => $event->language))) === null &&
        		TranslateModule::translator()->autoTranslate) {
        		$translation = $event->message;

        		preg_match_all('/\{(.*?)\}/', $translation, $matches);
        		$matches = $matches[0];
        		for($i = 0; $i < count($matches); $i++)
        			$translation = str_replace($matches[$i], "_{$i}_", $translation);
        		
        		$translation = TranslateModule::translator()->googleTranslate(
        				$translation, 
        				$event->language, 
        				Yii::app()->getLocale()->getLanguageID(Yii::app()->sourceLanguage)
        			);
        		if($translation !== false) {
        			for($i = 0; $i < count($matches); $i++)
        				$translation = str_replace("_{$i}_", $matches[$i], $translation);

        			$messageModel = new Message;
        			$messageModel->attributes = array('id' => $model->id, 'language' => $event->language, 'translation' => $translation);
        			if(!$messageModel->save())
        				$messageModel = null;
        		}
        	}
            if($messageModel !== null) {
            	$event->message = $messageModel->translation;
            	return true;
            }
            Yii::log(TranslateModule::t('Message {message} could not be translated. The message has been added to missing translations.', array('message' => $event->message)));
            self::$messages[$model->id] = array('language' => $event->language, 'message' => $event->message, 'category' => $event->category);
        }
        return false;
    }
    
    /**
     * generates a link or button to the page where you translate the missing translations found in this page
     * 
     * @param string $label label of the link
     * @param string $type accepted types are : link and button
     * @return string
     */
    public function translateLink($label = 'Translate', $type = 'link') {
    	$label = TranslateModule::t($label);
        $form = CHtml::form(Yii::app()->getController()->createUrl('/translate/translate/missingOnPage'));
        foreach(self::$messages as $index => $message)
            foreach($message as $name => $value)
                $form .= CHtml::hiddenField(self::ID."-missing[$index][$name]", $value);
        if($type === 'button')
            $form .= CHtml::submitButton($label);
        else
            $form .= CHtml::linkButton($label);
        $form .= CHtml::endForm();
        return $form;
    }
    
    /**
     * generates a link or button that generates a dialog to the page where you translate the missing translations found in this page
     * 
     * @param string $label label of the link
     * @param mixed $title title of the popup
     * @param string $type accepted types are : link and button
     * @return string
     */
    public function translateDialogLink($label='Translate',$title=null,$type='link'){
    	$label = TranslateModule::t($label);
        return $this->ajaxDialog($label,'translate/translate/missingOnPage',$title,$type,array('data'=>array(self::ID.'-missing'=>self::$messages)));
    }
    
    private function ajaxDialog($label, $url, $title=null, $type='link', $ajaxOptions=array()){
        
        $id=self::ID.'-dialog';
        
        $ajaxOptions=array_merge(array(
            'update'=> "#$id",
            'type'=>'post',
            'complete'=>"function(){ $('#{$id}').dialog('option', 'position', 'center').dialog('open');}",
        ),$ajaxOptions);
        
        $url=Yii::app()->getController()->createUrl($url);
        
        if($type==='button')
            $content=CHtml::ajaxButton($label,$url,$ajaxOptions);
        else
            $content=CHtml::ajaxLink($label,$url,$ajaxOptions);
        
        $content.=Yii::app()->getController()->widget('zii.widgets.jui.CJuiDialog',array(
            'options'=>array_merge($this->dialogOptions,array('title'=>$title)),
            'id'=>$id
        ),true);
        return $content;
    }

    /**
     * translate some message from $sourceLanguage to $targetLanguage using google translate api
     * googleApiKey must be defined to use this service
     * @param string $message to be translated
     * @param string $targetLanguage language to translate the message to, if null it will use the current language in use
     * @param mixed $sourceLanguage language that the message is written in, if null it will use the application source language
     * @return string translated message
     */
    public function googleTranslate($message,$targetLanguage=null,$sourceLanguage=null) {
        if($targetLanguage===null)
            $targetLanguage=Yii::app()->getLanguage();
        if($sourceLanguage===null)
            $sourceLanguage=Yii::app()->sourceLanguage;
        if(empty($sourceLanguage))
            throw new CException(TranslateModule::t('Source language must be defined'));
        if($targetLanguage===$sourceLanguage)
            throw new CException(TranslateModule::t('targetLanguage must be different than sourceLanguage'));
        $query=$this->queryGoogle(array('q'=>$message,'source'=>$sourceLanguage,'target'=>$targetLanguage));
        if($query===false)
            return false;
        if(is_array($message)){
            foreach($query->translations as $translation)
                $translated[]=$translation->translatedText;
            return $translated;
        }
        return $query->translations[0]->translatedText;
    }

    /**
     * query google translate api 
     * 
     * @param array $args
     * @param string $method the method to use, use null to translate
     * accepted values are null(translate), "languages" and "detect"
     * @return stdClass the google response object
     */
    protected function queryGoogle($args=array(),$method=null){
        if(empty($this->googleApiKey))
            throw new CException(TranslateModule::t('You must provide your google api key in option googleApiKey'));
        if($method!==null)
            $method="/{$method}";
        $url=preg_replace('/%5B\d+%5D/','',"https://www.googleapis.com/language/translate/v2{$method}?".http_build_query(array_merge($args,array('key'=>$this->googleApiKey))));

        if(in_array('curl',get_loaded_extensions())){//curl has much better performance
            $curl=curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//to speed up the query
            $trans = curl_exec($curl);
            curl_close($curl);
        }else
            $trans=file_get_contents($url);
        
        if(!$trans)
            return false;
        $trans=json_decode($trans);
        
        if(isset($trans->error)){
            Yii::log('Google translate error:'.$trans->error->code.'. '.$trans->error->message,CLogger::LEVEL_ERROR,'translate');
            return false;
        }elseif(!isset($trans->data)){
            Yii::log('Google translate error:'.print_r($trans,true),CLogger::LEVEL_ERROR,'translate');
            return false;
        }else
            return $trans->data;
    }
    
    public function getFormatByType($format_type, $format_id, $datetime_format=false) {
    	$res = false;
    
    	$datetime_format = (!empty($datetime_format) ?
    			$datetime_format : Yii::app()->getLocale()->getDateTimeFormat());
    
    	switch ($format_type) {
    		case 'date': {
    			$res =Yii::app()->getLocale()->getDateFormat($format_id);
    		} break;
    		case 'time': {
    			$res =Yii::app()->getLocale()->getTimeFormat($format_id);
    		} break;
    		case 'datetime': {
    			$res =strtr(
    					$datetime_format,
    					array(
    							"{0}" => Yii::app()->getLocale()->getTimeFormat($format_id),
    							"{1}" => Yii::app()->getLocale()->getDateFormat($format_id),
    					)
    			);
    		} break;
    	}
    
    	return $res;
    }
    
    
    public function getDbFormat($format_type) {
    	$dt_format =Yii::app()->params['database_format']['dateTimeFormat'];
    	return $this->getFormatByType($format_type, 'database', $dt_format);
    }
    
    
    /**
     * Convert a date, time or datetime to local/given format to local format
     * @param <mixed>   $dt date  time, datetime or timestamp
     * @param <string>  $format_type  'date' | 'time' | 'datetime' used as output
     * @param <string>  $to_format_id  id/width of the format (short, medium, ..)
     * @param <string>  $from_format  manually specified locale format string
     * @param <bool>    $is_timestamp if true, dt will be considered a timestamp
     * @return <mixed>  formatted string or false if fail
     */
    public function toLocal($dt, $format_type=false, $to_format_id=false, $from_format=false, $is_timestamp=false) {
    	$res =false;
    
    	// kind of data we are convering; datetime is default:
    	$format_type =(!empty($format_type) ? $format_type : 'datetime');
    
    	// format id ("width") of data we are convering to; small is default:
    	$to_format_id =(!empty($to_format_id) ? $to_format_id : 'short');
    
    	if (!$is_timestamp) {
    		// default storage format:
    		$from_format =(!empty($from_format) ? $from_format : $this->getDbFormat($format_type));
    
    		$res =Yii::app()->dateFormatter->format(
    				$this->getFormatByType($format_type, $to_format_id),
    				CDateTimeParser::parse($dt, $from_format)
    		);
    	}
    	else {
    		$res =Yii::app()->dateFormatter->format(
    				$this->getFormatByType($format_type, $to_format_id),
    				$dt // timestamp
    		);
    	}
    
    	return $res;
    }
    
    
    /**
     * Convert a date, time or datetime to local/given format to database format
     * @param <mixed>   $dt date  time, datetime or timestamp
     * @param <string>  $to_format_type  'date' | 'time' | 'datetime' used as output
     * @param <string>  $from_format_id  id/width of the format (short, medium, ..)
     * @param <string>  $from_format_type  'date' | 'time' | 'datetime' used as input
     * @param <string>  $from_format  manually specified locale format string
     * @param <bool>    $is_timestamp if true, dt will be considered a timestamp
     * @return <mixed>  formatted string or false if fail
     */
    public function toDatabase($dt, $to_format_type=false, $from_format_id=false,
    		$from_format_type=false, $from_format=false, $is_timestamp=false) {
    	$res =false;
    
    	// kind of data we are convering to; datetime is default:
    	$to_format_type =(!empty($to_format_type) ? $to_format_type : 'datetime');
    
    	// kind of data we are convering from; by default it is the
    	// same as to_format_type, that it is a common situation:
    	$from_format_type =(!empty($from_format_type) ? $from_format_type : $to_format_type);
    
    	// format id ("width") of data we are convering from; small is default:
    	$from_format_id =(!empty($from_format_id) ? $from_format_id : 'short');
    
    	if (!$is_timestamp) {
    		$from_format =(!empty($from_format) ?
    				$from_format : $this->getFormatByType($from_format_type, $from_format_id));
    
    		$res =Yii::app()->dateFormatter->format(
    				$this->getFormatByType($to_format_type, 'database'),
    				CDateTimeParser::parse($dt, $from_format)
    		);
    	}
    	else {
    		$res =Yii::app()->dateFormatter->format(
    				$this->getFormatByType($to_format_type, 'database'),
    				$dt // timestamp
    		);
    	}
    
    	return $res;
    }
    
    
    /**
     *
     * @param <type> $dt
     * @param <type> $format_type
     * @param <type> $format_id
     * @param <type> $is_timestamp
     * @return <type>
     */
    public function splitDatetime($dt, $format_type=false, $format_id=false, $is_timestamp=false) {
    	$res =false;
    	$timestamp =0;
    
    	if (!$is_timestamp) {
    		// kind of data we are convering to; datetime is default:
    		$format_type =(!empty($format_type) ? $format_type : 'datetime');
    		// format id ("width") of data we are convering from; small is default:
    		$format_id =(!empty($format_id) ? $format_id : 'short');
    		$from_format =$this->getFormatByType($format_type, $format_id);
    		$timestamp =CDateTimeParser::parse($dt, $from_format);
    	}
    	else {
    		$timestamp =$dt;
    	}
    
    	if ($timestamp > 0) {
    		// not using getdate() as we want to have 2 digits values
    
    		$my_format ='yyyy-MM-dd HH:mm:ss';
    		$my_date_string =Yii::app()->dateFormatter->format($my_format, $timestamp);
    
    		$res =array(
    				'date'=>substr($my_date_string, 0, 10),
    				'time'=>substr($my_date_string, 11),
    				'datetime'=>$my_date_string,
    				'year'=>substr($my_date_string, 0, 4),
    				'yy'=>substr($my_date_string, 2, 2),
    				'month'=>substr($my_date_string, 5, 2),
    				'day'=>substr($my_date_string, 8, 2),
    				'hour'=>substr($my_date_string, 11, 2),
    				'min'=>substr($my_date_string, 14, 2),
    				'sec'=>substr($my_date_string, 17, 2),
    		);
    	}
    
    	return $res;
    }
    
    
    /**
     *
     * @param <type> $dt_arr
     * @param <type> $to_format_type
     * @param <type> $format_id
     * @param <type> $from_format_id
     * @return <type>
     */
    public function mergeDatetime($dt_arr, $to_format_type=false, $format_id=false, $from_format_id=false) {
    	// kind of data we are convering to; datetime is default:
    	$to_format_type =(!empty($to_format_type) ? $to_format_type : 'datetime');
    	// format id ("width") of data we are convering from; small is default:
    	$format_id =(!empty($format_id) ? $format_id : 'short');
    	$from_format_id =(!empty($from_format_id) ? $from_format_id : $format_id);
    
    	if (isset($dt_arr['date'])) {
    		$from_format =$this->getFormatByType('date', $from_format_id);
    		$timestamp =CDateTimeParser::parse($dt_arr['date'], $from_format);
    		$dt_info =getdate($timestamp);
    		$dt_arr['day']=$dt_info['mday'];
    		$dt_arr['month']=$dt_info['mon'];
    		$dt_arr['year']=$dt_info['year'];
    	}
    
    	if (isset($dt_arr['time'])) {
    		$from_format =$this->getFormatByType('time', $from_format_id);
    		$timestamp =CDateTimeParser::parse($dt_arr['time'], $from_format);
    		$dt_info =getdate($timestamp);
    		$dt_arr['hour']=$dt_info['hours'];
    		$dt_arr['min']=$dt_info['minutes'];
    		$dt_arr['sec']=$dt_info['seconds'];
    	}
    
    	$timestamp =mktime(
    			(isset($dt_arr['hour']) ? $dt_arr['hour'] : null),
    			(isset($dt_arr['min']) ? $dt_arr['min'] : null),
    			(isset($dt_arr['sec']) ? $dt_arr['sec'] : null),
    			(isset($dt_arr['month']) ? $dt_arr['month'] : null),
    			(isset($dt_arr['day']) ? $dt_arr['day'] : null),
    			(isset($dt_arr['year']) ? $dt_arr['year'] : null)
    	);
    
    	$my_format ='yyyy-MM-dd HH:mm:ss';
    	$to_format =$this->getFormatByType($to_format_type, $format_id);
    	$res =Yii::app()->dateFormatter->format($my_format, $timestamp);
    
    	return $res;
    }
    
    /**
     * helper so you can use MPTransalate::someMethod($args) 
     * 
     * php 5.3 only
     * 
     * @param mixed $method
     * @param mixed $args
     * @return mixed
     */
    static function __callStatic($method,$args){
        return call_user_func_array(array(TranslateModule::translator(),$method),$args);
    }
    
}