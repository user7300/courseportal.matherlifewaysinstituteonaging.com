<?php

/**
 * This is the model class for table "{{translate_view_message}}".
 *
 * The followings are the available columns in table '{{translate_view_message}}':
 * @property integer $message_id
 * @property integer $view_id
 */
class ViewMessage extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ViewMessage the static model class
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return TranslateModule::translator()->getViewSource()->viewMessageTable;
	}
	
	public function behaviors()
	{
		return array(
				'ERememberFiltersBehavior' => array(
						'class' => 'translate.behaviors.ERememberFiltersBehavior',
				)
		);
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('message_id, view_id', 'required', 'except' => 'search'),
			array('message_id, view_id', 'numerical', 'integerOnly' => true),
			array('message_id', 'exist', 'attributeName' => 'id', 'className' => 'MessageSource', 'except' => 'search'),
			array('view_id', 'exist', 'attributeName' => 'id', 'className' => 'ViewSource', 'except' => 'search'),
				
			array('message_id, view_id', 'safe', 'on' => 'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
				'messageSource' => array(self::BELONGS_TO, 'MessageSource', 'message_id'),
				'viewSource' => array(self::BELONGS_TO, 'ViewSource', 'view_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'message_id' => TranslateModule::t('Message ID'),
			'view_id' => TranslateModule::t('View ID'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria = new CDbCriteria;

		$criteria->compare($this->getTableAlias(false, false).'.message_id', $this->message_id);
		$criteria->compare($this->getTableAlias(false, false).'.view_id', $this->view_id);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}
}