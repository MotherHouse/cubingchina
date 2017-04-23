<?php

class PosCollection extends Statistics {

	public static function build($statistic, $page = 1, $recursive = true) {
		if ($statistic['type'] === 'each') {
			$collections = [];
			$events = Events::getNormalEvents();
			$temp = $statistic;
			$temp['type'] = 'single';
			foreach ($events as $eventId=>$name) {
				$temp['eventIds'] = ["$eventId"];
				$collections[$eventId] = self::build($temp);
			}
			return self::makeStatisticsData($statistic, array(
				'statistic'=>$collections,
				'select'=>$events,
				'selectHandler'=>'Yii::t("event", "$name")',
				'selectKey'=>'event',
			));
		}
		$command = Yii::app()->wcaDb->createCommand();
		$command->select([
				'personId', 'personName',
				'SUM(CASE WHEN pos=:pos AND best>0 THEN 1 ELSE 0 END) AS count',
				'COUNT(roundTypeId) AS rounds',
				'SUM(CASE WHEN pos=:pos AND best>0 THEN 1 ELSE 0 END) / COUNT(roundTypeId) AS frequency',
			])
			->from('Results rs')
			->leftJoin('Countries country', 'country.id=rs.personCountryId')
			->where('', [':pos'=>$statistic['pos']]);
		if (!empty($statistic['eventIds'])) {
			$command->andWhere(['in', 'eventId', $statistic['eventIds']]);
		}
		if (!empty($statistic['round'])) {
			$command->andWhere(['in', 'roundTypeId', $statistic['round']]);
		}
		ActiveRecord::applyRegionCondition($command, $statistic['region'] ?? 'China');
		$cmd = clone $command;
		$command->group('personId')
		->having('rounds >= 10 AND count > 0')
		->order('frequency DESC, count DESC, personName ASC')
		->limit(self::$limit)
		->offset(($page - 1) * self::$limit);
		$columns = [
			[
				'header'=>'Yii::t("statistics", "Person")',
				'value'=>'Persons::getLinkByNameNId($data["personName"], $data["personId"])',
				'type'=>'raw',
			],
			[
				'header'=>'Yii::t("statistics", "Frequency")',
				'value'=>'number_format($data["frequency"] * 100, 2) . "% ({$data[\'count\']}/{$data[\'rounds\']})"',
			],
		];
		$rows = $command->queryAll();
		$statistic['count'] = $cmd->select('count(DISTINCT personId) AS count')->andWhere('pos=:pos')->queryScalar();
		$statistic['rank'] = ($page - 1) * self::$limit;
		$statistic['rankKey'] = 'frequency';
		if ($page > 1 && $rows !== array() && $recursive) {
			$stat = self::build($statistic, $page - 1, false);
			foreach (array_reverse($stat['rows']) as $row) {
				if ($row['frequency'] === $rows[0]['frequency']) {
					$statistic['rank']--;
				} else {
					break;
				}
			}
		}
		return self::makeStatisticsData($statistic, $columns, $rows);
	}
}
