<?php

/**
 * This is the model class for table "{{translate_compiled_view_message}}".
 *
 * The followings are the available columns in table '{{translate_compiled_view_message}}':
 * @property integer $message_source_id
 * @property integer $compiled_view_id
 */
class CompiledViewMessage extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return CompiledViewMessage the static model class
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
		return '{{translate_compiled_view_message}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('message_source_id, compiled_view_id', 'required'),
			array('message_source_id, compiled_view_id', 'numerical', 'integerOnly' => true),
				
			array('message_source_id, compiled_view_id', 'safe', 'on' => 'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
				'messageSource' => array(self::BELONGS_TO, 'MessageSource', 'message_source_id'),
				'compiledView' => array(self::BELONGS_TO, 'CompiledView', 'compiled_view_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'message_source_id' => 'Message Source',
			'compiled_view_id' => 'Compiled View',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria = new CDbCriteria;

		$criteria->compare('message_source_id', $this->message_source_id);
		$criteria->compare('compiled_view_id', $this->compiled_view_id);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}
}