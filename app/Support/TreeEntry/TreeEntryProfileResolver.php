<?php

namespace App\Support\TreeEntry;

use InvalidArgumentException;

final class TreeEntryProfileResolver
{
    public function validationRules(string $survey): array
    {
        $surveyConfig = config("tree-entry.surveys.{$survey}");
        if (!is_array($surveyConfig)) {
            throw new InvalidArgumentException("Unknown tree-entry survey [{$survey}].");
        }

        $rules = [];
        foreach ($surveyConfig['usesProfiles'] ?? [] as $profileName) {
            $profile = config("tree-entry.profiles.{$profileName}");
            if (!is_array($profile)) {
                throw new InvalidArgumentException("Unknown tree-entry profile [{$profileName}].");
            }

            $rules = $this->merge($rules, $profile['validation'] ?? []);
        }

        return $this->merge($rules, $surveyConfig['validation'] ?? []);
    }

    private function merge(array $base, array $overrides): array
    {
        foreach ($overrides as $key => $value) {
            if (
                isset($base[$key])
                && is_array($base[$key])
                && is_array($value)
                && !array_is_list($base[$key])
                && !array_is_list($value)
            ) {
                $base[$key] = $this->merge($base[$key], $value);
            } else {
                $base[$key] = $value;
            }
        }

        return $base;
    }
}
