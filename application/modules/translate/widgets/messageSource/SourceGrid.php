<?php

Yii::import('zii.widgets.grid.CGridView');

class SourceGrid extends CGridView {
	
	public $translateModulePathAlias = 'modules.translate';
	
	public $sources;
	
	public $viewRoute = 'messageSource/view';
	public $deleteRoute = 'messageSource/delete';
	
	public function init() {
		$modelName = get_class($this->sources);
		$userStateAttr = "{$this->getId()}-$modelName-";
		
		if(isset($_GET["{$modelName}_page"])) {
			Yii::app()->getUser()->setState("$userStateAttr-page", $_GET["{$modelName}_page"]);
		} else if(Yii::app()->getUser()->hasState("$userStateAttr-page")) {
			$_GET["{$modelName}_page"] = Yii::app()->getUser()->getState("$userStateAttr-page");
		}

		foreach($this->sources->getSafeAttributeNames() as $safeAttr) {
			if(isset($_GET[$modelName][$safeAttr])) {
				Yii::app()->getUser()->setState("$userStateAttr-$safeAttr", $_GET[$modelName][$safeAttr]);
			} else if(Yii::app()->getUser()->hasState("$userStateAttr-$safeAttr")) {
				$this->sources->$safeAttr = Yii::app()->getUser()->getState("$userStateAttr-$safeAttr");
			}
		}
		
		Yii::import($this->translateModulePathAlias . 'models.*');
		
		$this->dataProvider = new CActiveDataProvider($this->sources, array('criteria' => $this->sources->search()->getDbCriteria()));
		$this->filter = $this->sources;
		$this->columns = array(
			array(
	            'name' => 'id',
	        ),
			array(
				'name' => 'category',
			),
	        array(
	            'name' => 'message',
	        	'htmlOptions' => array('width' => '600'),
	        ),
			array(
				'header' => TranslateModule::t('Missing Translations?'),
				'type' => 'boolean',
				'value' => '$data->isMissingTranslations()',
			),
	        array(
	            'class' => 'CButtonColumn',
	            'template' => '{view}{delete}',
	        	'viewButtonLabel' => TranslateModule::t('View Translations'),
	        	'viewButtonUrl' => 'Yii::app()->getController()->createUrl("'.$this->viewRoute.'", array("id" => $data->id))',
	            'deleteButtonUrl' => 'Yii::app()->getController()->createUrl("'.$this->deleteRoute.'", array("id" => $data->id))',
	        )
		);
		
		parent::init();
	}
	
	public function hasSourceMessageWithMissingTranslations() {
		foreach($this->dataProvider->getData() as $item)
			if($item->isMissingTranslations())
				return true;
		return false;
	}

}