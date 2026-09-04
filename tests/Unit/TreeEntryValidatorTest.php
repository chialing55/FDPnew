<?php

namespace Tests\Unit;

use App\Support\TreeEntry\TreeEntryProfileResolver;
use App\Support\TreeEntry\TreeEntryValidator;
use Tests\TestCase;

class TreeEntryValidatorTest extends TestCase
{
    private TreeEntryValidator $validator;
    private array $rules;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new TreeEntryValidator;
        $this->rules = (new TreeEntryProfileResolver)->validationRules('fushan_geo_trees');
    }

    public function test_geo_profile_inherits_base_and_fushan_family_rules(): void
    {
        $this->assertSame(1, $this->rules['minimumDbh']);
        $this->assertTrue($this->rules['requireDateAndDbh']);
        $this->assertTrue($this->rules['disallowDuplicateCodes']);
        $this->assertSame(['C', 'I', 'P', 'R'], $this->rules['allowedCodes']);
        $this->assertSame(['0', '-1', '-2', '-3'], $this->rules['allowedStatuses']);
        $this->assertSame('census5_part', $this->rules['previousData']);
    }

    public function test_valid_active_row_passes_and_code_is_uppercased(): void
    {
        $result = $this->validate(['code' => 'ip']);

        $this->assertTrue($result->passes());
        $this->assertSame('IP', $result->rows[0]['code']);
    }

    public function test_date_and_dbh_are_required_and_date_must_be_real(): void
    {
        $missing = $this->validate(['date' => '', 'dbh' => '']);
        $invalid = $this->validate(['date' => '2026-02-30']);

        $this->assertError($missing->errors, 'date', 'required');
        $this->assertError($missing->errors, 'dbh', 'required');
        $this->assertError($invalid->errors, 'date', 'date_format');
    }

    public function test_status_controls_dbh_and_code(): void
    {
        $nonzero = $this->validate(['status' => '-1', 'dbh' => 10]);
        $code = $this->validate(['status' => '-1', 'dbh' => 0, 'code' => 'I']);
        $activeZero = $this->validate(['status' => '', 'dbh' => 0]);

        $this->assertError($nonzero->errors, 'dbh', 'status_requires_zero');
        $this->assertError($code->errors, 'code', 'status_disallows_code');
        $this->assertError($activeZero->errors, 'dbh', 'active_disallows_zero');
    }

    public function test_status_must_be_allowed_by_the_survey_profile(): void
    {
        $invalid = $this->validate(['status' => '-4', 'dbh' => 0]);
        $valid = $this->validate(['status' => '-3', 'dbh' => 0]);

        $this->assertError($invalid->errors, 'status', 'invalid_status');
        $this->assertTrue($valid->passes());
    }

    public function test_status_code_exception_can_be_defined_by_a_site_profile(): void
    {
        $rules = array_replace($this->rules, ['statusCodeExceptions' => ['F'], 'allowedCodes' => ['C', 'F', 'I', 'P', 'R']]);
        $row = $this->validRow(['status' => '-1', 'dbh' => 0, 'code' => 'F']);

        $result = $this->validator->validate([$row], [$row['stemid'] => $this->previousRow()], $rules);

        $this->assertTrue($result->passes());
    }

    public function test_active_dbh_must_meet_minimum(): void
    {
        $result = $this->validate(['dbh' => 0.9]);

        $this->assertError($result->errors, 'dbh', 'minimum');
    }

    public function test_shrink_requires_confirmation_or_c(): void
    {
        $missing = $this->validate(['dbh' => 9]);
        $confirmed = $this->validate(['dbh' => 9, 'confirm' => '1']);
        $changedPom = $this->validate(['dbh' => 9, 'code' => 'C', 'pom' => 1.5, 'note' => '改測點']);

        $this->assertError($missing->errors, 'confirm', 'shrink_confirmation_required');
        $this->assertTrue($confirmed->passes());
        $this->assertTrue($changedPom->passes());
    }

    public function test_confirmation_is_rejected_without_shrink_or_with_c(): void
    {
        $notSmaller = $this->validate(['dbh' => 10, 'confirm' => '1']);
        $withC = $this->validate(['dbh' => 9, 'confirm' => '1', 'code' => 'C', 'pom' => 1.5, 'note' => '改測點']);

        $this->assertError($notSmaller->errors, 'confirm', 'unexpected_shrink_confirmation');
        $this->assertError($withC->errors, 'confirm', 'change_code_disallows_confirmation');
    }

    public function test_code_format_allowed_values_and_branch_rule_are_validated(): void
    {
        $this->assertError($this->validate(['code' => 'PI'])->errors, 'code', 'order');
        $this->assertError($this->validate(['code' => 'II'])->errors, 'code', 'duplicate');
        $this->assertError($this->validate(['code' => 'I P'])->errors, 'code', 'whitespace');
        $this->assertError($this->validate(['code' => 'X'])->errors, 'code', 'invalid_code');
        $this->assertError($this->validate(['code' => 'R', 'branch' => 0])->errors, 'code', 'branch_only');
    }

    public function test_c_requires_changed_pom_and_note_and_pom_change_requires_c(): void
    {
        $samePom = $this->validate(['code' => 'C', 'note' => '改測點']);
        $missingNote = $this->validate(['code' => 'C', 'pom' => 1.5]);
        $missingC = $this->validate(['pom' => 1.5]);

        $this->assertError($samePom->errors, 'pom', 'change_code_requires_change');
        $this->assertError($missingNote->errors, 'note', 'change_code_requires_note');
        $this->assertError($missingC->errors, 'code', 'pom_change_requires_code');
    }

    public function test_new_record_c_is_rejected(): void
    {
        $result = $this->validate(['code' => 'C', 'pom' => 1.5, 'note' => '改測點'], 'new');

        $this->assertError($result->errors, 'code', 'new_record_disallowed');
    }

    public function test_locked_rows_are_skipped_only_with_trusted_lock_list(): void
    {
        $row = $this->validRow(['date' => '', 'dbh' => '', '_entryLock' => ['display' => 'M']]);
        $notTrusted = $this->validator->validate([$row], [], $this->rules);
        $trustedLock = $this->validator->validate([$row], [], $this->rules, 'existing', ['000001.0']);

        $this->assertTrue($notTrusted->fails());
        $this->assertTrue($trustedLock->passes());
    }

    public function test_all_rows_are_validated_and_errors_contain_row_indexes(): void
    {
        $rows = [
            $this->validRow(['stemid' => '000001.0', 'date' => '']),
            $this->validRow(['stemid' => '000002.0', 'dbh' => 0.5]),
        ];
        $previous = [
            '000001.0' => $this->previousRow(),
            '000002.0' => $this->previousRow(),
        ];

        $result = $this->validator->validate($rows, $previous, $this->rules);

        $this->assertContains(0, array_column($result->errors, 'row'));
        $this->assertContains(1, array_column($result->errors, 'row'));
    }

    public function test_draft_save_skips_rows_without_a_date(): void
    {
        $notStarted = $this->validRow(['date' => '', 'dbh' => '']);
        $startedButInvalid = $this->validRow(['stemid' => '000002.0', 'date' => '2026-09-03', 'dbh' => '']);

        $skipped = $this->validator->validate(
            [$notStarted],
            [],
            $this->rules,
            'existing',
            [],
            true,
        );
        $validated = $this->validator->validate(
            [$notStarted, $startedButInvalid],
            ['000002.0' => $this->previousRow()],
            $this->rules,
            'existing',
            [],
            true,
        );

        $this->assertTrue($skipped->passes());
        $this->assertError($validated->errors, 'dbh', 'required');
        $this->assertSame([1], array_values(array_unique(array_column($validated->errors, 'row'))));
    }

    private function validate(array $overrides = [], string $mode = 'existing')
    {
        $row = $this->validRow($overrides);

        return $this->validator->validate(
            [$row],
            [$row['stemid'] => $this->previousRow()],
            $this->rules,
            $mode,
        );
    }

    private function validRow(array $overrides = []): array
    {
        return array_replace([
            'stemid' => '000001.0',
            'date' => '2026-09-03',
            'status' => '',
            'code' => '',
            'dbh' => 10,
            'pom' => 1.3,
            'note' => '',
            'confirm' => '',
            'branch' => 0,
        ], $overrides);
    }

    private function previousRow(): array
    {
        return ['dbh' => 10, 'pom' => 1.3];
    }

    private function assertError(array $errors, string $field, string $code): void
    {
        $this->assertNotEmpty(array_filter(
            $errors,
            fn (array $error) => $error['field'] === $field && $error['code'] === $code,
        ), "Expected error {$field}:{$code} was not returned.");
    }
}
