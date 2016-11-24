<?php

/**
 * This is the model class for table "result".
 *
 * The followings are the available columns in table 'result':
 * @property integer $result_id
 * @property integer $result_api_id
 * @property string $result_desc
 * @property string $result_content
 * @property string $result_created_at
 * @property string $result_updated_at
 *
 * The followings are the available model relations:
 * @property Api $resultApi
 */
class Result extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'result';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('result_api_id, result_content', 'required'),
            array('result_api_id', 'numerical', 'integerOnly'=>true),
            array('result_desc', 'length', 'max'=>128),
            array('result_created_at', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('result_id, result_api_id, result_desc, result_content, result_created_at, result_updated_at', 'safe', 'on'=>'search'),
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
            'api' => array(self::BELONGS_TO, 'Api', 'result_api_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'result_id' => 'Result',
            'result_api_id' => 'Result Api',
            'result_desc' => 'Result Desc',
            'result_content' => 'Result Content',
            'result_created_at' => '创建时间',
            'result_updated_at' => '更新时间',
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
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('result_id', $this->result_id);
        $criteria->compare('result_api_id', $this->result_api_id);
        $criteria->compare('result_desc', $this->result_desc, true);
        $criteria->compare('result_content', $this->result_content, true);
        $criteria->compare('result_created_at', $this->result_created_at, true);
        $criteria->compare('result_updated_at', $this->result_updated_at, true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Result the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function modify($result_desc, $result_content)
    {
        $this->result_desc = $result_desc;
        $this->result_content = $result_content;
        $this->save();
    }

    public function create($api_id, $result_desc, $result_content)
    {
        $o = new self();
        $o->result_api_id = $api_id;
        $o->result_desc = $result_desc;
        $o->result_content = $result_content;
        $o->result_created_at = Dh::now();
        if (!$o->save()) {
            throw new CException(__METHOD__ . ' failed for: ' . var_export($o->getErrors(), true));
        }
        return $o;
    }
}
