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
<?php session_start(); 
    // here is my sample url : statement.php/155?start_date=2025-08-01&end_date=2025-11-14  and leger id /155
    //$ledger_id = basename($_SERVER['REQUEST_URI']);
    $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        
preg_match('/statement\.php\/(\d+)/', $url, $match);
//echo '<pre>'; print_r($match); echo '</pre>';
$ledger_id = $match[1];
    ?>
<h3><a href="/appdemo/Entry-page.php?login=<?= $ledger_id ?>&user_type=ledger">Go back</a></h3>
    
    <iframe id="dynamicIframe" src=""></iframe>

    <script>
        // Function to get URL parameters
        function getUrlParameter(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }

        // Get the parameters
        // here is my sample url : statement.php/155?start_date=2025-08-01&end_date=2025-11-14 
        const param1 = window.location.pathname.split('/').pop(); // Get the last part of the path as ledger_id
        const param2 = getUrlParameter('start_date');
        const param3 = getUrlParameter('end_date');
        // Construct the dynamic URL for the iframe
        const baseIframeUrl = 'https://new.555xch.pro/app_statement'; // Replace with your base URL
       // const iframeUrl = `${baseIframeUrl}?ledger_id=${param1}&date=${param2}&master=${param3}`;
        
        const iframeUrl = `${baseIframeUrl}/${param1}?start_date=${param2}&end_date=${param3}`; 

        // Set the iframe source when the page loads
        window.onload = function() {
            const iframe = document.getElementById('dynamicIframe');
            iframe.src = iframeUrl;
        };
    </script>
<?php //print_r($_SESSION); ?>
</body>
</html>
