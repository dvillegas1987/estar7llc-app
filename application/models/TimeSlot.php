<?php

class TimeSlot extends MyAppModel
{
    public const DB_TBL = 'tbl_time_slots';
    public const DB_TBL_PREFIX = 'tslot_';

    public const DAY_INDIVIDUAL_DAYS = 1;
    public const DAY_ALL_DAYS = 2;

    public const DAY_MONDAY = 1;
    public const DAY_TUESDAY = 2;
    public const DAY_WEDNESDAY = 3;
    public const DAY_THURSDAY = 4;
    public const DAY_FRIDAY = 5;
    public const DAY_SATURDAY = 6;
    public const DAY_SUNDAY = 0;



    /**
     * __contruct
     *
     * @param  int $timeSlotId
     * @return void
     */
    public function __construct(int $timeSlotId = 0)
    {
        parent::__construct(self::DB_TBL, self::DB_TBL_PREFIX . 'id', $timeSlotId);
    }

    public static function getSlotTypeArr(int $langId): array
    {
        return [
            self::DAY_INDIVIDUAL_DAYS => Labels::getLabel('LBL_Individual_Days', $langId),
            self::DAY_ALL_DAYS => Labels::getLabel('LBL_All_Days', $langId)
        ];
    }

    public static function getDaysArr(int $langId): array
    {
        return [
            self::DAY_MONDAY => Labels::getLabel('LBL_MONDAY', $langId),
            self::DAY_TUESDAY => Labels::getLabel('LBL_TUESDAY', $langId),
            self::DAY_WEDNESDAY => Labels::getLabel('LBL_WEDNESDAY', $langId),
            self::DAY_THURSDAY => Labels::getLabel('LBL_THURSDAY', $langId),
            self::DAY_FRIDAY => Labels::getLabel('LBL_FRIDAY', $langId),
            self::DAY_SATURDAY => Labels::getLabel('LBL_SATURDAY', $langId),
            self::DAY_SUNDAY => Labels::getLabel('LBL_SUNDAY', $langId),
        ];
    }

    public static function getTimeSlotsArr(): array
    {
        $timeSlots = [];
        $startTime          = "00:00";
        $endTime            = "24:00";
        $frequency           = 30;
        for ($i = strtotime($startTime); $i <= strtotime($endTime); $i = $i + $frequency * 60) {
            $timeSlots[date("H:i", $i)] = date("H:i", $i);
        }
        return $timeSlots;
    }


    public function timeSlotsByAddrId(int $addressId): array
    {
        $addressId = FatUtility::int($addressId);
        $srch = new SearchBase(static::DB_TBL, 'ts');
        $srch->addCondition(self::tblFld('record_id'), '=', $addressId);
        $srch->addOrder(self::tblFld('day'), 'ASC');
        $srch->addOrder(self::tblFld('from_time'), 'ASC');       
        $rs = $srch->getResultSet();
        return  FatApp::getDb()->fetchAll($rs);
    }

    public function timeSlotsByAddrIdAndDay($addressId, $day)
    {
        $addressId = FatUtility::int($addressId);
        $srch = new SearchBase(static::DB_TBL, 'ts');
        $srch->addCondition(self::tblFld('record_id'), '=', $addressId);
        $srch->addCondition(self::tblFld('day'), '=', $day);
        $rs = $srch->getResultSet();
        return  FatApp::getDb()->fetchAll($rs);
    }
}
