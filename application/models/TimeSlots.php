<?php

class TimeSlots extends MyAppModel
{
    public const DB_TBL = 'tbl_time_slots';
    public const DB_TBL_PREFIX = 'tslot_';
    
    public const TYPE_SHOP = 1;

    private $type = 0;
    
    /**
     * __construct
     *
     * @param  int $type
     * @param  int $tslotId
     * @return void
     */
    public function __construct(int $type, int $tslotId = 0)
    {
        $this->type = $type;
        parent::__construct(self::DB_TBL, self::DB_TBL_PREFIX . 'id', $tslotId);
    }
    
    /**
     * updateRecord
     *
     * @param  array $data
     * @return bool
     */
    public function updateRecord(array $data): bool
    {
        $data['tslot_type'] = $this->type;
        return FatApp::getDb()->insertFromArray(self::DB_TBL, $data, false, [], $data);
    }
    
    /**
     * getWeekDays
     *
     * @param  int $langId
     * @return array
     */
    public static function getWeekDays(int $langId): array
    {
        return [
            0 => Labels::getLabel('LBL_SUNDAY', $langId),
            1 => Labels::getLabel('LBL_MONDAY', $langId),
            2 => Labels::getLabel('LBL_TUESDAY', $langId),
            3 => Labels::getLabel('LBL_WEDNESDAY', $langId),
            4 => Labels::getLabel('LBL_THIRSTDAY', $langId),
            5 => Labels::getLabel('LBL_FRIDAY', $langId),
            6 => Labels::getLabel('LBL_SATRDAY', $langId),
        ];
    }
}
