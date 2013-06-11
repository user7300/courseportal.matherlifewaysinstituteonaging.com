<?php

Yii::import('zii.widgets.grid.CGridView');

class MissingTranslationGrid extends CGridView {
	
	public $translateModulePathAlias = 'modules.translate';
	
	public $sourceMessage;
	
	public $createMessageRoute = 'message/create';
	
	public function init() {
		if(!isset($this->sourceMessageId))
			throw new CException(TranslateModule::t('A sourceMessageId must be set in MissingTranslationGrid.'));
		
		Yii::import($this->translateModulePathAlias . 'models.*');
		
		$this->dataProvider = new CActiveDataProvider('Message', array('criteria' => Message::model()->missingTranslations($this->sourceMessageId)->getDbCriteria()));
		$this->columns = array(
				array(
						'name' => 'language'
				),
				array(
						'class' => 'CButtonColumn',
						'template' => '{update}',
						'updateButtonLabel' => TranslateModule::t('Create Translation'),
						'updateButtonUrl' => 'Yii::app()->getController()->createUrl("'.$this->createMessageRoute.'", array("id" => '.$this->sourceMessageId.', "languageId" => $data->language))',
				)
		);
		
		parent::init();
	}

}