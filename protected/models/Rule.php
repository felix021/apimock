<?php

/**
 * This is the model class for table "rule".
 *
 * The followings are the available columns in table 'rule':
 * @property integer $rule_id
 * @property string $rule_name
 * @property integer $rule_batch_id
 * @property integer $rule_api_id
 * @property integer $rule_result_id
 * @property string $rule_created_at
 * @property string $rule_updated_at
 *
 * The followings are the available model relations:
 * @property Api $ruleApi
 * @property Batch $ruleBatch
 * @property Result $ruleResult
 */
class Rule extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'rule';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('rule_batch_id, rule_api_id, rule_result_id', 'required'),
            array('rule_batch_id, rule_api_id, rule_result_id', 'numerical', 'integerOnly'=>true),
            array('rule_name', 'length', 'max'=>128),
            array('rule_created_at', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('rule_id, rule_name, rule_batch_id, rule_api_id, rule_result_id, rule_created_at, rule_updated_at', 'safe', 'on'=>'search'),
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
            'api' => array(self::BELONGS_TO, 'Api', 'rule_api_id'),
            'batch' => array(self::BELONGS_TO, 'Batch', 'rule_batch_id'),
            'result' => array(self::BELONGS_TO, 'Result', 'rule_result_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'rule_id' => 'Rule',
            'rule_name' => 'Rule Name',
            'rule_batch_id' => 'Rule Batch',
            'rule_api_id' => 'Rule Api',
            'rule_result_id' => 'Rule Result',
            'rule_created_at' => '创建时间',
            'rule_updated_at' => '更新时间',
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

        $criteria->compare('rule_id', $this->rule_id);
        $criteria->compare('rule_name', $this->rule_name, true);
        $criteria->compare('rule_batch_id', $this->rule_batch_id);
        $criteria->compare('rule_api_id', $this->rule_api_id);
        $criteria->compare('rule_result_id', $this->rule_result_id);
        $criteria->compare('rule_created_at', $this->rule_created_at, true);
        $criteria->compare('rule_updated_at', $this->rule_updated_at, true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Rule the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function saveRule($batch_id, $api_id, $result_id)
    {
        $o = new self();
        $o->rule_batch_id = $batch_id;
        $o->rule_api_id = $api_id;
        $o->rule_result_id = $result_id;
        $o->rule_created_at = Dh::now();
        try {
            if (!$o->save()) {
                throw new CException(__METHOD__ . ' failed for: ' . var_export($o->getErrors(), true));
            }
            return $o;
        } catch (CDbException $e) {
            if ($e->getCode() == 23000) {
                $o = $this->findByAttributes(['rule_batch_id' => $batch_id, 'rule_api_id' => $api_id]);
                if (!$o) {
                    throw new CDbException("duplicated but not found");
                }
                $o->rule_result_id = $result_id;
                $o->save();
                return $o;
            } else {
                throw $e;
            }
        }
    }
}
