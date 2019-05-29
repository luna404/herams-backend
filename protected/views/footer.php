<div class="footer">
<?php

use Carbon\Carbon;
use prime\helpers\Icon;
use prime\models\ar\Project;
use yii\helpers\Html;
// Render all statistics.
$projects = Project::find()->all();
$stats[] = [
    'icon' => Icon::project(),
    'count' => count($projects),
    'subject' => \Yii::t('app', 'HeRAMS projects')
];
$stats[] = [
    'icon' => Icon::contributors(),
    'count' => \iter\reduce(function(int $accumulator, Project $project) {
        return $accumulator + $project->getContributorCount();
    }, $projects, 0),
    'subject' => \Yii::t('app', 'Contributors')
];

$stats[] = [
    'icon' => Icon::healthFacility(),
    'count' => \Yii::$app->cache->get('totalFacilityCount') ?: '?',
    'subject' => \Yii::t('app', 'Health facilities')
];
echo Html::beginTag('div', ['class' => 'stats']);
foreach($stats as $stat) {
    echo Html::beginTag('div');
    echo $stat['icon'];
    echo Html::tag('div', $stat['count'], ['class' => 'counter']);
    echo Html::tag('div', $stat['subject'], ['class' => 'subject']);
    echo Html::endTag('div');
}
echo Html::endTag('div');
?>
<div class="status"><?= Icon::sync() ?> Latest update: <span class="value">
            <?php
            if (false !== $ts = \Yii::$app->cache->get('lastUpdatedTimestamp')) {
                $lastUpdated = Carbon::createFromTimestampUTC($ts)->diffForHumans();
            } else {
                $lastUpdated = \Yii::t('app', 'Unknown');
            }

            if (false !== $projectId = Yii::$app->cache->get('lastUpdatedProject')) {
                $lastProject = Project::findOne(['id' => $projectId])->title;
            } else {
                $lastProject = \Yii::t('app', 'Unknown');
            }

            echo "$lastProject / $lastUpdated";

            ?></span>
</div>
    <?php

echo Html::a(Icon::chevronLeft(), '#', ['class' => 'left', 'id' => 'footer-left']);
echo Html::a(Icon::chevronRight(), '#', ['class' => 'right', 'id' => 'footer-right']);

echo Html::endTag('div');
$this->registerJs(<<<JS
const footer = document.querySelector('.footer .stats');
let timer;
const moveRight = function() {
    footer.append(footer.firstChild);
    clearTimeout(timer);
    timer = setTimeout(moveRight, 5000);
};
timer = setTimeout(moveRight, 5000);

document.getElementById('footer-right').addEventListener('click', moveRight);
document.getElementById('footer-left').addEventListener('click', function() {
    footer.prepend(footer.lastChild);
    clearTimeout(timer);
    timer = setTimeout(moveRight, 5000);
});

JS

);
?>

</div>
