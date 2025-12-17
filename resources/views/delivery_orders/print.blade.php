<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project - {{ $deliveryOrder->dono }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        
        .delivery-order {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #000;
            padding: 20px;
            position: relative;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .company-info {
            text-align: center;
            flex: 1;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .company-details {
            font-size: 15px;
            margin-bottom: 5px;
        }
        
        .do-number-section {
            text-align: right;
            min-width: 200px;
        }
        
        .do-number-line {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .delivery-order-title {
            font-size: 18px;
            font-weight: bold;
        }
        
        .do-number {
            font-size: 14px;
            font-weight: bold;
        }
        
        .section {
            margin-bottom: 15px;
        }
        
        .section-title {
            font-weight: bold;
            margin-bottom: 5px;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }
        
        .two-column {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .column {
            flex: 1;
        }
        
        .three-column {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .col-3 {
            flex: 1;
            margin-right: 10px;
        }
        
        .col-3:last-child {
            margin-right: 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        table, th, td {
            border: 1px solid #000;
        }
        
        th, td {
            padding: 5px;
            text-align: left;
            vertical-align: top;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .time-table td {
            height: 40px; /* Increased height for signatures */
            vertical-align: bottom;
            text-align: center;
            position: relative;
            padding: 10px;
        }

        /* Signature table specific styles */
        .signature-table {
            margin-top: 10px;
            width: 100%;
        }
        
        .signature-table th {
            text-align: center;
            height: 50px;
            vertical-align: bottom;
            padding-bottom: 10px;
        }
        
        .signature-table td {
            height: 150px; /* Increased height for signatures */
            vertical-align: bottom;
            text-align: center;
            position: relative;
            padding: 10px;
        }
        
        .signature-content {
            position: absolute;
            bottom: 15px;
            left: 10px;
            right: 10px;
            text-align: center;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            padding-top: 8px;
            margin-top: 5px;
        }
        
        .signature-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .multi-line {
            line-height: 1.2;
        }
        
        .warning {
            background-color: #fffacd;
            border: 1px solid #ffd700;
            padding: 8px;
            margin: 10px 0;
            font-size: 10px;
        }
        
        .total-section {
            margin-top: 20px;
            font-weight: bold;
        }
        
        .no-border-table {
            border: none;
        }
        
        .no-border-table td {
            border: none;
            padding: 2px 0;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mb-10 {
            margin-bottom: 10px;
        }
        
        .mt-20 {
            margin-top: 20px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .delivery-order {
                border: none;
                padding: 10px;
            }
            
        }
    </style>
</head>
<body>
    <div class="delivery-order">
        <!-- Header Section -->
        <div class="header">
            <div class="do-number-section">
                <div class="do-number-line">
                    <div class="do-number">Project Number: {{ $deliveryOrder->dono }}</div>
                    @if (isset($deliveryOrder->task_number))
                    <div class="do-number">DO Number: {{ $deliveryOrder->task_number }}</div>
                    @endif
                </div>
            </div>
            <div class="company-info">
                <div class="company-name">{{ $company->name ?? 'COMPANY NAME' }}</div>
                <div class="company-details"> 
                    ({{ $company->ssm ?? '' }})<br>
                    {{ $company->address1 ?? '' }}
                    {{ $company->address2 ?? '' }}<br>
                    {{ $company->address3 ?? '' }}
                    {{ $company->address4 ?? '' }}<br>
                    Tel: {{ $company->phone ?? '' }} 
                    {{ $company->email ? 'Email: ' . $company->email : '' }}
                </div>
            </div>
        </div>

        <!-- Customer and Site Information -->
        <div class="section">
            <table class="no-border-table">
                <tr>
                    <td width="20%"><strong>NAME</strong></td>
                    <td><b>: </b></td>
                    <td width="80%"> &nbsp;{{ $deliveryOrder->customer->company ?? 'Customer Name' }}</td>
                </tr>
                <tr>
                    <td><strong>SITE ADDRESS</strong></td>
                    <td><b>: </b></td>
                    <td> &nbsp;{{ $deliveryOrder->place_name }} - {{ $deliveryOrder->place_address }}</td>
                </tr>
                <tr>
                    <td><strong>DATE</strong></td>
                    <td><b>: </b></td>
                    <td> &nbsp;{{ \Carbon\Carbon::parse($deliveryOrder->date)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td><strong>P/O NO</strong></td>
                    <td><b>: </b></td>
                    <td> &nbsp;</{{ $deliveryOrder->purchase_order_no ?? '' }}</td>
                </tr>
            </table>
        </div>

        <!-- Product and Specifications -->
        <div class="section">
            <table>
                <tr>
                    <th width="33%">Product Description</th>
                    <th width="33%">28 Days Strength</th>
                    <th width="34%">Slump</th>
                </tr>
                <tr>
                    <td>{{ $deliveryOrder->product->name ?? 'Product Name' }} ({{ $deliveryOrder->product->code ?? 'Code' }})</td>
                    <td>{{ $deliveryOrder->strength_at ?? '' }}</td>
                    <td>{{ $deliveryOrder->slump ?? '' }} </td>
                </tr>
            </table>
        </div>

        <!-- Time Information -->
        <div class="section">
            <table class="time-table">
                <tr>
                    <th width="33%">Time Batch</th>
                    <th width="33%">Time Arrive</th>
                    <th width="34%">Time Discharged</th>
                </tr>
                <tr>
                    <td>{{ $deliveryOrder->start_time ? \Carbon\Carbon::parse($deliveryOrder->start_time)->format('H:i') : '' }}</td>
                    <td>{{ $deliveryOrder->arrival_time ?? '' }}</td>
                    <td>{{ $deliveryOrder->end_time ? \Carbon\Carbon::parse($deliveryOrder->end_time)->format('H:i') : '' }}</td>
                </tr>
            </table>
        </div>

        <!-- Remarks -->
        <div class="section">
            <table>
                <tr>
                    <th>Remarks</th>
                </tr>
                <tr>
                    <td>{{ $deliveryOrder->remark ?? 'No remarks' }}</td>
                </tr>
            </table>
        </div>

        <!-- Quantity Information -->
        <div class="section">
            <table>
                <tr>
                    <th width="33%">Total Order</th>
                    @if(isset($deliveryOrder->this_load))
                    <th width="33%">This Load</th>
                    @endif
                    <th width="34%">Progress Total</th>
                </tr>
                <tr>
                    <td>{{ number_format($deliveryOrder->total_order, 2) }} m³</td>
                    @if(isset($deliveryOrder->this_load))
                    <td>{{ number_format($deliveryOrder->this_load, 2) }} m³</td>
                    @endif
                    <td>{{ number_format($deliveryOrder->progress_total, 2) }} m³</td>
                </tr>
            </table>
        </div>

        <!-- Signatures Section -->
        <div class="section">
            <table class="signature-table">
                <tr>
                    <th width="33%">PLAN SUPERVISOR</th>
                    <th width="33%">DRIVER'S SIGNATURE</th>
                    <th width="34%">CUSTOMER'S REPRESENTATIVE<br>SIGNATURE<br>WITH COMPANY CHOP</th>
                </tr>
                <tr>
                    <td>
                        <div class="signature-content">
                            <div class="signature-line"></div>
                        </div>
                    </td>
                    <td>
                        <div class="signature-content">
                            <div class="signature-line"></div>
                        </div>
                    </td>
                    <td>
                        <div class="signature-content">
                            <div class="signature-line"></div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Footer Information -->
        <div class="section text-center mt-20">
            <small>Serial No: {{ $deliveryOrder->dono }} | Created on: {{ date('d/m/Y H:i') }}</small>
        </div>
    </div>
</body>
</html>