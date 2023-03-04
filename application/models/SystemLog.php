<?php

class SystemLog extends MyAppModel
{
    public const DB_TBL = 'tbl_system_logs';
    public const DB_TBL_PREFIX = 'slog_';

    public const TYPE_ERROR = 1;

    public function __construct($logId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $logId);
    }

    public static function clearOldLog()
    {
        FatApp::getDb()->deleteRecords(
            self::DB_TBL,
            array(
                'smt' => 'slog_created_at < ?',
                'vals' => array(
                    date('Y-m-d', strtotime("-3 Day"))
                )
            )
        );
    }

    public static function set($msg, $type = self::TYPE_ERROR)
    {
        FatApp::getDb()->insertFromArray(
            self::DB_TBL,
            array(
                'slog_details' => $msg,
                'slog_type' => $type,
                'slog_created_at' => date('Y-m-d H:i:s'),
            )
        );
    }
}
