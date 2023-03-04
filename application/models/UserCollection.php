<?php
class UserCollection extends FatModel
{
    public const DB_TBL = 'tbl_user_collections';
    public const DB_TBL_PREFIX = 'uc_';

    public const TYPE_SAVED_FOR_LATER = 1;

    private $type = 0;
    private $userId = 0;
    
    /**
     * __construct
     *
     * @param  int $userId
     * @param  int $type
     * @return void
     */
    public function __construct(int $userId, int $type = 0)
    {
        $this->userId = $userId;
        $this->type = $type;
        $this->langId = CommonHelper::getLangId();
    }
    
    /**
     * save
     *
     * @param  int $recordId
     * @return bool
     */
    public function save(int $recordId) : bool
    {
        if (1 > $this->userId || 1 > $this->type) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->langId);
            return false;
        }

        $data = array(
            'uc_user_id' => $this->userId,
            'uc_type' => $this->type,
            'uc_record_id' => $recordId
        );

        if (!FatApp::getDb()->insertFromArray(self::DB_TBL, $data, false, array(), $data)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }
    
    /**
     * delete
     *
     * @param  int $recordId
     * @return bool
     */
    public function deleteRecords(int $recordId = 0) : bool
    {
        if (1 > $this->userId || 1 > $this->type) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->langId);
            return false;
        }

        $vals = [$this->userId, $this->type];
        $st = 'uc_user_id = ? and uc_type = ?';
        
        if (0 < $recordId) {
            $st .= ' and uc_record_id = ?';
            $vals =  array_merge($vals , [$recordId]) ;
        }
        
        return FatApp::getDb()->deleteRecords(self::DB_TBL, array('smt' => $st, 'vals' => $vals ));
    }
}
