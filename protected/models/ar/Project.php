<?php
declare(strict_types=1);

namespace prime\models\ar;

use app\queries\ProjectQuery;
use app\queries\ResponseQuery;
use prime\components\LimesurveyDataProvider;
use prime\interfaces\FacilityListInterface;
use prime\interfaces\HeramsResponseInterface;
use prime\lists\SurveyFacilityList;
use prime\models\ActiveRecord;
use prime\models\forms\ResponseFilter;
use prime\models\permissions\Permission;
use prime\objects\HeramsCodeMap;
use prime\objects\HeramsResponse;
use prime\objects\HeramsSubject;
use prime\traits\LoadOneAuthTrait;
use SamIT\LimeSurvey\Interfaces\SurveyInterface;
use yii\base\NotSupportedException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\validators\BooleanValidator;
use yii\validators\DefaultValueValidator;
use yii\validators\NumberValidator;
use yii\validators\RangeValidator;
use yii\validators\RequiredValidator;
use yii\validators\SafeValidator;
use yii\validators\StringValidator;
use yii\validators\UniqueValidator;
use function iter\filter;

/**
 * Class Tool
 * @property int $id
 * @property int $base_survey_eid
 * @property string $title
 * @method static ProjectQuery find()
 * @property Page[] $pages
 * @property int $status
 * @property Workspace[] $workspaces
 * @property-read SurveyInterface $survey
 */
class Project extends ActiveRecord {
    use LoadOneAuthTrait;
    public const STATUS_ONGOING = 0;
    public const STATUS_BASELINE = 1;
    public const STATUS_TARGET = 2;
    public const STATUS_EMERGENCY_SPECIFIC = 3;

    const PROGRESS_ABSOLUTE = 'absolute';
    const PROGRESS_PERCENTAGE = 'percentage';

    public function statusText(): string
    {
        return $this->statusOptions()[$this->status];
    }
    public function init()
    {
        parent::init();
        $this->typemap = [
            'A1' => 'Primary',
            'A2' => 'Primary',
            'A3' => 'Secondary',
            'A4' => 'Secondary',
            'A5' => 'Tertiary',
            'A6' => 'Tertiary',
            "" => 'Other',
        ];

        $this->overrides = [
            'facilityCount' => null,
            'typeCounts' => null,
            'contributorCount' => null
        ];
        $this->status = self::STATUS_ONGOING;
    }

    public function statusOptions()
    {
        return [
            self::STATUS_ONGOING => 'Ongoing',
            self::STATUS_BASELINE => 'Baseline',
            self::STATUS_TARGET => 'Target',
            self::STATUS_EMERGENCY_SPECIFIC => 'Emergency specific'
        ];
    }

    public function attributeLabels()
    {
        return [
            'base_survey_eid' => \Yii::t('app', 'Survey')
        ];
    }

    public function attributeHints()
    {
        return [
            'name_code' => \Yii::t('app', 'Question code containing the name (case sensitive)'),
            'type_code' => \Yii::t('app', 'Question code containing the type (case sensitive)'),
            'typemap' => \Yii::t('app', 'Map facility types for use in the world map'),
            'status' => \Yii::t('app','Project status is shown on the world map')
        ];
    }

    /**
     * @return LimesurveyDataProvider
     */
    protected function limesurveyDataProvider() {
        return app()->limesurveyDataProvider;
    }

    /**
     * @return \SamIT\LimeSurvey\Interfaces\SurveyInterface
     */
    public function getSurvey(): SurveyInterface
    {
        return $this->limesurveyDataProvider()->getSurvey($this->base_survey_eid);
    }

    public function dataSurveyOptions()
    {
        $existing = Project::find()->select('base_survey_eid')->indexBy('base_survey_eid')->column();

        $surveys = filter(function($details) use ($existing) {
            return $this->base_survey_eid == $details['sid'] || !isset($existing[$details['sid']]);
        }, $this->limesurveyDataProvider()->listSurveys());

        $result = ArrayHelper::map($surveys, 'sid', function ($details) use ($existing) {
                return $details['surveyls_title'] . (($details['active'] == 'N') ? " (INACTIVE)" : "");
        });

        return $result;
    }

    public function canBeDeleted(): bool
    {
        return $this->getWorkspaceCount() === 0;
    }

    public function beforeDelete()
    {
        return $this->canBeDeleted();
    }

    public function getWorkspaces()
    {
        return $this->hasMany(Workspace::class, ['tool_id' => 'id'])->inverseOf('project');
    }
    public function getWorkspaceCount(): int
    {
        return $this->isRelationPopulated('workspaces') ? count($this->workspaces) : (int)$this->getWorkspaces()->count();
    }

    public function getTypemapAsJson()
    {
        return Json::encode($this->typemap, JSON_PRETTY_PRINT);
    }

    public function getOverridesAsJson()
    {
        return Json::encode($this->overrides, JSON_PRETTY_PRINT);
    }

    public function setTypemapAsJson($value)
    {
        $this->typemap = Json::decode($value);
    }

    public function setOverridesAsJson($value)
    {
        $this->overrides= Json::decode($value);
    }


    public function rules()
    {
        return [
            [['overrides'], DefaultValueValidator::class, 'value' => [
                'facilityCount' => null,
                'typeCounts' => null,
                'contributorCount' => null
            ]],
            [[
                'title', 'base_survey_eid'
            ], RequiredValidator::class],
            [['title'], StringValidator::class],
            [['title'], UniqueValidator::class],
            [['base_survey_eid'], RangeValidator::class, 'range' => array_keys($this->dataSurveyOptions())],
            [['hidden'], BooleanValidator::class],
            [['latitude', 'longitude'], NumberValidator::class, 'integerOnly' => false],
            [['typemapAsJson', 'overridesAsJson'], SafeValidator::class],
            [['status'], RangeValidator::class, 'range' => array_keys($this->statusOptions())]
        ];
    }

    public function  getResponses(): ResponseQuery
    {
        return $this->hasMany(Response::class, ['workspace_id' => 'id'])->via('workspaces');
    }


    public function getTypeCounts()
    {
        if (null !== $result = $this->getOverride('typeCounts')) {
            return $result;
        }
        \Yii::beginProfile(__FUNCTION__);
        $map = is_array($this->typemap) ? $this->typemap : [];
        // Always have a mapping for the empty / unknown value.
        if (!empty($map) && !isset($map[HeramsResponseInterface::UNKNOWN_VALUE])) {
            $map[HeramsResponseInterface::UNKNOWN_VALUE] = "Unknown";
        }
        // Initialize counts
        $counts = [];
        foreach($map as $key => $value) {
            $counts[$value] = 0;
        }

        $query = $this->getResponses()
            ->groupBy([
                "json_unquote(json_extract([[data]], '$.{$this->getMap()->getType()}'))"
            ])
            ->select([
                'count' => 'count(*)',
                'type' => "json_unquote(json_extract([[data]], '$.{$this->getMap()->getType()}'))",
            ])
             ->indexBy('type')
            ->asArray();

        foreach($query->column() as $type => $count) {
            if (empty($map)) {
                $counts[$type] = ($counts[$type] ?? 0) + 1;
            } elseif (isset($map[$type])) {
                $counts[$map[$type]]++;
            } else {
                $counts[$map[HeramsResponseInterface::UNKNOWN_VALUE]]++;
            }
        }

        \Yii::endProfile(__FUNCTION__);
        return $counts;
    }

    public function getFunctionalityCounts(): array
    {
        $query = $this->getResponses()
            ->groupBy([
                "json_unquote(json_extract([[data]], '$.{$this->getMap()->getFunctionality()}'))"
            ])
            ->select([
                'count' => 'count(*)',
                'functionality' => "json_unquote(json_extract([[data]], '$.{$this->getMap()->getFunctionality()}'))",
            ])
            ->indexBy('functionality')
            ->orderBy('functionality')
            ->asArray();

        $map = [
            'A1' => \Yii::t('app', 'Full'),
            'A2' => \Yii::t('app', 'Partial'),
            'A3' => \Yii::t('app', 'None'),
            HeramsResponse::UNKNOWN_VALUE => \Yii::t('app', 'Unknown'),
        ];

        $result = [];
        foreach($query->column() as $key => $value) {
            $label = isset($map[$key]) ? $map[$key] : $map[HeramsResponse::UNKNOWN_VALUE];
            $result[$label] = ($result[$label] ?? 0) + $value;
        }
        return $result;
    }


    public function getSubjectAvailabilityCounts(): array
    {
        \Yii::beginProfile(__FUNCTION__);
        $counts = [
            HeramsSubject::FULLY_AVAILABLE => 0,
            HeramsSubject::PARTIALLY_AVAILABLE => 0,
            HeramsSubject::NOT_AVAILABLE => 0,
            HeramsSubject::NOT_PROVIDED=> 0,
        ];
        /** @var HeramsResponseInterface $heramsResponse */
        foreach ($this->getResponses()->each() as $heramsResponse)
        {
            foreach ($heramsResponse->getSubjects() as $subject) {
                $subjectAvailability = $subject->getAvailability();
                if (!isset($subjectAvailability, $counts[$subjectAvailability])) {
                    continue;
                }
                $counts[$subjectAvailability]++;
            }
        }
        ksort($counts);
        $map = [
            'A1' => \Yii::t('app', 'Full'),
            'A2' => \Yii::t('app', 'Partial'),
            'A3' => \Yii::t('app', 'None'),
//            'A4' => \Yii::t('app', 'Not normally provided'),
        ];

        $result = [];
        foreach($counts as $key => $value) {
            if (isset($map[$key])) {
                $result[$map[$key]] = $value;
            }
        }

        \Yii::endProfile(__FUNCTION__);
        return $result;
    }

    public function userCan($operation, User $user)
    {
        return $user->isAdmin || Permission::isAllowed($user, $this, $operation);
    }

    public function getPermissions()
    {
        return $this->hasMany(Permission::class, ['target_id' => 'id'])
            ->andWhere(['target' => self::class]);
    }

    /**
     * String representation of object
     * @link https://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        throw new NotSupportedException();
    }

    /**
     * Constructs the object
     * @link https://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        throw new NotSupportedException();
    }
    public function getFacilities(): FacilityListInterface
    {
        return new SurveyFacilityList($this->getSurvey());
    }

    public function getMap(): HeramsCodeMap
    {
        return new HeramsCodeMap();
    }

    public function getPages() {
        return $this->hasMany(Page::class, ['tool_id' => 'id'])->andWhere(['parent_id' => null])->orderBy('sort');
    }

    public function getAllPages()
    {
        return $this->hasMany(Page::class, ['tool_id' => 'id'])->orderBy('COALESCE([[parent_id]], [[id]])');
    }

   public function getContributorCount(): int
    {
        $result = $this->getOverride('contributorCount');
        if (!isset($result)) {
            $result = (int) Permission::find()->where([
                'target' => Workspace::class,
                'target_id' => $this->getWorkspaces()->select('id')->column(),
                'source' => User::class,
                'permission' => [
                    Permission::PERMISSION_WRITE,
                    Permission::PERMISSION_ADMIN
                ]
            ])->
            distinct()
                ->select('source_id')
                ->count();

        }
        return $result;
    }

    public function getFacilityCount(): int
    {
        return (int) ($this->getOverride('facilityCount')
            ?? (new ResponseFilter(null, $this->getMap()))->filterQuery($this->getResponses())->count());
    }

    public function getResponseCount(): int
    {
        return (int) ($this->getOverride('responseCount') ?? $this->getResponses()->count());
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getOverride($name)
    {
        return $this->overrides[$name] ?? null;
    }

    public function exportDashboard(): array
    {
        $pages = [];
        foreach ($this->pages as $page) {
            $pages[] = $page->export();
        }
        return $pages;
    }
}
