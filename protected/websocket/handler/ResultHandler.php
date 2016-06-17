<?php

class ResultHandler extends MsgHandler {

	public function process() {
		$action = $this->getAction();
		if ($action !== '') {
			if (strtolower($action) != 'fetch' && !$this->checkAccess()) {
				return;
			}
			$method = 'action' . ucfirst($action);
			if (method_exists($this, $method)) {
				return $this->$method();
			}
		}
	}

	public function actionFetch() {
		$round = LiveEventRound::model()->findByAttributes(array(
			'competition_id'=>$this->competition->id,
			'event'=>"{$this->msg->params->event}",
			'round'=>"{$this->msg->params->round}",
		));
		$results = LiveResult::model()->findAllByAttributes(array(
			'competition_id'=>$this->competition->id,
			'event'=>"{$this->msg->params->event}",
			'round'=>"{$this->msg->params->round}",
		));
		if ($round !== null && $round->isClosed) {
			$results = array_filter($results, function($result) use ($round) {
				if ($result->best == 0) {
					return false;
				}
				return true;
			});
		}
		switch ($this->msg->params->filter) {
			case 'females':
				$results = array_filter($results, function($result) {
					return $result->user->gender == User::GENDER_FEMALE;
				});
				break;
			case 'children':
				$birthday = $this->competition->date - (365 * 12 + 3) * 86400;
				$results = array_filter($results, function($result) use($birthday) {
					return $result->user->birthday >= $birthday;
				});
				break;
		}
		$this->success('result.all', array_map(function($result) {
			return $result->getShowAttributes();
		}, array_values($results)));
	}

	public function actionUpdate() {
		$data = $this->msg->result;
		$result = LiveResult::model()->findByPk($data->id);
		if ($result == null) {
			return;
		}
		$result->value1 = $data->value1;
		$result->value2 = $data->value2;
		$result->value3 = $data->value3;
		$result->value4 = $data->value4;
		$result->value5 = $data->value5;
		$result->best = $data->best;
		$result->average = $data->average;
		$result->calculateRecord('single');
		$result->calculateRecord('average');
		if ($result->best == 0) {
			$result->create_time = 0;
			$result->update_time = 0;
		} else {
			if ($result->create_time == 0) {
				$result->create_time = time();
			}
			$result->update_time = time();
		}
		$result->operator_id = $this->user->id;
		$result->save();
		foreach ($result->getBeatedRecords('single') as $res) {
			$this->broadcastSuccess('result.update', $res->getShowAttributes());
		}
		foreach ($result->getBeatedRecords('average') as $res) {
			$this->broadcastSuccess('result.update', $res->getShowAttributes());
		}
		$this->broadcastSuccess('result.update', $result->getShowAttributes());
		$eventRound = $result->eventRound;
		if ($eventRound->status == LiveEventRound::STATUS_OPEN) {
			$eventRound->status = LiveEventRound::STATUS_LIVE;
			$eventRound->save();
			$this->broadcastSuccess('round.update', $eventRound->getBroadcastAttributes());
		}
	}

	public function actionUser() {
		$results = LiveResult::model()->findAllByAttributes(array(
			'competition_id'=>$this->competition->id,
			'user_type'=>$this->msg->user->type,
			'user_id'=>$this->msg->user->id,
		));
		usort($results, function($resA, $resB) {
			$temp = $resA->wcaEvent->rank - $resB->wcaEvent->rank;
			if ($temp == 0) {
				$temp = $resB->wcaRound->rank - $resA->wcaRound->rank;
			}
			return $temp;
		});
		$temp = array();
		foreach ($results as $result) {
			if ($result->best == 0) {
				continue;
			}
			if (!isset($temp[$result->event])) {
				$temp[$result->event] = array(
					'event'=>$result->event,
					'results'=>array(),
				);
			}
			$temp[$result->event]['results'][] = $result->getShowAttributes(true);
		}
		$this->success('result.user', array_values($temp));
	}

	public function actionAttribute() {

	}

	public function actionRound() {
		$round = LiveEventRound::model()->findByAttributes(array(
			'competition_id'=>$this->competition->id,
			'event'=>"{$this->msg->round->event}",
			'round'=>"{$this->msg->round->id}",
		));
		if ($round != null) {
			foreach (array('number', 'cut_off', 'time_limit', 'status') as $attribute) {
				if (isset($this->msg->round->$attribute)) {
					$round->$attribute = $this->msg->round->$attribute;
				}
			}
			$round->save();
			$this->broadcastSuccess('round.update', $round->getBroadcastAttributes());
		}
	}

	public function actionReset() {
		$round = LiveEventRound::model()->findByAttributes(array(
			'competition_id'=>$this->competition->id,
			'event'=>"{$this->msg->round->event}",
			'round'=>"{$this->msg->round->id}",
		));
		if ($round != null) {
			$round->removeResults();
			$competition = $this->competition;
			$results = array();
			if ($round->round == '1' || $round->round == 'd') {
				//empty results of first rounds
				$registrations = Registration::getRegistrations($competition);
				foreach ($registrations as $registration) {
					foreach ($registration->events as $event) {
						$model = new LiveResult();
						$model->competition_id = $competition->id;
						$model->user_id = $registration->user_id;
						$model->number = $registration->number;
						$model->event = $round->event;
						$model->round = $round->round;
						$model->format = $round->format;
						$model->save();
						$results[] = $model;
					}
				}
			} else {
				if (($lastRound = $round->lastRound) !== null) {
					foreach (array_slice($lastRound->results, 0, $round->number) as $result) {
						$model = new LiveResult();
						$model->competition_id = $competition->id;
						$model->user_id = $result->user_id;
						$model->number = $result->number;
						$model->event = $round->event;
						$model->round = $round->round;
						$model->format = $round->format;
						$model->save();
						$results[] = $model;
					}
				}
			}
			$this->success('result.all', array_map(function($result) {
				return $result->getShowAttributes();
			}, $results));
		}
	}

	public function actionEvent() {

	}

	public function actionPerson() {

	}
}
