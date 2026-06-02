<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SignalWire SIP Endpoint Error</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #dc3545;
            color: white;
            padding: 20px;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-top: none;
        }
        .error-section {
            background-color: white;
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #dc3545;
        }
        .label {
            font-weight: bold;
            color: #495057;
        }
        .value {
            margin-left: 10px;
            font-family: 'Courier New', monospace;
            background-color: #e9ecef;
            padding: 5px 10px;
            border-radius: 3px;
            display: inline-block;
        }
        pre {
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚨 SignalWire SIP Endpoint Error</h1>
        <p>An error occurred while managing a SIP endpoint on SignalWire platform</p>
    </div>

    <div class="content">
        <div class="error-section">
            <h3>Operation Details</h3>
            <p>
                <span class="label">Operation:</span>
                <span class="value">{{ strtoupper($error['operation'] ?? 'unknown') }}</span>
            </p>
            <p>
                <span class="label">Timestamp:</span>
                <span class="value">{{ now()->toDateTimeString() }}</span>
            </p>
        </div>

        <div class="error-section">
            <h3>User Information</h3>
            <p>
                <span class="label">User ID:</span>
                <span class="value">{{ $error['user_id'] ?? 'N/A' }}</span>
            </p>
            <p>
                <span class="label">User Email:</span>
                <span class="value">{{ $error['user_email'] ?? 'N/A' }}</span>
            </p>
        </div>

        <div class="error-section">
            <h3>Error Details</h3>
            @if(isset($error['status_code']))
            <p>
                <span class="label">HTTP Status:</span>
                <span class="value">{{ $error['status_code'] }}</span>
            </p>
            @endif

            @if(isset($error['error_type']))
            <p>
                <span class="label">Error Type:</span>
                <span class="value">{{ $error['error_type'] }}</span>
            </p>
            @endif

            <p>
                <span class="label">Error Message:</span>
            </p>
            <pre>{{ $error['error_message'] ?? 'No error message available' }}</pre>
        </div>

        @if(isset($error['api_response']) && $error['api_response'])
        <div class="error-section">
            <h3>SignalWire API Response</h3>
            <pre>{{ json_encode($error['api_response'], JSON_PRETTY_PRINT) }}</pre>
        </div>
        @endif

        @if(isset($error['trace']))
        <div class="error-section">
            <h3>Stack Trace</h3>
            <pre>{{ $error['trace'] }}</pre>
        </div>
        @endif

        <div class="error-section">
            <h3>Action Required</h3>
            <p>
                This error prevents the ManDown mobile application from working correctly for this user.
                Please investigate and resolve as soon as possible.
            </p>
            <p>
                <strong>Possible causes:</strong>
            </p>
            <ul>
                <li>SignalWire API credentials are invalid or expired</li>
                <li>SignalWire service is temporarily unavailable</li>
                <li>Network connectivity issues</li>
                <li>User data inconsistency (missing email, etc.)</li>
                <li>SIP endpoint limit reached on SignalWire account</li>
            </ul>
        </div>

        <div class="error-section">
            <h3>Next Steps</h3>
            <ol>
                <li>Check application logs for more details: <code>storage/logs/laravel.log</code></li>
                <li>Verify SignalWire credentials in <code>.env</code> file</li>
                <li>Test SignalWire API manually using route: <code>/test_signalwire_create_sip_relay</code></li>
                <li>Contact SignalWire support if issue persists</li>
            </ol>
        </div>
    </div>
</body>
</html>
