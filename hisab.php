<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hisab Page</title>
    <!-- <style>
        iframe {
            width: 100%;
            height: 500px;
            border: none;
        }
    </style> -->
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>
<body>
<?php session_start(); ?>
    <h3><a href="hisablist.php?login=<?=$_GET['ledger_id']?>&user_type=ledger">Go back</a></h3>
    <iframe id="dynamicIframe" src=""></iframe>

    <script>
        // Function to get URL parameters
        function getUrlParameter(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }

        // Get the parameters
        const param1 = getUrlParameter('ledger_id');
        const param2 = getUrlParameter('date');
        const param3 = getUrlParameter('updated_by');
        // Construct the dynamic URL for the iframe
        const baseIframeUrl = 'https://new.555xch.pro/ledger_till_date_reports_app'; // Replace with your base URL
        const iframeUrl = `${baseIframeUrl}?ledger_id=${param1}&date=${param2}&master=${param3}`;

        // Set the iframe source when the page loads
        window.onload = function() {
            const iframe = document.getElementById('dynamicIframe');
            iframe.src = iframeUrl;
        };
    </script>
<?php //print_r($_SESSION); ?>
</body>
</html>
