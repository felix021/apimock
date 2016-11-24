<?php

/**
 * This is the model class for table "api".
 *
 * The followings are the available columns in table 'api':
 * @property integer $api_id
 * @property string $api_name
 * @property string $api_desc
 * @property integer $api_result_id
 * @property string $api_created_at
 * @property string $api_updated_at
 *
 * The followings are the available model relations:
 * @property Result[] $results
 */
class Api extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'api';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('api_name', 'required'),
            array('api_result_id', 'numerical', 'integerOnly'=>true),
            array('api_name', 'length', 'max'=>64),
            array('api_desc', 'length', 'max'=>128),
            array('api_created_at', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('api_id, api_name, api_desc, api_result_id, api_created_at, api_updated_at', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'result' => array(self::BELONGS_TO, 'Result', 'api_result_id'),
            'resultSet' => array(self::HAS_MANY, 'Result', 'result_api_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'api_id' => 'Api',
            'api_name' => 'Api Name',
            'api_desc' => 'Api Desc',
            'api_result_id' => '目前选用的result',
            'api_created_at' => '创建时间',
            'api_updated_at' => '更新时间',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search($key)
    {
        $criteria=new CDbCriteria;
        $criteria->addSearchCondition('api_name', '%' . $key . '%', false, 'OR');
        $criteria->addSearchCondition('api_desc', '%' . $key . '%', false, 'OR');
        return self::model()->findAll($criteria);
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Api the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function create($api_name, $api_desc)
    {
        $o = new self();
        $o->api_name = self::format($api_name);
        $o->api_desc = $api_desc;
        $o->api_created_at = Dh::now();
        if (!$o->save()) {
            throw new CException(__METHOD__ . " failed for: " . var_export($o->getErrors(), true));
        }
        return $o;
    }

    public function changeResult($api_result_id)
    {
        $this->api_result_id = $api_result_id;
        $this->save();
    }

    public function changeDesc($api_desc)
    {
        $this->api_desc = $api_desc;
        $this->save();
    }

    public function findByName($api_name)
    {
        return $this->with('result')->findByAttributes(['api_name' => $api_name]);
    }

    public static function format($api_name)
    {
        return '/' . trim($api_name, '/');
    }
}
