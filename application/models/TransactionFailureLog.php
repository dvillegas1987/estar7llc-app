<?php

class TransactionFailureLog extends MyAppModel
{
    public const DB_TBL = 'tbl_transactions_failure_log';
    public const DB_TBL_PREFIX = 'txnlog_';

    public const LOG_TYPE_CHECKOUT = 1;

    /**
     * __construct
     *
     * @param  mixed $id
     * @return void
     */
    public function __construct(int $id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->objMainTableRecord->setSensitiveFields(
            [self::DB_TBL_PREFIX . 'id']
        );
    }

    /**
     * getLogTypes
     *
     * @return array
     */
    public static function getLogTypes(): array
    {
        return [
            self::LOG_TYPE_CHECKOUT
        ];
    }

    /**
     * saveResponse
     *
     * @param  int $type
     * @param  mixed $response
     * @param  mixed $error
     * @return bool
     */
    public static function set(int $type, string $recordId, string $response, string &$error = ''): bool
    {
        if (1 > $type || !in_array($type, self::getLogTypes()) || empty($response) || empty($recordId)) {
            $error = Labels::getLabel('MSG_INVALID_REQUEST', CommonHelper::getLangId());
            return false;
        }

        $data = [
            self::DB_TBL_PREFIX . 'type' => $type,
            self::DB_TBL_PREFIX . 'record_id' => $recordId,
            self::DB_TBL_PREFIX . 'response' => $response
        ];
        $self = new self();
        $self->assignValues($data);

        if (!$self->save()) {
            $error = $self->getError();
            return false;
        }
        return true;
    }
}
