# 💰 Financial Management Modules

## Overview
The Financial Management modules provide comprehensive billing, invoicing, payment processing, and financial reporting capabilities for the AI SERVICE NETWORK MANAGEMENT SYSTEM.

---

## 📋 Available Modules

### 1. **Invoices Module** (`invoices.php`)
Complete invoice management system with automated billing capabilities.

#### Features
- ✅ Automated invoice generation
- ✅ Recurring invoices
- ✅ Multi-currency support
- ✅ Tax calculation
- ✅ PDF generation
- ✅ Email delivery

#### Installation
```bash
# Create invoices table
CREATE TABLE invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    client_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    subtotal DECIMAL(10,2) NOT NULL,
    tax_rate DECIMAL(5,2) DEFAULT 0,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    discount_type ENUM('fixed', 'percentage') DEFAULT 'fixed',
    discount_value DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    paid_amount DECIMAL(10,2) DEFAULT 0,
    status ENUM('draft', 'sent', 'paid', 'partial', 'overdue', 'cancelled') DEFAULT 'draft',
    payment_method VARCHAR(50),
    notes TEXT,
    terms TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date),
    INDEX idx_client (client_id)
);

CREATE TABLE invoice_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    tax_rate DECIMAL(5,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    service_id INT,
    period_start DATE,
    period_end DATE,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    INDEX idx_invoice (invoice_id)
);
```

#### Configuration
```php
// config/invoicing.php
return [
    'invoice_prefix' => 'INV-',
    'invoice_number_format' => '{prefix}{year}{month}{number}',
    'starting_number' => 1000,
    'payment_terms' => 30, // days
    'late_fee_percentage' => 5,
    'late_fee_grace_period' => 7, // days
    'currencies' => ['USD', 'EUR', 'GBP', 'PLN'],
    'tax_rates' => [
        'standard' => 23,
        'reduced' => 8,
        'zero' => 0
    ],
    'pdf_template' => 'default',
    'auto_send' => true,
    'reminder_schedule' => [7, 3, 0, -3, -7] // days before/after due date
];
```

#### Invoice Operations
```php
// Generate invoice
$invoiceId = generateInvoice($clientId, [
    'items' => [
        [
            'description' => 'Internet Service - Premium Package',
            'quantity' => 1,
            'unit_price' => 99.99,
            'period_start' => '2025-01-01',
            'period_end' => '2025-01-31'
        ]
    ],
    'tax_rate' => 23,
    'due_date' => date('Y-m-d', strtotime('+30 days'))
]);

// Send invoice
sendInvoice($invoiceId, [
    'email' => true,
    'pdf' => true,
    'template' => 'professional'
]);

// Generate recurring invoices
generateRecurringInvoices([
    'frequency' => 'monthly',
    'day_of_month' => 1
]);
```

---

### 2. **Add Invoice Module** (`add_invoice.php`)
Manual invoice creation interface with advanced features.

#### Features
- ✅ Line item management
- ✅ Service integration
- ✅ Tax calculation
- ✅ Discount application
- ✅ Preview functionality
- ✅ Template selection

#### Advanced Invoice Creation
```php
// Create custom invoice
$invoice = new Invoice();
$invoice->setClient($clientId)
    ->addItem('Custom Service', 1, 150.00)
    ->addItem('Setup Fee', 1, 50.00)
    ->applyDiscount('percentage', 10)
    ->setTaxRate(23)
    ->setDueDate('+15 days')
    ->setNotes('Thank you for your business!')
    ->save();

// Apply credit note
applyCreditNote($invoiceId, $creditNoteId);

// Split invoice
$splitInvoices = splitInvoice($invoiceId, [
    ['percentage' => 60],
    ['percentage' => 40]
]);
```

---

### 3. **Payments Module** (`payments.php`)
Payment tracking and reconciliation system.

#### Features
- ✅ Payment recording
- ✅ Multiple payment methods
- ✅ Partial payments
- ✅ Payment allocation
- ✅ Refund processing
- ✅ Payment history

#### Installation
```bash
# Create payments table
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payment_number VARCHAR(50) UNIQUE NOT NULL,
    client_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    payment_date DATE NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'credit_card', 'paypal', 'other') NOT NULL,
    reference_number VARCHAR(100),
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'completed',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    INDEX idx_client (client_id),
    INDEX idx_date (payment_date),
    INDEX idx_status (status)
);

CREATE TABLE payment_allocations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payment_id INT NOT NULL,
    invoice_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    allocated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id),
    FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    INDEX idx_payment (payment_id),
    INDEX idx_invoice (invoice_id)
);
```

#### Payment Processing
```php
// Record payment
$paymentId = recordPayment([
    'client_id' => $clientId,
    'amount' => 299.99,
    'payment_method' => 'bank_transfer',
    'reference_number' => 'TRX123456',
    'allocations' => [
        ['invoice_id' => 1001, 'amount' => 199.99],
        ['invoice_id' => 1002, 'amount' => 100.00]
    ]
]);

// Process refund
$refundId = processRefund($paymentId, [
    'amount' => 50.00,
    'reason' => 'Service credit',
    'method' => 'bank_transfer'
]);

// Auto-allocate payments
autoAllocatePayments($clientId);
```

---

### 4. **Add Payment Module** (`add_payment.php`)
Payment entry interface with smart allocation.

#### Features
- ✅ Invoice selection
- ✅ Auto-allocation
- ✅ Overpayment handling
- ✅ Payment confirmation
- ✅ Receipt generation
- ✅ Integration with payment gateways

#### Payment Gateway Integration
```php
// config/payment_gateways.php
return [
    'stripe' => [
        'enabled' => true,
        'public_key' => 'pk_live_...',
        'secret_key' => 'sk_live_...',
        'webhook_secret' => 'whsec_...'
    ],
    'paypal' => [
        'enabled' => true,
        'client_id' => 'AY...',
        'secret' => 'EL...',
        'mode' => 'live' // or 'sandbox'
    ],
    'bank_transfer' => [
        'enabled' => true,
        'account_details' => [
            'bank_name' => 'Example Bank',
            'account_number' => '1234567890',
            'swift' => 'EXAMPLEBIC'
        ]
    ]
];
```

---

### 5. **Tariffs Module** (`tariffs.php`)
Service pricing and tariff management.

#### Features
- ✅ Flexible pricing structures
- ✅ Usage-based billing
- ✅ Tiered pricing
- ✅ Promotional rates
- ✅ Bundle management
- ✅ Currency conversion

#### Installation
```bash
# Create tariffs table
CREATE TABLE tariffs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    billing_type ENUM('fixed', 'usage', 'tiered', 'volume') DEFAULT 'fixed',
    billing_cycle ENUM('monthly', 'quarterly', 'semi-annual', 'annual', 'one-time') DEFAULT 'monthly',
    base_price DECIMAL(10,2) NOT NULL,
    setup_fee DECIMAL(10,2) DEFAULT 0,
    currency VARCHAR(3) DEFAULT 'USD',
    tax_inclusive BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'promotional') DEFAULT 'active',
    valid_from DATE,
    valid_until DATE,
    max_customers INT,
    features JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_code (code)
);

CREATE TABLE tariff_tiers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tariff_id INT NOT NULL,
    min_usage INT NOT NULL,
    max_usage INT,
    price_per_unit DECIMAL(10,4) NOT NULL,
    FOREIGN KEY (tariff_id) REFERENCES tariffs(id),
    INDEX idx_tariff (tariff_id)
);
```

#### Tariff Management
```php
// Create tariff
$tariffId = createTariff([
    'name' => 'Premium Internet 100Mbps',
    'code' => 'PREM100',
    'base_price' => 99.99,
    'setup_fee' => 49.99,
    'features' => [
        'speed' => '100/100 Mbps',
        'data_cap' => 'Unlimited',
        'support' => '24/7',
        'static_ip' => true
    ]
]);

// Create tiered tariff
createTieredTariff([
    'name' => 'Pay As You Go',
    'code' => 'PAYG',
    'tiers' => [
        ['min' => 0, 'max' => 100, 'price_per_unit' => 0.10],
        ['min' => 101, 'max' => 500, 'price_per_unit' => 0.08],
        ['min' => 501, 'max' => null, 'price_per_unit' => 0.05]
    ]
]);

// Apply promotional rate
applyPromotion($tariffId, [
    'discount_percentage' => 50,
    'valid_for_months' => 3,
    'new_customers_only' => true
]);
```

---

### 6. **Service Packages** (`internet_packages.php`, `tv_packages.php`)
Bundled service management.

#### **Internet Packages Module**
##### Features
- ✅ Speed tier management
- ✅ Data cap configuration
- ✅ Fair usage policy
- ✅ Add-on services
- ✅ Package comparison
- ✅ Availability mapping

##### Package Configuration
```php
// Create internet package
$packageId = createInternetPackage([
    'name' => 'Fiber Pro 1Gbps',
    'download_speed' => 1000,
    'upload_speed' => 1000,
    'data_cap' => null, // unlimited
    'monthly_price' => 149.99,
    'contract_length' => 24,
    'features' => [
        'static_ip' => 1,
        'email_accounts' => 10,
        'cloud_storage' => '1TB',
        'security_suite' => true
    ]
]);

// Set availability
setPackageAvailability($packageId, [
    'networks' => [1, 2, 3],
    'regions' => ['downtown', 'suburbs'],
    'technology' => ['fiber', 'cable']
]);
```

#### **TV Packages Module**
##### Features
- ✅ Channel lineup management
- ✅ Package bundling
- ✅ Premium channels
- ✅ VOD integration
- ✅ Set-top box management

---

### 7. **Services Module** (`services.php`)
General service management and provisioning.

#### Features
- ✅ Service lifecycle management
- ✅ Activation/deactivation
- ✅ Service dependencies
- ✅ Resource allocation
- ✅ SLA tracking
- ✅ Service reporting

#### Service Operations
```php
// Provision service
$serviceId = provisionService($clientId, [
    'type' => 'internet',
    'package_id' => $packageId,
    'start_date' => date('Y-m-d'),
    'contract_months' => 24,
    'installation_address' => $address,
    'equipment' => ['router', 'modem']
]);

// Suspend service
suspendService($serviceId, [
    'reason' => 'non_payment',
    'effective_date' => date('Y-m-d'),
    'auto_resume' => true,
    'resume_on_payment' => true
]);

// Service upgrade
upgradeService($serviceId, $newPackageId, [
    'effective_date' => date('Y-m-d', strtotime('+1 month')),
    'prorate' => true
]);
```

---

## 📊 Financial Analytics & Reporting

### Revenue Analytics
```php
// Monthly recurring revenue (MRR)
$mrr = calculateMRR([
    'include_discounts' => true,
    'include_one_time' => false
]);

// Customer lifetime value (CLV)
$clv = calculateCLV($clientId);

// Revenue forecast
$forecast = forecastRevenue([
    'period' => '12_months',
    'growth_rate' => 0.05,
    'churn_rate' => 0.02
]);
```

### Financial Reports
```php
// Generate financial summary
$summary = generateFinancialSummary([
    'period' => 'quarterly',
    'year' => 2025,
    'quarter' => 1
]);

// Accounts receivable aging
$aging = getAccountsReceivableAging([
    'buckets' => [30, 60, 90, 120],
    'as_of_date' => date('Y-m-d')
]);

// Tax report
$taxReport = generateTaxReport([
    'period' => 'monthly',
    'year' => 2025,
    'month' => 1
]);
```

---

## 🔧 Advanced Features

### Automated Billing
```php
// Set up billing run
$billingRun = new BillingRun();
$billingRun->setDate(date('Y-m-d'))
    ->includeRecurring(true)
    ->includeUsageBased(true)
    ->includeOneTime(true)
    ->preview();

// Execute billing run
if ($billingRun->isValid()) {
    $results = $billingRun->execute();
    // Results: invoices_created, total_amount, errors
}
```

### Payment Automation
```php
// Auto-charge credit cards
$chargeResults = autoChargeStoredCards([
    'invoice_status' => 'sent',
    'days_after_due' => 3,
    'max_attempts' => 3
]);

// Direct debit processing
processDirect<h debits([
    'batch_date' => date('Y-m-d', strtotime('+3 days')),
    'notification_days' => 14
]);
```

### Dunning Management
```php
// config/dunning.php
return [
    'levels' => [
        [
            'days_overdue' => 7,
            'action' => 'reminder_email',
            'template' => 'friendly_reminder'
        ],
        [
            'days_overdue' => 14,
            'action' => 'warning_email',
            'template' => 'payment_warning'
        ],
        [
            'days_overdue' => 30,
            'action' => 'service_suspension',
            'notification' => true
        ],
        [
            'days_overdue' => 60,
            'action' => 'collection_agency',
            'minimum_amount' => 100
        ]
    ]
];
```

---

## 🚨 Troubleshooting

### Common Issues

#### 1. Invoice Generation Failures
```php
// Debug invoice generation
$debug = debugInvoiceGeneration($clientId);
// Check: active_services, billing_info, tax_configuration

// Validate invoice data
$validation = validateInvoiceData($invoiceData);
if (!$validation['valid']) {
    print_r($validation['errors']);
}
```

#### 2. Payment Allocation Issues
```sql
-- Find unallocated payments
SELECT p.*, c.name as client_name
FROM payments p
JOIN clients c ON p.client_id = c.id
LEFT JOIN payment_allocations pa ON p.id = pa.payment_id
WHERE pa.id IS NULL AND p.status = 'completed';

-- Fix payment allocation
CALL allocate_payment(payment_id);
```

#### 3. Currency Conversion
```php
// Update exchange rates
updateExchangeRates([
    'source' => 'ecb', // European Central Bank
    'base_currency' => 'USD'
]);

// Convert amount
$converted = convertCurrency(100, 'EUR', 'USD', date('Y-m-d'));
```

---

## 🔗 Related Modules
- [Client Management](../client-management/README.md)
- [Service Management](../services/README.md)
- [Reporting System](../reporting/financial-reports.md)
- [Payment Gateway Integration](../integration/payment-gateways.md)

---

**Module Version**: 3.5.0  
**Last Updated**: January 2025  
**Maintainer**: Finance Team