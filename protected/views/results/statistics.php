<div class="col-lg-12">
  <div class="row">
    <?php foreach ($statistics as $name=>$statistic): ?>
    <div class="<?php echo $statistic['class']; ?>">
    <?php if (isset($statistic['columns'])): ?>
      <h3><?php echo Yii::t('statistics', $name); ?></h3>
      <?php $this->renderPartial('statistic', array(
        'statistic'=>$statistic,
        'id'=>$statistic['id'],
        'class'=>' ' . $statistic['id'],
      )); ?>
    <?php else: ?>
      <h3 class="pull-left"><?php echo Yii::t('statistics', $name); ?></h3>
      <div class="pull-left stat-select">
        <?php echo CHtml::dropdownList($statistic['id'], '', $statistic['select']); ?>
      </div>
      <div class="clearfix"></div>
      <?php $keys = array_keys($statistic['statistic']); ?>
      <?php foreach ($statistic['statistic'] as $key=>$stat): ?>
      <?php $this->renderPartial('statistic', array(
        'statistic'=>$stat,
        'id'=>$statistic['id'] . '_' . $key,
        'class'=>($key === $keys[0] ? ' ' : ' hide ') . $statistic['id'],
      )); ?>
      <?php endforeach; ?>
    <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php
Yii::app()->clientScript->registerScript('statistics',
<<<EOT
  $('select').on('change', function() {
    var that = $(this),
      value = that.val(),
      name = that.attr('name');
      console.log(value, name)
    $('.' + name).addClass('hide').filter('#' + name + '_' + value).removeClass('hide');
  }).trigger('change');
EOT
);
