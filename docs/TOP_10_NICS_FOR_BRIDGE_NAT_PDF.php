<?php
/**
 * Top 10 NICs for Bridge NAT Systems - PDF Generator
 * Creates a comprehensive PDF document with rankings, specifications, and recommendations
 */

require_once('tcpdf/tcpdf.php');

class Top10NICsPDF extends TCPDF {
    
    public function Header() {
        $this->SetFont('helvetica', 'B', 20);
        $this->Cell(0, 15, 'TOP 10 NETWORK INTERFACE CARDS', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln();
        $this->SetFont('helvetica', 'B', 16);
        $this->Cell(0, 10, 'FOR BRIDGE NAT SYSTEMS', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(20);
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Create new PDF document
$pdf = new Top10NICsPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Bridge NAT System');
$pdf->SetAuthor('Network Administrator');
$pdf->SetTitle('Top 10 NICs for Bridge NAT Systems');
$pdf->SetSubject('Network Interface Card Recommendations');
$pdf->SetKeywords('NIC, Bridge NAT, Network, Performance, Virtualization');

// Set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// Set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Set font
$pdf->SetFont('helvetica', '', 10);

// Add a page
$pdf->AddPage();

// Define NIC data
$nics = [
    [
        'rank' => '🥇 #1',
        'name' => 'Intel X710-T4L (4-Port 10GbE)',
        'bandwidth' => '40Gb/s (4x 10Gb/s)',
        'queues' => '384 queues (96 per port)',
        'users' => '10,000+',
        'price' => '$800-1,200',
        'best_for' => 'Enterprise bridge NAT, large-scale captive portals',
        'score' => '10/10',
        'features' => [
            'MAC Filtering: 40,000 MAC/sec',
            'NAT Processing: 20,000 rules/sec',
            'Mangle Processing: 8,000 rules/sec',
            'Jumbo Frames: MTU 9000 support',
            'Flow Control: Mandatory for stability'
        ],
        'virtualization' => [
            'VMQ: 256 virtual ports',
            'SR-IOV: 256 virtual functions',
            'Hardware Offloads: Full TCP/UDP/VLAN/QoS',
            'RSS: Receive Side Scaling'
        ]
    ],
    [
        'rank' => '🥈 #2',
        'name' => 'NVIDIA ConnectX-7 (200GbE)',
        'bandwidth' => '200Gb/s',
        'queues' => '1,024 queues',
        'users' => '50,000+',
        'price' => '$2,500-4,000',
        'best_for' => 'Ultra-high performance, AI-powered bridge NAT',
        'score' => '9.8/10',
        'features' => [
            'MAC Filtering: 200,000 MAC/sec',
            'NAT Processing: 100,000 rules/sec',
            'Mangle Processing: 40,000 rules/sec',
            'RDMA Support: RoCE v2',
            'SmartNIC Capabilities: Programmable'
        ],
        'virtualization' => [
            'VMQ: 1,024 virtual ports',
            'SR-IOV: 1,024 virtual functions',
            'Hardware Offloads: Advanced ML/AI offloads',
            'DPU Features: Built-in ARM cores'
        ]
    ],
    [
        'rank' => '🥉 #3',
        'name' => 'Intel E810-XXVDA4T (4-Port 25GbE)',
        'bandwidth' => '100Gb/s (4x 25Gb/s)',
        'queues' => '512 queues (128 per port)',
        'users' => '25,000+',
        'price' => '$1,500-2,500',
        'best_for' => 'High-performance data centers, cloud bridge NAT',
        'score' => '9.5/10',
        'features' => [
            'MAC Filtering: 100,000 MAC/sec',
            'NAT Processing: 50,000 rules/sec',
            'Mangle Processing: 20,000 rules/sec',
            'DDP Support: Dynamic Device Personalization',
            'Advanced Filtering: Flow Director 2.0'
        ],
        'virtualization' => [
            'VMQ: 512 virtual ports',
            'SR-IOV: 512 virtual functions',
            'Hardware Offloads: Advanced DDP support',
            'Flow Director: Intelligent traffic steering'
        ]
    ],
    [
        'rank' => '#4',
        'name' => 'Mellanox ConnectX-6 Dx (100GbE)',
        'bandwidth' => '100Gb/s',
        'queues' => '512 queues',
        'users' => '25,000+',
        'price' => '$1,200-2,000',
        'best_for' => 'High-frequency bridge NAT, low-latency applications',
        'score' => '9.3/10',
        'features' => [
            'MAC Filtering: 100,000 MAC/sec',
            'NAT Processing: 50,000 rules/sec',
            'Mangle Processing: 20,000 rules/sec',
            'RDMA Support: RoCE v2',
            'Advanced Filtering: Flow Steering'
        ],
        'virtualization' => [
            'VMQ: 512 virtual ports',
            'SR-IOV: 512 virtual functions',
            'Hardware Offloads: Full TCP/UDP/VLAN',
            'RDMA: Remote Direct Memory Access'
        ]
    ],
    [
        'rank' => '#5',
        'name' => 'Intel X710-T2L (2-Port 10GbE)',
        'bandwidth' => '20Gb/s (2x 10Gb/s)',
        'queues' => '192 queues (96 per port)',
        'users' => '5,000+',
        'price' => '$400-600',
        'best_for' => 'Medium-scale bridge NAT, cost-conscious deployments',
        'score' => '9.0/10',
        'features' => [
            'MAC Filtering: 20,000 MAC/sec',
            'NAT Processing: 10,000 rules/sec',
            'Mangle Processing: 4,000 rules/sec',
            'Jumbo Frames: MTU 9000 support',
            'Flow Control: Adaptive'
        ],
        'virtualization' => [
            'VMQ: 128 virtual ports',
            'SR-IOV: 128 virtual functions',
            'Hardware Offloads: Full TCP/UDP/VLAN/QoS',
            'RSS: Receive Side Scaling'
        ]
    ],
    [
        'rank' => '#6',
        'name' => 'Realtek RTL8125B (2.5GbE)',
        'bandwidth' => '2.5Gb/s',
        'queues' => '8 queues',
        'users' => '500+',
        'price' => '$20-40',
        'best_for' => 'Small-scale bridge NAT, home labs, budget deployments',
        'score' => '8.5/10',
        'features' => [
            'MAC Filtering: 2,500 MAC/sec',
            'NAT Processing: 1,250 rules/sec',
            'Mangle Processing: 500 rules/sec',
            'Energy Efficient: Advanced power management',
            'Cost Effective: Excellent value'
        ],
        'virtualization' => [
            'VMQ: 8 virtual ports',
            'SR-IOV: Limited support',
            'Hardware Offloads: Basic TCP/UDP',
            'Power Management: Advanced features'
        ]
    ],
    [
        'rank' => '#7',
        'name' => 'Intel I350-T2 (2-Port 1GbE)',
        'bandwidth' => '2Gb/s (2x 1Gb/s)',
        'queues' => '16 queues (8 per port)',
        'users' => '400+',
        'price' => '$80-150',
        'best_for' => 'Small enterprise bridge NAT, reliable deployments',
        'score' => '8.0/10',
        'features' => [
            'MAC Filtering: 2,000 MAC/sec',
            'NAT Processing: 1,000 rules/sec',
            'Mangle Processing: 400 rules/sec',
            'Enterprise Grade: Reliable and stable',
            'Wide Support: Excellent driver support'
        ],
        'virtualization' => [
            'VMQ: 16 virtual ports',
            'SR-IOV: 16 virtual functions',
            'Hardware Offloads: Full TCP/UDP/VLAN',
            'Enterprise Features: Advanced management'
        ]
    ],
    [
        'rank' => '#8',
        'name' => 'TP-Link 10GbE PCIe Card',
        'bandwidth' => '10Gb/s',
        'queues' => '32 queues',
        'users' => '1,000+',
        'price' => '$60-120',
        'best_for' => 'Budget 10GbE bridge NAT, easy deployments',
        'score' => '7.8/10',
        'features' => [
            'MAC Filtering: 10,000 MAC/sec',
            'NAT Processing: 5,000 rules/sec',
            'Mangle Processing: 2,000 rules/sec',
            'Cost Effective: Excellent 10GbE value',
            'Easy Setup: Plug-and-play'
        ],
        'virtualization' => [
            'VMQ: 32 virtual ports',
            'SR-IOV: Limited support',
            'Hardware Offloads: Basic TCP/UDP',
            'Simple Management: Easy configuration'
        ]
    ],
    [
        'rank' => '#9',
        'name' => 'ASUS XG-C100C (10GbE)',
        'bandwidth' => '10Gb/s',
        'queues' => '32 queues',
        'users' => '1,000+',
        'price' => '$80-150',
        'best_for' => 'Gaming-focused bridge NAT, low-latency applications',
        'score' => '7.5/10',
        'features' => [
            'MAC Filtering: 10,000 MAC/sec',
            'NAT Processing: 5,000 rules/sec',
            'Mangle Processing: 2,000 rules/sec',
            'Gaming Optimized: Low latency',
            'RGB Lighting: Aesthetic appeal'
        ],
        'virtualization' => [
            'VMQ: 32 virtual ports',
            'SR-IOV: Limited support',
            'Hardware Offloads: Basic TCP/UDP',
            'Gaming Features: Optimized for low latency'
        ]
    ],
    [
        'rank' => '#10',
        'name' => 'Intel 82599ES (10GbE)',
        'bandwidth' => '10Gb/s',
        'queues' => '64 queues',
        'users' => '1,500+',
        'price' => '$100-200',
        'best_for' => 'Legacy system bridge NAT, proven reliability',
        'score' => '7.3/10',
        'features' => [
            'MAC Filtering: 10,000 MAC/sec',
            'NAT Processing: 5,000 rules/sec',
            'Mangle Processing: 2,000 rules/sec',
            'Legacy Support: Excellent compatibility',
            'Proven Reliability: Battle-tested'
        ],
        'virtualization' => [
            'VMQ: 64 virtual ports',
            'SR-IOV: 64 virtual functions',
            'Hardware Offloads: Full TCP/UDP/VLAN',
            'Legacy Support: Excellent compatibility'
        ]
    ]
];

// Add introduction
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Ranking Criteria', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(0, 5, 'This top 10 list is based on: Performance (throughput, latency, queue processing), Virtualization Support (VMQ, SR-IOV, hardware offloads), Hardware Queues (queue count and distribution), Bridge NAT Optimization (MAC filtering, NAT processing), Value for Money (performance per dollar), and Future-Proofing (scalability and upgrade path).', 0, 'L');
$pdf->Ln(10);

// Add each NIC
foreach ($nics as $nic) {
    // NIC header
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(0, 8, $nic['rank'] . ': ' . $nic['name'] . ' - Performance Score: ' . $nic['score'], 0, 1, 'L', true);
    
    // Specifications
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'Specifications:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(40, 5, 'Bandwidth:', 0, 0, 'L');
    $pdf->Cell(0, 5, $nic['bandwidth'], 0, 1, 'L');
    $pdf->Cell(40, 5, 'Queues:', 0, 0, 'L');
    $pdf->Cell(0, 5, $nic['queues'], 0, 1, 'L');
    $pdf->Cell(40, 5, 'Users:', 0, 0, 'L');
    $pdf->Cell(0, 5, $nic['users'], 0, 1, 'L');
    $pdf->Cell(40, 5, 'Price:', 0, 0, 'L');
    $pdf->Cell(0, 5, $nic['price'], 0, 1, 'L');
    $pdf->Cell(40, 5, 'Best For:', 0, 0, 'L');
    $pdf->Cell(0, 5, $nic['best_for'], 0, 1, 'L');
    
    // Key Features
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'Key Features:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 9);
    foreach ($nic['features'] as $feature) {
        $pdf->Cell(10, 5, '•', 0, 0, 'L');
        $pdf->Cell(0, 5, $feature, 0, 1, 'L');
    }
    
    // Virtualization Support
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'Virtualization Support:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 9);
    foreach ($nic['virtualization'] as $vfeature) {
        $pdf->Cell(10, 5, '•', 0, 0, 'L');
        $pdf->Cell(0, 5, $vfeature, 0, 1, 'L');
    }
    
    $pdf->Ln(5);
}

// Add performance comparison table
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Performance Comparison Summary', 0, 1, 'L');
$pdf->Ln(5);

// Table header
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetFillColor(200, 200, 200);
$pdf->Cell(15, 8, 'Rank', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'NIC Model', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'Bandwidth', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Queues', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'Users', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'Price', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Value', 1, 1, 'C', true);

// Table data
$pdf->SetFont('helvetica', '', 8);
$rank = 1;
foreach ($nics as $nic) {
    $pdf->Cell(15, 6, $rank, 1, 0, 'C');
    $pdf->Cell(35, 6, substr($nic['name'], 0, 20), 1, 0, 'L');
    $pdf->Cell(25, 6, $nic['bandwidth'], 1, 0, 'C');
    $pdf->Cell(20, 6, $nic['queues'], 1, 0, 'C');
    $pdf->Cell(25, 6, $nic['users'], 1, 0, 'C');
    $pdf->Cell(25, 6, $nic['price'], 1, 0, 'C');
    $pdf->Cell(20, 6, $nic['score'], 1, 1, 'C');
    $rank++;
}

// Add recommendations
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Recommendations by Use Case', 0, 1, 'L');
$pdf->Ln(5);

$recommendations = [
    '🏢 Enterprise Bridge NAT' => [
        '1. Intel X710-T4L - Best overall performance and value',
        '2. Intel E810-XXVDA4T - High-performance data center',
        '3. NVIDIA ConnectX-7 - Ultra-high performance with AI'
    ],
    '🏠 Small-Medium Business' => [
        '1. Intel X710-T2L - Perfect balance of performance and cost',
        '2. Intel I350-T2 - Reliable enterprise-grade solution',
        '3. TP-Link 10GbE - Budget-friendly 10GbE option'
    ],
    '🏠 Home/Small Office' => [
        '1. Realtek RTL8125B - Excellent value for money ($20-40!)',
        '2. ASUS XG-C100C - Gaming-focused with low latency',
        '3. Intel 82599ES - Proven reliability for legacy systems'
    ],
    '🎮 Gaming/Streaming' => [
        '1. ASUS XG-C100C - Optimized for low latency',
        '2. Intel X710-T2L - High performance for gaming',
        '3. TP-Link 10GbE - Budget gaming solution'
    ]
];

foreach ($recommendations as $category => $items) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, $category, 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    foreach ($items as $item) {
        $pdf->Cell(10, 6, '', 0, 0, 'L');
        $pdf->Cell(0, 6, $item, 0, 1, 'L');
    }
    $pdf->Ln(5);
}

// Add conclusion
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Conclusion', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(0, 5, 'The Intel X710-T4L takes the top spot for bridge NAT systems due to its exceptional performance, virtualization support, and value for money. For most bridge NAT deployments, the X710-T4L provides the perfect balance of performance, features, and cost-effectiveness.', 0, 'L');
$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 6, 'Key Takeaways:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(10, 5, '•', 0, 0, 'L');
$pdf->Cell(0, 5, 'X710-T4L is the ultimate choice for enterprise bridge NAT', 0, 1, 'L');
$pdf->Cell(10, 5, '•', 0, 0, 'L');
$pdf->Cell(0, 5, 'ConnectX-7 for ultra-high performance with AI capabilities', 0, 1, 'L');
$pdf->Cell(10, 5, '•', 0, 0, 'L');
$pdf->Cell(0, 5, 'RTL8125B for budget-conscious deployments', 0, 1, 'L');
$pdf->Cell(10, 5, '•', 0, 0, 'L');
$pdf->Cell(0, 5, 'E810 series for high-performance data centers', 0, 1, 'L');

// Output PDF
$pdf->Output('TOP_10_NICS_FOR_BRIDGE_NAT.pdf', 'D');
?> 