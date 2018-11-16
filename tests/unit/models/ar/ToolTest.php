<?php
namespace prime\tests\unit\models\ar;

class ToolTest extends ActiveRecordTest
{
    public function validSamples(): array
    {
        return [
            [
                [
                    'title' => __CLASS__,
                    'base_survey_eid' => 12345,
                ]
            ],
        ];
    }

    public function invalidSamples(): array
    {
        return [
            [
                []
            ]
        ];
    }

}