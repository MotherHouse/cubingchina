<div class="col-lg-12 competition-wca">
  <p class="text-muted"><small><?php echo Yii::t('statistics', 'Generated on {time}.', array(
    '{time}'=>date('Y-m-d H:i:s', $time),
  )); ?></small></p>
  <?php $form = $this->beginWidget('CActiveForm', array(
    'htmlOptions'=>array(
      'role'=>'form',
      'class'=>'form-inline',
    ),
    'method'=>'get',
    'action'=>array(
      '/results/statistics',
      'name'=>'sum-of-ranks',
    ),
  )); ?>
    <div class="form-group row">
      <?php foreach (Events::getNormalTranslatedEvents() as $eventId=>$name): ?>
      <div class="col-lg-2 col-sm-3 col-xs-6">
        <div class="checkbox">
          <label>
            <?php echo CHtml::checkBox('event[]', in_array("$eventId", $eventIds), array(
              'value'=>$eventId,
            )); ?>
            <?php echo $name; ?>
          </label>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php foreach (Results::getRankingTypes() as $_type): ?>
    <?php echo CHtml::tag('button', array(
      'type'=>'submit',
      'name'=>'type',
      'value'=>$_type,
      'class'=>'btn btn-' . ($type == $_type ? 'warning' : 'theme'),
    ), Yii::t('common', ucfirst($_type))); ?>
    <?php endforeach; ?>
  <?php $this->endWidget(); ?>
  <?php
  $this->widget('RankGridView', array(
    'dataProvider'=>new NonSortArrayDataProvider($statistic['rows'], array(
      'pagination'=>array(
        'pageSize'=>Statistics::$limit,
        'pageVar'=>'page',
      ),
      'sliceData'=>false,
      'totalItemCount'=>$statistic['count'],
    )),
    'template'=>'{items}{pager}',
    'enableSorting'=>false,
    'front'=>true,
    'rankKey'=>$statistic['rankKey'],
    'rank'=>$statistic['rank'],
    'count'=>($page - 1) * 100,
    'columns'=>array_map(function($column) {
      $column['header'] = Yii::app()->evaluateExpression($column['header']);
      return $column;
    }, $statistic['columns']),
  )); ?>
</div>