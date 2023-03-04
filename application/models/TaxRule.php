<?php
class TaxRule extends MyAppModel
{
    const DB_TBL = 'tbl_tax_rules';
    const DB_TBL_PREFIX = 'taxrule_';

    const TYPE_ALL_STATES = -1;
    const TYPE_INCLUDE_STATES = 1;
    const TYPE_EXCLUDE_STATES = 2;

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
        $srch = new SearchBase(static::DB_TBL, 'taxRule');
        return $srch;
    }

    /**
    * getTypeOptions
    *
    * @param  int $langId
    * @return array
    */
    public static function getTypeOptions(int $langId): array
    {
        return array(
            self::TYPE_ALL_STATES => Labels::getLabel('LBL_ALL_STATES', $langId),
            self::TYPE_INCLUDE_STATES => Labels::getLabel('LBL_INCLUDE_STATES', $langId),
            self::TYPE_EXCLUDE_STATES => Labels::getLabel('LBL_EXCLUDE_STATES', $langId),
        );
    }

    /**
    * deleteRules
    *
    * @param  int $taxCatId
    * @return bool
    */
    public function deleteRules(int $taxCatId): bool
    {
        if(!FatApp::getDb()->query('DELETE rules, ruleDetails FROM '. self::DB_TBL .' rules LEFT JOIN '. TaxRuleCombined::DB_TBL .' ruleDetails ON ruleDetails.taxruledet_taxrule_id  = rules.taxrule_id WHERE rules.taxrule_taxcat_id = '. $taxCatId)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    /**
    * getRuleForm
    *
    * @param  int $langId
    * @return object
    */
    public static function getRuleForm(int $langId): object
    {
        
        $frm = new Form('frmTaxRule');
        $frm->addHiddenField('', 'taxcat_id', 0);

        /* [ TAX CATEGORY RULE FORM */
        $frm->addHiddenField('', 'taxrule_id[]', 0);
        /*$frm->addRequiredField(Labels::getLabel('LBL_Rule_Name', $langId), 'taxrule_name', '');*/
        $fld = $frm->addFloatField(Labels::getLabel('LBL_Tax_Rate(%)', $langId), 'taxrule_rate[]', '');
        $fld->requirements()->setPositive();

        // $fld = $frm->addCheckBox(Labels::getLabel('LBL_Combined_Tax', $langId), 'taxrule_is_combined[]', 1);
		
		$taxStructures = TaxStructure::getAllAssoc($langId);
        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Select_Tax', $langId), 'taxrule_taxstr_id[]', $taxStructures, '', array(), Labels::getLabel('LBL_Select_Tax', $langId));
        $fld->requirements()->setRequired();
        /* ] */

        /* [ TAX CATEGORY RULE LOCATIONS FORM */
        $countryObj = new Countries();
        $countriesOptions = $countryObj->getCountriesArr($langId, true);
        $countriesOptions = array(-1 => Labels::getLabel('LBL_Rest_of_the_world', $langId)) + $countriesOptions;
        array_walk($countriesOptions, function (&$v) {
            $v = str_replace("'", "\'", trim($v));
        });
        $locattionTypeOtions = static::getTypeOptions($langId);

        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Country', $langId), 'taxruleloc_country_id[]', $countriesOptions, '', array(), Labels::getLabel('LBL_Select_Country', $langId));
        $fld->requirements()->setRequired();
        $fld = $frm->addSelectBox(Labels::getLabel('LBL_States_Type', $langId), 'taxruleloc_type[]', $locattionTypeOtions, '', array(), Labels::getLabel('LBL_Select', $langId));
        $fld->requirements()->setRequired();
        $fld = $frm->addSelectBox(Labels::getLabel('LBL_States', $langId), 'taxruleloc_state_id[]', array(), '', array(), '');
        $fld->requirements()->setRequired();
        /* ] */

        /* [ TAX GROUP RULE COMBINED DETAILS FORM */
        $frm->addHiddenField('', 'taxruledet_id[]');
        /* $frm->addTextBox(Labels::getLabel('LBL_Name', $langId), 'taxruledet_name[]', ''); */
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Tax_Rate(%)', $langId), 'taxruledet_rate[]', '');
        //$fld->requirements()->required();
        /* ] */

        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $languages = Language::getAllNames();
        foreach ($languages as $languageId => $lang) {
            if ($languageId == $siteDefaultLangId) {
                $frm->addRequiredField(Labels::getLabel('LBL_Rule_Name', $languageId), 'taxrule_name[' . $languageId . '][]');
            } else {
                $frm->addTextBox(Labels::getLabel('LBL_Rule_Name', $languageId), 'taxrule_name[' . $languageId . '][]');
            }
            /*if ($languageId == $siteDefaultLangId) {
                $frm->addRequiredField(Labels::getLabel('LBL_Tax_Name', $languageId), 'taxruledet_name[' . $languageId . '][]');
            } else {*/
            $frm->addTextBox(Labels::getLabel('LBL_Tax_Name', $languageId), 'taxruledet_name[' . $languageId . '][]');
            /*}*/
        }

        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
        unset($languages[$siteDefaultLangId]);
        if (!empty($translatorSubscriptionKey) && count($languages) > 0) {
            $frm->addCheckBox(Labels::getLabel('LBL_Translate_To_Other_Languages', $langId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }

        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save', $langId));
        return $frm;
    }

    /**
    * getRules
    *
    * @param  int $taxCatId
    * @param  int $langId
    * @return array
    */
    public function getRules(int $taxCatId, int $langId): array
    {
        $srch = TaxRule::getSearchObject();
        $srch->joinTable(TaxStructure::DB_TBL, 'LEFT JOIN', 'taxstr_id = taxrule_taxstr_id');
        $srch->joinTable(TaxStructure::DB_TBL_LANG, 'LEFT JOIN', 'taxrule_taxstr_id = taxstrlang_taxstr_id and taxstrlang_lang_id = '.$langId);
        $srch->addCondition('taxrule_taxcat_id', '=', $taxCatId);
        $srch->addMultipleFields(array('taxrule_id', 'taxrule_name', 'taxrule_taxcat_id', 'taxrule_taxstr_id', 'taxrule_rate', 'taxstr_id', 'IFNULL(taxstr_name, taxstr_identifier) as taxstr_name', 'taxstr_parent', 'taxstr_is_combined'));
        $res = $srch->getResultSet();
        $rulesData = FatApp::getDb()->fetchAll($res);
        return $rulesData;
    }

    /**
    * getCombinedRuleDetails
    *
    * @param  array $rulesIds
    * @param int $langId
    * @return array
    */
    public function getCombinedRuleDetails(array $rulesIds, int $langId): array
    {
        if (empty($rulesIds)) {
            return [];
        }
        $srch = TaxRuleCombined::getSearchObject();
        $srch->joinTable(TaxStructure::DB_TBL, 'LEFT JOIN', 'taxruledet_taxstr_id = taxstr_id');
        $srch->joinTable(TaxStructure::DB_TBL_LANG, 'LEFT JOIN', 'taxruledet_taxstr_id = taxstrlang_taxstr_id and taxstrlang_lang_id = '.$langId);
        $srch->addCondition('taxruledet_taxrule_id', 'IN', $rulesIds);
        $srch->addMultipleFields(array('taxstr_id', 'taxruledet_taxrule_id', 'taxruledet_rate', 'IFNULL(taxstr_name, taxstr_identifier) as taxstr_name', 'taxstr_parent'));
        $srch->doNotCalculateRecords();
        $rs = $srch->getResultSet();
        $combinedData = FatApp::getDb()->fetchAll($rs);
        return self::groupDataByKey($combinedData, 'taxruledet_taxrule_id');
    }

    /**
    * getLocations
    *
    * @param  int $taxCatId
    * @return array
    */
    public function getLocations(int $taxCatId, bool $joinCountryState = false, $langId = 0): array
    {
        $srch = TaxRuleLocation::getSearchObject();
        $srch->addCondition('taxruleloc_taxcat_id', '=', $taxCatId);
        if ($joinCountryState) {
            $srch->joinTable(States::DB_TBL, 'LEFT OUTER JOIN', 'taxruleloc_state_id = s.state_id', 's');
            $srch->joinTable(States::DB_TBL_LANG, 'LEFT OUTER JOIN', 's.state_id = s_l.statelang_state_id AND statelang_lang_id = ' . $langId, 's_l');

            $srch->joinTable(Countries::DB_TBL, 'LEFT OUTER JOIN', 'taxruleloc_country_id = c.country_id', 'c');
            $srch->joinTable(Countries::DB_TBL_LANG, 'LEFT OUTER JOIN', 'c.country_id = c_l.countrylang_country_id AND countrylang_lang_id = ' . $langId, 'c_l');

            $srch->addMultipleFields(array('taxruleloc_taxcat_id', 'taxruleloc_taxrule_id', 'taxruleloc_country_id', 'taxruleloc_state_id', 'taxruleloc_type', 'taxruleloc_unique', 'IFNULL(country_name, country_code) as country_name', 'IFNULL(state_name, state_identifier) as state_name'));
        }
        $res = $srch->getResultSet();
        $locationsData = FatApp::getDb()->fetchAll($res);
        return self::groupDataByKey($locationsData, 'taxruleloc_taxrule_id');
    }

    /**
    * groupDataByKey
    *
    * @param  array $data
    * @param  string $key
    * @return array
    */
    public static function groupDataByKey(array $data, string $key): array
    {
        $groupedData = [];
        if (!empty($data)) {
            foreach ($data as $val) {
                $groupedData[$val[$key]][] = $val;
            }
        }
        return $groupedData;
    }
}
