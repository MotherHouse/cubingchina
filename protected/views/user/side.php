<aside class="page-sidebar col-md-2 col-sm-3 affix-top">
  <section class="widget hidden-xs">
    <?php $this->widget('zii.widgets.CMenu', array(
      'htmlOptions'=>array(
        'class'=>'nav',
      ),
      'items'=>array(
        array(
          'url'=>array('/user/profile'),
          'label'=>Yii::t('common', 'Profile'),
          'active'=>in_array($this->action->id, array('profile', 'edit')),
        ),
        array(
          'url'=>array('/user/preferredEvents'),
          'label'=>Yii::t('common', 'Preferred Events'),
        ),
        // array(
        //   'url'=>array('/user/password'),
        //   'label'=>Yii::t('common', 'Change Password'),
        // ),
        array(
          'url'=>array('/user/competitions'),
          'label'=>Yii::t('common', 'My Registration'),
        ),
        array(
          'url'=>array('/user/competitionHistory'),
          'label'=>Yii::t('common', 'Competition History'),
          'visible'=>$this->user->wcaid != '',
        ),
        array(
          'url'=>array('/user/cert'),
          'label'=>Yii::t('common', 'My Certificates'),
          'visible'=>$this->user->hasCerts,
        ),
      ),
    )); ?>
  </section><!--//widget-->
  <?php $this->widget('zii.widgets.CMenu', array(
    'htmlOptions'=>array(
      'class'=>'nav nav-tabs visible-xs',
    ),
    'items'=>array(
      array(
        'url'=>array('/user/profile'),
        'label'=>Yii::t('common', 'Profile'),
        'active'=>in_array($this->action->id, array('profile', 'edit')),
      ),
      array(
        'url'=>array('/user/preferredEvents'),
        'label'=>Yii::t('common', 'Preferred Events'),
      ),
      // array(
      //   'url'=>array('/user/password'),
      //   'label'=>Yii::t('common', 'Change Password'),
      // ),
      array(
        'url'=>array('/user/competitions'),
        'label'=>Yii::t('common', 'My Registration'),
      ),
      array(
        'url'=>array('/user/competitionHistory'),
        'label'=>Yii::t('common', 'Competition History'),
        'visible'=>$this->user->wcaid != '',
      ),
      array(
        'url'=>array('/user/cert'),
        'label'=>Yii::t('common', 'My Certificates'),
        'visible'=>$this->user->hasCerts,
      ),
    ),
  )); ?>
</aside>
