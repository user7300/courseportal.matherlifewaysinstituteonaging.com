<?php

class Survey extends CWidget {
	
	private $_data = 
		array(
			'showStats' => false,
			'survey' => array('model' => null), 
			'title' => array('show' => true),
			'description' => array('show' => true),
			'form' => array('show' => true),
			'question' => array('show' => true),
			'submitButton' => array('show' => true)
	   );
	
	public function run() {
		if($this->_data['survey']['model'] !== null) {
			if($this->_data['survey']['model'] instanceof SurveyForm) {
				if(!isset($this->_data['form']['options']['action']))
					$this->_data['form']['options']['action'] = $this->getController()->createUrl('surveyor/survey/submit');
				$this->render('survey', $this->_data);
				return;
			} else if($this->_data['survey']['model'] instanceof SurveyAR) {
				$this->render('surveyStats', $this->_data);
				return;
			}
		}
		throw new CHttpException(500, 'survey model must be an instance of SurveyForm or SurveyAR.');
	}
	
	public function setModel($surveyModel) {
		$this->_data['survey']['model'] = $surveyModel;
		foreach($this->_data as $key => $val) {
			if(!isset($this->_data[$key]['options']['id']))
				$this->_data[$key]['options']['id'] = "{$key}_{$this->_data['survey']['model']->name}";
		}
	}
	
	public function __set($name, $value) {
		if(isset($this->_data[$name])) {
			if(is_array($this->_data[$name])) {
				if(!is_array($value))
					$value = array($value);
				$this->_data[$name] = CMap::mergeArray($this->_data[$name], $value);
			} else {
				$this->_data[$name] = $value;
			}
		} else {
			parent::__set($name, $value);
		}
	}
	
}