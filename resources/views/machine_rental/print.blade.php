<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Machine Rental Delivery Order - {{ $machineRental->delivery_order_number }}</title>
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
        
        .text-left {
            text-align: left;
        }
        
        .mb-10 {
            margin-bottom: 10px;
        }
        
        .mt-20 {
            margin-top: 20px;
        }
        
       .items-table th {
            text-align: center;
        }

        .items-table td:nth-child(1) { /* No column */
            text-align: center;
        }

        .items-table td:nth-child(2) { /* Particulars column */
            text-align: left;
        }

        .items-table td:nth-child(3) { /* UOM column */
            text-align: center;
        }

        .items-table td:nth-child(4) { /* Quantity column */
            text-align: center;
        }

        .items-table td:nth-child(5), /* Unit Price column */
        .items-table td:nth-child(6) { /* Amount column */
            text-align: right;
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
                    <div class="delivery-order-title"><u>MACHINE RENTAL DO</u></div>
                    <div class="do-number">No. {{ $machineRental->delivery_order_number }}</div>
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
                    Tel: {{ $company->phone ?? '' }} |
                    {{ $company->email ? 'Email: ' . $company->email : '' }}
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="section">
            <table class="no-border-table">
                <tr>
                    <td width="15%"><strong>TO</strong></td>
                    <td width="2%"><b>:</b></td>
                    <td width="38%">&nbsp;{{ $machineRental->customer->company ?? 'Customer Name' }}</td>
                    <td width="15%"><strong>DATE</strong></td>
                    <td width="2%"><b>:</b></td>
                    <td width="28%">&nbsp;{{ \Carbon\Carbon::parse($machineRental->date)->format('d/m/Y') }}</td>
                </tr>
            </table>
        </div>

        <!-- Rental Items Table -->
        <div class="section">
            <table class="items-table">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="40%">Particulars</th>
                        <th width="10%">UOM</th>
                        <th width="15%">Quantity</th>
                        <th width="15%">Unit Price (RM)</th>
                        <th width="15%">Amount (RM)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($machineRental->items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item->product->name ?? 'Product' }}</strong>
                            @if($item->description)
                            <br><small>{{ $item->description }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->uom ?? '-' }}</td>
                        <td class="text-center">{{ number_format($item->quantity) }}</td>
                        <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                    @endforeach                    
                </tbody>
            </table>
        </div>

        <!-- Total Amount -->
        <div class="section">
            <table class="no-border-table">
                <tr>
                    <td width="70%" class="text-right"><strong>TOTAL</strong></td>
                    <td width="30%" class="text-right"><strong>RM {{ number_format($machineRental->total_amount, 2) }}</strong></td>
                </tr>
            </table>
        </div>

        <!-- Signatures Section -->
        <div class="section">
            <table class="signature-table">
                <tr>
                    <th width="33%">ISSUED BY</th>
                    <th width="33%">APPROVED BY</th>
                    <th width="34%">RECEIVED BY</th>
                </tr>
                <tr>
                    <td>
                        <div class="signature-content">
                            @if($machineRental->issued_by && $machineRental->user)
                                <div class="signature-title">{{ $machineRental->user->name }}</div>
                            @endif
                            <div class="signature-line"></div>
                        </div>
                    </td>
                    <td>
                        <div class="signature-content">
                            @if($machineRental->approved_by)
                                <div class="signature-title">{{ $machineRental->approved_by }}</div>
                            @endif
                            <div class="signature-line"></div>
                        </div>
                    </td>
                    <td>
                        <div class="signature-content">
                            @if($machineRental->received_by)
                                <div class="signature-title">{{ $machineRental->received_by }}</div>
                            @endif
                            <div class="signature-line"></div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Footer Information -->
        <div class="section text-center mt-20">
            <small>Serial No: {{ $machineRental->delivery_order_number }} | Created on: {{ $machineRental->created_at->format('d/m/Y H:i') }}</small>
        </div>
    </div>
</body>
</html>