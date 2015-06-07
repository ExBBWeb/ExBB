<?php
namespace Extension\Module\Helpers;

/**
* Timezones Helper
* @url http://jeka.by/post/1034/timezones-list/
*/

class TimeZones {
    public function getTimeZoneSelect($selectedZone = NULL) {
        $regions = array(
			'Europe' => \DateTimeZone::EUROPE,
			'Aisa' => \DateTimeZone::ASIA,
            'Africa' => \DateTimeZone::AFRICA,
            'America' => \DateTimeZone::AMERICA,
            'Antarctica' => \DateTimeZone::ANTARCTICA,
            'Atlantic' => \DateTimeZone::ATLANTIC,
            'Indian' => \DateTimeZone::INDIAN,
            'Pacific' => \DateTimeZone::PACIFIC
        );
 
		$structure = '';
 
        foreach ($regions as $mask) {
            $zones = \DateTimeZone::listIdentifiers($mask);
            $zones = $this->prepareZones($zones);
 
            foreach ($zones as $zone) {
                $continent = $zone['continent'];
                $city = $zone['city'];
				$subcity = $zone['subcity'];
                $p = $zone['p'];
                $timeZone = $zone['time_zone'];
 
                if (!isset($selectContinent)) {
                    $structure .= '<optgroup label="'.$continent.'">';
                }
                elseif ($selectContinent != $continent) {
                    $structure .= '</optgroup><optgroup label="'.$continent.'">';
                }
 
                if ($city) {
                    if ($subcity) {
                        $city = $city . '/'. $subcity;
                    }
 
                    $structure .= "<option ".(($timeZone == $selectedZone) ? 'selected="selected "':'') . " value=\"".($timeZone)."\">(".$p. " UTC) " .str_replace('_',' ',$city)."</option>";
                }
 
                $selectContinent = $continent;
            }
        }
 
        $structure .= '</optgroup>';
 
        return $structure;
    }
 
    private function prepareZones(array $timeZones) {
        $list = array();
        foreach ($timeZones as $zone) {
            $time = new \DateTime(NULL, new \DateTimeZone($zone));
            $p = $time->format('P');
            if ($p > 13) {
                continue;
            }
            $parts = explode('/', $zone);
 
            $list[$time->format('P')][] = array(
                'time_zone' => $zone,
                'continent' => isset($parts[0]) ? $parts[0] : '',
                'city' => isset($parts[1]) ? $parts[1] : '',
                'subcity' => isset($parts[2]) ? $parts[2] : '',
                'p' => $p,
            );
        }
 
        ksort($list, SORT_NUMERIC);
 
        $zones = array();
        foreach ($list as $grouped) {
            $zones = array_merge($zones, $grouped);
        }
 
        return $zones;
    }
}
?>