<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Currency Converter</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="converter-card">
            <h1>💱 Currency Converter</h1>
            <p class="subtitle">Get live exchange rates instantly</p>
            
            <?php
            // Initialize variables
            $amount = '';
            $from_currency = 'USD';
            $to_currency = 'INR';
            $conversion_fee = 0;
            $result = '';
            $error = '';
            $last_updated = '';
            $user_detected_currency = 'INR';
            
            // Auto-detect user's currency based on location
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $location_response = @file_get_contents('https://ipapi.co/json/');
                if ($location_response !== false) {
                    $location_data = json_decode($location_response, true);
                    if (isset($location_data['currency'])) {
                        $user_detected_currency = $location_data['currency'];
                        $from_currency = $user_detected_currency;
                    }
                }
            }
            
            // Process form submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
                $from_currency = isset($_POST['from_currency']) ? $_POST['from_currency'] : 'USD';
                $to_currency = isset($_POST['to_currency']) ? $_POST['to_currency'] : 'INR';
                $conversion_fee = isset($_POST['conversion_fee']) ? floatval($_POST['conversion_fee']) : 0;
                
                if ($amount <= 0) {
                    $error = 'Please enter a valid amount greater than 0.';
                } elseif ($from_currency === $to_currency) {
                    $error = 'Please select different currencies for conversion.';
                } elseif ($conversion_fee < 0 || $conversion_fee > 100) {
                    $error = 'Conversion fee must be between 0 and 100%.';
                } else {
                    // API endpoint
                    $api_url = "https://api.exchangerate-api.com/v4/latest/{$from_currency}";
                    
                    // Fetch data from API
                    $response = @file_get_contents($api_url);
                    
                    if ($response === false) {
                        $error = 'Failed to fetch exchange rates. Please check your internet connection.';
                    } else {
                        $data = json_decode($response, true);
                        
                        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['rates'])) {
                            $error = 'Invalid response from API. Please try again later.';
                        } else {
                            // Get exchange rate
                            if (isset($data['rates'][$to_currency])) {
                                $rate = $data['rates'][$to_currency];
                                $converted_amount = $amount * $rate;
                                
                                // Apply conversion fee
                                $fee_amount = $converted_amount * ($conversion_fee / 100);
                                $final_amount = $converted_amount - $fee_amount;
                                $effective_rate = $final_amount / $amount;
                                
                                // Format result
                                $result = number_format($amount, 2) . ' ' . $from_currency . ' = ' . 
                                         number_format($converted_amount, 2) . ' ' . $to_currency;
                                
                                // Get last updated timestamp
                                if (isset($data['date'])) {
                                    $last_updated = 'Last updated: ' . date('F j, Y', strtotime($data['date']));
                                }
                                
                                // Show exchange rate
                                $rate_display = 'Exchange Rate: 1 ' . $from_currency . ' = ' . 
                                               number_format($rate, 4) . ' ' . $to_currency;
                                
                            } else {
                                $error = 'Currency code not found in API response.';
                            }
                        }
                    }
                }
            }
            ?>
            
            <form method="POST" action="" class="converter-form" id="converterForm">
                <div class="form-group">
                    <label for="amount">Amount</label>
                    <input 
                        type="number" 
                        id="amount" 
                        name="amount" 
                        step="0.01" 
                        min="0.01" 
                        value="<?php echo htmlspecialchars($amount); ?>" 
                        placeholder="Enter amount"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="conversion_fee">Conversion Fee (%)</label>
                    <input 
                        type="number" 
                        id="conversion_fee" 
                        name="conversion_fee" 
                        step="0.01" 
                        min="0" 
                        max="100" 
                        value="<?php echo htmlspecialchars($conversion_fee); ?>" 
                        placeholder="Enter fee percentage (0-100)"
                    >
                </div>
                
                <div class="form-group">
                    <label for="from_currency">From Currency</label>
                    <select id="from_currency" name="from_currency" required>
                        <option value="USD" <?php echo $from_currency === 'USD' ? 'selected' : ''; ?>>USD - US Dollar</option>
                        <option value="EUR" <?php echo $from_currency === 'EUR' ? 'selected' : ''; ?>>EUR - Euro</option>
                        <option value="INR" <?php echo $from_currency === 'INR' ? 'selected' : ''; ?>>INR - Indian Rupee</option>
                        <option value="GBP" <?php echo $from_currency === 'GBP' ? 'selected' : ''; ?>>GBP - British Pound</option>
                        <option value="JPY" <?php echo $from_currency === 'JPY' ? 'selected' : ''; ?>>JPY - Japanese Yen</option>
                        <option value="AUD" <?php echo $from_currency === 'AUD' ? 'selected' : ''; ?>>AUD - Australian Dollar</option>
                        <option value="CAD" <?php echo $from_currency === 'CAD' ? 'selected' : ''; ?>>CAD - Canadian Dollar</option>
                        <option value="CHF" <?php echo $from_currency === 'CHF' ? 'selected' : ''; ?>>CHF - Swiss Franc</option>
                        <option value="CNY" <?php echo $from_currency === 'CNY' ? 'selected' : ''; ?>>CNY - Chinese Yuan</option>
                        <option value="SGD" <?php echo $from_currency === 'SGD' ? 'selected' : ''; ?>>SGD - Singapore Dollar</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="to_currency">To Currency</label>
                    <select id="to_currency" name="to_currency" required>
                        <option value="USD" <?php echo $to_currency === 'USD' ? 'selected' : ''; ?>>USD - US Dollar</option>
                        <option value="EUR" <?php echo $to_currency === 'EUR' ? 'selected' : ''; ?>>EUR - Euro</option>
                        <option value="INR" <?php echo $to_currency === 'INR' ? 'selected' : ''; ?>>INR - Indian Rupee</option>
                        <option value="GBP" <?php echo $to_currency === 'GBP' ? 'selected' : ''; ?>>GBP - British Pound</option>
                        <option value="JPY" <?php echo $to_currency === 'JPY' ? 'selected' : ''; ?>>JPY - Japanese Yen</option>
                        <option value="AUD" <?php echo $to_currency === 'AUD' ? 'selected' : ''; ?>>AUD - Australian Dollar</option>
                        <option value="CAD" <?php echo $to_currency === 'CAD' ? 'selected' : ''; ?>>CAD - Canadian Dollar</option>
                        <option value="CHF" <?php echo $to_currency === 'CHF' ? 'selected' : ''; ?>>CHF - Swiss Franc</option>
                        <option value="CNY" <?php echo $to_currency === 'CNY' ? 'selected' : ''; ?>>CNY - Chinese Yuan</option>
                        <option value="SGD" <?php echo $to_currency === 'SGD' ? 'selected' : ''; ?>>SGD - Singapore Dollar</option>
                    </select>
                </div>
                
                <button type="submit" class="convert-btn">Convert</button>
            </form>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($result): ?>
                <div class="result-box">
                    <div class="result-amount"><?php echo htmlspecialchars($result); ?></div>
                    <?php if (isset($rate_display)): ?>
                        <div class="rate-info"><?php echo htmlspecialchars($rate_display); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($conversion_fee > 0 && isset($final_amount)): ?>
                        <div class="fee-details">
                            <div class="fee-item">
                                <span>Base Converted:</span>
                                <span><?php echo number_format($converted_amount, 2) . ' ' . $to_currency; ?></span>
                            </div>
                            <div class="fee-item">
                                <span>Fee (<?php echo $conversion_fee; ?>%):</span>
                                <span>-<?php echo number_format($fee_amount, 2) . ' ' . $to_currency; ?></span>
                            </div>
                            <div class="fee-item final">
                                <span>Final Amount:</span>
                                <span><?php echo number_format($final_amount, 2) . ' ' . $to_currency; ?></span>
                            </div>
                            <div class="fee-item">
                                <span>Effective Rate:</span>
                                <span>1 <?php echo $from_currency; ?> = <?php echo number_format($effective_rate, 4) . ' ' . $to_currency; ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($last_updated): ?>
                        <div class="last-updated"><?php echo htmlspecialchars($last_updated); ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
</body>
</html>