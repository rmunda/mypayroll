<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size:11px; color:#1A1A18; margin:0; }
  .header { background:#185FA5; color:white; padding:18px 20px; display:flex; justify-content:space-between; }
  .brand  { font-size:18px; font-weight:bold; }
  .sub    { font-size:12px; opacity:.85; margin-top:3px; }
  .meta   { display:flex; gap:30px; padding:12px 20px; background:#F4F3EF; flex-wrap:wrap; }
  .ml label{ display:block; font-size:9px; color:#6B6B65; text-transform:uppercase; margin-bottom:2px; }
  .ml span { font-size:12px; font-weight:600; }
  .body   { padding:16px 20px; }
  table   { width:100%; border-collapse:collapse; }
  th      { background:#F4F3EF; text-align:left; padding:5px 8px; font-size:9px; text-transform:uppercase; color:#6B6B65; }
  td      { padding:5px 8px; border-bottom:1px solid #ECEAE4; }
  .total  td { font-weight:bold; border-top:2px solid #1A1A18; border-bottom:none; }
  .net    { background:#185FA5; color:white; font-size:15px; padding:12px 20px; text-align:right; font-weight:bold; }
  .foot   { padding:8px 20px; font-size:9px; color:#9E9E98; text-align:center; border-top:1px solid #ECEAE4; }
</style>
</head>
<body>

<div class="header">
  <div style="display:flex;align-items:center;gap:14px;">
    @if($company->logo)
      <img src="{{ Storage::disk('public')->path($company->logo) }}" style="height:48px;width:auto;object-fit:contain;">
    @endif
    <div>
      <div class="brand">{{ $company->company_name }}</div>
      @if($company->tagline)
        <div class="sub">{{ $company->tagline }}</div>
      @endif
      @if($company->city || $company->state)
        <div style="font-size:10px;opacity:.8;margin-top:2px;">
          {{ implode(', ', array_filter([$company->city, $company->state, $company->pincode])) }}
        </div>
      @endif
    </div>
  </div>
  <div style="text-align:right;font-size:10px;opacity:.85;">
    <div style="font-size:12px;font-weight:bold;margin-bottom:4px;">Pay Slip &mdash; {{ $slip->payrollRun->period_label }}</div>
    @if($company->gstin)<div>GSTIN: {{ $company->gstin }}</div>@endif
    @if($company->pan)<div>PAN: {{ $company->pan }}</div>@endif
    <div>Generated: {{ now()->format('d M Y') }}</div>
  </div>
</div>

<div class="meta">
  <div class="ml"><label>Employee</label><span>{{ $slip->employee->name }}</span></div>
  <div class="ml"><label>ID</label><span>{{ $slip->employee->employee_code }}</span></div>
  <div class="ml"><label>Designation</label><span>{{ $slip->employee->designation }}</span></div>
  <div class="ml"><label>Department</label><span>{{ $slip->employee->department->name }}</span></div>
  <div class="ml"><label>PAN</label><span>{{ $slip->employee->pan_number ?? 'N/A' }}</span></div>
</div>

<div class="meta" style="margin-top:1px;">
  <div class="ml"><label>Period</label><span>{{ $slip->payrollRun->period_start->format('d M Y') }} &ndash; {{ $slip->payrollRun->period_end->format('d M Y') }}</span></div>
  <div class="ml"><label>Working days</label><span>{{ $slip->working_days }}</span></div>
  <div class="ml"><label>Present</label><span>{{ $slip->present_days }}</span></div>
  <div class="ml"><label>Leave</label><span>{{ $slip->leave_days }}</span></div>
  <div class="ml"><label>Absent</label><span>{{ $slip->absent_days }}</span></div>
</div>

<div class="body">
<table>
  <thead>
    <tr>
      <th>Earnings</th><th style="text-align:right">Amount (INR)</th>
      <th>Deductions</th><th style="text-align:right">Amount (INR)</th>
    </tr>
  </thead>
  <tbody>
    <tr><td>Basic salary</td><td align="right">{{ number_format($slip->basic,2) }}</td><td>Provident Fund (12%)</td><td align="right">{{ number_format($slip->pf_employee,2) }}</td></tr>
    <tr><td>HRA</td><td align="right">{{ number_format($slip->hra,2) }}</td><td>ESI (0.75%)</td><td align="right">{{ number_format($slip->esi_employee,2) }}</td></tr>
    <tr><td>Transport allowance</td><td align="right">{{ number_format($slip->transport_allowance,2) }}</td><td>Professional tax</td><td align="right">{{ number_format($slip->professional_tax,2) }}</td></tr>
    <tr><td>Special allowance</td><td align="right">{{ number_format($slip->special_allowance,2) }}</td><td>TDS</td><td align="right">{{ number_format($slip->tds,2) }}</td></tr>
    @if($slip->bonus > 0)
    <tr><td>Bonus</td><td align="right">{{ number_format($slip->bonus,2) }}</td><td></td><td></td></tr>
    @endif
    <tr class="total"><td>Gross earnings</td><td align="right">{{ number_format($slip->gross_earnings,2) }}</td><td>Total deductions</td><td align="right">{{ number_format($slip->total_deductions,2) }}</td></tr>
  </tbody>
</table>
</div>

<div class="net">Net Pay: INR {{ number_format($slip->net_pay,2) }}</div>
<div class="foot">
  Computer-generated pay slip &mdash; no signature required &mdash; {{ $company->company_name }}
  @if($company->address_line1) &mdash; {{ implode(', ', array_filter([$company->address_line1, $company->address_line2, $company->city, $company->state, $company->pincode])) }}@endif
  @if($company->phone) &mdash; {{ $company->phone }}@endif
</div>

</body>
</html>