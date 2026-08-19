<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('h')) {
    function h($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('money')) {
    function money($value)
    {
        if ($value === null || $value === '') {
            return '0';
        }

        return number_format((float) $value, 2);
    }
}

if (!function_exists('pretty_date')) {
    function pretty_date($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '--';
        }

        $formats = array('d-m-Y', 'Y-m-d', 'd/m/Y', 'Y/m/d');
        foreach ($formats as $format) {
            $dt = DateTime::createFromFormat($format, $value);
            if ($dt instanceof DateTime) {
                return $dt->format('d M, Y');
            }
        }

        $ts = strtotime($value);
        return $ts ? date('d M, Y', $ts) : $value;
    }
}

if (!function_exists('pick')) {
    function pick(array $row, array $keys, $fallback = '--')
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return $fallback;
    }
}

$todayRows = isset($tdledger['today']) && is_array($tdledger['today']) ? $tdledger['today'] : array();
$totalRows = count($todayRows);
$ledgerId = isset($_GET['ledger_id']) ? $_GET['ledger_id'] : '';
$selectedDate = isset($_GET['date']) ? $_GET['date'] : '';
$masterId = isset($_GET['master']) ? $_GET['master'] : '';

$openingBalance = isset($opening) ? $opening : 0;
$openingLedgerBalance = isset($ob) ? $ob : 0;
$todayHisab = isset($today_hisab) ? $today_hisab : 0;
$finalHisab = isset($final_hisab) ? $final_hisab : 0;
$backVoucher = isset($backvoucher) ? $backvoucher : 0;
$kistAmount = isset($kist) ? $kist : 0;

$rowTotals = 0;
foreach ($todayRows as $row) {
    $rowTotals += isset($row['finalsum']) ? (float) $row['finalsum'] : (float) (isset($row['tamnt']) ? $row['tamnt'] : 0);
}

$grandTotal = (float) $openingBalance + (float) $openingLedgerBalance + (float) $todayHisab + (float) $finalHisab + (float) $backVoucher + (float) $kistAmount + (float) $rowTotals;

$homeUrl = isset($_GET['parentid'])
    ? 'https://app.555xch.pro/Parent.php?parentid=' . urlencode($_GET['parentid']) . '&name=' . urlencode(isset($_GET['name']) ? $_GET['name'] : '')
    : 'javascript:history.back()';
$entryUrl = 'Entry-page.php?login=' . urlencode($ledgerId) . '&user_type=ledger' . (isset($_GET['parentid']) ? '&parentid=' . urlencode($_GET['parentid']) . '&name=' . urlencode(isset($_GET['name']) ? $_GET['name'] : '') : '');
$statementUrl = 'statement.php/' . urlencode($ledgerId) . '?start_date=' . date('Y-m-01') . '&end_date=' . date('Y-m-d');
$listUrl = 'hisablist.php?login=' . urlencode($ledgerId) . '&user_type=ledger';
$logoutUrl = 'https://new.555xch.pro/appdemo/Login.html';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hisab Till Date</title>
    <style>
        :root {
            --bg: #0e1220;
            --bg-soft: #141a2c;
            --panel: rgba(18, 24, 40, 0.96);
            --panel-2: rgba(28, 36, 58, 0.92);
            --text: #f4f7fb;
            --muted: #aeb9cf;
            --line: rgba(255, 255, 255, 0.08);
            --accent: #7cd4ff;
            --accent-2: #ffd36b;
            --good: #73e6b6;
            --warn: #ff8f70;
            --shadow: 0 22px 58px rgba(0, 0, 0, 0.38);
        }

        * { box-sizing: border-box; }

        html, body {
            min-height: 100%;
            margin: 0;
            background:
                radial-gradient(circle at 18% 0%, rgba(124, 212, 255, 0.16), transparent 30%),
                radial-gradient(circle at 84% 4%, rgba(255, 211, 107, 0.12), transparent 26%),
                linear-gradient(180deg, #090d18 0%, #101729 42%, #0e1220 100%);
            color: var(--text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .page {
            width: min(1280px, calc(100% - 24px));
            margin: 0 auto;
            padding: 16px 0 28px;
        }

        .topbar {
            display: grid;
            grid-template-columns: 1.1fr auto;
            gap: 16px;
            align-items: center;
            margin-bottom: 16px;
        }

        .brand {
            display: grid;
            gap: 6px;
        }

        .brand h1 {
            margin: 0;
            font-size: clamp(1.45rem, 4vw, 2.2rem);
            line-height: 1.05;
        }

        .brand p {
            margin: 0;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 36px;
            padding: 0 12px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.05);
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--text);
            white-space: nowrap;
        }

        .chip strong {
            color: var(--accent-2);
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 0 14px;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.05);
            font-size: 0.86rem;
            font-weight: 800;
            box-shadow: var(--shadow);
        }

        .actions a.primary {
            background: linear-gradient(135deg, #1d7cf8, #0f4da4);
            border-color: rgba(124, 212, 255, 0.22);
        }

        .actions a.highlight {
            background: linear-gradient(135deg, #1d8c6d, #0b5d49);
            border-color: rgba(115, 230, 182, 0.22);
        }

        .hero {
            display: grid;
            grid-template-columns: 1.3fr 0.9fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .panel {
            border: 1px solid var(--line);
            border-radius: 20px;
            background: var(--panel);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .panel-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 18px 14px;
            border-bottom: 1px solid var(--line);
        }

        .panel-head h2,
        .panel-head h3 {
            margin: 0;
        }

        .panel-head p {
            margin: 4px 0 0;
            color: var(--muted);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            padding: 16px 18px 18px;
        }

        .summary-card {
            display: grid;
            gap: 6px;
            min-height: 96px;
            padding: 14px 14px 16px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.03));
        }

        .summary-card span {
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .summary-card b {
            font-size: clamp(1.12rem, 2.4vw, 1.8rem);
            line-height: 1.1;
        }

        .summary-card small {
            color: var(--muted);
            font-size: 0.84rem;
        }

        .summary-card.is-accent b {
            color: var(--accent);
        }

        .summary-card.is-good b {
            color: var(--good);
        }

        .summary-card.is-warn b {
            color: var(--warn);
        }

        .report {
            margin-bottom: 16px;
        }

        .report-toolbar {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            padding: 16px 18px 18px;
        }

        .metric {
            padding: 14px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: var(--panel-2);
        }

        .metric span {
            display: block;
            color: var(--muted);
            font-size: 0.76rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 6px;
        }

        .metric b {
            font-size: 1.15rem;
        }

        .table-wrap {
            overflow-x: auto;
            border-top: 1px solid var(--line);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: top;
        }

        th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #11172a;
            color: var(--accent-2);
            font-size: 0.76rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            white-space: nowrap;
        }

        tbody tr:nth-child(odd) td {
            background: rgba(255, 255, 255, 0.02);
        }

        tbody tr:hover td {
            background: rgba(124, 212, 255, 0.04);
        }

        .amount {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .subtle {
            color: var(--muted);
        }

        .row-good td.amount {
            color: var(--good);
            font-weight: 800;
        }

        .row-warn td.amount {
            color: var(--warn);
            font-weight: 800;
        }

        .empty {
            padding: 20px 18px 24px;
            color: var(--muted);
            text-align: center;
        }

        .notes {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            padding: 0 18px 18px;
        }

        .note-card {
            padding: 14px 16px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.04);
        }

        .note-card span {
            display: block;
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 6px;
        }

        .note-card b {
            font-size: 1.1rem;
        }

        @media (max-width: 980px) {
            .hero,
            .topbar {
                grid-template-columns: 1fr;
            }

            .chip-row {
                justify-content: flex-start;
            }

            .summary-grid,
            .report-toolbar,
            .notes {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 680px) {
            .page {
                width: min(100%, calc(100% - 14px));
                padding-top: 10px;
            }

            .summary-grid,
            .report-toolbar,
            .notes {
                grid-template-columns: 1fr;
            }

            th, td {
                padding: 12px 12px;
                font-size: 0.92rem;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="topbar">
            <div class="brand">
                <h1>Ledger till date report</h1>
                <p>Native CodeIgniter view for <?= h(pretty_date($selectedDate)) ?>. Designed to keep the report readable inside the app.</p>
            </div>
            <div class="chip-row">
                <div class="chip">Ledger ID <strong><?= h($ledgerId) ?></strong></div>
                <div class="chip">Master <strong><?= h($masterId ?: '-') ?></strong></div>
            </div>
        </div>

        <div class="actions">
            <a class="primary" href="<?= h($listUrl) ?>">Back to Hisab List</a>
            <a class="highlight" href="<?= h($entryUrl) ?>">Open Entry</a>
            <a href="<?= h($statementUrl) ?>">Statement</a>
            <a href="<?= h($homeUrl) ?>">Home</a>
            <a href="<?= h($logoutUrl) ?>">Logout</a>
        </div>

        <section class="hero">
            <article class="panel">
                <div class="panel-head">
                    <div>
                        <h2>Session summary</h2>
                        <p>Key figures prepared by the controller.</p>
                    </div>
                    <div class="chip">Selected date <strong><?= h(pretty_date($selectedDate)) ?></strong></div>
                </div>
                <div class="summary-grid">
                    <div class="summary-card is-accent">
                        <span>Opening balance</span>
                        <b><?= h(money($openingBalance)) ?></b>
                        <small>From ledger opening balance logic</small>
                    </div>
                    <div class="summary-card is-good">
                        <span>Today hisab</span>
                        <b><?= h(money($todayHisab)) ?></b>
                        <small>From final ledger results</small>
                    </div>
                    <div class="summary-card is-warn">
                        <span>Final hisab</span>
                        <b><?= h(money($finalHisab)) ?></b>
                        <small>Controller value for closing view</small>
                    </div>
                </div>
            </article>

            <article class="panel">
                <div class="panel-head">
                    <div>
                        <h3>Adjustments</h3>
                        <p>Supporting totals shown as separate signals.</p>
                    </div>
                </div>
                <div class="report-toolbar">
                    <div class="metric">
                        <span>Ledger opening</span>
                        <b><?= h(money($openingLedgerBalance)) ?></b>
                    </div>
                    <div class="metric">
                        <span>Back voucher</span>
                        <b><?= h(money($backVoucher)) ?></b>
                    </div>
                    <div class="metric">
                        <span>Kist</span>
                        <b><?= h(money($kistAmount)) ?></b>
                    </div>
                    <div class="metric">
                        <span>Row total</span>
                        <b><?= h(money($rowTotals)) ?></b>
                    </div>
                </div>
                <div class="notes">
                    <div class="note-card">
                        <span>Rows</span>
                        <b><?= h($totalRows) ?></b>
                    </div>
                    <div class="note-card">
                        <span>Working total</span>
                        <b><?= h(money($grandTotal)) ?></b>
                    </div>
                </div>
            </article>
        </section>

        <section class="panel report">
            <div class="panel-head">
                <div>
                    <h2>Ledger movement</h2>
                    <p>Each prepared day row is shown below in a compact, responsive table.</p>
                </div>
                <div class="chip">Entries <strong><?= h($totalRows) ?></strong></div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 20%;">Date</th>
                            <th style="width: 18%;">Shift</th>
                            <th style="width: 18%;">Party</th>
                            <th style="width: 14%;">Final Sum</th>
                            <th style="width: 14%;">Amount</th>
                            <th style="width: 16%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($todayRows): ?>
                            <?php foreach ($todayRows as $row): ?>
                                <?php
                                $rowDate = pick($row, array('Date', 'date', 't_date', 'created_date'));
                                $shift = pick($row, array('shift_name', 'shiftName', 'shift_id', 'shift'));
                                $party = pick($row, array('PartyName', 'party_name', 'ledger_name', 'PartyId', 'party_id'));
                                $sumValue = isset($row['finalsum']) ? $row['finalsum'] : (isset($row['tamnt']) ? $row['tamnt'] : 0);
                                $amountValue = isset($row['tamnt']) ? $row['tamnt'] : $sumValue;
                                $status = $sumValue >= 0 ? 'Net positive' : 'Net negative';
                                $statusClass = $sumValue >= 0 ? 'row-good' : 'row-warn';
                                ?>
                                <tr class="<?= h($statusClass) ?>">
                                    <td><?= h(pretty_date($rowDate)) ?></td>
                                    <td><?= h($shift) ?></td>
                                    <td><?= h($party) ?></td>
                                    <td class="amount"><?= h(money($sumValue)) ?></td>
                                    <td class="amount"><?= h(money($amountValue)) ?></td>
                                    <td class="subtle"><?= h($status) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty">No ledger rows were returned for the selected date.</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2>Additional notes</h2>
                    <p>These values are part of the controller payload and are shown without browser-side recalculation.</p>
                </div>
            </div>
            <div class="notes">
                <div class="note-card">
                    <span>Opening balance ledger</span>
                    <b><?= h(money($openingLedgerBalance)) ?></b>
                </div>
                <div class="note-card">
                    <span>Selected hisab day</span>
                    <b><?= h(pretty_date($selectedDate)) ?></b>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
