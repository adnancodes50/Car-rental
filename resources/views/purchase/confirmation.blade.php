<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Confirmation - Rent2Recover</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }

        .confirmation-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 900px;
            margin: 100px auto 20px auto;
            overflow-y: visible;
        }

        .confirmation-header {
            background: #679767;
            color: white;
            padding: 30px 15px;
            text-align: center;
        }

        .confirmation-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .confirmation-header h1 {
            font-size: 2.2rem;
            margin-bottom: 15px;
        }

        .confirmation-header p.lead {
            font-size: 1.2rem;
            margin-bottom: 0;
        }

        .purchase-details {
            padding: 30px;
        }

        .detail-row {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #555;
        }

        .detail-value {
            font-weight: 500;
            color: #333;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #eee;
        }

        .whatsapp-btn {
            background: #679767;
            border: none;
            padding: 12px 35px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .whatsapp-btn:hover {
            background: #5a855a;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(103, 151, 103, 0.3);
            color: white;
        }

        .home-btn {
            background: #6c757d;
            border: none;
            padding: 12px 35px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .home-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
            color: white;
        }

        @media print {
            body {
                background: white !important;
            }
            .confirmation-card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
            .button-container {
                display: none !important;
            }
        }

        @media (max-width: 768px) {
            .confirmation-card {
                margin: 80px 15px 20px 15px;
            }

            .confirmation-header {
                padding: 20px 10px;
            }

            .confirmation-icon {
                font-size: 60px;
                margin-bottom: 15px;
            }

            .confirmation-header h1 {
                font-size: 1.8rem;
            }

            .confirmation-header p.lead {
                font-size: 1rem;
            }

            .purchase-details {
                padding: 20px;
            }

            .button-container {
                flex-direction: column;
                gap: 15px;
            }

            .whatsapp-btn,
            .home-btn {
                width: 100%;
                text-align: center;
                padding: 12px 25px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        @include('.frontend.partials.navbar')

        <div class="confirmation-card">
            <div class="confirmation-header">
                <div class="confirmation-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h1 class="mb-3">Purchase Confirmed!</h1>
                <p class="lead mb-0">Thank you for your purchase. Here are your order details.</p>
            </div>

            <div class="purchase-details">
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h3 class="mb-3">Order Summary</h3>

                        <div class="detail-row row">
                            <div class="col-md-4 detail-label">Order Number:</div>
                            <div class="col-md-8 detail-value">#{{ $purchase->id }}</div>
                        </div>

                        <div class="detail-row row">
                            <div class="col-md-4 detail-label">Date:</div>
                            <div class="col-md-8 detail-value">{{ $purchase->created_at->format('F d, Y H:i A') }}</div>
                        </div>

                        <div class="detail-row row">
                            <div class="col-md-4 detail-label">Status:</div>
                            <div class="col-md-8 detail-value">
                                <span class="badge bg-success">{{ strtoupper($purchase->payment_status) }}</span>
                            </div>
                        </div>

                        <div class="detail-row row">
                            <div class="col-md-4 detail-label">Payment Method:</div>
                            <div class="col-md-8 detail-value">{{ ucfirst($purchase->payment_method) }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                @php
                                    $equipmentImage = $purchase->equipment->image ?? null;
                                    $imageUrl = $equipmentImage ? asset('storage/' . $equipmentImage) : asset('images/placeholder.png');
                                @endphp
                                <img src="{{ $imageUrl }}"
                                     alt="{{ $purchase->equipment->name }}"
                                     class="img-fluid rounded mb-3"
                                     style="max-height: 150px; object-fit: cover;">
                                <h5 class="mb-2">{{ $purchase->equipment->name }}</h5>
                                <p class="text-muted small mb-0">Equipment Purchase</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h4 class="mb-3">Equipment Details</h4>
                        <div class="detail-row row">
                            <div class="col-md-6 detail-label">Equipment:</div>
                            <div class="col-md-6 detail-value">{{ $purchase->equipment->name }}</div>
                        </div>
                        <div class="detail-row row">
                            <div class="col-md-6 detail-label">Quantity:</div>
                            <div class="col-md-6 detail-value">{{ $purchase->quantity }}</div>
                        </div>
                        <div class="detail-row row">
                            <div class="col-md-6 detail-label">Location:</div>
                            <div class="col-md-6 detail-value">{{ $purchase->location->name ?? 'N/A' }}</div>
                        </div>
                        <div class="detail-row row">
                            <div class="col-md-6 detail-label">Total Price:</div>
                            <div class="col-md-6 detail-value">R{{ number_format($purchase->total_price, 2) }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h4 class="mb-3">Payment Details</h4>
                        <div class="detail-row row">
                            <div class="col-md-6 detail-label">Deposit Paid:</div>
                            <div class="col-md-6 detail-value text-success fw-bold">
                                R{{ number_format($purchase->deposit_paid, 2) }}
                            </div>
                        </div>
                        <div class="detail-row row">
                            <div class="col-md-6 detail-label">Remaining Balance:</div>
                            <div class="col-md-6 detail-value">
                                R{{ number_format(max($purchase->total_price - $purchase->deposit_paid, 0), 2) }}
                            </div>
                        </div>
                        <div class="detail-row row">
                            <div class="col-md-6 detail-label">Paid At:</div>
                            <div class="col-md-6 detail-value">
                                {{ $purchase->paid_at ? $purchase->paid_at->format('F d, Y H:i A') : 'N/A' }}
                            </div>
                        </div>
                        @if($purchase->receipt_url)
                        <div class="detail-row row">
                            <div class="col-md-6 detail-label">Receipt:</div>
                            <div class="col-md-6 detail-value">
                                <a href="{{ $purchase->receipt_url }}" target="_blank" class="text-decoration-none">
                                    <i class="bi bi-receipt"></i> View Receipt
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <h4 class="mb-3">Customer Information</h4>
                        <div class="detail-row row">
                            <div class="col-md-5 detail-label">Name:</div>
                            <div class="col-md-7 detail-value">{{ $purchase->customer->name }}</div>
                        </div>
                        <div class="detail-row row">
                            <div class="col-md-5 detail-label">Email:</div>
                            <div class="col-md-7 detail-value">{{ $purchase->customer->email }}</div>
                        </div>
                        <div class="detail-row row">
                            <div class="col-md-5 detail-label">Phone:</div>
                            <div class="col-md-7 detail-value">{{ $purchase->customer->phone }}</div>
                        </div>
                        <div class="detail-row row">
                            <div class="col-md-5 detail-label">Location:</div>
                            <div class="col-md-7 detail-value">{{ $purchase->customer->country }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h4 class="mb-3">Next Steps</h4>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Important:</strong> Please contact us via WhatsApp to arrange equipment pickup/delivery and discuss the remaining balance payment.
                        </div>
                        <p class="text-muted">
                            <i class="bi bi-envelope me-1"></i>
                            A receipt has been sent to your email: <strong>{{ $purchase->customer->email }}</strong>
                        </p>
                    </div>
                </div>

                <div class="button-container">
                    <a href="/" class="home-btn">
                        <i class="bi bi-house me-2"></i> Back to Home
                    </a>
                    <button onclick="openWhatsApp()" class="whatsapp-btn">
                        <i class="bi bi-whatsapp me-2"></i> Contact via WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openWhatsApp() {
            const phone = "27673285525";
            const message = `Hi Wayne, I just completed a purchase (Order #{{ $purchase->id }}) for {{ $purchase->equipment->name }}. Please advise on next steps for pickup/delivery.`;
            const url = `https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(message)}`;
            window.open(url, '_blank');
        }

        // Clear session after showing confirmation
        window.onload = function() {
            fetch('/clear-purchase-session', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });
        };
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
