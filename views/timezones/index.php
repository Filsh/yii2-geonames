<?php

use filsh\geonames\Module;
use filsh\geonames\models\Timezone;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\helpers\Url;
/**
 * @var View $this
 * @var ActiveDataProvider $dataProvider
 * @var Timezone $filterModel
 */

$this->title = Module::t('geonames', 'Timezones');
$this->params['breadcrumbs'][] = $this->title;

?>

<?php $this->beginContent('@filsh/geonames/views/layout.php') ?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel'  => $filterModel,
    'layout'       => "{items}\n{pager}",
    'columns'      => [
        'country',
        'timezone',
        [
            'attribute' => 'title',
            'format' => 'raw',
            'value' => function($model) {
                $titles = [];
                foreach(Yii::$app->controller->module->supportLanguages as $language) {
                    $model->language = $language;
                    if($model->title !== null) {
                        $titles[] = strtoupper($language) . ': ' . $model->title;
                    }
                }
                if(!empty($titles)) {
                    return implode($titles, '</br>');
                }
            }
        ],
        'offset_gmt',
        'offset_dst',
        'offset_raw',
        'order_popular',
        [
            'class'      => ActionColumn::className(),
            'template'   => '{update} {delete}',
            'urlCreator' => function ($action, $model) {
                return Url::to(['timezones/' . $action, 'id' => $model['id']]);
            },
            'options' => [
                'style' => 'width: 5%'
            ],
        ]
    ],
]) ?>

<?php $this->endContent() ?>