<?php

namespace App\Jobs;

use App\Models\FsSeedsFulldata;
use App\Models\FsSeedsRecord1;

class FsSeedsCheck
{
    protected array $specialCSP = ['九芎', '凹葉越橘', '五節芒', 'UNKCOM1', 'UNKCOM2', 'UNKCOM3'];

    public function check($record, $spinfo, $existingSigns = [])
    {
        $trap = intval($record['trap']);
        if ($trap < 1 || $trap > 107 || $trap == 42) return $this->fail($record, 'Trap 不正確');
        if ($record['csp'] == 'nothing') return $this->pass($record);
        if ($record['count'] == 0) return $this->fail($record, '數量 不得為 0。');
        if ($record['csp'] == '') return $this->fail($record, '種類 不得為 空白。');

        if (in_array($record['csp'], ['栲屬', '薹屬']) && $record['code'] != '6') {
            return $this->fail($record, '該種類之類別欄位應為 6。');
        }

        switch ($record['code']) {
            case '0': return $this->fail($record, '類別 不得為 0。');
            case '1': return $this->checkCode1($record, $spinfo);
            case '2': return $this->checkCode2($record);
            case '3': return $this->checkCode3($record);
            case '4': return $this->checkCode4($record);
            case '5': return $this->checkCode5($record);
            case '6': return $this->checkCode6($record);
            default: return $this->fail($record, '未知的類別。');
        }
        $checksign = $record['census'] . $record['trap'] . $record['csp'] . $record['code'];
        if (in_array($checksign, $existingSigns)) {
            return $this->fail($record, '重複。');
        }
        return $this->pass($record);
    }

    protected function checkCode1(&$r, $spinfo)
    {
        $size = $spinfo[$r['csp']]['size'] ?? 'B';

        if ($size == 'B') {
            if ($this->isZeroOrEmpty($r['seeds'])) return $this->fail($r, '種子數 不得為 0。');
            if ($r['seeds'] < $r['count']) return $this->fail($r, '種子數不應小於數量。');

            if (in_array($r['csp'], $this->specialCSP)) {
                if ($r['viability'] !== 'NA') return $this->fail($r, '活性應為 NA。');
                if ($r['seeds'] === 'NA') return $this->fail($r, '種子數不應為 NA。');
            } else {
                if ($this->isEmpty($r['viability'])) return $this->fail($r, '活性 不得為 空白。');
                if ($r['viability'] === 'NA') return $this->fail($r, '活性不應為 NA。');
                if ($r['seeds'] === 'NA') return $this->fail($r, '種子數不應為 NA。');
                if ($r['viability'] > $r['seeds']) return $this->fail($r, '活性數不應大於種子數。');
            }
        } else {
            if ($r['viability'] !== 'NA') return $this->fail($r, '活性應為 NA。');
            if ($r['seeds'] !== 'NA') return $this->fail($r, '種子數應為 NA。');
        }

        $res = $this->checkFragments($r);	if ($res) return $res;
        $res = $this->checkSexBlank($r);	if ($res) return $res;
        return $this->pass($r);
    }

    protected function checkCode2(&$r)
    {
        if ($this->isZeroOrEmpty($r['seeds'])) return $this->fail($r, '種子數不得為 0。');
        if ($r['seeds'] != $r['count']) return $this->fail($r, '種子數應等於數量。');

        if (in_array($r['csp'], $this->specialCSP)) {
            if ($r['viability'] !== 'NA') return $this->fail($r, '活性應為 NA。');
            if ($r['seeds'] === 'NA') return $this->fail($r, '種子數不應為 NA。');
        } else {
            if ($this->isEmpty($r['viability'])) return $this->fail($r, '活性 不得為 空白。');
            if ($r['viability'] === 'NA') return $this->fail($r, '活性不應為 NA。');
            if ($r['seeds'] === 'NA') return $this->fail($r, '種子數不應為 NA。');
            if ($r['viability'] > $r['seeds']) return $this->fail($r, '活性數不應大於種子數。');
        }

        $res = $this->checkFragments($r);	if ($res) return $res;
        $res = $this->checkSexBlank($r);	if ($res) return $res;
        return $this->pass($r);
    }

    protected function checkCode3(&$r)
    {
		$res = $this->checkSeeds($r);	if ($res) return $res;
		$res = $this->checkViability($r);	if ($res) return $res;

        if ($this->isEmpty($r['fragments'])) return $this->fail($r, '碎片3數量不得為空白。');
        if ($r['fragments'] > $r['count']) return $this->fail($r, '碎片3數量不應大於數量。');

        $res = $this->checkSexBlank($r);	if ($res) return $res;
        return $this->pass($r);
    }

    protected function checkCode4(&$r)
    {
		$res = $this->checkSeeds($r);	if ($res) return $res;
		$res = $this->checkViability($r);	if ($res) return $res;

        if ($r['count'] != '1') return $this->fail($r, '數量應為 1。');

        $res = $this->checkFragments($r);	if ($res) return $res;
        $res = $this->checkSexBlank($r);	if ($res) return $res;
        return $this->pass($r);
    }

    protected function checkCode5(&$r)
    {
		$res = $this->checkSeeds($r);  	if ($res) return $res;
		$res = $this->checkViability($r);	if ($res) return $res;
        $res = $this->checkFragments($r);	if ($res) return $res;
        $res = $this->checkSexBlank($r);	if ($res) return $res;
        return $this->pass($r);
    }

    protected function checkCode6(&$r)
    {
		$res = $this->checkSeeds($r);  	if ($res) return $res;
		$res = $this->checkViability($r);	if ($res) return $res;

        if ($r['count'] != '1') return $this->fail($r, '數量應為 1。');

        $res = $this->checkFragments($r);	if ($res) return $res;

        if ($r['csp'] == '長葉木薑子') {
            if ($this->isEmpty($r['sex'])) return $this->fail($r, '種類為長葉木薑子，性別欄位不得為 空白。');
        } else {
            $res = $this->checkSexBlank($r);	if ($res) return $res;
        }

        return $this->pass($r);
    }

    // ========== 共用檢查工具函式 ==========

    protected function checkSeeds(&$r)
    {
        if (!$this->isZeroOrEmpty($r['seeds'])) {
            return $this->fail($r, '種子數應為 空白/0。');
        }
        $r['seeds'] = '0';
		return null;
    }

    protected function checkViability(&$r)
    {
        if (!$this->isZeroOrEmpty($r['viability'])) {
            return $this->fail($r, '活性應為 空白/0。');
        }
        $r['viability'] = '0';
		return null;
    }

    protected function checkFragments(&$r)
    {
        if (!$this->isZeroOrEmpty($r['fragments'])) {
            return $this->fail($r, '碎片3數量 應為 空白/0。');
      
        }
        $r['fragments'] = '0';
		return null;
    }

    protected function checkSexBlank(&$r)
    {
        if (isset($r['sex']) && trim((string)$r['sex']) !== '') {
			return $this->fail($r, '性別欄位應為 空白。');
        }
		return null;
    }

    protected function isEmpty($v)
    {
        return trim((string)$v) === '';
    }

    protected function isZeroOrEmpty($v)
    {
        return $this->isEmpty($v) || trim((string)$v) === '0';
    }

    protected function fail($record, $note)
    {
        return ['result' => $record, 'checknote' => $note];
    }

    protected function pass($record)
    {
        return ['result' => $record, 'checknote' => ''];
    }
}
