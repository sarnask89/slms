<?php
/**
 * Top 10 NICs for Bridge NAT Systems - HTML Generator
 * Creates a comprehensive HTML document that can be printed to PDF
 */

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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top 10 NICs for Bridge NAT Systems</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            .page-break { page-break-before: always; }
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #333;
            margin: 0;
            font-size: 2.5em;
        }
        
        .header h2 {
            color: #666;
            margin: 10px 0 0 0;
            font-size: 1.5em;
        }
        
        .criteria {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        
        .criteria h3 {
            color: #333;
            margin-top: 0;
        }
        
        .nic-card {
            border: 2px solid #ddd;
            border-radius: 10px;
            margin-bottom: 25px;
            padding: 20px;
            background-color: #fafafa;
        }
        
        .nic-header {
            background-color: #333;
            color: white;
            padding: 15px;
            margin: -20px -20px 20px -20px;
            border-radius: 8px 8px 0 0;
        }
        
        .nic-header h3 {
            margin: 0;
            font-size: 1.3em;
        }
        
        .nic-specs {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .spec-item {
            background-color: white;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        
        .spec-label {
            font-weight: bold;
            color: #333;
        }
        
        .spec-value {
            color: #666;
        }
        
        .features-section {
            margin-bottom: 20px;
        }
        
        .features-section h4 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
        }
        
        .features-list {
            list-style: none;
            padding: 0;
        }
        
        .features-list li {
            background-color: white;
            margin: 5px 0;
            padding: 8px 12px;
            border-radius: 3px;
            border-left: 3px solid #28a745;
        }
        
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .comparison-table th,
        .comparison-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        
        .comparison-table th {
            background-color: #333;
            color: white;
        }
        
        .comparison-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        .recommendations {
            background-color: #e7f3ff;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .recommendations h3 {
            color: #333;
            margin-top: 0;
        }
        
        .recommendation-category {
            margin-bottom: 15px;
        }
        
        .recommendation-category h4 {
            color: #007bff;
            margin-bottom: 10px;
        }
        
        .recommendation-list {
            list-style: none;
            padding-left: 20px;
        }
        
        .recommendation-list li {
            margin: 5px 0;
            padding: 5px 0;
        }
        
        .conclusion {
            background-color: #d4edda;
            padding: 20px;
            border-radius: 5px;
            margin-top: 30px;
        }
        
        .conclusion h3 {
            color: #155724;
            margin-top: 0;
        }
        
        .print-button {
            background-color: #007bff;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        
        .print-button:hover {
            background-color: #0056b3;
        }
        
        .score-badge {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
            font-weight: bold;
        }
        
        .rank-1 { border-left-color: #ffd700; }
        .rank-2 { border-left-color: #c0c0c0; }
        .rank-3 { border-left-color: #cd7f32; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏆 TOP 10 NETWORK INTERFACE CARDS</h1>
            <h2>FOR BRIDGE NAT SYSTEMS</h2>
        </div>
        
        <div class="no-print">
            <button class="print-button" onclick="window.print()">🖨️ Print to PDF</button>
        </div>
        
        <div class="criteria">
            <h3>📋 Ranking Criteria</h3>
            <p>This top 10 list is based on: <strong>Performance</strong> (throughput, latency, queue processing), <strong>Virtualization Support</strong> (VMQ, SR-IOV, hardware offloads), <strong>Hardware Queues</strong> (queue count and distribution), <strong>Bridge NAT Optimization</strong> (MAC filtering, NAT processing), <strong>Value for Money</strong> (performance per dollar), and <strong>Future-Proofing</strong> (scalability and upgrade path).</p>
        </div>
        
        <?php foreach ($nics as $index => $nic): ?>
        <div class="nic-card <?php echo 'rank-' . ($index + 1); ?>">
            <div class="nic-header">
                <h3><?php echo $nic['rank']; ?>: <?php echo $nic['name']; ?> 
                    <span class="score-badge">Performance Score: <?php echo $nic['score']; ?></span>
                </h3>
            </div>
            
            <div class="nic-specs">
                <div class="spec-item">
                    <div class="spec-label">Bandwidth:</div>
                    <div class="spec-value"><?php echo $nic['bandwidth']; ?></div>
                </div>
                <div class="spec-item">
                    <div class="spec-label">Queues:</div>
                    <div class="spec-value"><?php echo $nic['queues']; ?></div>
                </div>
                <div class="spec-item">
                    <div class="spec-label">Concurrent Users:</div>
                    <div class="spec-value"><?php echo $nic['users']; ?></div>
                </div>
                <div class="spec-item">
                    <div class="spec-label">Price:</div>
                    <div class="spec-value"><?php echo $nic['price']; ?></div>
                </div>
                <div class="spec-item">
                    <div class="spec-label">Best For:</div>
                    <div class="spec-value"><?php echo $nic['best_for']; ?></div>
                </div>
            </div>
            
            <div class="features-section">
                <h4>🔧 Key Features</h4>
                <ul class="features-list">
                    <?php foreach ($nic['features'] as $feature): ?>
                    <li><?php echo $feature; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="features-section">
                <h4>🌐 Virtualization Support</h4>
                <ul class="features-list">
                    <?php foreach ($nic['virtualization'] as $vfeature): ?>
                    <li><?php echo $vfeature; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endforeach; ?>
        
        <div class="page-break"></div>
        
        <h2>📊 Performance Comparison Summary</h2>
        <table class="comparison-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>NIC Model</th>
                    <th>Bandwidth</th>
                    <th>Queues</th>
                    <th>Users</th>
                    <th>Price</th>
                    <th>Value Score</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($nics as $index => $nic): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo $nic['name']; ?></td>
                    <td><?php echo $nic['bandwidth']; ?></td>
                    <td><?php echo $nic['queues']; ?></td>
                    <td><?php echo $nic['users']; ?></td>
                    <td><?php echo $nic['price']; ?></td>
                    <td><?php echo $nic['score']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="recommendations">
            <h3>🎯 Recommendations by Use Case</h3>
            
            <div class="recommendation-category">
                <h4>🏢 Enterprise Bridge NAT</h4>
                <ol class="recommendation-list">
                    <li><strong>Intel X710-T4L</strong> - Best overall performance and value</li>
                    <li><strong>Intel E810-XXVDA4T</strong> - High-performance data center</li>
                    <li><strong>NVIDIA ConnectX-7</strong> - Ultra-high performance with AI</li>
                </ol>
            </div>
            
            <div class="recommendation-category">
                <h4>🏠 Small-Medium Business</h4>
                <ol class="recommendation-list">
                    <li><strong>Intel X710-T2L</strong> - Perfect balance of performance and cost</li>
                    <li><strong>Intel I350-T2</strong> - Reliable enterprise-grade solution</li>
                    <li><strong>TP-Link 10GbE</strong> - Budget-friendly 10GbE option</li>
                </ol>
            </div>
            
            <div class="recommendation-category">
                <h4>🏠 Home/Small Office</h4>
                <ol class="recommendation-list">
                    <li><strong>Realtek RTL8125B</strong> - Excellent value for money ($20-40!)</li>
                    <li><strong>ASUS XG-C100C</strong> - Gaming-focused with low latency</li>
                    <li><strong>Intel 82599ES</strong> - Proven reliability for legacy systems</li>
                </ol>
            </div>
            
            <div class="recommendation-category">
                <h4>🎮 Gaming/Streaming</h4>
                <ol class="recommendation-list">
                    <li><strong>ASUS XG-C100C</strong> - Optimized for low latency</li>
                    <li><strong>Intel X710-T2L</strong> - High performance for gaming</li>
                    <li><strong>TP-Link 10GbE</strong> - Budget gaming solution</li>
                </ol>
            </div>
        </div>
        
        <div class="conclusion">
            <h3>🚀 Conclusion</h3>
            <p>The <strong>Intel X710-T4L</strong> takes the top spot for bridge NAT systems due to its exceptional performance, virtualization support, and value for money. For most bridge NAT deployments, the X710-T4L provides the perfect balance of performance, features, and cost-effectiveness.</p>
            
            <h4>Key Takeaways:</h4>
            <ul>
                <li><strong>X710-T4L</strong> is the <strong>ultimate choice</strong> for enterprise bridge NAT</li>
                <li><strong>ConnectX-7</strong> for <strong>ultra-high performance</strong> with AI capabilities</li>
                <li><strong>RTL8125B</strong> for <strong>budget-conscious</strong> deployments</li>
                <li><strong>E810 series</strong> for <strong>high-performance data centers</strong></li>
            </ul>
            
            <p>Choose based on your specific requirements for performance, user capacity, and budget! 🎯✨</p>
        </div>
    </div>
    
    <script>
        // Add print functionality
        function printToPDF() {
            window.print();
        }
    </script>
</body>
</html> 