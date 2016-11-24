<?php

/**
 * This is the model class for table "batch".
 *
 * The followings are the available columns in table 'batch':
 * @property integer $batch_id
 * @property string $batch_name
 * @property string $batch_created_at
 * @property string $batch_updated_at
 *
 * The followings are the available model relations:
 * @property Rule[] $ruleSet
 */
class Batch extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'batch';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('batch_name', 'required'),
            array('batch_name', 'length', 'max'=>128),
            array('batch_created_at', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('batch_id, batch_name, batch_created_at, batch_updated_at', 'safe', 'on'=>'search'),
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
            'ruleSet' => array(self::HAS_MANY, 'Rule', 'rule_batch_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'batch_id' => 'Batch',
            'batch_name' => 'Batch Name',
            'batch_created_at' => '创建时间',
            'batch_updated_at' => '更新时间',
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
        $criteria->addSearchCondition('batch_name', '%' . $key . '%', false, 'OR');
        $criteria->addCondition('batch_name REGEXP :key', 'OR');
        $criteria->params['key'] = $key;
        return self::model()->findAll($criteria);
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Batch the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function create($batch_name)
    {
        $o = new self();
        $o->batch_name = $batch_name;
        $o->batch_created_at = Dh::now();
        try {
            if (!$o->save()) {
                throw new CException(__METHOD__ . ' failed for: ' . var_export($o->getErrors(), true));
            }
        } catch (CDbException $e) {
            if ($e->getCode() == 23000) {
                throw new CDbException("场景重名");
            } else {
                throw $e;
            }
        }
        return $o;
    }

    public function changeName($batch_name)
    {
        $this->batch_name = $batch_name;
        try {
            $this->save();
        } catch (CDbException $e) {
            if ($e->getCode() == 23000) {
                throw new CDbException("场景重名");
            } else {
                throw $e;
            }
        }
    }

    public function apply()
    {
        $trans = DbHelper::startTrans($this->getDbConnection());
        try {
            foreach ($this->ruleSet as $rule) {
                $rule->api->changeResult($rule->rule_result_id);
            }
            $trans->commit();
        } catch (Exception $e) {
            $trans->rollback();
            throw $e;
        }
    }
}
