<?php

namespace Honeybee\Ui\Activity;

interface ActivityInterface
{
    const TYPE_WORKFLOW = 'workflow';

    const TYPE_GENERAL = 'general';

    public function getName();
    public function getType();
    public function getDescription();
    public function getLabel();
    public function getVerb();
    public function getRels();
    public function getAccepting();
    public function getSending();
    public function getSettings();
    public function getUrl();
    public function getScopeKey();
}
