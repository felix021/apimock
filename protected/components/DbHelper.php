<?php

/**
 * 数据库助手
 */
class DbHelper
{
    const LEVEL_REPEATABLE_READ     = 1;
    const LEVEL_READ_COMMITED       = 2;
    const LEVEL_READ_UNCOMMITED     = 3;
    const LEVEL_SERIALIZABLE        = 4;

    public static $level_map = [
        self::LEVEL_REPEATABLE_READ     => 'REPEATABLE READ',
        self::LEVEL_READ_UNCOMMITED     => 'READ UNCOMMITTED',
        self::LEVEL_READ_COMMITED       => 'READ COMMITTED',
        self::LEVEL_SERIALIZABLE        => 'SERIALIZABLE',
    ];

    /**
     * 在指定名字的DB组件上，开启事务
     * @param mixed $db 可以传组件的id，或者传CDbConnection对象
     * @return CDbTransaction
     * @throws CDbException
     */
    public static function startTrans($db)
    {
        $conn = self::getDbConnection($db);
        $dbtrans = $conn->beginTransaction();
        if ($dbtrans->active && $conn->active) {
            return $dbtrans;
        }
        throw new CDbException('create transaction failed');
    }

    /**
     * 在指定名字的DB组件上，修改隔离级别
     * @param mixed $db 可以传组件的id，或者传CDbConnection对象
     * @return null
     * @throws CDbException
     */
    public static function setIsolationLevel($db, $level = self::LEVEL_REPEATABLE_READ)
    {
        if (!array_key_exists($level, self::$level_map)) {
            throw new CDbException("unknown level($level)");
        }
        $level_name = self::$level_map[$level];
        $conn = self::getDbConnection($db);
        $conn->createCommand("SET SESSION TRANSACTION ISOLATION LEVEL $level_name")->execute();
    }

    /**
     * 检查当前的业务是否在数据库事务中运行
     *
     * @param mixed $db 可以传组件的id，或者传CDbConnection对象
     * @return CDbTransaction 顺便返回当前运行的数据库事务的对象
     * @throws CDbException 如果当前的数据库连接没有运行在事务中，则抛出异常
     */
    public static function transactionCheck($db)
    {
        $conn        = self::getDbConnection($db);
        $transaction = $conn->getCurrentTransaction();
        if ($transaction == null || !$transaction->active) {
            throw new CDbException('not in transaction');
        }
        return $transaction;
    }

    /**
     * 获取数据库连接
     * @param mixed $db
     * @return CDbConnection
     */
    protected static function getDbConnection($db)
    {
        if (is_string($db)) {
            $db = Yii::app()->getComponent($db);
        }
        if ($db instanceof CDbConnection) {
            return $db;
        }
        throw new CDbException('invalid connection');
    }
}
