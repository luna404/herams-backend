<?php

interface SurveyInterface {
    /**
     * @return int The unique ID for this survey.
     */
    public function getId();

    /**
     * @return GroupInterface[]
     */
    public function getGroups();
}