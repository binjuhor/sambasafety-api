<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi\Collections;

use Binjuhor\SambasafetyApi\Models\MvrRecord;

class MvrCollection extends Collection
{
    public function getCompleted(): MvrCollection
    {
        return $this->filter(fn(MvrRecord $mvr) => $mvr->isCompleted());
    }

    public function getPending(): MvrCollection
    {
        return $this->filter(fn(MvrRecord $mvr) => !$mvr->isCompleted());
    }

    public function withViolations(): MvrCollection
    {
        return $this->filter(fn(MvrRecord $mvr) => $mvr->hasViolations());
    }

    public function withAccidents(): MvrCollection
    {
        return $this->filter(fn(MvrRecord $mvr) => $mvr->hasAccidents());
    }

    public function forState(string $state): MvrCollection
    {
        return $this->filter(fn(MvrRecord $mvr) => strtolower($mvr->state) === strtolower($state));
    }

    public function getTotalViolations(): int
    {
        return array_sum($this->pluck('getViolationCount'));
    }

    public function getTotalAccidents(): int
    {
        return array_sum($this->pluck('getAccidentCount'));
    }
}