<?php

class UpdatedRecordLogSearch extends SearchBase
{
    private $langId;

    public function __construct($langId = 0)
    {
        parent::__construct(UpdatedRecordLog::DB_TBL, 'urlog');
        $this->langId = FatUtility::int($langId);
    }
}
