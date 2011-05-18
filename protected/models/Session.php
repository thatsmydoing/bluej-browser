<?php

/**
 * This is the model class for table "Session".
 *
 * @author Thomas Dy <thatsmydoing@gmail.com>
 * @copyright Copyright &copy; 2010-2011 Ateneo de Manila University
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * The followings are the available columns in table 'Session':
 * @property integer $id
 * @property integer $userId
 * @property string $date
 * @property string $type
 *
 * A generic session. Delegates actions to it's "subclasses",
 * CompileSession and InvocationSession (so far).
 */
class Session extends CActiveRecord {
	private $_child;

	public function __get($var) {
		if($var == 'child') {
			if(isset($_child)) {
				return $_child;
			}
			$type = strtolower(substr($this->type, 0, 1)) . substr($this->type, 1);
			$_child = $this->$type;
			return $_child;
		}
		return parent::__get($var);
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @return Session the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'Session';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('userId', 'numerical', 'integerOnly'=>true),
			array('date, type', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, userId, date, type', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'compileSession' => array(self::HAS_ONE, 'CompileSession', 'id'),
			'import' => array(self::HAS_ONE, 'Import', 'sessionId'),
			'invocationSession' => array(self::HAS_ONE, 'InvocationSession', 'id'),
			'user' => array(self::BELONGS_TO, 'User', 'userId'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => 'ID',
			'userId' => 'User',
			'date' => 'Date',
			'type' => 'Type',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);

		$criteria->compare('userId',$this->userId);

		$criteria->compare('date',$this->date,true);

		$criteria->compare('type',$this->type,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Run before deleting a session, cascades the deletions.
	 */
	protected function beforeDelete() {
		$model = CActiveRecord::model($this->type)->findByPk($this->id)->delete();
		return parent::beforeDelete();
	}
}
