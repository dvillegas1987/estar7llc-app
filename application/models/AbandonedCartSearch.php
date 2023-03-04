<?php

class AbandonedCartSearch extends SearchBase
{
    public function __construct()
    {
        parent::__construct(AbandonedCart::DB_TBL, 'ch');
    }

    public function joinUsers($joinCredentials = false)
    {
        $this->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', AbandonedCart::DB_TBL_PREFIX . 'user_id = user.user_id', 'user');
        if ($joinCredentials == true) {
            $this->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'user.user_id = user_cred.credential_user_id', 'user_cred');
        }
    }
    
    public function joinSellerProducts($langId)
    {
        $this->joinTable(SellerProduct::DB_TBL, 'LEFT OUTER JOIN', AbandonedCart::DB_TBL_PREFIX . 'selprod_id = sp.selprod_id', 'sp');
        $langId = FatUtility::int($langId);
        if ($langId > 0) {
            $this->joinTable(SellerProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', 'sp_l.selprodlang_selprod_id = sp.selprod_id AND sp_l.selprodlang_lang_id = ' . $langId, 'sp_l');
        }
    }
    
    public function addActionCondition($action = 0)
    {
        if ($action > 0 && $action <= AbandonedCart::ACTION_PURCHASED) {
            $this->addCondition(AbandonedCart::DB_TBL_PREFIX . 'action', '=', $action);
        } else {
            $this->addCondition(AbandonedCart::DB_TBL_PREFIX . 'action', '!=', AbandonedCart::ACTION_PURCHASED);
        }
    }
    
    public function addSubQueryCondition()
    {
        $this->addDirectCondition(AbandonedCart::DB_TBL_PREFIX . 'id IN(select max(' . AbandonedCart::DB_TBL_PREFIX . 'id) from ' . AbandonedCart::DB_TBL . ' GROUP BY ' . AbandonedCart::DB_TBL_PREFIX . 'user_id, ' . AbandonedCart::DB_TBL_PREFIX . 'selprod_id)');
    }
    
}
