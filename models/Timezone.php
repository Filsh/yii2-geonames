<?php

namespace filsh\geonames\models;

use Yii;
use yii\data\ActiveDataProvider;
use filsh\geonames\Module;

/**
 * This is the model class for table "{{%timezones}}".
 *
 * @property integer $id
 * @property string $country
 * @property string $timezone
 * @property string $offset_gmt
 * @property string $offset_dst
 * @property string $offset_raw
 * @property integer $order_popular
 * @property integer $create_time
 * @property integer $update_time
 *
 * @property Country $country
 */
class Timezone extends \yii\db\ActiveRecord
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_UPDATE_TRANSLATIONS = 'update-translations';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => ['create_time', 'update_time'],
                    self::EVENT_BEFORE_UPDATE => 'update_time',
                ],
            ],
            'translations' => [
                'class' => 'dosamigos\translateable\TranslateableBehavior',
                'relation' => 'timezoneTranslations',
                'translationAttributes' => ['title']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(
            parent::attributes(),
            ['translations']
        );
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%timezones}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['country', 'timezone', 'offset_gmt', 'offset_dst', 'offset_raw'], 'required'],
            [['offset_gmt', 'offset_dst', 'offset_raw'], 'number'],
            [['order_popular'], 'integer'],
            [['country'], 'string', 'max' => 2],
            [['timezone'], 'string', 'max' => 255],
            [['country', 'timezone'], 'unique', 'targetAttribute' => ['country', 'timezone'], 'message' => 'The combination of Country and Timezone has already been taken.'],
            [['translations'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_CREATE => ['country', 'timezone', 'offset_gmt', 'offset_dst', 'offset_raw', 'order_popular'],
            self::SCENARIO_UPDATE => ['country', 'timezone', 'offset_gmt', 'offset_dst', 'offset_raw', 'order_popular'],
            self::SCENARIO_UPDATE_TRANSLATIONS => ['translations'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Module::t('geonames', 'ID'),
            'country' => Module::t('geonames', 'Country'),
            'timezone' => Module::t('geonames', 'Timezone'),
            'offset_gmt' => Module::t('geonames', 'Offset Gmt'),
            'offset_dst' => Module::t('geonames', 'Offset Dst'),
            'offset_raw' => Module::t('geonames', 'Offset Raw'),
            'order_popular' => Module::t('geonames', 'Order Popular'),
            'create_time' => Module::t('geonames', 'Create Time'),
            'update_time' => Module::t('geonames', 'Update Time'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['iso' => 'country']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTimezoneTranslation()
    {
        return $this->hasOne(TimezoneTranslations::className(), ['timezone_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTimezoneTranslations()
    {
        return $this->hasMany(TimezoneTranslations::className(), ['timezone_id' => 'id']);
    }

    public function saveTranslations()
    {
        return $this->getDb()->transaction(function() {
            TimezoneTranslations::deleteAll(['timezone_id' => $this->id]);

            foreach($this->translations as $language => $translation) {
                $this->language = $language;
                $this->title = $translation;

                $this->saveTranslation();
            }
            return true;
        });
    }
}