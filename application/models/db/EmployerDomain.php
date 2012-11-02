<?php defined('BASEPATH') or exit('No direct script access allowed');  

/**
 * This is the model class for table "employer_domain".
 *
 * The followings are the available columns in table 'employer_domain':
 * @property integer $id
 * @property string $domain
 */
class EmployerDomain extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return EmployerDomain the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{employer_domain}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('domain', 'required'),
			array('domain', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, domain', 'safe', 'on'=>'search'),
		);
	}
	
	public function behaviors() {
		return array_merge(parent::behaviors(), array('toArray' => array('class' => 'behaviors.EArrayBehavior')));
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => t('ID'),
			'domain' => t('Domain'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('domain',$this->domain,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}