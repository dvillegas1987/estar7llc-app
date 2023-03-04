<?php
class TaxRuleLocation extends MyAppModel
{
    const DB_TBL = 'tbl_tax_rule_locations';
    const DB_TBL_PREFIX = 'taxruleloc_';

    public function __construct(int $id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    /**
    * getSearchObject
    *
    * @return object
    */
    public static function getSearchObject(): object
    {
        $srch = new SearchBase(static::DB_TBL, 'taxRuleLoc');
        return $srch;
    }

    /**
    * updateLocations
    *
    * @param  array $data
    * @return bool
    */
    public function updateLocations(array $data): bool
    {
        if (0 >= FatUtility::int($data['taxruleloc_taxcat_id']) || 0 >= FatUtility::int($data['taxruleloc_taxrule_id'])) {
            return false;
        }
        if (!FatApp::getDb()->insertFromArray(self::DB_TBL, $data, true, array(), $data)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    /**
    * deleteLocations
    *
    * @param  int $taxCatId
    * @return bool
    */
    public function deleteLocations(int $taxCatId): bool
    {
        if(!FatApp::getDb()->deleteRecords(
            self::DB_TBL,
            array(
                'smt'=> self::DB_TBL_PREFIX .'taxcat_id=? ',
                'vals'=>array($taxCatId)
            )
        )) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }
}
